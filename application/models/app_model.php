<?php

class App_model extends CI_Model
{
    /**
     * This function check if supplied user is a member of any groups and if so
     * returns them in the response and if not returns a string "no groups
     */

    public function getGroups($id)
    {
        $this->db->select('groups.GroupID,GroupName,groupDescription');
        $this->db->from('groupusers');
        $this->db->join('groups', 'groupusers.GroupID=groups.GroupID');
        $this->db->where('groupusers.UserID', $id);

        $query = $this->db->get();
        $response = array();
        $i = 0;

        if ($query->num_rows() > 0) // If user is a member of any group then add to response
        {

            foreach ($query->result() as $row) {
                $response[$i] = array(
                    'groupId' => $row->GroupID,
                    'groupName' => $row->GroupName,
                    'groupDescription' => $row->groupDescription);
                $i++;
            }
        } else { // else return no notices
            $response = "no groups";
        }

        return $response;
    }

    public function insertGroup($groupName, $groupDescription)
    {
        $groupData = array('GroupName' => $groupName, 'GroupDescription' => $groupDescription);
        $this->db->insert('groups', $groupData);
        $groupId = $this->db->insert_id();

        $groupUsersData = array('UserID' => $_SESSION['userId'], 'GroupID' => $groupId);
        $this->db->insert('groupusers', $groupUsersData);

    }


    public function insertEvent($roleName, $eventName, $eventDescription, $eventDate, $groupId)
    {

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

        $query = $this->db->get(); //return all user that firstname, surname or username partially match supplied
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

