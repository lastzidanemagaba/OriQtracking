<?php

class Jadwalsholat extends CI_Controller {
	
	public function index() {
		if ($this->session->logged_in == 1) {
			$userID = $this->session->user_id;
			$this->load->view('jadwal_sholat', array(
				'adminID' => $userID
			));
		} else {
			header('Location: https://dev.jtindonesia.com/admin/login');
		}
	}
	
	public function add() {
		if ($this->session->logged_in == 1) {
			$userID = $this->session->user_id;
			$this->load->view('add_jadwal_sholat', array(
				'adminID' => $userID
			));
		} else {
			header('Location: https://dev.jtindonesia.com/admin/login');
		}
	}
	
	public function edit() {
		$id = intval($this->input->post('id'));
		if ($this->session->logged_in == 1) {
			$userID = $this->session->user_id;
			$this->load->view('edit_jadwal_sholat', array(
				'adminID' => $userID,
				'editedJadwalSholatID' => $id
			));
		} else {
			header('Location: https://dev.jtindonesia.com/admin/login');
		}
	}
}
