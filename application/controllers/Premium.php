<?php

class Premium extends CI_Controller {
	
	public function index() {
		if ($this->session->logged_in == 1) {
			$adminID = $this->session->user_id;
			$this->load->view('premium', array(
				'adminID' => $adminID
			));
		} else {
			$this->load->view('login');
		}
	}

	public function add() {
		if ($this->session->logged_in == 1) {
			$userID = $this->session->user_id;
			$this->load->view('add_premium', array(
				'adminID' => $userID
			));
		} else {
			header('Location: http://localhost/admin/login');
		}
	}

	public function edit() {
		$id = intval($this->input->post('id'));
		if ($this->session->logged_in == 1) {
			$userID = $this->session->user_id;
			$this->load->view('edit_premium', array(
				'adminID' => $userID,
				'editedPremiumID' => $id
			));
		} else {
			header('Location: http://localhost/admin/login');
		}
	}
}
