<?php

class Settings extends CI_Controller {

	public function index() {
		if ($this->session->logged_in == 1) {
			$adminID = $this->session->user_id;
			$this->load->view('settings', array(
				'adminID' => $adminID
			));
		} else {
			$this->load->view('login');
		}
	}

	public function add() {
		if ($this->session->logged_in == 1) {
			$userID = $this->session->user_id;
			$this->load->view('add_setting', array(
				'adminID' => $userID
			));
		} else {
			header('Location: http://localhost/admin/login');
		}
	}
}
