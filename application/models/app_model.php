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
        //encrypt group data
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

        //encrypt event data
        $this->load->model('authentication_model');
        $eventName = $this->authentication_model->encrypt($eventName);
        $eventDescription = $this->authentication_model->encrypt($eventDescription);
        $eventDate = $this->authentication_model->encrypt($eventDate);

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
 * This function inserts a new notice and defines which users can see the event by inserting
 * records into the event_role table
 */

    public function insertNotice($noticeName, $noticeDescription, $groupId, $roleNames)
    {

        //encrypt event data
        $this->load->model('authentication_model');
        $noticeName = $this->authentication_model->encrypt($noticeName);
        $noticeDescription = $this->authentication_model->encrypt($noticeDescription);

        //insert record into the events table
        $noticeData = array(
            'name' => $noticeName, 'description' => $noticeDescription,
             'group_id' => $groupId);

        $this->db->insert('notice', $noticeData);
        $noticeId = $this->db->insert_id();

        //find the role ids using the provided role names and insert records into the notice_role table
        foreach($roleNames as $roleName)
        {
            $query = $this->db->get_where('role', array('name' => $roleName, 'group_id' => $groupId ));

            if ($query->num_rows() > 0)
            {
                $row = $query->row();
                $noticeRoleData = array(
                    'notice_id' => $noticeId, 'role_id' => $row->id);
                $this->db->insert('notice_role', $noticeRoleData);
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

            // decrypt data
            $firstName = $this->authentication_model->decrypt( $row->first_name);
            $lastName = $this->authentication_model->decrypt( $row->last_name);
            $username = $this->authentication_model->decrypt( $row->username);

            $response[$i] = array(
                'firstName' => $firstName,
                'lastName' => $lastName,
                'username' => $username,
                'id' => $row->id
            );
            $i++;
        }

        return $response;
    }


    /*
     * This function gets all the events data that the authenticated user is authorised to view
     */
    public function getEvents($groupId)
    {
        $this->load->model('authentication_model');

        // get the current users role for the supplied group id
        $roleId = $this->authentication_model->getUserRole($groupId);  //returns false if no role id

        if($roleId)  //user has a role for group
        {

            // run database query
            $this->db->select('event.id,event.name,event.description, event.date');
            $this->db->from('event_role');
            $this->db->join('event', 'event_role.event_id = event.id', 'inner');
            $this->db->where('event_role.role_id',$roleId);
            $this->db->where('event.group_id',$groupId );

            $query = $this->db->get();

            $response = array();
            $i = 0;


            // add the events to an array
            foreach ($query->result() as $row)
            {
                // decrypt data
                $name = $this->authentication_model->decrypt( $row->name);
                $description = $this->authentication_model->decrypt( $row->description);
                $date = $this->authentication_model->decrypt( $row->date);

                $response[$i] = array(
                    'id' => $row->id,
                    'name' => $name,
                    'description' => $description,
                    'date' => $date
                );
                $i++;
            }

            return $response;
        }else{
            $response = 'Access Denied';
            return $response;
        }

    }



    /*
    * This function gets all the notices data that the authenticated user is authorised to view
    */
    public function getNotices($groupId)
    {
        $this->load->model('authentication_model');

        // get the current users role for the supplied group id
        $roleId = $this->authentication_model->getUserRole($groupId);  //returns false if no role id

        if($roleId)  //user has a role for group
        {

            // run database query
            $this->db->select('notice.id,notice.name,notice.description, notice.created');
            $this->db->from('notice_role');
            $this->db->join('notice', 'notice_role.notice_id = notice.id', 'inner');
            $this->db->where('notice_role.role_id',$roleId);
            $this->db->where('notice.group_id',$groupId );

            $query = $this->db->get();

            $response = array();
            $i = 0;


            // add the events to an array
            foreach ($query->result() as $row)
            {
                // decrypt data
                $name = $this->authentication_model->decrypt( $row->name);
                $description = $this->authentication_model->decrypt( $row->description);
                $created = $this->authentication_model->decrypt( $row->created);

                $response[$i] = array(
                    'id' => $row->id,
                    'name' => $name,
                    'description' => $description,
                    'date' => $created
                );
                $i++;
            }

            return $response;
        }else{
            $response = 'Access Denied';
            return $response;
        }

    }

}

