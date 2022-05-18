<?php

class StringDB {
	
	static function getCurrentLang($instance, $userID) {
		$user = $instance->db->query("SELECT * FROM `users` WHERE `id`=".$userID)->row_array();
		if ($user == NULL) {
			return "id";
		} else {
			return $user['lang_code'];
		}
	}
	
	static function get_strings() {
		$strings = [
			"text1" => array("en" => "Document sent", "id" => "Dokumen dikirim"),
			"text2" => array("en" => "added by", "id" => "ditambahkan oleh"),
			"text3" => array("en" => "deleted by", "id" => "dihapus oleh"),
			"text4" => array("en" => "entered", "id" => "masuk ke"),
			"text5" => array("en" => "exited", "id" => "keluar dari"),
			"text6" => array("en" => "Message broadcast", "id" => "Broadcast pesan")
		];
		return $strings;
	}
	
	static function get($instance, $userID, $stringName) {
		$langCode = StringDB::getCurrentLang($instance, $userID);
		$strings = StringDB::get_strings();
		return $strings[$stringName][$langCode];
	}
	
	static function get_with_lang_code($langCode, $stringName) {
		$strings = StringDB::get_strings();
		return $strings[$stringName][$langCode];
	}
}
