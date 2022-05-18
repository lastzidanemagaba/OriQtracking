<?php

class Main extends CI_Controller {
	
	public function index() {
		if ($this->session->logged_in == 1) {
			header('Location: https://dev.jtindonesia.com/admin/admin');
		} else {
			header('Location: https://dev.jtindonesia.com/admin/login');
		}
	}
}
