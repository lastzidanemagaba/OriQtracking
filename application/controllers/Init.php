<?php

class Init extends CI_Controller {

	public function init_chapters() {
		for ($i=0; $i<114; $i++) {
			$data = json_decode(file_get_contents(base_url() . "userdata/surah/" . ($i+1) . ".json"), true)[strval($i+1)];
			$this->db->query("UPDATE `chapters` SET `chapter_id`=\"" . $data['name_latin'] . "\", `meaning`=\"" . $data['translations']['id']['name'] . "\", `verses`=" . $data['number_of_ayah'] . " WHERE `id`=" . ($i+1));
		}
	}
	
	public function get_juz_number($chapter, $ayah) {
		if ($chapter == 1) {
			return 1;
		} else if ($chapter == 2) {
			if ($ayah >= 1 && $ayah <= 141) {
				return 1;
			} else if ($ayah >= 142 && $ayah <= 252) {
				return 2;
			} else if ($ayah >= 253) {
				return 3;
			}
		} else if ($chapter == 3) {
			if ($ayah >= 1 && $ayah <= 92) {
				return 3;
			} else if ($ayah >= 93) {
				return 4;
			}
		} else if ($chapter == 4) {
			if ($ayah >= 1 && $ayah <= 23) {
				return 4;
			} else if ($ayah >= 24 && $ayah <= 147) {
				return 5;
			} else if ($ayah >= 148) {
				return 6;
			}
		} else if ($chapter == 5) {
			if ($ayah >= 1 && $ayah <= 82) {
				return 6;
			} else if ($ayah >= 83) {
				return 7;
			}
		} else if ($chapter == 6) {
			if ($ayah >= 1 && $ayah <= 110) {
				return 7;
			} else if ($ayah >= 111) {
				return 8;
			}
		} else if ($chapter == 7) {
			if ($ayah >= 1 && $ayah <= 87) {
				return 8;
			} else if ($ayah >= 88) {
				return 9;
			}
		} else if ($chapter == 8) {
			if ($ayah >= 1 && $ayah <= 40) {
				return 9;
			} else if ($ayah >= 41) {
				return 10;
			}
		} else if ($chapter == 9) {
			if ($ayah >= 1 && $ayah <= 92) {
				return 10;
			} else if ($ayah >= 93) {
				return 11;
			}
		} else if ($chapter == 10) {
			return 11;
		} else if ($chapter == 11) {
			if ($ayah >= 1 && $ayah <= 5) {
				return 11;
			} else if ($ayah >= 6) {
				return 12;
			}
		} else if ($chapter == 12) {
			if ($ayah >= 1 && $ayah <= 52) {
				return 12;
			} else if ($ayah >= 53) {
				return 13;
			}
		} else if ($chapter == 13) {
			return 13;
		} else if ($chapter == 14) {
			if ($ayah >= 1 && $ayah <= 52) {
				return 13;
			}
		} else if ($chapter == 15) {
			if ($ayah >= 1) {
				return 14;
			}
		} else if ($chapter == 16) {
			if ($ayah >= 1 && $ayah <= 128) {
				return 14;
			}
		} else if ($chapter == 17) {
			if ($ayah >= 1) {
				return 15;
			}
		} else if ($chapter == 18) {
			if ($ayah >= 1 && $ayah <= 74) {
				return 15;
			} else if ($ayah >= 75) {
				return 16;
			}
		} else if ($chapter == 19) {
			return 16;
		} else if ($chapter == 20) {
			if ($ayah >= 1 && $ayah <= 135) {
				return 16;
			}
		} else if ($chapter == 21) {
			if ($ayah >= 1) {
				return 17;
			}
		} else if ($chapter == 22) {
			if ($ayah >= 1 && $ayah <= 78) {
				return 17;
			}
		} else if ($chapter == 23) {
			if ($ayah >= 1) {
				return 18;
			}
		} else if ($chapter == 24) {
			return 18;
		} else if ($chapter == 25) {
			if ($ayah >= 1 && $ayah <= 20) {
				return 18;
			} else if ($ayah >= 21) {
				return 19;
			}
		} else if ($chapter == 26) {
			return 19;
		} else if ($chapter == 27) {
			if ($ayah >= 1 && $ayah <= 55) {
				return 19;
			} else if ($ayah >= 56) {
				return 20;
			}
		} else if ($chapter == 28) {
			return 20;
		} else if ($chapter == 29) {
			if ($ayah >= 1 && $ayah <= 45) {
				return 20;
			} else if ($ayah >= 46) {
				return 21;
			}
		} else if ($chapter == 30) {
			return 21;
		} else if ($chapter == 31) {
			return 21;
		} else if ($chapter == 32) {
			return 21;
		} else if ($chapter == 33) {
			if ($ayah >= 1 && $ayah <= 30) {
				return 21;
			} else if ($ayah >= 31) {
				return 22;
			}
		} else if ($chapter == 34) {
			return 22;
		} else if ($chapter == 35) {
			return 22;
		} else if ($chapter == 36) {
			if ($ayah >= 1 && $ayah <= 27) {
				return 22;
			} else if ($ayah >= 28) {
				return 23;
			}
		} else if ($chapter == 37) {
			return 23;
		} else if ($chapter == 38) {
			return 23;
		} else if ($chapter == 39) {
			if ($ayah >= 1 && $ayah <= 31) {
				return 23;
			} else if ($ayah >= 32) {
				return 24;
			}
		} else if ($chapter == 40) {
			return 24;
		} else if ($chapter == 41) {
			if ($ayah >= 1 && $ayah <= 46) {
				return 24;
			} else if ($ayah >= 47) {
				return 25;
			}
		} else if ($chapter == 41) {
			if ($ayah >= 1 && $ayah <= 46) {
				return 24;
			} else if ($ayah >= 47) {
				return 25;
			}
		} else if ($chapter == 42) {
			return 25;
		} else if ($chapter == 43) {
			return 25;
		} else if ($chapter == 44) {
			return 25;
		} else if ($chapter == 45) {
			if ($ayah >= 1 && $ayah <= 37) {
				return 25;
			}
		} else if ($chapter == 46) {
			if ($ayah >= 1) {
				return 26;
			}
		} else if ($chapter == 47) {
			return 26;
		} else if ($chapter == 48) {
			return 26;
		} else if ($chapter == 49) {
			return 26;
		} else if ($chapter == 50) {
			return 26;
		} else if ($chapter == 51) {
			if ($ayah >= 1 && $ayah <= 30) {
				return 26;
			} else if ($ayah >= 31) {
				return 27;
			}
		} else if ($chapter == 52) {
			return 27;
		} else if ($chapter == 53) {
			return 27;
		} else if ($chapter == 54) {
			return 27;
		} else if ($chapter == 55) {
			return 27;
		} else if ($chapter == 56) {
			return 27;
		} else if ($chapter == 57) {
			if ($ayah >= 1 && $ayah <= 29) {
				return 27;
			}
		} else if ($chapter == 58) {
			if ($ayah >= 1) {
				return 28;
			}
		} else if ($chapter == 59) {
			return 28;
		} else if ($chapter == 60) {
			return 28;
		} else if ($chapter == 61) {
			return 28;
		} else if ($chapter == 62) {
			return 28;
		} else if ($chapter == 63) {
			return 28;
		} else if ($chapter == 64) {
			return 28;
		} else if ($chapter == 65) {
			return 28;
		} else if ($chapter == 66) {
			if ($ayah >= 1 && $ayah <= 12) {
				return 28;
			}
		} else if ($chapter == 67) {
			if ($ayah >= 1) {
				return 29;
			}
		} else if ($chapter == 68) {
			return 29;
		} else if ($chapter == 69) {
			return 29;
		} else if ($chapter == 70) {
			return 29;
		} else if ($chapter == 71) {
			return 29;
		} else if ($chapter == 72) {
			return 29;
		} else if ($chapter == 73) {
			return 29;
		} else if ($chapter == 74) {
			return 29;
		} else if ($chapter == 75) {
			return 29;
		} else if ($chapter == 76) {
			return 29;
		} else if ($chapter >= 78) {
			return 30;
		}
		return -1;
	}
	
