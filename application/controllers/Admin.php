<?php

class Admin extends CI_Controller {
	
	public function index() {
		if ($this->session->logged_in == 1) {
			$adminID = $this->session->user_id;
			$this->load->view('admin', array(
				'adminID' => $adminID
			));
		} else {
			header('Location: http://localhost/admin/login');
		}
	}
	
	public function add() {
		if ($this->session->logged_in == 1) {
			$adminID = $this->session->user_id;
			$this->load->view('add_admin', array(
				'adminID' => $adminID
			));
		} else {
			header('Location: http://localhost/admin/login');
		}
	}
	
	public function edit() {
		$adminID = intval($this->input->post('id'));
		if ($this->session->logged_in == 1) {
			$currentAdminID = $this->session->user_id;
			$this->load->view('edit_admin', array(
				'adminID' => $currentAdminID,
				'editedAdminID' => $adminID
			));
		} else {
			header('Location: http://localhost/admin/login');
		}
	}
	
	public function login() {
		$email = $this->input->post('email');
		$password = $this->input->post('password');
		$admins = $this->db->query("SELECT * FROM `admins` WHERE `email`='".$email."'")->result_array();
		if (sizeof($admins) > 0) {
			$admin = $admins[0];
			$this->session->set_userdata(array(
				'logged_in' => 1,
				'user_id' => intval($admin['id']),
				'name' => $admin['name']
			));
			$admin['response_code'] = 1;
			echo json_encode($admin);
		} else {
			echo json_encode(array(
				'response_code' => -1
			));
		}
	}
	
	public function add_admin() {
		$name = $this->input->post('name');
		$email = $this->input->post('email');
		$password = $this->input->post('password');
		$profilePictureChanged = intval($this->input->post('profile_picture_changed'));
		$admins = $this->db->query("SELECT * FROM `admins` WHERE `email`='".$email."'")->result_array();
		if (sizeof($admins) > 0) {
			echo json_encode(array(
				'response_code' => -1
			));
		} else {
			if ($profilePictureChanged == 1) {
				$config = array(
					'upload_path' => './userdata/',
					'allowed_types' => "*",
					'overwrite' => TRUE,
					'max_size' => 10240
				);
				$this->load->library('upload', $config);
				if ($this->upload->do_upload("profile_picture")) {
					$this->db->insert("admins", array(
						"name" => $name,
						"email" => $email,
						"password" => $password,
						"photo" => $this->upload->data()['file_name']
					));
					echo json_encode(array(
						'response_code' => 1
					));
				} else {
					echo json_encode(array(
						'response_code' => -2
					));
				}
			} else {
				$this->db->insert("admins", array(
					"name" => $name,
					"email" => $email,
					"password" => $password
				));
				echo json_encode(array(
					'response_code' => 1
				));
			}
		}
	}
	
	public function edit_admin() {
		$id = intval($this->input->post('id'));
		$name = $this->input->post('name');
		$email = $this->input->post('email');
		$emailChanged = intval($this->input->post('email_changed'));
		$profilePictureChanged = intval($this->input->post('profile_picture_changed'));
		$password = $this->input->post('password');
		if ($emailChanged == 0) {
			$this->db->where("id", $id);
			$this->db->update("admins", array(
				"name" => $name,
				"password" => $password
			));
			if ($profilePictureChanged == 1) {
				$config = array(
					'upload_path' => './userdata/',
					'allowed_types' => "*",
					'overwrite' => TRUE,
					'max_size' => 10240
				);
				$this->load->library('upload', $config);
				if ($this->upload->do_upload("profile_picture")) {
					$this->db->where("id", $id);
					$this->db->update("admins", array(
						"photo" => $this->upload->data()['file_name']
					));
				}
			}
			echo json_encode(array(
				'response_code' => 1
			));
		} else {
			$admins = $this->db->query("SELECT * FROM `admins` WHERE `email`='".$email."'")->result_array();
			if (sizeof($admins) > 0) {
				echo json_encode(array(
					'response_code' => -1
				));
			} else {
				$this->db->where("id", $id);
				$this->db->update("admins", array(
					"name" => $name,
					"email" => $email,
					"password" => $password
				));
				if ($profilePictureChanged == 1) {
					$config = array(
						'upload_path' => './userdata/',
						'allowed_types' => "*",
						'overwrite' => TRUE,
						'max_size' => 10240
					);
					$this->load->library('upload', $config);
					if ($this->upload->do_upload("profile_picture")) {
						$this->db->where("id", $id);
						$this->db->update("admins", array(
							"photo" => $this->upload->data()['file_name']
						));
					}
				}
				echo json_encode(array(
					'response_code' => 1
				));
			}
		}
	}
	
