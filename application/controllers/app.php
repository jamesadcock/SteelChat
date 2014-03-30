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
            $this->load->model('app_model');
            $response = $this->app_model->getGroups();

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
            $eventName = $this->input->post('event_name');
            $eventDescription = $this->input->post('event_description');
            $eventDate = $this->input->post('event_date');  //date should be in following format: 2013-08-05 18:19:03'
            $groupId = $this->input->post('group_id');
            $roleName = array($this->input->post('role_name'));  // this needs to be parsed into an array

            if($this->authentication_model->isGroupMember($groupId)) //check if user has permission to add
            {                                                                  //event
                $this->load->model('app_model');
                $this->app_model->insertEvent(
                    $eventName, $eventDescription, $eventDate, $groupId, $roleName);

                echo 'Event Added';
            }else{
                echo 'Access Denied';
            }

        } else {
            echo 'Access Denied';
        }
    }


    /**
     * This controller adds a new notice and returns a string if successful
     */

    public function addNotice()
    {
        session_start();

        $this->load->model('authentication_model');

        if ($this->authentication_model->isAuthenticated()) // check if current user is authenticated
        {
            $noticeName = $this->input->post('notice_name');
            $noticeDescription = $this->input->post('notice_description');
            $groupId = $this->input->post('group_id');
            $roleName = array($this->input->post('role_name'));  // this needs to be parsed into an array

            if($this->authentication_model->isGroupMember($groupId)) //check if user has permission to add
            {                                                                  //event
                $this->load->model('app_model');
                $this->app_model->insertNotice(
                    $noticeName, $noticeDescription, $groupId, $roleName);

                echo 'Notice Added';
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

        $this->load->model('authentication_model');


        if ($this->authentication_model->isAuthenticated()) // check if current user is authenticated
        {
            $searchString = $this->input->post('search_string');

            $this->load->model('app_model');
            $response = $this->app_model->getUsers($searchString);
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        } else {
            echo 'Access Denied';
        }


    }



    /*
   * This controller returns a json encoded array or users based on the supplied search string
   */
    public function searchGroups()
    {
        session_start();

        $this->load->model('authentication_model');


        if ($this->authentication_model->isAuthenticated()) // check if current user is authenticated
        {
            $searchString = $this->input->get('search_string');

            $this->load->model('app_model');
            $response = $this->app_model->searchGroups($searchString);
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        } else {
            echo 'Access Denied';
        }


    }


    /*
     * This controller gets all the events for the supplied group that the current user is authorised to
     * view
     */
    public function getEvents()
    {
        session_start();

        $this->load->model('authentication_model');

        if ($this->authentication_model->isAuthenticated()) // check if current user is authenticated
        {
            $groupId = $this->input->post('group_id');

            if($this->authentication_model->isGroupMember($groupId)) //check if user has permission to add
            {                                                        //event
                $this->load->model('app_model');
                $response = $this->app_model->getEvents($groupId);
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($response));

            }else
            {
                echo 'Access Denied';
            }

        } else {
            echo 'Access Denied';
        }
    }



    /*
     * This controller gets all the notices for the supplied group that the current user is authorised to
     * view
     */
    public function getNotices()
    {
        session_start();

        $this->load->model('authentication_model');

        if ($this->authentication_model->isAuthenticated()) // check if current user is authenticated
        {
            $groupId = $this->input->post('group_id');

            if($this->authentication_model->isGroupMember($groupId)) //check if user has permission to add
            {                                                        //event
                $this->load->model('app_model');
                $response = $this->app_model->getNotices($groupId);
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($response));

            }else
            {
                echo 'Access Denied';
            }

        } else {
            echo 'Access Denied';
        }
    }




    /*
     * The controller sends an invite to the supplied user for the supplied group
     */
    public function inviteUser()
    {

        session_start();

        $this->load->model('authentication_model');

        if ($this->authentication_model->isAuthenticated()) // check if current user is authenticated
        {
            $groupId = $this->input->get('group_id');
            $userId = $this->input->get('user_id');

            if($this->authentication_model->isGroupAdmin($groupId)) //check if user has permission to add
            {                                                        //event
                $this->load->model('app_model');
                $this->app_model->insertInviteUser($groupId, $userId);

                echo 'user invited';

            }else
            {
                echo 'Access Denied';
            }

        } else {
            echo 'Access Denied';
        }
    }

    /*
     * This controller gets and returns the group name and id for all of the groups that the authenticated
     * user is member of.
     */

    public function getInvites()
    {
        session_start();

        $this->load->model('authentication_model');

        if ($this->authentication_model->isAuthenticated()){ // check if current user is authenticated

            $this->load->model('app_model');
            $response = $this->app_model->getInvites();

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));

        } else {
            echo 'Access Denied';
        }

    }

    public function processInvite()
    {

        session_start();

        $this->load->model('authentication_model');

        if ($this->authentication_model->isAuthenticated()){ // check if current user is authenticated

            $groupId = $this->input->get('group_id');
            $decision = $this->input->get('decision');

            $this->load->model('app_model');

            if($decision == 'accept'){

                 $response = $this->app_model->joinGroup($groupId);
            }
            else {
                $response = $this->app_model->deleteInvite();
            }


        } else {
            echo 'Access Denied';
        }
        echo $response;

    }

}



