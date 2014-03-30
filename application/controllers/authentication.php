<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Authentication extends CI_Controller
{

    /**
     *This controller authenticates the user and return the string true if the authentication is
     * successful and false if it is not.  It is mapped to the URI authentication/signin and takes the
     * username and password as parameters.
     */

    public function signIn()
    {
        $username = $this->input->post('username');
        $password = $this->input->post('password');

        $this->load->model('authentication_model');
        $response = $this->authentication_model->authenticateUser($username, $password);


        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }


    /**
     * This controller creates a new user account and returns a string advising if successful.
     */
    public function signUp()
    {
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        $firstName = $this->input->post('first_name');
        $lastName = $this->input->post('last_name');
        $emailAddress = $this->input->post('email_address');
        $this->load->model('authentication_model');
        $response = $this->authentication_model->insertUserAccount($username, $password, $firstName, $lastName, $emailAddress);
        echo $response;


    }

    /**
     * This controller sends and email with a one time authentication token to the supplied email address
     */
    public function forgottenPassword()
    {
        $emailAddress = $this->input->post('email_address');

        $this->load->model('authentication_model');

        if(!empty($emailAddress)){
             $this->authentication_model->sendResetPasswordEmail($emailAddress);
        }

        echo 'If your details are in the system a password reset email has been sent to your email address';
    }


    /*
     * This controller authenticates the user if the authentication token is valid and renders
     * a change password form.  When the user submits th form their password is changed and the
     * authentication token is reset to an empty string.
     */
    public function resetPassword()

    {
        session_start();
        $token = $this->input->get('token');

        $this->load->model('authentication_model');

        if(!empty($token)) {        // if authentication token has been supplied attempt to authenticate user
            $this->authentication_model->oneTimeAuthenticate($token);
        }

        if ($this->authentication_model->isAuthenticated()) {

            //form validation rules
            $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[8]|max_length[255]|xss_clean');
            $this->form_validation->set_rules('confirm_password', 'Password Confirmation', 'trim|required|matches[password]');

            if ($this->form_validation->run() == FALSE) {  // if form is not valid reload it
                $this->load->view('reset_password');
            } else {  // change users password

                $password = $this->input->post('password');
                $this->load->model('authentication_model');
                $this->authentication_model->changePassword($password);

                $viewData['message'] = 'Your password has now been reset, you can now login to the TeamSync app with
                    your new password';
                $this->load->view('form_success', $viewData);
            }
        } else {
            $viewData['message'] = 'The link has either been used or is invalid';
            $this->load->view('form_success', $viewData);
        }

    }



    /**
     * This controller updates the authenticated user's account details.
     */
    public function updateUserDetails()
    {
        session_start();

        $this->load->model('authentication_model');

        if ($this->authentication_model->isAuthenticated()) // check if current user is authenticated
        {

            $password = $this->input->post('password');
            $firstName = $this->input->post('first_name');
            $lastName = $this->input->post('last_name');
            $emailAddress = $this->input->post('email_address');
            $this->load->model('authentication_model');
            $response = $this->authentication_model->updateUserAccount($password, $firstName, $lastName, $emailAddress);
            echo $response;
        }
        else{
            echo 'Access Denied';
        }


    }

}

