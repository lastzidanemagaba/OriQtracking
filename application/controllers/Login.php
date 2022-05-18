<?php

class Login extends CI_Controller {
	
	public function index() {
		if ($this->session->logged_in == 1) {
			$adminID = $this->session->user_id;
			$this->load->view('admin', array(
				'adminID' => $adminID
			));
		} else {
			$this->load->view('login');
		}
	}
}
