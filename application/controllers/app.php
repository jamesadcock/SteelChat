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
            $userId = $this->input->get('userId');
            $this->load->model('app_model');
            $response = $this->app_model->getGroups($userId);

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        } else {
            echo 'Access Denied';
        }

    }

    public function addGroup()
    {
        session_start();

        $this->load->model('authentication_model');
        if ($this->authentication_model->isAuthenticated()) // check if current user is authenticated
        {
            $groupName = $this->input->get('groupName');
            $groupDescription = $this->input->get('groupDescription');

            $this->load->model('app_model');
            $this->app_model->insertGroup($groupName, $groupDescription);
            echo 'Group Added';

        }
        else {
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

            $roleName = $this->inpunt->get('roleName');
            $eventName = $this->input->get('eventName');
            $eventDescription = $this->input->get('eventDescription');
            $eventDate = $this->input->get('eventDate');
            $groupId = $this->input->get('groupId');

            $this->load->model('app_model');
            $response = $this->app_model->insertEvent(
                $roleName, $eventName, $eventDescription, $eventDescription, $eventDate, $groupId);

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        } else {
            echo 'Access Denied';
        }


    }
}