	public function delete_admin() {
		$adminID = intval($this->input->post('id'));
		$this->db->query("DELETE FROM `admins` WHERE `id`=".$adminID);
	}
	
	public function get_admins() {
		$admins = $this->db->query("SELECT * FROM `admins` ORDER BY `name`")->result_array();
		echo json_encode($admins);
	}
	
	public function get_users() {
		$users = $this->db->query("SELECT * FROM `users` WHERE `premium`=0 ORDER BY `name`")->result_array();
		echo json_encode($users);
	}
	
	public function get_premium_users() {
		$users = $this->db->query("SELECT * FROM `users` WHERE `premium`=1 ORDER BY `name`")->result_array();
		echo json_encode($users);
	}
	
	public function get_panduan_umroh() {
		$panduan = $this->db->query("SELECT * FROM `panduan_umroh` ORDER BY `title`")->result_array();
		echo json_encode($panduan);
	}
	
	public function get_panduan_umroh_bab() {
		$panduan = $this->db->query("SELECT * FROM `panduan_umroh_bab` ORDER BY `id`")->result_array();
		echo json_encode($panduan);
	}
	
	public function get_panduan_umroh_subbab() {
		$babID = intval($this->input->post('bab_id'));
		$panduan = $this->db->query("SELECT * FROM `panduan_umroh_subbab` WHERE `panduan_umroh_bab`=".$babID." ORDER BY `id`")->result_array();
		echo json_encode($panduan);
	}
	
	public function get_panduan_umroh_by_bab_subbab() {
		$babID = intval($this->input->post('bab_id'));
		$subbabID = intval($this->input->post('subbab_id'));
		$panduan = $this->db->query("SELECT * FROM `panduan_umroh` WHERE `panduan_umroh_bab`=".$babID." AND `panduan_umroh_subbab`=".$subbabID." ORDER BY `id`")->result_array();
		echo json_encode($panduan);
	}
	
	public function get_panduan_umroh_by_id() {
		$id = intval($this->input->post('id'));
		$panduan = $this->db->query("SELECT * FROM `panduan_umroh` WHERE `id`=" . $id)->row_array();
		echo json_encode($panduan);
	}
	
	public function get_panduan_umroh_bab_by_id() {
		$id = intval($this->input->post('id'));
		$panduan = $this->db->query("SELECT * FROM `panduan_umroh_bab` WHERE `id`=" . $id)->row_array();
		echo json_encode($panduan);
	}
	
	public function get_panduan_umroh_subbab_by_id() {
		$id = intval($this->input->post('id'));
		$panduan = $this->db->query("SELECT * FROM `panduan_umroh_subbab` WHERE `id`=" . $id)->row_array();
		echo json_encode($panduan);
	}
	
	public function get_jadwal_sholat() {
		$waktuSholat = $this->db->query("SELECT * FROM `waktu_sholat`")->result_array();
		echo json_encode($waktuSholat);
	}
	
	public function get_jadwal_sholat_by_id() {
		$id = intval($this->input->post('id'));
		$waktuSholat = $this->db->query("SELECT * FROM `waktu_sholat` WHERE `id`=" . $id)->row_array();
		echo json_encode($waktuSholat);
	}
	
	public function get_admin_by_id() {
		$id = intval($this->input->post('id'));
		$admin = $this->db->query("SELECT * FROM `admins` WHERE `id`=" . $id)->row_array();
		echo json_encode($admin);
	}
	
	public function upgrade_user() {
		$id = intval($this->input->post('id'));
		$this->db->query("UPDATE `users` SET `premium`=1 WHERE `id`=".$id);
	}
	
