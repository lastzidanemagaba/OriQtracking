<?php

class Panduanumroh extends CI_Controller {
	
	public function index() {
		if ($this->session->logged_in == 1) {
			$userID = $this->session->user_id;
			$this->load->view('panduan_umroh_bab', array(
				'adminID' => $userID
			));
		} else {
			header('Location: https://dev.jtindonesia.com/admin/login');
		}
	}
	
	public function add() {
		if ($this->session->logged_in == 1) {
			$userID = $this->session->user_id;
			$this->load->view('add_panduan_umroh_bab', array(
				'adminID' => $userID
			));
		} else {
			header('Location: https://dev.jtindonesia.com/admin/login');
		}
	}
	
	public function add_subbab() {
		$babID = intval($this->input->post('bab_id'));
		if ($this->session->logged_in == 1) {
			$userID = $this->session->user_id;
			$this->load->view('add_panduan_umroh_subbab', array(
				'adminID' => $userID,
				'babID' => $babID
			));
		} else {
			header('Location: https://dev.jtindonesia.com/admin/login');
		}
	}
	
	public function add_panduan_umroh() {
		$babID = intval($this->input->post('bab_id'));
		$subbabID = intval($this->input->post('subbab_id'));
		if ($this->session->logged_in == 1) {
			$userID = $this->session->user_id;
			$this->load->view('add_panduan_umroh', array(
				'adminID' => $userID,
				'babID' => $babID,
				'subbabID' => $subbabID
			));
		} else {
			header('Location: https://dev.jtindonesia.com/admin/login');
		}
	}
	
	public function view() {
		$babID = intval($this->input->post('bab_id'));
		$subbabID = intval($this->input->post('subbab_id'));
		if ($this->session->logged_in == 1) {
			$userID = $this->session->user_id;
			$this->load->view('panduan_umroh_item', array(
				'adminID' => $userID,
				'babID' => $babID,
				'subbabID' => $subbabID
			));
		} else {
			header('Location: https://dev.jtindonesia.com/admin/login');
		}
	}
	
	public function view_subbab() {
		$babID = intval($this->input->post('bab_id'));
		if ($this->session->logged_in == 1) {
			$userID = $this->session->user_id;
			$this->load->view('panduan_umroh_subbab', array(
				'adminID' => $userID,
				'babID' => $babID
			));
		} else {
			header('Location: https://dev.jtindonesia.com/admin/login');
		}
	}
	
	public function view_panduan_umroh() {
		$id = intval($this->input->post('id'));
		if ($this->session->logged_in == 1) {
			$userID = $this->session->user_id;
			$this->load->view('panduan_umroh', array(
				'adminID' => $userID,
				'id' => $id
			));
		} else {
			header('Location: https://dev.jtindonesia.com/admin/login');
		}
	}
	
	public function edit() {
		$id = intval($this->input->post('id'));
		$babID = intval($this->input->post('bab_id'));
		$subbabID = intval($this->input->post('subbab_id'));
		if ($this->session->logged_in == 1) {
			$userID = $this->session->user_id;
			$this->load->view('edit_panduan_umroh', array(
				'adminID' => $userID,
				'editedID' => $id,
				'editedBabID' => $babID,
				'editedSubBabID' => $subbabID
			));
		} else {
			header('Location: https://dev.jtindonesia.com/admin/login');
		}
	}
	
	public function edit_bab() {
		$id = intval($this->input->post('id'));
		if ($this->session->logged_in == 1) {
			$userID = $this->session->user_id;
			$this->load->view('edit_panduan_umroh_bab', array(
				'adminID' => $userID,
				'editedID' => $id
			));
		} else {
			header('Location: https://dev.jtindonesia.com/admin/login');
		}
	}
	
	public function edit_subbab() {
		$id = intval($this->input->post('id'));
		if ($this->session->logged_in == 1) {
			$userID = $this->session->user_id;
			$this->load->view('edit_panduan_umroh_subbab', array(
				'adminID' => $userID,
				'editedID' => $id
			));
		} else {
			header('Location: https://dev.jtindonesia.com/admin/login');
		}
	}
}
