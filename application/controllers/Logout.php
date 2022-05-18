<?php

class Logout extends CI_Controller {
	
	public function index() {
		$this->session->set_userdata(array(
			'logged_in' => 0,
			'user_id' => 0,
			'name' => ''
		));
		header("Location: https://dev.jtindonesia.com/admin");
	}
}