	public function downgrade_user() {
		$id = intval($this->input->post('id'));
		$this->db->query("UPDATE `users` SET `premium`=0 WHERE `id`=".$id);
	}
	
	public function add_panduan_umroh() {
		$babID = intval($this->input->post('bab_id'));
		$subbabID = intval($this->input->post('subbab_id'));
		$title = $this->input->post('title');
		$arabicText = $this->input->post('arabic_text');
		$spelling = $this->input->post('spelling');
		$meaning = $this->input->post('meaning');
		$this->db->insert("panduan_umroh", array(
			"panduan_umroh_bab" => $babID,
			"panduan_umroh_subbab" => $subbabID,
			"title" => $title,
			"arabic_text" => $arabicText,
			"spelling" => $spelling,
			"meaning" => $meaning
		));
	}
	
	public function add_panduan_umroh_bab() {
		$titleID = $this->input->post('title_id');
		$titleAR = $this->input->post('title_ar');
		$this->db->insert("panduan_umroh_bab", array(
			"title_id" => $titleID,
			"title_ar" => $titleAR
		));
	}
	
	public function add_panduan_umroh_subbab() {
		$babID = intval($this->input->post('bab_id'));
		$titleID = $this->input->post('title_id');
		$titleAR = $this->input->post('title_ar');
		$this->db->insert("panduan_umroh_subbab", array(
			"panduan_umroh_bab" => $babID,
			"title_id" => $titleID,
			"title_ar" => $titleAR
		));
	}
	
	public function edit_panduan_umroh() {
		$id = intval($this->input->post('id'));
		$title = $this->input->post('title');
		$arabicText = $this->input->post('arabic_text');
		$spelling = $this->input->post('spelling');
		$meaning = $this->input->post('meaning');
		$this->db->where("id", $id);
		$this->db->update("panduan_umroh", array(
			"title" => $title,
			"arabic_text" => $arabicText,
			"spelling" => $spelling,
			"meaning" => $meaning
		));
	}
	
	public function edit_panduan_umroh_bab() {
		$id = intval($this->input->post('id'));
		$titleID = $this->input->post('title_id');
		$titleAR = $this->input->post('title_ar');
		$this->db->where("id", $id);
		$this->db->update("panduan_umroh_bab", array(
			"title_id" => $titleID,
			"title_ar" => $titleAR
		));
	}
	
	public function edit_panduan_umroh_subbab() {
		$id = intval($this->input->post('id'));
		$titleID = $this->input->post('title_id');
		$titleAR = $this->input->post('title_ar');
		$this->db->where("id", $id);
		$this->db->update("panduan_umroh_subbab", array(
			"title_id" => $titleID,
			"title_ar" => $titleAR
		));
	}
	
	public function delete_panduan_umroh() {
		$id = $this->input->post('id');
		$this->db->query("DELETE FROM `panduan_umroh` WHERE `id`=".$id);
	}
	
	public function delete_panduan_umroh_bab() {
		$babID = $this->input->post('bab_id');
		$this->db->query("DELETE FROM `panduan_umroh_bab` WHERE `id`=".$babID);
	}
	
	public function delete_panduan_umroh_subbab() {
		$subbabID = $this->input->post('subbab_id');
		$this->db->query("DELETE FROM `panduan_umroh_subbab` WHERE `id`=".$subbabID);
	}
	
	public function add_jadwal_sholat() {
		$description = $this->input->post('description');
		$this->db->insert("waktu_sholat", array(
			"description" => $description
		));
	}
	
	public function edit_jadwal_sholat() {
		$id = intval($this->input->post('id'));
		$description = $this->input->post('description');
		$this->db->where("id", $id);
		$this->db->update("waktu_sholat", array(
			"description" => $description
		));
	}
	
	public function delete_jadwal_sholat() {
		$id = $this->input->post('id');
		$this->db->query("DELETE FROM `waktu_sholat` WHERE `id`=".$id);
	}
	
