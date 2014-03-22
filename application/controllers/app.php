<?php

class App extends CI_Controller
{

    /**
     * This controller returns all the groups for the supplied userID
     */
    public function getGroups()
    {
        session_start();

        $this->load->model('authentication_model');

        if ($this->authentication_model->isAuthenticated()) // check if current user is authenticated
        {
            $userId = $this->input->get('user_id');
            $this->load->model('app_model');
            $response = $this->app_model->getGroups($userId);

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        } else {
            echo 'Access Denied';
        }

    }

    //This controller adds a new group and returns a string if successful

    public function addGroup()
    {
        session_start();

        $this->load->model('authentication_model');
        if ($this->authentication_model->isAuthenticated()) // check if current user is authenticated
        {
            $groupName = $this->input->get('group_name');
            $groupDescription = $this->input->get('group_description');

            $this->load->model('app_model');
            $this->app_model->insertGroup($groupName, $groupDescription);
            echo 'Group Added';

        } else {
            echo 'Access Denied';
        }

    }

    /**
     * This controller adds a new event and returns a string if successful
     */

    public function addEvent()
    {
        session_start();

        $this->load->model('authentication_model');

        if ($this->authentication_model->isAuthenticated()) // check if current user is authenticated
        {
            $eventName = $this->input->get('event_name');
            $eventDescription = $this->input->get('event_description');
            $eventDate = $this->input->get('event_date');  //date should be in following format: 2013-08-05 18:19:03'
            $groupId = $this->input->get('group_id');
            $roleName = array($this->input->get('role_name'));  // this needs to be parsed into an array

            if($this->authentication_model->isGroupMember($groupId)) //check if user has permission to add
            {                                                                  //event
                $this->load->model('app_model');
                $this->app_model->insertEvent(
                    $eventName, $eventDescription, $eventDate, $groupId, $roleName);

                echo 'Group Added';
            }else{
                echo 'Access Denied';
            }

        } else {
            echo 'Access Denied';
        }
    }

    /*
     * This controller returns a json encoded array or users based on the supplied search string
     */
    public function getUsers()
    {
        session_start();

        $searchString = $this->input->get('search_string');
        $this->load->model('authentication_model');


        if ($this->authentication_model->isAuthenticated()) // check if current user is authenticated
        {
            $this->load->model('app_model');
            $response = $this->app_model->getUsers($searchString);
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        } else {
            echo 'Access Denied';
        }


    }


    public function inviteUser()
    {
        $this->input->get('group_id');
        $this->input->get('user_id');
    }
}



