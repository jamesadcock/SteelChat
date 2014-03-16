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
}
