<?php

class App_model extends CI_Model
{
    /**
     * This method check if supplied user is a member of any groups and if so
     * returns them in the response and if not returns a string "no groups
     */
    public function getGroups()
    {
        $id = $_SESSION['userId'];
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
                    'groupName' => $this->authentication_model->decrypt($row->name),
                    'groupDescription' => $this->authentication_model->decrypt($row->description));

                $i++;
            }
        } else { // else return no notices
            $response = "no groups";
        }

        return $response;
    }


    /*
     * This method creates a group.  It first inserts data into the group table. It then creates the default
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

        //insert default creator role into DB
        $creatorRoleData = array('name' => 'creator', 'is_admin' => 1, 'group_id' => $groupId);
        $this->db->insert('role', $creatorRoleData);
        $roleId = $this->db->insert_id();

        //insert default member role into DB
        $memberRoleData = array('name' => 'member', 'is_admin' => 0, 'group_id' => $groupId);
        $this->db->insert('role', $memberRoleData);

        //insert data in to user_group table
        $groupUsersData = array('user_id' => $_SESSION['userId'], 'group_id' => $groupId, 'role_id' => $roleId);
        $this->db->insert('user_group', $groupUsersData);

    }

    /*
     * This method inserts a new event and defines which users can see the event by inserting
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
            'date' => $eventDate, 'group_id' => $groupId,
            'user_id' => $_SESSION['userId']);

        $this->db->insert('event', $eventData);
        $eventId = $this->db->insert_id();

        //find the role ids using the provided role names and insert records into the event_role table
        foreach ($roleNames as $roleName) {
            $query = $this->db->get_where('role', array('name' => $roleName, 'group_id' => $groupId));

            if ($query->num_rows() > 0) {
                $row = $query->row();
                $eventRoleData = array(
                    'event_id' => $eventId, 'role_id' => $row->id);
                $this->db->insert('event_role', $eventRoleData);
            } else {
                echo 'No data returned';
            }

        }
    }


    /*
 * This method inserts a new notice and defines which users can see the event by inserting
 * records into the event_role table
 */

    public function insertNotice($noticeName, $noticeDescription, $groupId, $roleNames)
    {

        //encrypt event data
        $this->load->model('authentication_model');
        $noticeName = $this->authentication_model->encrypt($noticeName);
        $noticeDescription = $this->authentication_model->encrypt($noticeDescription);

        //insert record into the notice table
        $noticeData = array(
            'name' => $noticeName, 'description' => $noticeDescription,
            'group_id' => $groupId,
            'user_id' => $_SESSION['userId']);

        $this->db->insert('notice', $noticeData);
        $noticeId = $this->db->insert_id();

        //find the role ids using the provided role names and insert records into the notice_role table
        foreach ($roleNames as $roleName) {
            $query = $this->db->get_where('role', array('name' => $roleName, 'group_id' => $groupId));

            if ($query->num_rows() > 0) {
                $row = $query->row();
                $noticeRoleData = array(
                    'notice_id' => $noticeId, 'role_id' => $row->id);
                $this->db->insert('notice_role', $noticeRoleData);
            } else {
                echo 'No data returned';
            }

        }
    }


    /*
     * This method gets all the users from that match the provided search string and return them
     * in an array
     */
    public function getUsers($searchString)
    {

        $searchString = $this->authentication_model->encrypt($searchString);

        $this->db->select('first_name,last_name,username,id')
            ->from('user')
            ->like('username', $searchString)
            ->or_like('first_name', $searchString)
            ->or_like('last_name', $searchString);

        $query = $this->db->get(); //return all user that first name, last name or username partially match supplied
        //search string
        $response = array();
        $i = 0;

        foreach ($query->result() as $row) {

            // decrypt data
            $firstName = $this->authentication_model->decrypt($row->first_name);
            $lastName = $this->authentication_model->decrypt($row->last_name);
            $username = $this->authentication_model->decrypt($row->username);

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
    * This method gets all the users from that match the provided search string and return them
    * in an array
    */
    public function searchGroups($searchString)
    {
        $searchString = $this->authentication_model->encrypt($searchString);

        $this->db->select('id,name')
            ->from('group')
            ->like('name', $searchString);

        $query = $this->db->get(); //return all groups that partially match supplied search string.
        $response = array();
        $i = 0;

        foreach ($query->result() as $row) {

            // decrypt data
            $groupName = $this->authentication_model->decrypt($row->name);

            $response[$i] = array(
                'groupName' => $groupName,
                'groupId' => $row->id
            );
            $i++;
        }

        return $response;
    }


    /*
     * This method gets all the events data that the authenticated user is authorised to view
     */
    public function getEvents($groupId)
    {
        $this->load->model('authentication_model');

        // get the current users role for the supplied group id
        $roleId = $this->authentication_model->getUserRole($groupId); //returns false if no role id

        if ($roleId) { //user has a role for group

            // run database query
            $this->db->select('event.id,event.name,event.description, event.date, user.username');
            $this->db->from('event_role');
            $this->db->join('event', 'event_role.event_id = event.id', 'inner');
            $this->db->join('user', 'user.id = event.user_id', 'inner');
            $this->db->where('event_role.role_id', $roleId);
            $this->db->where('event.group_id', $groupId);

            $query = $this->db->get();

            $response = array();
            $i = 0;

            // add the events to an array
            foreach ($query->result() as $row) {
                // decrypt data
                $name = $this->authentication_model->decrypt($row->name);
                $description = $this->authentication_model->decrypt($row->description);
                $date = $this->authentication_model->decrypt($row->date);
                $username = $this->authentication_model->decrypt($row->username);

                $response[$i] = array(
                    'id' => $row->id,
                    'name' => $name,
                    'description' => $description,
                    'date' => $date,
                    'username' => $username
                );
                $i++;
            }

            return $response;
        } else {
            $response = 'Access Denied';
            return $response;
        }

    }


    /*
    * This method gets all the notices data that the authenticated user is authorised to view
    */
    public function getNotices($groupId)
    {
        $this->load->model('authentication_model');

        // get the current users role for the supplied group id
        $roleId = $this->authentication_model->getUserRole($groupId); //returns false if no role id

        if ($roleId) { //user has a role for group

            // run database query
            $this->db->select('notice.id,notice.name,notice.description, notice.created, user.username');
            $this->db->from('notice_role');
            $this->db->join('notice', 'notice_role.notice_id = notice.id', 'inner');
            $this->db->join('user', 'user.id = notice.user_id', 'inner');
            $this->db->where('notice_role.role_id', $roleId);
            $this->db->where('notice.group_id', $groupId);

            $query = $this->db->get();

            $response = array();
            $i = 0;


            // add the events to an array
            foreach ($query->result() as $row) {
                // decrypt data
                $name = $this->authentication_model->decrypt($row->name);
                $description = $this->authentication_model->decrypt($row->description);
                $created = $this->authentication_model->decrypt($row->created);
                $username = $this->authentication_model->decrypt($row->username);

                $response[$i] = array(
                    'id' => $row->id,
                    'name' => $name,
                    'description' => $description,
                    'date' => $created,
                    'username' => $username
                );
                $i++;
            }

            return $response;
        } else {
            $response = 'Access Denied';
            return $response;
        }

    }

    /*
     * This method inserts a row into the user_invite table, to indicate that the supplied user
     * id has been invited to join the group.
     */

    public function  insertInviteUser($groupId, $userId)
    {
        $userInviteData = array('user_id' => $userId, 'group_id' => $groupId);
        $this->db->insert('user_invite', $userInviteData);

    }


    /*
     * This method returns the group name and id for all groups that the authenticated user has been invited to
     */
    public function getInvites()
    {
        $this->db->select('group.id,group.name');
        $this->db->from('user_invite');
        $this->db->join('group', 'user_invite.group_id = group.id', 'inner');
        $this->db->where('user_invite.user_id', $_SESSION['userId']);

        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            // add the events to an array
            $i = 0;
            foreach ($query->result() as $row) {

                // decrypt data
                $name = $this->authentication_model->decrypt($row->name);

                $response[$i] = array(
                    'id' => $row->id,
                    'name' => $name,
                );
                $i++;
            }
        } else {
            $response = 'No invites';
        }

        return $response;

    }


    /*
     * This method accepts a group id as an argument and adds the authenticated user to the group with the default
     * role of member
     */
    public function joinGroup($groupId)
    {
        $userInviteData = array('user_id' => $_SESSION['userId'], 'group_id' => $groupId);
        $query = $this->db->get_where('user_invite', $userInviteData);

        if ($query->num_rows() > 0) {
            // get member role for this group
            $roleData = array('group_id' => $groupId, 'name' => 'member');
            $query = $this->db->get_where('role', $roleData);
            $row = $query->row();
            $roleId = $row->id;

            // insert record into user_group table
            $userGroupData = array('user_id' => $_SESSION['userId'], 'group_id' => $groupId, 'role_id' => $roleId);
            $this->db->insert('user_group', $userGroupData);

            //delete invite
            $this->deleteInvite($groupId);

            $response = 'user added to group';
        } else {
            $response = 'user has not been invited';
        }

        return $response;

    }


    /*
     * This method deletes an invite from the user_invite tale
     */
    public function deleteInvite($groupId)
    {
        $this->db->where('group_id', $groupId);
        $this->db->where('user_id', $_SESSION['userId']);
        $this->db->delete('user_invite');

        $response = 'invite deleted';
        return $response;
    }


    /*
     * This method deletes role  from the user_invite table
     */
    public function deleteRole($groupId, $roleId)
    {

        $query = $this->db->get_where('user_group', array('role_id' => $roleId));

        if ($query->num_rows() == 0) { //check if role is being used

            $this->db->where('group_id', $groupId);
            $this->db->where('id', $roleId);
            $this->db->delete('role');

            $response = 'role deleted';
            return $response;
        } else {


            $response = 'unable to delete role';
            return $response;

        }
    }


    public function getGroupRoles($groupId)
    {


        $query = $this->db->get_where('role', array('group_id' => $groupId));

        $response = array();
        $i = 0;

        if ($query->num_rows() > 0) // If user is a member of any group then add to response
        {

            //get and decrypt group data
            foreach ($query->result() as $row) {

                $response[$i] = array(
                    'roleId' => $row->id,
                    'roleName' => $row->name);

                $i++;
            }
        } else { // else return no notices
            $response = "no groups";
        }

        return $response;
    }


    /*
     * Insert a new role into the role table
     */
    public function insertRole($groupId, $roleName)
    {

        //insert default role into DB
        $roleData = array('name' => $roleName, 'is_admin' => 0, 'group_id' => $groupId);
        $this->db->insert('role', $roleData);

    }


    /*
     * Insert a new role into the join_request table
     */
    public function insertJoinRequest($groupId)
    {

        //insert default role into DB
        $joinRequestData = array('group_id' => $groupId, 'user_id' => $_SESSION['userId']);
        $this->db->insert('join_request', $joinRequestData);

    }


}

