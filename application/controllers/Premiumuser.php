<?php

class Premiumuser extends CI_Controller {
	
	public function index() {
		if ($this->session->logged_in == 1) {
			$userID = $this->session->user_id;
			$this->load->view('premium_user', array(
				'adminID' => $userID
			));
		} else {
			header('Location: http://localhost/admin/login');
		}
	}
}