	public function init_verses() {
		for ($i=0; $i<114; $i++) {
			$data = json_decode(file_get_contents(base_url() . "userdata/surah/" . ($i+1) . ".json"), true)[strval($i+1)];
			$verses = intval($data['number_of_ayah']);
			for ($j=0; $j<$verses; $j++) {
				$this->db->query("INSERT INTO `verses` (`chapter_id`, `verse_number`, `verse`, `meaning`) VALUES (" . ($i+1) . ", " . ($j+1) . ", \"" . $data['text'][strval($j+1)] . "\", \"" . $data['translations']['id']['text'][strval($j+1)] . "\")");
			}
		}
	}
	
	public function init_spellings() {
		$data = json_decode(file_get_contents(base_url() . "userdata/surah/quran.json"), true);
		for ($i=0; $i<6236; $i++) {
			$this->db->query("UPDATE `verses` SET `spelling`=\"" . $data[$i+1] . "\" WHERE `id`=" . ($i+1));
		}
	}
	
	public function init_juzes() {
		for ($i=0; $i<114; $i++) {
			$data = json_decode(file_get_contents(base_url() . "userdata/surah/" . ($i+1) . ".json"), true)[strval($i+1)];
			$verses = intval($data['number_of_ayah']);
			for ($j=0; $j<$verses; $j++) {
				$this->db->query("UPDATE `verses` SET `juz`=" . $this->get_juz_number($i+1, $j+1) . " WHERE `chapter_id`=" . ($i+1) . " AND  `verse_number`=" . ($j+1));
			}
		}
	}
	
	public function chapters() {
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue904\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue905\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue906\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue907\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue908\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue90B\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue90C\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue90D\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue90E\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue90F\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue910\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue911\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue912\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue913\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue914\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue915\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue916\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue917\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue918\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue919\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue91A\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue91B\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue91C\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue91D\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue91E\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue91F\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue920\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue921\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue922\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue923\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue924\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue925\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue926\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue92E\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue92F\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue930\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue931\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue909\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue939\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue927\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue928\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue929\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue92A\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue92B\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue92C\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue92D\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue932\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue902\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue933\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue934\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue935\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue936\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue937\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue938\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue939\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue93A\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue93B\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue93C\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue900\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue901\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue941\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue942\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue943\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue944\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue945\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue946\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue947\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue948\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue949\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue94A\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue94B\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue94C\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue94D\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue94E\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue94F\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue950\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue951\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue952\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue93D\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue93E\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue93F\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue940\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue953\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue954\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue955\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue956\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue957\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue958\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue959\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue95A\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue95B\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue95C\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue95D\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue95E\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue95F\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue960\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue961\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue962\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue963\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue964\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue965\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue966\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue967\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue968\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue969\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue96A\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue96B\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue96C\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue96D\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue96E\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue96F\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue970\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue971\"]"));
		$this->db->insert("chapters", array("chapter_ar" => "[\"\ue972\"]"));
	}
}
