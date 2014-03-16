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


    public function insertEvent($roleName, $eventName, $eventDescription, $eventDate, $groupId)
    {

    }

}

