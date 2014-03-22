<?php

class App_model extends CI_Model
{
    /**
     * This function check if supplied user is a member of any groups and if so
     * returns them in the response and if not returns a string "no groups
     */
    public function getGroups($id)
    {
        $this->load->model('authentication_model');

        $this->db->select('group.id,name,description');
        $this->db->from('user_group');
        $this->db->join('group', 'user_group.group_id=group.id');
        $this->db->where('user_group.user_id', $id);

        $query = $this->db->get();
        $response = array();
        $i = 0;

        if ($query->num_rows() > 0) // If user is a member of any group then add to response
        {

           //get and decrypt group data
            foreach ($query->result() as $row) {

                $response[$i] = array(
                    'groupId' => $row->id,
                    'groupName' => $this->authentication_model->decrypt( $row->name),
                    'groupDescription' => $this->authentication_model->decrypt($row->description));

                $i++;
            }
        } else { // else return no notices
            $response = "no groups";
        }

        return $response;
    }



   /*
    * This function creates a group.  It first inserts data into the group table. It then creates the default
    * role for the group which is "creator" in the role table.  It then inserts a record into the user_group
    * table with the id of the newly created group, the id of the default role and the user_id of the
    * authenticated user.
    */
    public function insertGroup($groupName, $groupDescription)
    {
        //encrypt users data
        $this->load->model('authentication_model');
        $groupName = $this->authentication_model->encrypt($groupName);
        $groupDescription = $this->authentication_model->encrypt($groupDescription);

        //insert data in to group table
        $groupData = array('name' => $groupName, 'description' => $groupDescription);
        $this->db->insert('group', $groupData);
        $groupId = $this->db->insert_id();

        //insert data in to role table
        $roleData = array('name' => 'creator', 'is_admin' => 1, 'group_id' => $groupId);
        $this->db->insert('role', $roleData);
        $roleId = $this->db->insert_id();

        //insert data in to user_group table
        $groupUsersData = array('user_id' => $_SESSION['userId'], 'group_id' => $groupId, 'role_id' => $roleId);
        $this->db->insert('user_group', $groupUsersData);

    }

    /*
     * This function inserts a new event and defines which users can see the event by inserting
     * records into the event_role table
     */

    public function insertEvent($eventName, $eventDescription, $eventDate, $groupId, $roleNames)
    {

        //insert record into the events table
        $eventData = array(
            'name' => $eventName, 'description' => $eventDescription,
            'date' => $eventDate, 'group_id' => $groupId);

        $this->db->insert('event', $eventData);
        $eventId = $this->db->insert_id();

        //find the role ids using the provided role names and insert records into the event_role table
        foreach($roleNames as $roleName)
        {
            $query = $this->db->get_where('role', array('name' => $roleName, 'group_id' => $groupId ));

            if ($query->num_rows() > 0)
            {
                $row = $query->row();
                $eventRoleData = array(
                    'event_id' => $eventId, 'role_id' => $row->id);
                $this->db->insert('event_role', $eventRoleData);
            }
            else{
                echo 'No data returned';
            }

        }
    }

    /*
     * This function gets all the users from that match the provided search string and return them
     * in an array
     */
    public function getUsers($searchString)
    {

        $this->db->select('FirstName,Surname,Username,UserID')
            ->from('users')
            ->like('Username', $searchString)
            ->or_like('FirstName', $searchString)
            ->or_like('Surname', $searchString);

        $query = $this->db->get(); //return all user that first name, last name or username partially match supplied
                                   //search string
        $response = array();
        $i = 0;

        foreach ($query->result() as $row) {
            $response[$i] = array(
                'firstName' => $row->FirstName,
                'surname' => $row->Surname,
                'username' => $row->Username,
                'id' => $row->UserID
            );
            $i++;
        }

        return $response;
    }

}