	public function get_premium_subscriptions() {
		$subscriptions = $this->db->get("premium_subscriptions")->result_array();
		for ($i=0; $i<sizeof($subscriptions); $i++) {
			$subscriptions[$i]['benefits'] = json_decode($subscriptions[$i]['benefits']);
		}
		echo json_encode($subscriptions);
	}

	public function get_premium_by_id() {
		$id = $this->input->post('id');
		$premium = $this->db->query("SELECT * FROM `premium_subscriptions` WHERE `id`=".$id)->row_array();
		echo json_encode($premium);
	}
	
	public function delete_premium_subscription() {
		$id = intval($this->input->post('id'));
		$this->db->where("id", $id);
		$this->db->delete("premium_subscriptions");
	}

	public function add_premium() {
		$productID = $this->input->post('product_id');
		$productName = $this->input->post('product_name');
		$productDescription = $this->input->post('product_description');
		$benefits = $this->input->post('benefits');
		$months = $this->input->post('months');
		$this->db->insert("premium_subscriptions", array(
			"product_id" => $productID,
			"product_name" => $productName,
			"product_description" => $productDescription,
			"benefits" => $benefits,
			"months" => $months
		));
	}

	public function save_premium() {
		$id = intval($this->input->post('id'));
		$productID = $this->input->post('product_id');
		$productName = $this->input->post('product_name');
		$productDescription = $this->input->post('product_description');
		$benefits = $this->input->post('benefits');
		$months = $this->input->post('months');
		$this->db->where("id", $id);
		$this->db->update("premium_subscriptions", array(
			"product_id" => $productID,
			"product_name" => $productName,
			"product_description" => $productDescription,
			"benefits" => $benefits,
			"months" => $months
		));
	}

	public function get_settings() {
		$settings = $this->db->query("SELECT * FROM `settings`")->result_array();
		echo json_encode($settings);
	}

	public function add_setting() {
		$name = $this->input->post('name');
		$description = $this->input->post('description');
		$dataType = $this->input->post('data_type');
		$value = $this->input->post('value');
		$this->db->insert("settings", array(
			"name" => $name,
			"description" => $description,
			"data_type" => $dataType,
			"value" => $value
		));
	}

	public function delete_setting() {
		$id = intval($this->input->post('id'));
		$this->db->where("id", $id);
		$this->db->delete("settings");
	}

	public function save_settings() {
		$ids = json_decode($this->input->post('ids'));
		$values = json_decode($this->input->post('values'));
		for ($i=0; $i<sizeof($ids); $i++) {
			$this->db->query("UPDATE `settings` SET `value`='".str_replace("\"", "\\\"", str_replace("'", "\'", $values[$i]))."' WHERE `id`=".$ids[$i]);
		}
	}

	public function update_settings() {
		$barangRekomendasiDescription = $this->input->post('barang_rekomendasi_description');
		$locationUpdateDelay = $this->input->post('location_update_delay');
		$mapLocationUpdateDelay = $this->input->post('map_location_update_delay');
		$minRequestLocationUpdateDelay = $this->input->post('min_request_location_update_delay');
		$locationUpdateAccuracy = $this->input->post('location_update_accuracy');
		$shareTextId = $this->input->post('share_text_id');
		$shareTextEn = $this->input->post('share_text_en');
		$minPlaceRadius = $this->input->post('min_place_radius');
		$maxPlaceRadius = $this->input->post('max_place_radius');
		$fcmServerKey = $this->input->post('fcm_server_key');
		$pushyApiKey = $this->input->post('pushy_api_key');
		$this->db->update("settings", array(
			"barang_rekomendasi_description" => $barangRekomendasiDescription,
			"location_update_delay" => $locationUpdateDelay,
			"map_location_update_delay" => $mapLocationUpdateDelay,
			"min_request_location_update_delay" => $minRequestLocationUpdateDelay,
			"location_update_accuracy" => $locationUpdateAccuracy,
			"share_text_id" => $shareTextId,
			"share_text_en" => $shareTextEn,
			"min_place_radius" => $minPlaceRadius,
			"max_place_radius" => $maxPlaceRadius,
			"fcm_server_key" => $fcmServerKey,
			"pushy_api_key" => $pushyApiKey
		));
	}
}
