<?php

class Site extends CI_Controller
{

    /**
     * This controller is the default controller and displays the homepage
     */
    public function index()
    {
        $this->load->view('homepage');
    }

    public function contact()
    {

        //form validation rules
        $this->form_validation->set_rules('name', 'Your Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('email_address', 'Email Address', 'trim|required|valid_email|xss_clean');
        $this->form_validation->set_rules('message', 'Message', 'required|xss_clean');

            if ($this->form_validation->run() == FALSE) {  // if form is not valid reload it
                $this->load->view('contact');
            } else {  // send email

                $name = $this->input->post('name');
                $email_address = $this->input->post('email_address');
                $message = $this->input->post('message');

                //send email
                $this->email->from($email_address);
                $this->email->to('jamesadcock1980@gmail.com');
                $this->email->subject('Teamsync enquiry:'. $name);
                $this->email->message($message);
                $this->email->send();


                $viewData['message'] = 'Your message has been sent.  Thank you for contacting us';
                $this->load->view('form_success', $viewData);
            }

    }
}
