<?php

include "phpqrcode/qrlib.php";
include "Uuid.php";
include "Util.php";
include "StringDB.php";

class User extends CI_Controller {
	
	public function index() {
		if ($this->session->logged_in == 1) {
			$userID = $this->session->user_id;
			$this->load->view('user', array(
				'adminID' => $userID
			));
		} else {
			header('Location: https://dev.jtindonesia.com/admin/login');
		}
	}
	
	public function is_user_premium() {
		header("Content-Type: application/json");
		$userID = intval($this->input->post('user_id'));
		$date = Util::get_local_date();
		$user = $this->db->query("SELECT * FROM `users` WHERE `id`=".$userID)->row_array();
		if ($user != NULL) {
			$premium = intval($user['premium']);
			$premiumMonths = intval($user['premium_months']);
			if ($premium == 1) {
				$lastPremiumDate = new DateTime($user['last_premium_date']);
				$lastPremiumDate->modify("+".$premiumMonths." month");
				$lastPremiumDate = $lastPremiumDate->format('Y-m-d h:i:s');
				if (new DateTime($date) < new DateTime($lastPremiumDate)) {
					echo 1;
				} else {
					$this->db->query("UPDATE `users` SET `premium`=0, `premium_months`=0, `last_premium_date`=NULL WHERE `id`=".$userID);
					echo 0;
				}
			} else {
				echo 0;
			}
		} else {
			echo 0;
		}
	}
	
	public function get_groups_($userID) {
		header("Content-Type: application/json");
		$groups = $this->db->query("SELECT * FROM `groups` WHERE `user_id`=" . $userID)->result_array();
		for ($i=0; $i<sizeof($groups); $i++) {
			$groups[$i]['sent_messages'] = $this->db->query("SELECT * FROM `read_group_message_statuses` WHERE `user_id`=" . $userID . " AND `group_id`=" . $groups[$i]['id'] . " AND `status`='sent'")->num_rows();
			$members = [];
			$groupMembers = $this->db->query("SELECT * FROM `group_members` WHERE `group_id`=" . $groups[$i]['id'] . " AND `approved`=1")->result_array();
			for ($j=0; $j<sizeof($groupMembers); $j++) {
				$member = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $groupMembers[$j]['user_id'])->row_array();
				$isAdmin = 0;
				if ($this->db->query("SELECT * FROM `group_admins` WHERE `group_id`=".$groups[$i]['id']." AND `user_id`=".$groupMembers[$j]['user_id'])->num_rows()>0) {
					$isAdmin = 1;
				}
				$member['role'] = $this->db->query("SELECT * FROM `roles` WHERE `id`=".$groupMembers[$j]['role_id'])->row_array();
				$member['is_admin'] = $isAdmin;
				array_push($members, $member);
			}
			$groups[$i]['members'] = $members;
			$groups[$i]['group_members'] = $groupMembers;
			$groups[$i]['user'] = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $groups[$i]['user_id'])->row_array();
			$groups[$i]['attachments'] = $this->db->query("SELECT * FROM `group_messages` WHERE `group_id`=" . $groups[$i]['id'] . " AND `message_type`!='text'")->result_array();
			$groups[$i]['places'] = $this->db->query("SELECT * FROM `places` WHERE `group_id`=".$groups[$i]['id'])->result_array();
			$groups[$i]['panic_users'] = $this->db->query("SELECT * FROM `group_panic_users` WHERE `group_id`=".$groups[$i]['id'])->result_array();
			$groups[$i]['is_panic'] = $this->db->query("SELECT * FROM `group_panic_users` WHERE `group_id`=".$groups[$i]['id'])->num_rows()>0?"1":"0";
			$notificationChannel = $this->db->query("SELECT * FROM `group_notification_channels` WHERE `user_id`=".$userID." AND `group_id`=".$groups[$i]['id'])->row_array();
			if ($notificationChannel == NULL) {
				$groups[$i]['notification_channel'] = "";
			} else {
				$groups[$i]['notification_channel'] = $notificationChannel['channel_id'];
			}
			$unreadMessages = 0;
			$groupMessages = $this->db->query("SELECT * FROM `group_messages` WHERE `group_id`=".$groups[$i]['id'])->result_array();
			for ($j=0; $j<sizeof($groupMessages); $j++) {
				$status = "unsent";
				$readMessageStatuses = $this->db->query("SELECT * FROM `read_group_message_statuses` WHERE `user_id`=".$userID." AND `group_id`=".$groups[$i]['id']." AND `group_message_id`=".$groupMessages[$j]['id'])->result_array();
				if (sizeof($readMessageStatuses) > 0) {
					$status = $readMessageStatuses[0]['status'];
				} else {
					$status = "unsent";
				}
				if ($status != "read") {
					$unreadMessages++;
				}
			}
			$groups[$i]['unread_messages'] = $unreadMessages;
			for ($j=0; $j<sizeof($groups[$i]['places']); $j++) {
				$groups[$i]['places'][$j]['marker_icon'] = $this->db->query("SELECT * FROM `marker_icons` WHERE `id`=".$groups[$i]['places'][$j]['marker_icon_id'])->row_array();
				$groups[$i]['places'][$j]['user'] = $this->db->query("SELECT * FROM `users` WHERE `id`=".$groups[$i]['places'][$j]['user_id'])->row_array();
			}
		}
		$groupMembers = [];
		$groups2 = $this->db->query("SELECT * FROM `groups` WHERE `user_id`=" . $userID)->result_array();
		if (sizeof($groups2) > 0) {
			$groupMembers = $this->db->query("SELECT * FROM `group_members` WHERE `user_id`=" . $userID . " AND `approved`=1 AND `user_id` NOT IN (SELECT `user_id` FROM `groups` WHERE `id`=" . $groups2[0]['id'] . ") AND `group_id`!=" . $groups2[0]['id'])->result_array();
		} else {
			$groupMembers = $this->db->query("SELECT * FROM `group_members` WHERE `user_id`=" . $userID . " AND `approved`=1")->result_array();
		}
		for ($i=0; $i<sizeof($groupMembers); $i++) {
			$group = $this->db->query("SELECT * FROM `groups` WHERE `id`=" . $groupMembers[$i]['group_id'])->row_array();
			if ($group != NULL) {
				$group['sent_messages'] = $this->db->query("SELECT * FROM `read_group_message_statuses` WHERE `user_id`=" . $userID . " AND `group_id`=" . $groupMembers[$i]['group_id'] . " AND `status`='sent'")->num_rows();
				$members = [];
				$groupMembers_ = $this->db->query("SELECT * FROM `group_members` WHERE `group_id`=" . $groupMembers[$i]['group_id'] . " AND `approved`=1")->result_array();
				
				for ($j=0; $j<sizeof($groupMembers_); $j++) {
					$member = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $groupMembers_[$j]['user_id'])->row_array();
					$isAdmin = 0;
					if ($this->db->query("SELECT * FROM `group_admins` WHERE `group_id`=".$group['id']." AND `user_id`=".$groupMembers_[$j]['user_id'])->num_rows()>0) {
						$isAdmin = 1;
					}
					$member['role'] = $this->db->query("SELECT * FROM `roles` WHERE `id`=".$groupMembers_[$j]['role_id'])->row_array();
					$member['is_admin'] = $isAdmin;
					array_push($members, $member);
				}
				$group['members'] = $members;
				$group['group_members'] = $groupMembers_;
				$group['user'] = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $groupMembers[$i]['user_id'])->row_array();
				$group['attachments'] = $this->db->query("SELECT * FROM `group_messages` WHERE `group_id`=" . $groupMembers[$i]['group_id'] . " AND `message_type`!='text'")->result_array();
				$group['places'] = $this->db->query("SELECT * FROM `places` WHERE `group_id`=".$group['id'])->result_array();
				$group['panic_users'] = $this->db->query("SELECT * FROM `group_panic_users` WHERE `group_id`=".$group['id'])->result_array();
				$group['is_panic'] = $this->db->query("SELECT * FROM `group_panic_users` WHERE `group_id`=".$group['id'])->num_rows()>0?"1":"0";
				$unreadMessages = 0;
				$groupMessages = $this->db->query("SELECT * FROM `group_messages` WHERE `group_id`=".$group['id'])->result_array();
				for ($j=0; $j<sizeof($groupMessages); $j++) {
					$status = "unsent";
					$readMessageStatuses = $this->db->query("SELECT * FROM `read_group_message_statuses` WHERE `user_id`=".$userID." AND `group_id`=".$group['id']." AND `group_message_id`=".$groupMessages[$j]['id'])->result_array();
					if (sizeof($readMessageStatuses) > 0) {
						$status = $readMessageStatuses[0]['status'];
					} else {
						$status = "unsent";
					}
					if ($status != "read") {
						$unreadMessages++;
					}
				}
				$group['unread_messages'] = $unreadMessages;
				for ($j=0; $j<sizeof($group['places']); $j++) {
					$group['places'][$j]['marker_icon'] = $this->db->query("SELECT * FROM `marker_icons` WHERE `id`=".$group['places'][$j]['marker_icon_id'])->row_array();
					$group['places'][$j]['user'] = $this->db->query("SELECT * FROM `users` WHERE `id`=".$group['places'][$j]['user_id'])->row_array();
				}
				array_push($groups, $group);
			}
		}
		return $groups;
	}

	public function get_groups() {
		header("Content-Type: application/json");
		$userID = intval($this->input->post('user_id'));
		$groups = $this->get_groups_($userID);
		echo json_encode($groups);
	}
	
	public function get_group_members() {
		header("Content-Type: application/json");
		$groupID = intval($this->input->post('group_id'));
		$members = [];
		$groupMembers = $this->db->query("SELECT * FROM `group_members` WHERE `group_id`=" . $groupID . " AND `exited`=0")->result_array();
		for ($j=0; $j<sizeof($groupMembers); $j++) {
			array_push($members, $this->db->query("SELECT * FROM `users` WHERE `id`=" . $groupMembers[$j]['user_id'])->row_array());
		}
		$group = array();
		$group['members'] = $members;
		$group['group_members'] = $groupMembers;
		echo json_encode($group);
	}
	
	public function delete_groups() {
		header("Content-Type: application/json");
		$userID = intval($this->input->post('user_id'));
		$ids = json_decode($this->input->post('ids'), true);
		$isJoiningGroup = false;
		for ($i=0; $i<sizeof($ids); $i++) {
			$groupMember = $this->db->query("SELECT * FROM `group_members` WHERE `group_id`=".$ids[$i]." AND `user_id`=".$userID)->row_array();
			if ($groupMember != NULL) {
				if (intval($groupMember['exited']) == 0) {
					$isJoiningGroup = true;
				}
			}
		}
		if ($isJoiningGroup) {
			echo json_encode(array('response_code' => -1));
		} else {
			for ($i=0; $i<sizeof($ids); $i++) {
				$groupMember = $this->db->query("SELECT * FROM `group_members` WHERE `group_id`=".$ids[$i]." AND `user_id`=".$userID." AND `exited`=1")->row_array();
				if ($groupMember != NULL) {
					$this->db->query("DELETE FROM `groups` WHERE `id`=" . $ids[$i]);
				}
			}
			echo json_encode(array('response_code' => 1));
		}
	}
	
	public function delete_private_chats() {
		header("Content-Type: application/json");
		$ids = json_decode($this->input->post('ids'), true);
		for ($i=0; $i<sizeof($ids); $i++) {
			$this->db->query("DELETE FROM `chats` WHERE `id`=" . $ids[$i]);
		}
	}
	
	/*private function send_update_location_pushy_message($userID, $groupID, $date) {
		header("Content-Type: application/json");
		$this->db->query("UPDATE `groups` SET `checking_members`=1 WHERE `id`=" . $groupID);
		Util::send_message_to_topic_no_notification($this, "group_".$groupID,
			json_encode(array(
				"type" => "request_update_location",
				"user_id" => "".$userID,
				"group_id" => "".$groupID
			)));
		$this->db->query("UPDATE `groups` SET `checking_members`=0, `last_check_date`='" . $date . "' WHERE `id`=" . $groupID);
	}
	
	public function check_group_members_update_location() {
		header("Content-Type: application/json");
		$userID = intval($this->input->post('user_id'));
		$groupID = intval($this->input->post('group_id'));
		$group = $this->db->query("SELECT * FROM `groups` WHERE `id`=" . $groupID)->row_array();
		if ($group != NULL) {
			if (intval($group['checking_members']) == 1) {
				return;
			}
			if ($group['last_check_date'] == NULL) {
				$this->send_update_location_pushy_message($userID, $groupID, $date);
			} else {
				$lastCheckDate = new DateTime($group['last_check_date']);
				$currentDate = new DateTime($date);
				$diffMilliseconds = $lastCheckDate->diff($currentDate)->format("%s")*1000;
				$settings = $this->db->query("SELECT * FROM `settings` LIMIT 1")->row_array();
				if ($settings != NULL) {
					if ($diffMilliseconds >= intval($settings['location_update_accuracy'])) {
						$this->send_update_location_pushy_message($userID, $groupID, $date);
					}
				}
			}
		}
	}*/
	
	public function delete_group_member() {
		header("Content-Type: application/json");
		$groupID = intval($this->input->post('group_id'));
		$userID = intval($this->input->post('user_id'));
		$this->db->query("DELETE FROM `group_members` WHERE `group_id`=" . $groupID . " AND `user_id`=" . $userID);
		$this->db->query("DELETE FROM `group_admins` WHERE `group_id`=" . $groupID . " AND `user_id`=" . $userID);
		$user = $this->db->query("SELECT * FROM `users` WHERE `id`=".$userID)->row_array();
		if ($user != NULL) {
			Util::send_message_no_notification($this, intval($user['id']), json_encode(array(
				"type" => "group_member_deleted",
				"user_id" => "".$userID,
	    		"group_id" => "".$groupID
			)));
		}
	}
	
	public function add_group_member() {
		header("Content-Type: application/json");
		$userID = intval($this->input->post('user_id'));
		$groupID = intval($this->input->post('group_id'));
		$userIDs = json_decode($this->input->post('user_ids'), true);
		$roleID = intval($this->input->post('role_id'));
		$date = Util::get_local_date();
		$user = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $userID)->row_array();
		for ($i=0; $i<sizeof($userIDs); $i++) {
			$this->db->insert("group_members", array(
				"group_id" => $groupID,
				"user_id" => intval($userIDs[$i]),
				"role_id" => $roleID,
				"approved" => 1,
				"date" => $date
			));
		}
		$groups = $this->db->query("SELECT * FROM `groups` WHERE `id`=" . $groupID)->result_array();
		for ($i=0; $i<sizeof($groups); $i++) {
			$groups[$i]['sent_messages'] = $this->db->query("SELECT * FROM `read_group_message_statuses` WHERE `user_id`=" . $userID . " AND `group_id`=" . $groups[$i]['id'] . " AND `status`='sent'")->num_rows();
			$members = [];
			$groupMembers = $this->db->query("SELECT * FROM `group_members` WHERE `group_id`=" . $groups[$i]['id'] . " AND `exited`=0")->result_array();
			for ($j=0; $j<sizeof($groupMembers); $j++) {
				array_push($members, $this->db->query("SELECT * FROM `users` WHERE `id`=" . $groupMembers[$j]['user_id'])->row_array());
			}
			$groups[$i]['members'] = $members;
			$groups[$i]['group_members'] = $groupMembers;
			$groups[$i]['user'] = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $groups[$i]['user_id'])->row_array();
			$groups[$i]['attachments'] = $this->db->query("SELECT * FROM `group_messages` WHERE `group_id`=" . $groups[$i]['id'] . " AND `message_type`!='text'")->result_array();
			$groups[$i]['places'] = $this->db->query("SELECT * FROM `places` WHERE `group_id`=".$groups[$i]['id'])->result_array();
			$unreadMessages = 0;
			$groupMessages = $this->db->query("SELECT * FROM `group_messages` WHERE `group_id`=".$groups[$i]['id'])->result_array();
			for ($j=0; $j<sizeof($groupMessages); $j++) {
				$status = "unsent";
				$readMessageStatuses = $this->db->query("SELECT * FROM `read_group_message_statuses` WHERE `user_id`=".$userID." AND `group_id`=".$groups[$i]['id']." AND `group_message_id`=".$groupMessages[$j]['id'])->result_array();
				if (sizeof($readMessageStatuses) > 0) {
					$status = $readMessageStatuses[0]['status'];
				} else {
					$status = "unsent";
				}
				if ($status != "read") {
					$unreadMessages++;
				}
			}
			$groups[$i]['unread_messages'] = $unreadMessages;
			for ($j=0; $j<sizeof($groups[$i]['places']); $j++) {
				$groups[$i]['places'][$j]['marker_icon'] = $this->db->query("SELECT * FROM `marker_icons` WHERE `id`=".$groups[$i]['places'][$j]['marker_icon_id'])->row_array();
				$groups[$i]['places'][$j]['user'] = $this->db->query("SELECT * FROM `users` WHERE `id`=".$groups[$i]['places'][$j]['user_id'])->row_array();
			}
		}
		$groupMembers = [];
		$groups2 = $this->db->query("SELECT * FROM `groups` WHERE `user_id`=" . $userID)->result_array();
		if (sizeof($groups2) > 0) {
			$groupMembers = $this->db->query("SELECT * FROM `group_members` WHERE `user_id`=" . $userID . " AND `user_id` NOT IN (SELECT `user_id` FROM `groups` WHERE `id`=" . $groups2[0]['id'] . ") AND `group_id`!=" . $groups2[0]['id'] . " AND `exited`=0")->result_array();
		} else {
			$groupMembers = $this->db->query("SELECT * FROM `group_members` WHERE `user_id`=" . $userID . " AND `exited`=0")->result_array();
		}
		for ($i=0; $i<sizeof($groupMembers); $i++) {
			$group = $this->db->query("SELECT * FROM `groups` WHERE `id`=" . $groupMembers[$i]['group_id'])->row_array();
			$group['sent_messages'] = $this->db->query("SELECT * FROM `read_group_message_statuses` WHERE `user_id`=" . $userID . " AND `group_id`=" . $groupMembers[$i]['group_id'] . " AND `status`='sent'")->num_rows();
			$members = [];
			$groupMembers_ = $this->db->query("SELECT * FROM `group_members` WHERE `group_id`=" . $groupMembers[$i]['group_id'] . " AND `exited`=0")->result_array();
			for ($j=0; $j<sizeof($groupMembers_); $j++) {
				array_push($members, $this->db->query("SELECT * FROM `users` WHERE `id`=" . $groupMembers_[$j]['user_id'])->row_array());
			}
			$group['members'] = $members;
			$group['group_members'] = $groupMembers_;
			$group['user'] = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $groupMembers[$i]['user_id'])->row_array();
			$group['attachments'] = $this->db->query("SELECT * FROM `group_messages` WHERE `group_id`=" . $groupMembers[$i]['group_id'] . " AND `message_type`!='text'")->result_array();
			$group['places'] = $this->db->query("SELECT * FROM `places` WHERE `group_id`=".$group['id'])->result_array();
			$unreadMessages = 0;
			$groupMessages = $this->db->query("SELECT * FROM `group_messages` WHERE `group_id`=".$group['id'])->result_array();
			for ($j=0; $j<sizeof($groupMessages); $j++) {
				$status = "unsent";
				$readMessageStatuses = $this->db->query("SELECT * FROM `read_group_message_statuses` WHERE `user_id`=".$userID." AND `group_id`=".$group['id']." AND `group_message_id`=".$groupMessages[$j]['id'])->result_array();
				if (sizeof($readMessageStatuses) > 0) {
					$status = $readMessageStatuses[0]['status'];
				} else {
					$status = "unsent";
				}
				if ($status != "read") {
					$unreadMessages++;
				}
			}
			$group['unread_messages'] = $unreadMessages;
			for ($j=0; $j<sizeof($group['places']); $j++) {
				$group['places'][$j]['marker_icon'] = $this->db->query("SELECT * FROM `marker_icons` WHERE `id`=".$group['places'][$j]['marker_icon_id'])->row_array();
				$group['places'][$j]['user'] = $this->db->query("SELECT * FROM `users` WHERE `id`=".$group['places'][$j]['user_id'])->row_array();
			}
			array_push($groups, $group);
		}
		echo json_encode($groups);
		if (sizeof($groups) > 0) {
			Util::send_message_no_notification($this, intval($user['id']), json_encode(array(
				"type" => "new_group_member",
				"chat_id" => "".$chatID,
				"message" => $messageObj,
				"group" => $groups[0]
			)));
		}
	}
	
	public function join_group() {
		header("Content-Type: application/json");
		$uniqueID = $this->input->post('unique_id'); // group unique id
		$userID = intval($this->input->post('user_id'));
		$groups = $this->db->query("SELECT * FROM `groups` WHERE `unique_id`='" . $uniqueID . "'")->result_array();
		if (sizeof($groups) > 0) {
			$group = $groups[0];
			$groupMembers = $this->db->query("SELECT * FROM `group_members` WHERE `group_id`=" . $group['id'] . " AND `user_id`=" . $userID . " AND `exited`=0")->result_array();
			if (sizeof($groupMembers) <= 0) {
				$roleID = 0;
				$roles = $this->db->query("SELECT * FROM `roles` WHERE `group_type`='".$group['group_type']."'")->result_array();
				for ($i=0; $i<sizeof($roles); $i++) {
					$role = $roles[$i];
					if (intval($role['is_default']) == 1) {
						$roleID = intval($role['id']);
						break;
					}
				}
				$this->db->insert("group_members", array(
					"group_id" => $group['id'],
					"user_id" => $userID,
					"role_id" => "".$roleID,
					"approved" => 0
				));
				for ($i=0; $i<sizeof($groups); $i++) {
					$groups[$i]['user'] = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $groups[$i]['user_id'])->row_array();
					$members = [];
					$groupMembers = $this->db->query("SELECT * FROM `group_members` WHERE `group_id`=" . $groups[$i]['id'] . " AND `exited`=0")->result_array();
					for ($j=0; $j<sizeof($groupMembers); $j++) {
						array_push($members, $this->db->query("SELECT * FROM `users` WHERE `id`=" . $groupMembers[$j]['user_id'])->row_array());
					}
					$groups[$i]['members'] = $members;
					$groups[$i]['group_members'] = $groupMembers;
				}
				echo json_encode(array(
					'response_code' => 1,
					'group_id' => $group['id'],
					'user_id' => $userID,
					'groups' => $groups
				));
			} else {
				echo json_encode(array('response_code' => -1, 'group_id' => $group['id'],
					'user_id' => $userID));
			}
		} else {
			echo json_encode(array('response_code' => -2, 'group_id' => $group['id'],
					'user_id' => $userID));
		}
		$groups = $this->get_group_by_id_(intval($groups[0]['id']), $userID);
		if (sizeof($groups) > 0) {
			Util::send_message_to_topic_no_notification($this, "group_".$groups[0]['id'], json_encode(array(
				"type" => "new_group_member",
				"group_id" => "".$groups[0]['id'],
				"joining_user_id" => $userID
			)));
			/*$groupMembers = $this->db->query("SELECT * FROM `group_members` WHERE `group_id`=".$groups[0]['id'] . " AND `exited`=0")->result_array();
			for ($i=0; $i<sizeof($groupMembers); $i++) {
				$groupMember = $this->db->query("SELECT * FROM `users` WHERE `id`=".$groupMembers[$i]['user_id'])->row_array();
				$isAdmin = false;
				$admins = $this->db->query("SELECT * FROM `group_admins` WHERE `group_id`=".$groups[0]['id']." AND `user_id`=".$groupMember['id'])->num_rows();
				if ($admins > 0) {
					$isAdmin = true;
				}
				if ($isAdmin && $groupMember != NULL) {
					Util::send_message_no_notification($this, intval($groupMember['id']), json_encode(array(
						"type" => "new_group_member",
						"group_id" => $groups[0]['id']
					)));
				}
			}*/
		}
	}
	
	public function exit_group() {
		header("Content-Type: application/json");
		$userID = intval($this->input->post('user_id'));
		$groupID = intval($this->input->post('group_id'));
		$this->db->query("UPDATE `group_members` SET `exited`=1 WHERE `user_id`=" . $userID . " AND `group_id`=" . $groupID);
		$group = $this->db->query("SELECT * FROM `groups` WHERE `id`=" . $groupID)->row_array();
		$isGroupAdmin = false;
		$groupAdmins = $this->db->query("SELECT * FROM `group_admins` WHERE `group_id`=".$groupID." AND `user_id`=".$userID)->num_rows();
		if ($groupAdmins > 0) {
			$isGroupAdmin = true;
		}
		if ($isGroupAdmin) {
			$this->db->query("DELETE FROM `group_admins` WHERE `group_id`=".$groupID." AND `user_id`=".$userID);
			$groupMember = $this->db->query("SELECT * FROM `group_members` WHERE `group_id`=".$groupID." AND `user_id`!=".$userID." AND `exited`=0 ORDER BY `date` LIMIT 1")->row_array();
			if ($groupMember != NULL) {
				$this->db->insert("group_admins", array(
					"group_id" => $groupID,
					"user_id" => intval($groupMember['user_id'])
				));
			}
		}
	}
	
	public function approve_group_member() {
		header("Content-Type: application/json");
		$id = intval($this->input->post('id'));
		$groupID = intval($this->input->post('group_id'));
		$this->db->query("UPDATE `group_members` SET `approved`=1 WHERE `id`=" . $id);
		$groupMember = $this->db->query("SELECT * FROM `group_members` WHERE `id`=" . $id . " AND `exited`=0")->row_array();
		if ($groupMember != null) {
			$user = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $groupMember['user_id'])->row_array();
			if ($user != null) {
				Util::send_message_no_notification($this, intval($user['id']), json_encode(array(
					"type" => "group_member_approved",
					"group_id" => $groupMember['group_id']
				)));
				Util::send_message_to_topic_no_notification($this, "group_".$groupID, json_encode(array(
					"type" => "group_member_refreshed",
					"group_id" => "".$groupID
				)));
				/*$groupMembers = $this->db->query("SELECT * FROM `group_members` WHERE `group_id`=".$groupID." AND `exited`=0")->result_array();
				for ($i=0; $i<sizeof($groupMembers); $i++) {
					$groupMember = $this->db->query("SELECT * FROM `users` WHERE `id`=".$groupMembers[$i]['user_id'])->row_array();
					Util::send_message_no_notification($this, intval($groupMember['id']), json_encode(array(
						"type" => "group_member_refreshed",
						"group_id" => $groupID
					)));
				}*/
			}
		}
	}

	public function get_group_sent_messages() {
		header("Content-Type: application/json");
		$userID = intval($this->input->post('user_id'));
		$sentMessages = 0;
		$groups = $this->db->query("SELECT * FROM `groups` WHERE `user_id`=" . $userID)->result_array();
		$checkedIDs = [];
		for ($i=0; $i<sizeof($groups); $i++) {
			$sentMessages += $this->db->query("SELECT * FROM `read_group_message_statuses` WHERE `user_id`=" . $userID . " AND `group_id`=" . $groups[$i]['id'] . " AND `status`='sent'")->num_rows();
			array_push($checkedIDs, array(
				"user_id" => $userID,
				"group_id" => $groups[$i]['id']
			));
		}
		$groupMembers = $this->db->query("SELECT * FROM `group_members` WHERE `user_id`=" . $userID . " AND `exited`=0")->result_array();
		for ($i=0; $i<sizeof($groupMembers); $i++) {
			$alreadyAdded = false;
			for ($j=0; $j<sizeof($checkedIDs); $j++) {
				if (($checkedIDs[$j]['user_id'] == $userID && $checkedIDs[$j]['group_id'] == $groupMembers[$i]['group_id'])) {
					$alreadyAdded = true;
					break;
				}
			}
			if (!$alreadyAdded) {
				$sentMessages += $this->db->query("SELECT * FROM `read_group_message_statuses` WHERE `user_id`=" . $userID . " AND `group_id`=" . $groupMembers[$i]['group_id'] . " AND `status`='sent'")->num_rows();
			}
		}
		echo $sentMessages;
	}

	public function get_sent_messages() {
		header("Content-Type: application/json");
		$userID = intval($this->input->post('user_id'));
		$sentMessages = $this->db->query("SELECT * FROM `read_message_statuses` WHERE (`sender_id`=" . $userID . " OR `receiver_id`=" . $userID . ") AND `status`='sent'")->num_rows();
		echo $sentMessages;
	}
	
	public function get_chats() {
		header("Content-Type: application/json");
		$userID = intval($this->input->post('user_id'));
		$chats = $this->db->query("SELECT * FROM `chats` WHERE `sender_id`=" . $userID . " OR `receiver_id`=" . $userID . " ORDER BY `date` DESC")->result_array();
		for ($i=0; $i<sizeof($chats); $i++) {
			$opponentID = 0;
			if ($userID == intval($chats[$i]['sender_id'])) {
				$opponentID = intval($chats[$i]['receiver_id']);
			} else {
				$opponentID = intval($chats[$i]['sender_id']);
			}
			$opponent = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $opponentID)->row_array();
			$chats[$i]['opponent'] = $opponent;
			$status = "";
			if ($userID == intval($chats[$i]['receiver_id'])) {
				$status = "none";
			} else if ($userID == intval($chats[$i]['sender_id'])) {
				$messageStatus = $this->db->query("SELECT * FROM `read_message_statuses` WHERE `receiver_id`=" . $opponentID . " ORDER BY `id` DESC LIMIT 1")->row_array();
				if ($messageStatus == null) {
					$status = "unsent";
				} else {
					$status = $messageStatus['status'];
				}
			}
			$chats[$i]['sent_messages'] = $this->db->query("SELECT * FROM `read_message_statuses` WHERE `chat_id`=".$chats[$i]['id']." AND `status`='sent'")->num_rows();
			$chats[$i]['unread_messages'] = $this->db->query("SELECT * FROM `read_message_statuses` WHERE `chat_id`=".$chats[$i]['id']." AND `receiver_id`=".$userID." AND `status`!='read'")->num_rows();
			$chats[$i]['messages'] = $this->db->query("SELECT * FROM `messages` WHERE `chat_id`=" . $chats[$i]['id'] . " ORDER BY `date`")
			  ->result_array();
			for ($j=0; $j<sizeof($chats[$i]['messages']); $j++) {
				$messageStatus = $this->db->query("SELECT * FROM `read_message_statuses` WHERE `message_id`=" . $chats[$i]['messages'][$j]['id'])->row_array();
				if ($messageStatus != NULL) {
					$chats[$i]['messages'][$j]['status'] = $messageStatus['status'];
				}
			}
			$chats[$i]['status'] = $status;
		}
		echo json_encode($chats);
	}
	
	public function get_chat_by_phone() {
		header("Content-Type: application/json");
		$myUserID = intval($this->input->post('my_user_id'));
		$opponentPhone = $this->input->post('opponent_phone');
		$date = Util::get_local_date();
		$opponents = $this->db->query("SELECT * FROM `users` WHERE `phone`='" . $opponentPhone . "'")->result_array();
		if (sizeof($opponents) > 0) {
			$opponent = $opponents[0];
			$opponentUserID = intval($opponent['id']);
			$chats = $this->db->query("SELECT * FROM `chats` WHERE (`sender_id`=" . $myUserID . " AND `receiver_id`=" . $opponentUserID . ") OR (`sender_id`=" . $opponentUserID . " AND `receiver_id`=" . $myUserID . ")")->result_array();
			$chat = array();
			if (sizeof($chats) <= 0) {
				$this->db->query("INSERT INTO `chats` (`sender_id`, `receiver_id`, `date`) VALUES (" . $myUserID . ", " . $opponentUserID . ", '" . $date . "')");
				$chatID = $this->db->insert_id();
				$chat = $this->db->query("SELECT * FROM `chats` WHERE `id`=" . $chatID)->row_array();
			} else {
				$chat = $chats[0];
			}
			if ($myUserID == intval($chat['sender_id'])) {
				$chat['opponent'] = $this->db->query("SELECT * FROM `users` WHERE `id`=" . intval($chat['receiver_id']))->row_array();
			} else {
				$chat['opponent'] = $this->db->query("SELECT * FROM `users` WHERE `id`=" . intval($chat['sender_id']))->row_array();
			}
			$chat['private_messages'] = $this->db->query("SELECT * FROM `messages` WHERE `chat_id`=" . $chat['id'])->result_array();
			$chat['response_code'] = 1;
			echo json_encode($chat);
		} else {
			$obj = array();
			$obj['response_code'] = -1;
			echo json_encode($obj);
		}
	}
	
	public function get_chat_by_id() {
		header("Content-Type: application/json");
		$id = intval($this->input->post('id'));
		$chat = $this->db->query("SELECT * FROM `chats` WHERE `id`=" . $id)->row_array();
		echo json_encode($chat);
	}
	
	public function get_chat_by_opponent_id() {
		header("Content-Type: application/json");
		$userID = intval($this->input->post('user_id'));
		$opponentID = intval($this->input->post('opponent_id'));
		$chat = $this->db->query("SELECT * FROM `chats` WHERE (`sender_id`=".$userID." AND `receiver_id`=".$opponentID.") OR (`sender_id`=".$opponentID." AND `receiver_id`=".$userID.")")
			->row_array();
		if ($chat == NULL) {
			$this->db->insert("chats", array(
				"room_id" => Uuid::generate_uuid(),
				"sender_id" => $userID,
				"receiver_id" => $opponentID
			));
			$chatID = $this->db->insert_id();
			$chat = $this->db->query("SELECT * FROM `chats` WHERE `id`=".$chatID)->row_array();
			echo json_encode($chat);
		} else {
			$chat['opponent'] = $this->db->query("SELECT * FROM `users` WHERE `id`=".$opponentID)->row_array();
			echo json_encode($chat);
		}
	}
	
	public function delete_group_message() {
		header("Content-Type: application/json");
		$groupMessageID = intval($this->input->post('group_message_id'));
		$userID = intval($this->input->post('user_id'));
		$deletedMessages = $this->db->query("SELECT * FROM `deleted_group_messages` WHERE `group_message_id`=".$groupMessageID." AND `user_id`=".$userID)->num_rows();
		if ($deletedMessages <= 0) {
			$this->db->insert("deleted_group_messages", array("group_message_id" => $groupMessageID, "user_id" => $userID));
		}
	}
	
	public function get_chapters() {
		header("Content-Type: application/json");
		$chapters = $this->db->query("SELECT * FROM `chapters`")->result_array();
		echo json_encode($chapters);
	}
	
	public function get_reciters() {
		header("Content-Type: application/json");
		$reciters = $this->db->query("SELECT * FROM `reciters`")->result_array();
		echo json_encode($reciters);
	}
	
	public function get_verses() {
		header("Content-Type: application/json");
		$chapterID = intval($this->input->post('chapter_id'));
		$verses = $this->db->query("SELECT * FROM `verses` WHERE `chapter_id`=" . $chapterID)->result_array();
		echo json_encode($verses);
	}
	
	public function get_verses_by_juz() {
		header("Content-Type: application/json");
		$juz = intval($this->input->post('juz'));
		$verses = $this->db->query("SELECT * FROM `verses` WHERE `juz`=" . $juz)->result_array();
		echo json_encode($verses);
	}
	
	public function get_audios() {
		header("Content-Type: application/json");
		$reciterID = intval($this->input->post('reciter_id'));
		$chapterID = intval($this->input->post('chapter_id'));
		$audios = $this->db->query("SELECT * FROM `audios` WHERE `reciter_id`=" . $reciterID . " AND `chapter_id`=" . $chapterID)->result_array();
		echo json_encode($audios);
	}
	
	public function get_audios_by_juz_number() {
		header("Content-Type: application/json");
		$juz = intval($this->input->post('juz'));
		$audios = $this->db->query("SELECT * FROM `audios` WHERE `juz`=" . $juz)->result_array();
		echo json_encode($audios);
	}
	
	public function get_themes() {
		header("Content-Type: application/json");
		$themes = $this->db->query("SELECT * FROM `themes`")->result_array();
		echo json_encode($themes);
	}
	
	public function get_all_juz_verses_count() {
		header("Content-Type: application/json");
		$counts = [];
		for ($i=0; $i<30; $i++) {
			$verses = $this->db->query("SELECT * FROM `verses` WHERE `juz`=" . ($i+1))->result_array();
			$lastChapter = 0;
			$lastChapterIndex = 0;
			$lastVerse = 0;
			if (sizeof($verses) > 0) {
				$lastChapter = intval($verses[sizeof($verses)-1]['chapter_id']);
				$lastVerse = intval($verses[sizeof($verses)-1]['verse_number']);
			}
			array_push($counts, array(
				"verses" => sizeof($verses),
				"last_chapter" => $lastChapter,
				"last_verse" => $lastVerse
			));
		}
		echo json_encode($counts);
	}
	
	public function get_juz_verses_count() {
		header("Content-Type: application/json");
		$juz = intval($this->input->post('juz'));
		$verses = $this->db->query("SELECT * FROM `verses` WHERE `juz`=" . $juz)->result_array();
		$lastChapter = 0;
		$lastChapterIndex = 0;
		$lastVerse = 0;
		if (sizeof($verses) > 0) {
			$lastChapter = intval($verses[sizeof($verses)-1]['chapter_id']);
			$lastVerse = intval($verses[sizeof($verses)-1]['verse_number']);
		}
		echo json_encode(array(
			"verses" => sizeof($verses),
			"last_chapter" => $lastChapter,
			"last_verse" => $lastVerse
		));
	}
	
	public function get_last_juz() {
		header("Content-Type: application/json");
		$userID = intval($this->input->post('user_id'));
		$user = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $userID)->row_array();
		echo json_encode(array(
			"last_juz" => intval($user['last_juz']),
			"last_chapter" => intval($user['last_chapter']),
			"last_verse" => intval($user['last_verse'])
		));
	}
	
	public function update_last_verse() {
		header("Content-Type: application/json");
		$userID = intval($this->input->post('user_id'));
		$lastJuz = intval($this->input->post('last_juz'));
		$lastChapter = intval($this->input->post('last_chapter'));
		$lastVerse = intval($this->input->post('last_verse'));
		$date = Util::get_local_date();
		$this->db->query("UPDATE `users` SET `last_juz`=" . $lastJuz . ", `last_chapter`=" . $lastChapter . ", `last_verse`=" . $lastVerse . ", `last_read_date`='" . $date . "' WHERE `id`=" . $userID);
	}
	
	public function login() {
		header("Content-Type: application/json");
		$email = $this->input->post('email');
		$password = $this->input->post('password');
		$deviceID = $this->input->post('device_id');
		$deviceName = $this->input->post('device_name');
		$langCode = $this->input->post('lang_code');
		$users = $this->db->query("SELECT * FROM `users` WHERE `email`='" . $email . "'")->result_array();
		if (sizeof($users) > 0) {
			$user = $users[0];
			if ($user['password'] == $password) {
				$this->db->query("UPDATE `users` SET `lang_code`='" . $langCode . "' WHERE `id`=" . $user['id']);
				if ($user['device_id'] == NULL || $user['device_id'] == $deviceID) {
					$this->db->query("UPDATE `users` SET `device_id`='" . $deviceID . "', `device_name`='" . $deviceName . "' WHERE `id`=" . $user['id']);
					$user['response_code'] = 1;
					echo json_encode($user);
				} else {
					// Same user already logged-in on another device
					$user['response_code'] = -3;
					echo json_encode($user);
				}
			} else {
				echo json_encode(array('response_code' => -1));
			}
		} else {
			echo json_encode(array('response_code' => -2));
		}
	}
	
	public function login_force() {
		header("Content-Type: application/json");
		$email = $this->input->post('email');
		$password = $this->input->post('password');
		$deviceID = $this->input->post('device_id');
		$deviceName = $this->input->post('device_name');
		$users = $this->db->query("SELECT * FROM `users` WHERE `email`='" . $email . "'")->result_array();
		if (sizeof($users) > 0) {
			$user = $users[0];
			if ($user['password'] == $password) {
				$this->db->query("UPDATE `users` SET `device_id`='" . $deviceID . "', `device_name`='" . $deviceName . "' WHERE `id`=" . $user['id']);
				$user['response_code'] = 1;
				echo json_encode($user);
			} else {
				echo json_encode(array('response_code' => -1));
			}
		} else {
			echo json_encode(array('response_code' => -2));
		}
	}
	
	public function logout() {
		header("Content-Type: application/json");
		$userID = intval($this->input->post('user_id'));
		$this->db->query("UPDATE `users` SET `device_id`='', `device_name`='' WHERE `id`=" . $userID);
	}
	
	public function logout_all_devices() {
		header("Content-Type: application/json");
		$userID = intval($this->input->post('user_id'));
		$this->db->query("UPDATE `users` SET `device_id`=NULL, `device_name`=NULL WHERE `id`=" . $userID);
		$user = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $userID)->row_array();
		if ($user != NULL) {
			Util::send_message_no_notification($this, intval($user['id']), json_encode(array(
				"type" => "logout_all_devices"
			)));
		}
	}
	
	public function login_with_google_temp_password() {
		header("Content-Type: application/json");
		$email = $this->input->post('email');
		$password = $this->input->post('password');
		$langCode = $this->input->post('lang_code');
		$users = $this->db->query("SELECT * FROM `users` WHERE `email`='" . $email . "'")->result_array();
		if (sizeof($users) > 0) {
			$user = $users[0];
			if ($user['temp_password'] == $password) {
				$this->db->query("UPDATE `users` SET `lang_code`='" . $langCode . "' WHERE `id`=" . $user['id']);
				$user['response_code'] = 1;
				echo json_encode($user);
			} else {
				echo json_encode(array('response_code' => -1));
			}
		} else {
			echo json_encode(array('response_code' => -2));
		}
	}
	
	public function login_with_facebook_temp_password() {
		header("Content-Type: application/json");
		$facebookUID = $this->input->post('facebook_uid');
		$password = $this->input->post('password');
		$langCode = $this->input->post('lang_code');
		$users = $this->db->query("SELECT * FROM `users` WHERE `facebook_uid`='" . $facebookUID . "'")->result_array();
		if (sizeof($users) > 0) {
			$user = $users[0];
			if ($user['temp_password'] == $password) {
				$this->db->query("UPDATE `users` SET `lang_code`='" . $langCode . "' WHERE `id`=" . $user['id']);
				$user['response_code'] = 1;
				echo json_encode($user);
			} else {
				echo json_encode(array('response_code' => -1));
			}
		} else {
			echo json_encode(array('response_code' => -2));
		}
	}
	
	public function update_scheduled_khatam_date() {
		header("Content-Type: application/json");
		$userID = intval($this->input->post('user_id'));
		$date = $this->input->post('scheduled_khatam_date');
		$date = Util::get_local_date();
		$this->db->query("UPDATE `users` SET `scheduled_khatam_date`='" . $date . "' WHERE `id`=" . $userID);
	}
	
	public function get_juz_histories() {
		header("Content-Type: application/json");
		$userID = intval($this->input->post('user_id'));
		$histories = $this->db->query("SELECT * FROM `histories` WHERE `user_id`=" . $userID . " ORDER BY `date` DESC")->result_array();
		echo json_encode($histories);
	}
	
	public function get_khatam_histories() {
		header("Content-Type: application/json");
		$userID = intval($this->input->post('user_id'));
		$histories = $this->db->query("SELECT * FROM `khatam_histories` WHERE `user_id`=" . $userID . " ORDER BY `date` DESC")->result_array();
		echo json_encode($histories);
	}
	
	public function update_last_juz() {
		header("Content-Type: application/json");
		$userID = intval($this->input->post('user_id'));
		$juz = intval($this->input->post('juz'));
		$date = Util::get_local_date();
		$histories = $this->db->query("SELECT * FROM `histories` WHERE `user_id`=" . $userID . " AND `juz`=" . $juz . " ORDER BY `date` DESC")->result_array();
		if (sizeof($histories) > 0) {
			$this->db->query("UPDATE `histories` SET `date`='" . $date . "' WHERE `id`=" . $histories[0]['id']);
		} else {
			$this->db->query("INSERT INTO `histories` (`user_id`, `juz`, `date`) VALUES (" . $userID . ", " . $juz . ", '" . $date . "')");
		}
	}
	
	public function search_quran() {
		header("Content-Type: application/json");
		$keyword = $this->input->post("keyword");
		$verses = $this->db->query("SELECT * FROM `verses` WHERE `spelling` LIKE '%" . $keyword . "%' OR `meaning` LIKE '%" . $keyword . "%'")
		  ->result_array();
		for ($i=0; $i<sizeof($verses); $i++) {
			$verses[$i]['chapter'] = $this->db->query("SELECT * FROM `chapters` WHERE `id`=" . $verses[$i]['chapter_id'])->row_array();
		}
		echo json_encode($verses);
	}
	
	public function insert_khatam_history() {
		header("Content-Type: application/json");
		$userID = intval($this->input->post('user_id'));
		$date = Util::get_local_date();
		$this->db->query("INSERT INTO `khatam_histories` (`user_id`, `date`) VALUES (" . $userID . ", '" . $date . "')");
	}
	
	public function get_rundowns() {
		header("Content-Type: application/json");
		$groupID = intval($this->input->post('group_id'));
		$rundowns = $this->db->query("SELECT * FROM `rundowns` WHERE `group_id`=" . $groupID)->result_array();
		echo json_encode($rundowns);
	}
	
	public function get_rundown_by_id() {
		header("Content-Type: application/json");
		$id = intval($this->input->post('id'));
		$rundown = $this->db->query("SELECT * FROM `rundowns` WHERE `id`=" . $id)->row_array();
		if ($rundown != NULL) {
			$rundown['group'] = $this->db->query("SELECT * FROM `groups` WHERE `id`=".$rundown['group_id'])->row_array();
		}
		echo json_encode($rundown);
	}
	
	public function get_schedules() {
		header("Content-Type: application/json");
		$userID = intval($this->input->post('user_id'));
		$groupID = intval($this->input->post('group_id'));
		$schedules = $this->db->query("SELECT * FROM `schedules` WHERE `group_id`=" . $groupID)->result_array();
		for ($i=0; $i<sizeof($schedules); $i++) {
			$schedules[$i]['schedule_confirmation'] = $this->db->query("SELECT * FROM `schedule_confirmations` WHERE `schedule_id`=".$schedules[$i]['id'])->row_array();
		}
		echo json_encode($schedules);
	}
	
	public function add_rundown() {
		header("Content-Type: application/json");
		$userID = intval($this->input->post('user_id'));
		$groupID = intval($this->input->post('group_id'));
		$dateStart = $this->input->post('date_start');
		$dateEnd = $this->input->post('date_end');
		$activityName = $this->input->post('activity_name');
		$placeName = $this->input->post('place_name');
		$address = $this->input->post('address');
		$panduan = $this->input->post('panduan');
		$larangan = $this->input->post('larangan');
		$doa = $this->input->post('doa');
		$gmt = intval($this->input->post('gmt'));
		$lat = doubleval($this->input->post('lat'));
		$lng = doubleval($this->input->post('lng'));
		$voiceRecorded = intval($this->input->post('voice_recorded'));
		$photoUploaded = intval($this->input->post('photo_uploaded'));
		$voiceDuration = intval($this->input->post('voice_duration'));
		$config = array(
			'upload_path' => './userdata/',
			'allowed_types' => "*",
			'overwrite' => TRUE,
			'max_size' => 10240
		);
		$this->load->library('upload', $config);
		$this->db->insert('rundowns', array(
			'user_id' => $userID,
			'group_id' => $groupID,
			'date_start' => $dateStart,
			'date_end' => $dateEnd,
			'activity_name' => $activityName,
			'place_name' => $placeName,
			'address' => $address,
			'panduan' => $panduan,
			'larangan' => $larangan,
			'doa' => $doa,
			"latitude" => $lat,
			"longitude" => $lng
		));
		$rundownID = intval($this->db->insert_id());
		$rundown = $this->db->query("SELECT * FROM `rundowns` WHERE `id`=".$rundownID)->row_array();
		if ($photoUploaded == 1) {
			if ($this->upload->do_upload('photo')) {
				$photoPath = $this->upload->data()['file_name'];
				$this->db->where("id", $rundownID);
				$this->db->update("rundowns", array(
					"photo" => $photoPath
				));
			}
		}
		if ($voiceRecorded == 1) {
			if ($this->upload->do_upload('voice')) {
				$voicePath = $this->upload->data()['file_name'];
				$this->db->where("id", $rundownID);
				$this->db->update("rundowns", array(
					"voice_path" => $voicePath,
					"voice_duration" => $voiceDuration
				));
			}
		}
		$group = $this->db->query("SELECT * FROM `groups` WHERE `id`=".$groupID)->row_array();
		$rundown['group'] = $group;
		/*$groupMembers = $this->db->query("SELECT * FROM `group_members` WHERE `group_id`=".$groupID." AND `exited`=0")->result_array();
		for ($i=0; $i<sizeof($groupMembers); $i++) {
			$groupMember = $this->db->query("SELECT * FROM `users` WHERE `id`=".$groupMembers[$i]['user_id'])->row_array();
			if ($groupMember != NULL) {
				Util::send_message_no_notification($this, intval($groupMember['id']), json_encode(array(
					"type" => "rundown_added",
					"rundown_id" => "".$rundown['id'],
					"user_id" => "".$userID,
					"group_id" => "".$groupID
				)));
			}
		}*/
		Util::send_message_to_topic_no_notification($this, "group_".$groupID, json_encode(array(
			"type" => "rundown_added",
			"rundown_id" => "".$rundownID,
			"rundown" => $rundown,
			"user_id" => "".$userID,
			"group_id" => "".$groupID
		)));
	}
	
	public function cancel_rundown() {
		header("Content-Type: application/json");
		$id = intval($this->input->post('id'));
		Util::send_message_to_topic_no_notification($this, "group_".$groupID, json_encode(array(
			"type" => "rundown_cancelled",
			"rundown_id" => "".$id
		)));
	}
	
	public function save_rundown() {
		header("Content-Type: application/json");
		$rundownID = intval($this->input->post('rundown_id'));
		$userID = intval($this->input->post('user_id'));
		$groupID = intval($this->input->post('group_id'));
		$dateStart = $this->input->post('date_start');
		$dateEnd = $this->input->post('date_end');
		$activityName = $this->input->post('activity_name');
		$placeName = $this->input->post('place_name');
		$address = $this->input->post('address');
		$panduan = $this->input->post('panduan');
		$larangan = $this->input->post('larangan');
		$doa = $this->input->post('doa');
		$voiceRecorded = intval($this->input->post('voice_recorded'));
		$photoUploaded = intval($this->input->post('photo_uploaded'));
		$config = array(
			'upload_path' => './userdata/',
			'allowed_types' => "*",
			'overwrite' => TRUE,
			'max_size' => 10240
		);
		$this->load->library('upload', $config);
		$this->db->where("id", $rundownID);
		$this->db->update('rundowns', array(
			'user_id' => $userID,
			'group_id' => $groupID,
			'date_start' => $dateStart,
			'date_end' => $dateEnd,
			'activity_name' => $activityName,
			'place_name' => $placeName,
			'address' => $address,
			'panduan' => $panduan,
			'larangan' => $larangan,
			'doa' => $doa
		));
		if ($photoUploaded == 1) {
			if ($this->upload->do_upload('photo')) {
				$photoPath = $this->upload->data()['file_name'];
				$this->db->where("id", $rundownID);
				$this->db->update("rundowns", array(
					"photo" => $photoPath
				));
			}
		}
		if ($voiceRecorded == 1) {
			if ($this->upload->do_upload('voice')) {
				$voicePath = $this->upload->data()['file_name'];
				$this->db->where("id", $rundownID);
				$this->db->update("rundowns", array(
					"voice_path" => $voicePath
				));
			}
		}
		$rundown = $this->db->query("SELECT * FROM `rundowns` WHERE `id`=".$rundownID)->row_array();
		$group = $this->db->query("SELECT * FROM `groups` WHERE `id`=".$groupID)->row_array();
		Util::send_message_to_topic_no_notification($this, "group_".$group['id'], json_encode(array(
			"type" => "rundown_edited",
			"rundown_id" => "".$id,
			"rundown" => $rundown,
			"group_id" => "".$groupID,
			"group" => $group
		)));
		/*$groupMembers = $this->db->query("SELECT * FROM `group_members` WHERE `group_id`=".$groupID." AND `exited`=0")->result_array();
		for ($i=0; $i<sizeof($groupMembers); $i++) {
			$groupMember = $this->db->query("SELECT * FROM `users` WHERE `id`=".$groupMembers[$i]['user_id'])->row_array();
			if ($groupMember != NULL) {
				Util::send_message_no_notification($this, intval($groupMember['id']), json_encode(array(
					"type" => "rundown_edited",
					"rundown_id" => "".$id,
					"rundown" => $rundown,
					"group_id" => "".$groupID,
					"group" => $group
				)));
			}
		}*/
	}
	
	public function finish_rundown() {
		$id = intval($this->input->post('id'));
		$this->db->query("UPDATE `rundowns` SET `finished`=1 WHERE `id`=" . $id);
	}
	
	public function delete_rundown() {
		$id = intval($this->input->post('id'));
		$groupID = intval($this->input->post('group_id'));
		$this->db->query("DELETE FROM `rundowns` WHERE `id`=" . $id);
		$rundown = $this->db->query("SELECT * FROM `rundowns` WHERE `id`=".$id)->row_array();
		$group = $this->db->query("SELECT * FROM `groups` WHERE `id`=".$groupID)->row_array();
		Util::send_message_to_topic_no_notification($this, "group_".$group['id'], json_encode(array(
			"type" => "rundown_deleted",
			"rundown_id" => "".$id,
			"rundown" => $rundown,
			"group_id" => "".$groupID,
			"group" => $group
		)));
		/*$groupMembers = $this->db->query("SELECT * FROM `group_members` WHERE `group_id`=".$groupID." AND `exited`=0")->result_array();
		for ($i=0; $i<sizeof($groupMembers); $i++) {
			$groupMember = $this->db->query("SELECT * FROM `users` WHERE `id`=".$groupMembers[$i]['user_id'])->row_array();
			if ($groupMember != NULL) {
				Util::send_message_no_notification($this, intval($groupMember['id']), json_encode(array(
					"type" => "rundown_deleted",
					"rundown_id" => "".$id,
					"rundown" => $rundown,
					"group_id" => "".$groupID,
					"group" => $group
				)));
			}
		}*/
	}
	
	public function notify_rundown() {
		$id = intval($this->input->post('id'));
		$userID = intval($this->input->post('user_id'));
		$rundowns = $this->db->query("SELECT * FROM `rundowns` WHERE `id`=" . $id)->result_array();
		if (sizeof($rundowns) > 0) {
			$rundown = $rundowns[0];
			$groupID = intval($rundown['group_id']);
			$groups = $this->db->query("SELECT * FROM `groups` WHERE `id`=" . $groupID)->result_array();
			if (sizeof($groups) > 0) {
				$group = $groups[0];
				$this->db->query("UPDATE `rundowns` SET `finished`=1 WHERE `id`=".$rundown['id']);
				//$user = $this->db->query("SELECT * FROM `users` WHERE `id`=".$userID)->row_array();
				//if ($user != NULL) {
					//$langCode = $user['lang_code'];
					$title = "Kegiatan ".$rundown['activity_name']." sudah selesai";
					$body = "Aktifitas ".$group['title']." berlanjut ke kegiatan berikutnya";
					/*if ($langCode == "en") {
						$title = "Activity ".$rundown['activity_name']." has been done";
						$body = "Group activity ".$group['title']." continues to the next event";
					}*/
					Util::send_message_to_topic_no_notification($this, "group_".$groupID, json_encode(array(
						"type" => "rundown_finish",
						"rundown_id" => "".$rundown['id'],
						"rundown" => $rundown,
						"user_id" => "".$userID,
						"group_id" => "".$groupID,
						"group" => $group
					)));
					/*$groupMembers = $this->db->query("SELECT * FROM `group_members` WHERE `group_id`=".$groupID." AND `exited`=0")->result_array();
					for ($i=0; $i<sizeof($groupMembers); $i++) {
						$groupMember = $this->db->query("SELECT * FROM `users` WHERE `id`=".$groupMembers[$i]['user_id'])->row_array();
						if ($groupMember != NULL) {
							Util::send_message_no_notification($this, intval($groupMember['id']), json_encode(array(
								"type" => "rundown_finish",
								"rundown_id" => "".$rundown['id'],
								"rundown" => $rundown,
								"user_id" => "".$userID,
								"group_id" => "".$groupID,
								"group" => $group
							)));
						}
					}*/
				//}
			}
		}
	}
	
	public function notify_schedule() {
		$id = intval($this->input->post('id'));
		$schedules = $this->db->query("SELECT * FROM `schedules` WHERE `id`=" . $id)->result_array();
		if (sizeof($schedules) > 0) {
			$schedule = $schedules[0];
			$groupID = intval($schedule['group_id']);
			$groups = $this->db->query("SELECT * FROM `groups` WHERE `id`=" . $groupID)->result_array();
			if (sizeof($groups) > 0) {
				$group = $groups[0];
				Util::send_message_to_topic_no_notification($this, "group_".$groupID, json_encode(array(
						"type" => "schedule_finish",
						"schedule_id" => "".$scheduleID,
						"group_id" => "".$groupID
					)));
			}
		}
	}
	
	public function get_contacts() {
		$userID = intval($this->input->post('user_id'));
		$contacts = $this->db->query("SELECT * FROM `contacts` WHERE `user_id`=" . $userID)->result_array();
		echo json_encode($contacts);
	}
	
	public function get_contacts_by_group() {
		$userID = intval($this->input->post('user_id'));
		$groupID = intval($this->input->post('group_id'));
		$contacts = $this->db->query("SELECT * FROM `contacts` WHERE `user_id`=" . $userID . " AND `contact_user_id` NOT IN (SELECT `user_id` FROM `group_members` WHERE `group_id`=" . $groupID . ")")->result_array();
		echo json_encode($contacts);
	}
	
	public function get_contacts_count_by_group() {
		$userID = intval($this->input->post('user_id'));
		$groupID = intval($this->input->post('group_id'));
		$contacts = $this->db->query("SELECT * FROM `contacts` WHERE `user_id`=" . $userID . " AND `contact_user_id` NOT IN (SELECT `user_id` FROM `group_members` WHERE `group_id`=" . $groupID . ")")->result_array();
		echo sizeof($contacts);
	}
	
	public function add_group() {
		$userID = intval($this->input->post('user_id'));
		$uniqueID = $this->get_last_unique_id();
		$userIDs = json_decode($this->input->post('user_ids'), true);
		$title = $this->input->post('title');
		$imageUploaded = intval($this->input->post('image_uploaded'));
		$groupType = $this->input->post('group_type');
		$date = Util::get_local_date();
		$config = array(
			'upload_path' => './userdata/',
			'allowed_types' => "*",
			'overwrite' => TRUE,
			'max_size' => 10240
		);
		$this->load->library('upload', $config);
		$fileName = Uuid::generate_uuid();
		QRcode::png($uniqueID, "userdata/" . $fileName, QR_ECLEVEL_L, 4, false, 0xFFFFFF, 0x1864A7);
		$this->db->insert('groups', array(
			'unique_id' => $uniqueID,
			'user_id' => $userID,
			'group_type' => $groupType,
			'title' => $title,
			'qr_image' => $fileName,
			'created_date' => $date,
			'last_modified_date' => $date
		));
		$groupID = intval($this->db->insert_id());
		if ($imageUploaded==1 && $this->upload->do_upload('photo')) {
			$photoPath = $this->upload->data()['file_name'];
			$this->db->where("id", $groupID);
			$this->db->update("groups", array(
				'photo' => $photoPath,
			));
		}
		$this->db->insert("group_admins", array(
			"group_id" => $groupID,
			"user_id" => $userID
		));
		$roleID = 0;
		$roles = $this->db->query("SELECT * FROM `roles` WHERE `group_type`='".$groupType."'")->result_array();
		for ($i=0; $i<sizeof($roles); $i++) {
			$role = $roles[$i];
			if (intval($role['is_default']) == 1) {
				$roleID = intval($role['id']);
				break;
			}
		}
		$this->db->insert("group_members", array(
			"group_id" => $groupID,
			"user_id" => $userID,
			"role_id" => "".$roleID,
			"approved" => 1
		));
		for ($i=0; $i<sizeof($userIDs); $i++) {
			if ($userIDs[$i] != $userID) {
				$this->db->insert("group_members", array(
					"group_id" => $groupID,
					"user_id" => $userIDs[$i],
					"role_id" => "".$roleID,
					"approved" => 1
				));
				$user = $this->db->query("SELECT * FROM `users` WHERE `id`=".$userIDs[$i])->row_array();
				if ($user != NULL) {
					Util::send_message_no_notification($this, intval($user['id']),
						json_encode(array(
							"type" => "new_group",
							"group_id" => "".$groupID
						)));
				}
			}
		}
		echo json_encode($this->db->query("SELECT * FROM `groups` WHERE `id`=" . $groupID)->row_array());
	}
	
	public function save_group() {
		$groupID = intval($this->input->post('group_id'));
		$userIDs = json_decode($this->input->post('user_ids'), true);
		$imageUploaded = intval($this->input->post('image_uploaded'));
		$groupType = $this->input->post('group_type');
		$title = $this->input->post('title');
		$config = array(
			'upload_path' => './userdata/',
			'allowed_types' => "*",
			'overwrite' => TRUE,
			'max_size' => 10240
		);
		$this->db->where("id", $groupID);
		$this->db->update("groups", array(
			'group_type' => $groupType,
			'title' => $title
		));
		$this->load->library('upload', $config);
		if ($imageUploaded==1 && $this->upload->do_upload('photo')) {
			$photoPath = $this->upload->data()['file_name'];
			$this->db->where("id", $groupID);
			$this->db->update("groups", array(
				'photo' => $photoPath,
			));
		}
		$roleID = 0;
		$roles = $this->db->query("SELECT * FROM `roles` WHERE `group_type`='".$groupType."'")->result_array();
		for ($i=0; $i<sizeof($roles); $i++) {
			$role = $roles[$i];
			if (intval($role['is_default']) == 1) {
				$roleID = intval($role['id']);
				break;
			}
		}
		for ($i=0; $i<sizeof($userIDs); $i++) {
			$this->db->insert("group_members", array(
				"group_id" => $groupID,
				"user_id" => $userIDs[$i],
				"role_id" => "".$roleID,
				"approved" => 1
			));
		}
		Util::send_message_to_topic_no_notification($this, "group_".$groupID, json_encode(array(
				"type" => "group_info_updated",
				"group_id" => "".$groupID
			)));
		echo json_encode($this->db->query("SELECT * FROM `groups` WHERE `id`=" . $groupID)->row_array());
	}
	
	public function get_messages() {
		$chatID = intval($this->input->post('chat_id'));
		$messages = $this->db->query("SELECT * FROM `messages` WHERE `chat_id`=" . $chatID . " ORDER BY `date`")->result_array();
		echo json_encode($messages);
	}
	
	public function get_private_message_by_id() {
		$messageID = intval($this->input->post('message_id'));
		$message = $this->db->query("SELECT * FROM `messages` WHERE `id`=" . $messageID)->row_array();
		$message['sender'] = $this->db->query("SELECT * FROM `users` WHERE `id`=".$message['sender_id'])->row_array();
		$message['receiver'] = $this->db->query("SELECT * FROM `users` WHERE `id`=".$message['receiver_id'])->row_array();
		echo json_encode($message);
	}
	
	public function get_group_message_by_id() {
		$messageID = intval($this->input->post('message_id'));
		$message = $this->db->query("SELECT * FROM `group_messages` WHERE `id`=" . $messageID)->row_array();
		$message['user'] = $this->db->query("SELECT * FROM `users` WHERE `id`=".$message['user_id'])->row_array();
		echo json_encode($message);
	}
	
	public function get_user_by_id() {
		$userID = intval($this->input->post('id'));
		$user = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $userID)->row_array();
		echo json_encode($user);
	}
	
	public function send_chat_message() {
		$chatID = intval($this->input->post('chat_id'));
		$senderID = intval($this->input->post('sender_id'));
		$receiverID = intval($this->input->post('receiver_id'));
		$message = $this->input->post('message');
		$date = Util::get_local_date();
		$this->db->insert("messages", array(
			"chat_id" => $chatID,
			"sender_id" => $senderID,
			"receiver_id" => $receiverID,
			"message" => $message,
			"date" => $date
		));
		$messageID = intval($this->db->insert_id());
		$messageObj = $this->db->query("SELECT * FROM `messages` WHERE `id`=" . $messageID)->row_array();
		$messageObj['sender'] = $this->db->query("SELECT * FROM `users` WHERE `id`=".$senderID)->row_array();
		$messageObj['receiver'] = $this->db->query("SELECT * FROM `users` WHERE `id`=".$receiverID)->row_array();
		$sender = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $senderID)->row_array();
		$receiver = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $receiverID)->row_array();
		$this->db->insert("read_message_statuses", array(
			"chat_id" => $chatID,
			"sender_id" => $senderID,
			"receiver_id" => $receiverID,
			"message_id" => $messageID,
			"status" => "unsent"
		));
		$this->db->query("UPDATE `chats` SET `date`='".$date."' WHERE `id`=".$chatID);
		$payload = json_encode(array(
				"type" => "message",
				"chat_id" => "".$chatID,
				"message_id" => "".$messageObj['id'],
				"sender_id" => "".$senderID,
				"unread_messages" => $this->get_unread_messages_count($chatID, $receiverID),
				"click_action" => "FLUTTER_NOTIFICATION_CLICK",
	    		"screen_path" => "/private_message",
	    		"screen_name" => "PrivateMessage",
	    		"chat_id" => "".$chatID,
	    		"opponent" => $sender
			));
		$privateMessageNotification = intval(Util::get_user_setting($this, $receiverID, "private_message_notification"));
		if ($privateMessageNotification == 1) {
			Util::insert_notification($this, $receiverID, false, null, "inbox", $sender['name'], strlen($message)>150?substr($message, 0, 150):$message, $payload, $date);
			Util::send_message($this, $receiverID, $sender['name'], $message, $payload);
		}
		echo json_encode(array(
				"type" => "message",
				"chat_id" => $chatID,
				"message" => $messageObj,
				"sender" => $sender,
				"sender_id" => $senderID
			));
	}

	public function send_chat_document() {
		$chatID = intval($this->input->post('chat_id'));
		$senderID = intval($this->input->post('sender_id'));
		$receiverID = intval($this->input->post('receiver_id'));
		$documentName = $this->input->post('document_name');
		$documentDuration = intval($this->input->post('document_duration'));
		$thumbnailUploaded = intval($this->input->post('thumbnail_uploaded'));
		$dataType = $this->input->post('data_type');
		$date = Util::get_local_date();
		$config = array(
			'upload_path' => './userdata/',
			'allowed_types' => "*",
			'overwrite' => TRUE,
			'max_size' => 10240
		);
		$this->load->library('upload', $config);
		if ($this->upload->do_upload('document')) {
			$this->db->insert("messages", array(
				"chat_id" => $chatID,
				"sender_id" => $senderID,
				"receiver_id" => $receiverID,
				"message_type" => $dataType,
				"document_name" => $documentName,
				"document_path" => $this->upload->data()['file_name'],
				"document_duration" => $documentDuration,
				"date" => $date
			));
			$messageID = intval($this->db->insert_id());
			if ($thumbnailUploaded == 1) {
				if ($this->upload->do_upload('thumbnail')) {
					$this->db->where('id', $messageID);
					$this->db->update("messages", array(
						"document_thumbnail" => $this->upload->data()['file_name']
					));
				}
			}
			$messageObj = $this->db->query("SELECT * FROM `messages` WHERE `id`=" . $messageID)->row_array();
			$messageObj['sender'] = $this->db->query("SELECT * FROM `users` WHERE `id`=".$senderID)->row_array();
			$messageObj['receiver'] = $this->db->query("SELECT * FROM `users` WHERE `id`=".$receiverID)->row_array();
			$sender = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $senderID)->row_array();
			$receiver = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $receiverID)->row_array();
			$this->db->insert("read_message_statuses", array(
				"sender_id" => $senderID,
				"receiver_id" => $receiverID,
				"message_id" => $messageID,
				"status" => "unsent"
			));
			$this->db->query("UPDATE `chats` SET `date`='".$date."' WHERE `id`=".$chatID);
			$langCode = $receiver['lang_code'];
			$title = "Dokumen diterima";
			if ($langCode == "en") {
				$title = "Picture received";
			}
			$payload = json_encode(array(
					"type" => "message",
					"chat_id" => "".$chatID,
					"message_id" => "".$messageObj['id'],
					"sender_id" => "".$senderID,
					"unread_messages" => $this->get_unread_messages_count($chatID, $receiverID),
					"click_action" => "FLUTTER_NOTIFICATION_CLICK",
		    		"screen_path" => "/private_message",
		    		"screen_name" => "PrivateMessage",
		    		"chat_id" => "".$chatID,
		    		"opponent" => $sender
				));
			Util::insert_notification($this, $receiverID, false, null, "inbox", $sender['name'], $title, $payload, $date);
			Util::send_message($this, $receiverID, $sender['name'], $title, $payload);
			echo json_encode(array(
				"type" => "message",
				"chat_id" => $chatID,
				"message" => $messageObj,
				"sender" => $sender
			));
		}
	}
	
	public function send_group_message() {
		$groupID = intval($this->input->post('group_id'));
		$userID = intval($this->input->post('user_id'));
		$message = $this->input->post('message');
		$messageType = $this->input->post('message_type');
		$date = Util::get_local_date();
		$this->db->insert("group_messages", array(
			"group_id" => $groupID,
			"user_id" => $userID,
			"message" => $message,
			"message_type" => $messageType,
			"date" => $date
		));
		$messageID = intval($this->db->insert_id());
		$messageObj = $this->db->query("SELECT * FROM `group_messages` WHERE `id`=" . $messageID)->row_array();
		$messageObj['user'] = $this->db->query("SELECT * FROM `users` WHERE `id`=".$userID)->row_array();
		$status = "unsent";
		$statuses = $this->db->query("SELECT * FROM `read_group_message_statuses` WHERE `user_id`=".$userID." AND `group_id`=".$groupID." AND `group_message_id`=".$messageObj['id'])
			->result_array();
		if (sizeof($statuses) > 0) {
			$status = $statuses[0]['status'];
		}
		$messageObj['status'] = $status;
		$user = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $userID)->row_array();
		$this->db->insert("read_group_message_statuses", array(
			"user_id" => $userID,
			"group_id" => $groupID,
			"group_message_id" => $messageID,
			"status" => "read"
		));
		$group = $this->db->query("SELECT * FROM `groups` WHERE `id`=".$groupID)->row_array();
		/*$groupMembers = $this->db->query("SELECT * FROM `group_members` WHERE `group_id`=".$groupID." AND `exited`=0")->result_array();
		for ($i=0; $i<sizeof($groupMembers); $i++) {
			$groupMember = $this->db->query("SELECT * FROM `users` WHERE `id`=".$groupMembers[$i]['user_id'])->row_array();
			if ($groupMember != NULL) {
				Util::send_message($this, intval($groupMember['id']), $groupMember['name'], $message,
					json_encode(array(
						"type" => "group_message",
						"group_id" => "".$groupID,
						"message_id" => "".$messageID,
						"user_id" => "".$userID,
						"click_action" => "FLUTTER_NOTIFICATION_CLICK",
			    		"screen_path" => "/group_chat",
			    		"screen_name" => "GroupChat",
			    		"group" => $group
					)));
			}
		}*/
		$payload = json_encode(array(
				"type" => "group_message",
				"group_id" => "".$groupID,
				"message_id" => "".$messageID,
				"user_id" => "".$userID,
				"unread_messages" => "".$this->get_group_unread_messages_count($userID, $groupID),
				"click_action" => "FLUTTER_NOTIFICATION_CLICK",
	    		"screen_path" => "/group_chat",
	    		"screen_name" => "GroupChat",
	    		"group" => $group
			));
		Util::insert_notification($this, 0, true, "group_".$groupID, "inbox", $group['title'], $user['name'].": ".$message, $payload, $date);
		$notificationChannel = $this->db->query("SELECT * FROM `group_notification_channels` WHERE `user_id`=".$userID." AND `group_id`=".$groupID)->row_array();
		if ($notificationChannel != NULL) {
			Util::send_message_to_topic($this, $notificationChannel['channel_id'], $group['title'], $user['name'].": ".$message, $payload);
		}
		$groupMembers = $this->db->query("SELECT * FROM `group_members` WHERE `group_id`=".$groupID)->result_array();
		for ($i=0; $i<sizeof($groupMembers); $i++) {
			$groupChatNotification = false;
			$userSetting = $this->db->query("SELECT * FROM `user_settings` WHERE `user_id`=".$groupMembers[$i]['user_id']." AND `name`='group_chat_notification'")->row_array();
			if ($userSetting != NULL) {
				$groupChatNotification = intval($userSetting['value'])==1?true:false;
			}
			if (!$groupChatNotification) {
				Util::send_message_to_topic_no_notification($this, "group_".$groupID, $payload);
			}
		}
		echo json_encode($messageObj);
	}
	
	public function get_total_sent_group_messages() {
		$userID = intval($this->input->post('user_id'));
		$groupID = intval($this->input->post('group_id'));
		$groupMessages = $this->db->query("SELECT * FROM `group_messages` WHERE `group_id`=".$groupID)->result_array();
		$totalsent = 0;
		for ($i=0; $i<sizeof($groupMessages); $i++) {
			$statuses = $this->db->query("SELECT * FROM `read_group_message_statuses` WHERE `user_id`=".$userID." AND `group_id`=".$groupID." AND `group_message_id`=".$groupMessages[$i]['id'])
				->result_array();
			if (sizeof($statuses) > 0) {
				$status = $statuses[0];
				if ($status['status'] == 'sent') {
					$totalsent++;
				}
			}
		}
		echo $totalsent;
	}
	
	public function send_group_document() {
		$groupID = intval($this->input->post('group_id'));
		$userID = intval($this->input->post('user_id'));
		$documentName = $this->input->post('document_name');
		$documentDuration = intval($this->input->post('document_duration'));
		$thumbnailUploaded = intval($this->input->post('thumbnail_uploaded'));
		$dataType = $this->input->post('data_type');
		$date = Util::get_local_date();
		$config = array(
			'upload_path' => './userdata/',
			'allowed_types' => "*",
			'overwrite' => TRUE,
			'max_size' => 10240
		);
		$group = $this->db->query("SELECT * FROM `groups` WHERE `id`=".$groupID)->row_array();
		$this->load->library('upload', $config);
		if ($this->upload->do_upload('document')) {
			$this->db->insert("group_messages", array(
				"group_id" => $groupID,
				"user_id" => $userID,
				"message_type" => $dataType,
				"document_name" => $documentName,
				"document_path" => $this->upload->data()['file_name'],
				"document_duration" => $documentDuration,
				"date" => $date
			));
			$messageID = intval($this->db->insert_id());
			if ($thumbnailUploaded == 1) {
				if ($this->upload->do_upload('thumbnail')) {
					$this->db->where('id', $messageID);
					$this->db->update("group_messages", array(
						"document_thumbnail" => $this->upload->data()['file_name']
					));
				}
			}
			$messageObj = $this->db->query("SELECT * FROM `group_messages` WHERE `id`=" . $messageID)->row_array();
			$messageObj['user'] = $this->db->query("SELECT * FROM `users` WHERE `id`=".$userID)->row_array();
			$sender = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $userID)->row_array();
			$this->db->insert("read_group_message_statuses", array(
				"user_id" => $userID,
				"group_id" => $groupID,
				"group_message_id" => $messageID,
				"status" => "unsent"
			));
			$langCode = $sender['lang_code'];
			$title = "Dokumen diterima";
			if ($langCode == "en") {
				$title = "Picture received";
			}
			$symbol = "📎";
			if ($dataType == "image") {
				$symbol = "🖼";
			} else if ($dataType == "audio") {
				$symbol = "🎵";
			} else if ($dataType == "video") {
				$symbol = "📹";
			}
			$payload = json_encode(array(
				"type" => "group_message",
				"group_id" => "".$groupID,
				"message_id" => "".$messageID,
				"unread_messages" => "".$this->get_group_unread_messages_count($userID, $groupID)
			));
			Util::insert_notification($this, 0, true, "group_".$groupID, "inbox", $group['title'], $sender['name'].": ".$symbol, $payload, $date);
			Util::send_message_to_topic($this, "group_".$groupID, $group['title'], $sender['name'].": ".$symbol, $payload);
			/*$groupMembers = $this->db->query("SELECT * FROM `group_members` WHERE `group_id`=".$groupID." AND `exited`=0")->result_array();
			for ($i=0; $i<sizeof($groupMembers); $i++) {
				$groupMember = $this->db->query("SELECT * FROM `users` WHERE `id`=".$groupMembers[$i]['user_id'])->row_array();
				if ($groupMember != NULL) {
					Util::send_message($this, intval($groupMember['id']), $sender['name'], $title,
						json_encode(array(
							"type" => "group_message",
							"group_id" => "".$groupID,
							"message_id" => "".$messageID,
							"click_action" => "FLUTTER_NOTIFICATION_CLICK",
				    		"screen_path" => "/group_chat",
				    		"screen_name" => "GroupChat",
				    		"group" => $group
						)));
				}
			}*/
			echo json_encode(array(
				"type" => "group_message",
				"group_id" => $groupID,
				"message" => $messageObj,
				"sender" => $sender
			));
		} else {
			echo json_encode($this->upload->display_errors());
		}
	}
	
	public function update_lang_code() {
		$userID = intval($this->input->post('user_id'));
		$langCode = $this->input->post('lang_code');
		$this->db->query("UPDATE `users` SET `lang_code`='".$langCode."' WHERE `id`=".$userID);
	}
	
	public function send_location() {
		$chatID = intval($this->input->post('chat_id'));
		$lat = doubleval($this->input->post('lat'));
		$lng = doubleval($this->input->post('lng'));
		$address = $this->input->post('address');
		$senderID = intval($this->input->post('sender_id'));
		$receiverID = intval($this->input->post('receiver_id'));
		$messageType = $this->input->post('message_type');
		$date = Util::get_local_date();
		$this->db->insert("messages", array(
			"chat_id" => $chatID,
			"sender_id" => $senderID,
			"receiver_id" => $receiverID,
			"latitude" => $lat,
			"longitude" => $lng,
			"address" => $address,
			"message_type" => $messageType,
			"date" => $date
		));
	}
	
	public function send_group_location() {
		$groupID = intval($this->input->post('group_id'));
		$lat = doubleval($this->input->post('lat'));
		$lng = doubleval($this->input->post('lng'));
		$address = $this->input->post('address');
		$userID = intval($this->input->post('user_id'));
		$messageType = $this->input->post('message_type');
		$date = Util::get_local_date();
		$this->db->insert("group_messages", array(
			"chat_id" => $chatID,
			"sender_id" => $senderID,
			"receiver_id" => $receiverID,
			"latitude" => $lat,
			"longitude" => $lng,
			"address" => $address,
			"message_type" => $messageType,
			"date" => $date
		));
	}
	
	public function get_private_messages_by_chat_id() {
		$userID = intval($this->input->post('user_id'));
		$chatID = intval($this->input->post('chat_id'));
		$start = intval($this->input->post('start'));
		$length = intval($this->input->post('length'));
		$privateMessages = $this->db->query("SELECT * FROM `messages` WHERE `chat_id`=" . $chatID . " ORDER BY `date` DESC LIMIT " . $start . "," . $length)->result_array();
		for ($i=0; $i<sizeof($privateMessages); $i++) {
			$deletedMessages = $this->db->query("SELECT * FROM `deleted_messages` WHERE `message_id`=".$privateMessages[$i]['id']." AND `user_id`=".$userID)->result_array();
			if (sizeof($deletedMessages) > 0) {
				$privateMessages[$i]['deleted'] = 1;
			} else {
				$privateMessages[$i]['deleted'] = 0;
			}
			$privateMessages[$i]['sender'] = $this->db->query("SELECT * FROM `users` WHERE `id`=".$privateMessages[$i]['sender_id'])->row_array();
			$privateMessages[$i]['receiver'] = $this->db->query("SELECT * FROM `users` WHERE `id`=".$privateMessages[$i]['receiver_id'])->row_array();
		}
		echo json_encode($privateMessages);
	}
	
	public function update_message_read_status() {
		$id = intval($this->input->post('id'));
		$status = $this->input->post('status');
		$this->db->query("UPDATE `read_message_statuses` SET `status`='" . $status . "' WHERE `id`=" . $id);
	}
	
	public function update_location() {
		$userID = intval($this->input->post('user_id'));
		$lat = doubleval($this->input->post('lat'));
		$lng = doubleval($this->input->post('lng'));
		$location = $this->input->post('location');
		$this->db->query("UPDATE `users` SET `lat`=" . $lat . ", `lng`=" . $lng . ", `current_location`='" . $location . "' WHERE `id`=" . $userID);
		$groups = $this->get_groups_($userID);
		$user = $this->db->query("SELECT * FROM `users` WHERE `id`=".$userID)->row_array();
		for ($i=0; $i<sizeof($groups); $i++) {
			$group = $groups[$i];
			$places = $this->db->query("SELECT * FROM `places` WHERE `group_id`=".$group['id'])->result_array();
			$nearestDistance = -1;
			$nearestPlaceID = 0;
			for ($j=0; $j<sizeof($places); $j++) {
				$place = $places[$j];
				$distance = Util::get_distance($lat, $lng, doubleval($place['latitude']), doubleval($place['longitude']));
				if ($nearestDistance == -1) {
					$nearestDistance = $distance;
					$nearestPlaceID = intval($place['id']);
				} else {
					if ($distance < $nearestDistance) {
						$nearestDistance = $distance;
						$nearestPlaceID = intval($place['id']);
					}
				}
			}
			for ($j=0; $j<sizeof($places); $j++) {
				$place = $places[$j];
				if ($nearestPlaceID == intval($place['id'])) {
					$radius = doubleval($place['radius']);
					$distance = Util::get_distance($lat, $lng, doubleval($place['latitude']), doubleval($place['longitude']));
					$inRadius = $this->db->query("SELECT * FROM `in_radius_users` WHERE `place_id`=".$place['id']." AND `user_id`=".$userID)->result_array();
					$isInRadius = false;
					if (sizeof($inRadius) > 0) {
						$isInRadius = intval($inRadius[0]['is_in_radius'])==1?true:false;
					} else {
						$this->db->insert("in_radius_users", array(
							"place_id" => $place['id'],
							"user_id" => $userID,
							"is_in_radius" => 0
						));
					}
					/*if ($distance <= $radius) {
						$this->db->query("UPDATE `in_radius_users` SET `is_in_radius`=1 WHERE `place_id`=".$place['id']." AND `user_id`=".$userID);
						if (!$isInRadius) {
							Util::send_message_to_topic($this, "group_".$group['id'], $group['title'], $user['name']." ".StringDB::get_with_lang_code("id", "text4")." ".$place['place_name'],
								json_encode(array(
									"type" => "user_entered_radius",
									"group_id" => "".$group['id'],
									"user_id" => "".$userID,
									"place" => $place
								)));
							break;
						}
					} else {
						$this->db->query("UPDATE `in_radius_users` SET `is_in_radius`=0 WHERE `place_id`=".$place['id']." AND `user_id`=".$userID);
						if ($isInRadius) {
							Util::send_message_to_topic($this, "group_".$group['id'], $group['title'], $user['name']." ".StringDB::get_with_lang_code("id", "text4")." ".$place['place_name'],
								json_encode(array(
									"type" => "user_exited_radius",
									"group_id" => "".$group['id'],
									"user_id" => "".$userID,
									"place" => $place
								)));
								break;
						}
					}*/
					break;
				}
			}
		}
	}
	
	public function update_biography() {
		$userID = intval($this->input->post('user_id'));
		$ktpImageChanged = intval($this->input->post('ktp_image_changed'));
		$passportImageChanged = intval($this->input->post('passport_image_changed'));
		$vaccinationCertificateImageChanged = intval($this->input->post('vaccination_certificate_image_changed'));
		$golDarah = $this->input->post('gol_darah');
		$penyakitKhusus = $this->input->post('penyakit_khusus');
		$obatKhusus = $this->input->post('obat_khusus');
		$alergi = $this->input->post('alergi');
		$config = array(
			'upload_path' => './userdata/',
			'allowed_types' => "*",
			'overwrite' => TRUE,
			'max_size' => 10240
		);
		$this->load->library('upload', $config);
		if ($ktpImageChanged == 1) {
			if ($this->upload->do_upload('ktp_image')) {
				$this->db->query("UPDATE `users` SET `ktp_image`='" . $this->upload->data()['file_name'] . "' WHERE `id`=" . $userID);
			}
		}
		if ($passportImageChanged == 1) {
			if ($this->upload->do_upload('passport_image')) {
				$this->db->query("UPDATE `users` SET `passport_image`='" . $this->upload->data()['file_name'] . "' WHERE `id`=" . $userID);
			}
		}
		if ($vaccinationCertificateImageChanged == 1) {
			if ($this->upload->do_upload('vaccination_certificate_image')) {
				$this->db->query("UPDATE `users` SET `vaccination_certificate_image`='" . $this->upload->data()['file_name'] . "' WHERE `id`=" . $userID);
			}
		}
		$this->db->query("UPDATE `users` SET `gol_darah`='" . $golDarah . "', `penyakit_khusus`='" . $penyakitKhusus . "', `obat_khusus`='" . $obatKhusus . "', `alergi`='" . $alergi . "' WHERE `id`=" . $userID);
		$user = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $userID)->row_array();
		echo json_encode($user);
	}
	
	public function signup() {
		$name = $this->input->post('name');
		$email = $this->input->post('email');
		$phone = $this->input->post('phone');
		$password = $this->input->post('password');
		$date = Util::get_local_date();
		$users = $this->db->query("SELECT * FROM `users` WHERE `email`='" . $email . "'")->result_array();
		if (sizeof($users) > 0) {
			echo json_encode(array('response_code' => -1));
		} else {
			$users = $this->db->query("SELECT * FROM `users` WHERE `phone`='" . $phone . "'")->result_array();
			if (sizeof($users) > 0) {
				echo json_encode(array('response_code' => -2));
				return;
			}
			$token = base64_encode(Util::generateUUID());
			$this->db->insert("users", array(
				"email" => $email,
				"password" => $password,
				"phone" => $phone,
				"name" => $name,
				"verification_token" => $token,
				"verification_token_date" => $date,
				"xmpp_password" => Util::generateUUID()
			));
			$userID = $this->db->insert_id();
			$this->load->helper('file');
			$verifData = file_get_contents('userdata/verification_email_template.html');
			$verifData = str_replace("[EMAIL]", $email, $verifData);
			$verifData = str_replace("[VERIFICATION_TOKEN]", $token, $verifData);
			$data = "api_key=".Constants::$API_KEY."&api_secret=".Constants::$API_SECRET."&action=verify_email&user_id=".$userID;
			$verifData = str_replace("[PAYLOAD]", urlencode(base64_encode(json_encode(array(
				"action" => "verify_email",
				"token" => Util::generateHMAC($userID, "verify_email", $data),
				"user_id" => $userID,
				"data" => array(
					"email" => $email,
					"verification_token" => $token
				)
			)))), $verifData);
			$key = utf8_encode(Constants::$API_SECRET);
			$bytes = utf8_encode($data);
			$hmacSha256 = hash_hmac('sha256', $bytes, $key);
			$subject = 'Verifikasi Alamat Email Anda';
			$headers = "From: admin@localhost\n";
			$headers .= "Reply-To: admin@localhost\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=UTF-8";
			$message = $verifData;
			mail($email, $subject, $message, $headers);
			echo json_encode(array('response_code' => 1));
		}
	}
	
	public function set_email_verified() {
		$token = $this->input->get('token');
		$this->db->query("UPDATE `users` SET `email_verified`=1 WHERE `verification_token`='".$token."'");
		$this->load->view("email_verified");
	}
	
	public function send_reset_password_email() {
		$email = $this->input->post('email');
		$users = $this->db->query("SELECT * FROM `users` WHERE `email`='" . $email . "'")->result_array();
		if (sizeof($users) > 0) {
			$verifData = file_get_contents(base_url() . "userdata/forgot_password_email_template.html");
			$userID = $users[0]['id'];
			$data = "api_key=".Constants::$API_KEY."&api_secret=".Constants::$API_SECRET."&action=reset_password&user_id=".$userID;
			$verifData = str_replace("[PAYLOAD]", urlencode(base64_encode(json_encode(array(
				"action" => "reset_password",
				"token" => Util::generateHMAC($userID, "reset_password", $data),
				"user_id" => $userID,
				"data" => array(
					"email" => $email
				)
			)))), $verifData);
			$key = utf8_encode(Constants::$API_SECRET);
			$bytes = utf8_encode($data);
			$hmacSha256 = hash_hmac('sha256', $bytes, $key);
			$subject = 'Atur Ulang Kata Sandi Anda';
			$headers = "From: admin@localhost\r\n\n";
			$headers .= "Reply-To: admin@localhost\r\n\n";
			$headers .= "MIME-Version: 1.0\r\n\n";
			$headers .= "Content-Type: text/html; charset=UTF-8\r\n\n";
			$message = $verifData;
			mail($email, $subject, $message, $headers);
		}
	}

	public function sign_in_with_google() {
		$googleUID = $this->input->post('google_uid');
		$name = $this->input->post('name');
		$photo = $this->input->post('photo');
		$email = $this->input->post('email');
		$tempPassword = base64_encode(Util::generateUUID());
		$users = $this->db->query("SELECT * FROM `users` WHERE `google_uid`='" . $googleUID . "'")->result_array();
		if (sizeof($users) > 0) {
			$user = $users[0];
			$this->db->query("UPDATE `users` SET `temp_password`='" . $tempPassword . "' WHERE `id`=" . $user['id']);
			$user['temp_password'] = $tempPassword;
			$user['response_code'] = 1;
			echo json_encode($user);
		} else {
			$users = $this->db->query("SELECT * FROM `users` WHERE `email`='" . $email . "'")->result_array();
			if (sizeof($users) > 0) {
				$user = $users[0];
			} else {
				$this->db->insert("users", array(
					"google_uid" => $googleUID,
					"name" => $name,
					"photo" => $photo,
					"email" => $email,
					"email_verified" => 1,
					"temp_password" => $tempPassword,
					"xmpp_password" => Util::generateUUID()
				));
				$id = $this->db->insert_id();
				$user = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $id)->row_array();
			}
			$user['response_code'] = 1;
			echo json_encode($user);
		}
	}

	public function sign_in_with_facebook() {
		$facebookUID = $this->input->post('facebook_uid');
		$name = $this->input->post('name');
		$photo = $this->input->post('photo');
		$email = $this->input->post('email');
		$tempPassword = base64_encode(Util::generateUUID());
		$users = $this->db->query("SELECT * FROM `users` WHERE `facebook_uid`='" . $facebookUID . "'")->result_array();
		if (sizeof($users) > 0) {
			$user = $users[0];
			$this->db->query("UPDATE `users` SET `temp_password`='" . $tempPassword . "' WHERE `id`=" . $user['id']);
			$user['temp_password'] = $tempPassword;
			$user['response_code'] = 1;
			echo json_encode($user);
		} else {
			$users = $this->db->query("SELECT * FROM `users` WHERE `email`='" . $email . "'")->result_array();
			if (sizeof($users) > 0) {
				$user = $users[0];
			} else {
				$this->db->insert("users", array(
					"facebook_uid" => $facebookUID,
					"name" => $name,
					"photo" => $photo,
					"email" => $email,
					"email_verified" => 1,
					"temp_password" => $tempPassword,
					"xmpp_password" => Util::generateUUID()
				));
				$id = $this->db->insert_id();
				$user = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $id)->row_array();
			}
			$user['response_code'] = 1;
			echo json_encode($user);
		}
	}
	
	public function verify() {
		header("Location: https://play.google.com/store/apps/details?id=com.test.appumroh");
	}
	
	public function verify_email() {
		$email = $this->input->post("email");
		$token = $this->input->post("token");
		$users = $this->db->query("SELECT * FROM `users` WHERE `email`='" . $email . "'")->result_array();
		if (sizeof($users) > 0) {
			$user = $users[0];
			if ($token == $user['verification_token']) {
				$verificationTokenDate = new DateTime($user['verification_token_date']);
				$currentDate = new DateTime(date("Y-m-d H:i:s"));
				$diffHours = intval($verificationTokenDate->diff($currentDate)->format("%h"));
				if ($diffHours >= 24) {
					echo json_encode(array('response_code' => -1));
				} else {
					$this->db->query("UPDATE `users` SET `email_verified`=1 WHERE `id`=" . $user['id']);
					echo json_encode(array('response_code' => 1));
				}
			} else {
				echo json_encode(array('response_code' => -2));
			}
		} else {
			echo json_encode(array('response_code' => -3));
		}
	}
	
	public function request_delete_data() {
	}
	
	public function reset_password() {
		$id = intval($this->input->post('id'));
		$password = $this->input->post('password');
		$this->db->query("UPDATE `users` SET `password`='" . $password . "' WHERE `id`=" . $id);
	}
	
	public function get_last_unique_id() {
		$groups = $this->db->query("SELECT * FROM `groups` ORDER BY CONVERT(`unique_id`, SIGNED INTEGER) DESC LIMIT 1")->result_array();
		if (sizeof($groups) > 0) {
			$group = $groups[0];
			return intval($group['unique_id'])+1;
		} else {
			return "0087456";
		}
	}
	
	public function update_fcm_key() {
		$userID = intval($this->input->post('user_id'));
		$fcmKey = $this->input->post('fcm_key');
		$this->db->query("UPDATE `users` SET `fcm_key`='" . $fcmKey . "' WHERE `id`=" . $userID);
	}
	
	public function update_pushy_token() {
		$userID = intval($this->input->post('user_id'));
		$pushyToken = $this->input->post('pushy_token');
		$this->db->query("UPDATE `users` SET `pushy_token`='" . $pushyToken . "' WHERE `id`=" . $userID);
	}
	
	public function notify_group() {
		$userID = intval($this->input->post('user_id'));
		$groupID = intval($this->input->post('group_id'));
		$date = Util::get_local_date();
		$user = array();
		$markerID = Util::generateUUID();
		$this->db->query("UPDATE `users` SET `panic_marker_id`='" . $markerID . "' WHERE `id`=" . $userID);
		$panicUser = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $userID)->row_array();
		$users = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $userID)->result_array();
		if (sizeof($users) > 0) {
			$user = $users[0];
		}
		$this->db->query("UPDATE `group_members` SET `is_panic`=1 WHERE `group_id`=".$groupID." AND `user_id`=".$userID);
		/*$groupMembers = $this->db->query("SELECT * FROM `group_members` WHERE `group_id`=" . $groupID . " AND `exited`=0")->result_array();
		for ($i=0; $i<sizeof($groupMembers); $i++) {
			$groupMember = $groupMembers[$i];
			$memberUserID = intval($groupMember['user_id']);
			if ($memberUserID != $userID) {
				$user = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $memberUserID)->row_array();
				Util::send_message($this, intval($user['id']), "Ada peserta sedang membutuhkan bantuan", "Klik untuk melihat lokasi peserta sekarang",
					json_encode(array(
						"type" => "panic",
						"user_id" => "" . $userID,
						"user" => $user,
						"panic_user" => $panicUser,
						"click_action" => "FLUTTER_NOTIFICATION_CLICK",
			    		"screen_path" => "/activity",
			    		"screen_name" => "Activity",
			    		"group_id" => "".$groupID,
			    		"panic_user_id" => "".$userID
					)));
			}
		}*/
		$this->db->query("DELETE FROM `group_panic_users` WHERE `user_id`=".$userID." AND `group_id`=".$groupID);
		$this->db->insert("group_panic_users", array(
			"user_id" => $userID,
			"group_id" => $groupID
		));
		$payload = json_encode(array(
				"type" => "panic",
				"user_id" => "" . $userID,
				"group_id" => "" . $groupID,
				"user" => $user,
				"panic_user" => $panicUser
			));
		Util::insert_notification($this, 0, true, "group_".$groupID, "inbox", "Ada peserta sedang membutuhkan bantuan", "Klik untuk melihat lokasi peserta sekarang", $payload, $date);
		Util::send_message_to_topic($this, "group_".$groupID, "Ada peserta sedang membutuhkan bantuan", "Klik untuk melihat lokasi peserta sekarang", $payload);
		echo json_encode(array(
			'response_code' => 1,
			'marker_id' => $markerID
		));
	}
	
	public function unnotify_group() {
		$userID = intval($this->input->post('user_id'));
		$groupID = intval($this->input->post('group_id'));
		$date = Util::get_local_date();
		$user = array();
		$panicUser = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $userID)->row_array();
		$users = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $userID)->result_array();
		if (sizeof($users) > 0) {
			$user = $users[0];
		}
		$this->db->query("UPDATE `group_members` SET `is_panic`=0 WHERE `group_id`=".$groupID." AND `user_id`=".$userID);
		$this->db->query("UPDATE `users` SET `panic_marker_id`=NULL WHERE `id`=" . $userID);
		$groupMembers = $this->db->query("SELECT * FROM `group_members` WHERE `group_id`=" . $groupID . " AND `exited`=0")->result_array();
		/*for ($i=0; $i<sizeof($groupMembers); $i++) {
			$groupMember = $groupMembers[$i];
			$memberUserID = intval($groupMember['user_id']);
			if ($memberUserID != $userID) {
				$user = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $memberUserID)->row_array();
				Util::send_message($this, intval($user['id']), "Ada peserta sedang membutuhkan bantuan", "Klik untuk melihat lokasi peserta sekarang",
					json_encode(array(
						"type" => "panic",
						"user_id" => "" . $userID,
						"user" => $user,
						"panic_user" => $panicUser,
						"click_action" => "FLUTTER_NOTIFICATION_CLICK",
			    		"screen_path" => "/activity",
			    		"screen_name" => "Activity",
			    		"group_id" => "".$groupID,
			    		"panic_user_id" => "".$userID
					)));
			}
		}*/
		$this->db->query("DELETE FROM `group_panic_users` WHERE `user_id`=".$userID." AND `group_id`=".$groupID);
		$payload = json_encode(array(
				"type" => "stop_panic",
				"user_id" => "" . $userID,
				"group_id" => "" . $groupID,
				"user" => $user,
				"panic_user" => $panicUser
			));
		Util::insert_notification($this, 0, true, "group_".$groupID, "panic", "Ada peserta sedang membutuhkan bantuan", "Klik untuk melihat lokasi peserta sekarang", $payload, $date);
		Util::send_message_to_topic_no_notification($this, "group_".$groupID, $payload);
	}
	
	public function update_panic_marker_id() {
		$userID = intval($this->input->post('user_id'));
		$markerID = $this->input->post('panic_marker_id');
		$this->db->query("UPDATE `users` SET `panic_marker_id`='" . $markerID . "' WHERE `id`=" . $userID);
	}
	
	public function park_driver() {
		$groupID = intval($this->input->post('group_id'));
		$userID = intval($this->input->post('user_id'));
		$lat = doubleval($this->input->post('lat'));
		$lng = doubleval($this->input->post('lng'));
		$date = Util::get_local_date();
		$this->db->query("UPDATE `users` SET `driver_status`='parkir', `parking_lat`=" . $lat . ", `parking_lng`=" . $lng . " WHERE `id`=" . $userID);
		$payload = json_encode(array(
			"group_id" => "".$groupID,
			"user_id" => "".$userID,
			"type" => "bus_parked",
			"click_action" => "FLUTTER_NOTIFICATION_CLICK",
			"screen_path" => "/activity",
			"screen_name" => "Activity",
			"group_id" => "".$groupID,
			"panic_user_id" => "0"
		));
		Util::insert_notification($this, 0, true, "group_".$groupID, "info", StringDB::get_with_lang_code("id", "text6"), $message, $payload, $date);
		Util::send_message_to_topic($this, "group_".$groupID, StringDB::get_with_lang_code("id", "text6"), $message, $payload);
	}
	
	public function unpark() {
		$groupID = intval($this->input->post('group_id'));
		$userID = intval($this->input->post('user_id'));
		$date = Util::get_local_date();
		$this->db->query("UPDATE `users` SET `driver_status`='berjalan' WHERE `id`=" . $userID);
		$payload = json_encode(array(
			"group_id" => "".$groupID,
			"user_id" => "".$userID,
			"type" => "bus_unparked",
			"click_action" => "FLUTTER_NOTIFICATION_CLICK",
			"screen_path" => "/activity",
			"screen_name" => "Activity",
			"group_id" => "".$groupID,
			"panic_user_id" => "0"
		));
		Util::insert_notification($this, 0, true, "group_".$groupID, "info", StringDB::get_with_lang_code("id", "text6"), $message, $payload, $date);
		Util::send_message_to_topic($this, "group_".$groupID, StringDB::get_with_lang_code("id", "text6"), $message, $payload);
	}
	
	public function broadcast_message() {
		$userID = intval($this->input->post('user_id'));
		$groupID = intval($this->input->post('group_id'));
		$message = $this->input->post('message');
		$date = Util::get_local_date();
		$sender = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $userID)->row_array();
		$id = Util::generateUUID();
		$group = $this->db->query("SELECT * FROM `groups` WHERE `id`=".$groupID)->row_array();
		if ($group != NULL) {
			$payload = json_encode(array(
				"id" => $id,
				"group_id" => "".$groupID,
				"type" => "broadcast",
				"user_id" => "" . $userID,
				"sender" => $sender,
				"message" => $message,
				"date" => $date,
				"click_action" => "FLUTTER_NOTIFICATION_CLICK",
				"screen_path" => "/activity",
				"screen_name" => "Activity",
				"group_id" => "".$groupID,
				"panic_user_id" => "0"
			));
			Util::insert_notification($this, 0, true, "group_".$groupID, "broadcast", $group['title'], "---".StringDB::get_with_lang_code("id", "text6")."---\n".$message, $payload, $date);
			Util::send_message_to_topic($this, "group_".$groupID, $group['title'], "---".StringDB::get_with_lang_code("id", "text6")."---\n".$message, $payload);
		}
		echo $id;
	}
	
	public function get_broadcasts() {
		$userID = intval($this->input->post('user_id'));
		$broadcasts = $this->db->query("SELECT * FROM `broadcasts` WHERE `user_id`=".$userID." ORDER BY `date` DESC")->result_array();
		for ($i=0; $i<sizeof($broadcasts); $i++) {
			$senderID = $broadcasts[$i]['sender_id'];
			if ($senderID != null && trim($senderID) != "null" && trim($senderID) != "") {
				$broadcasts[$i]['sender'] = $this->db->query("SELECT * FROM `users` WHERE `id`=".$senderID)->row_array();
			} else {
				$broadcasts[$i]['sender'] = null;
			}
		}
		echo json_encode($broadcasts);
	}
	
	public function get_broadcasts_by_group_id() {
		$userID = intval($this->input->post('user_id'));
		$groupID = intval($this->input->post('group_id'));
		$broadcasts = $this->db->query("SELECT * FROM `broadcasts` WHERE `user_id`=".$userID." AND `group_id`=".$groupID." ORDER BY `date` DESC")->result_array();
		for ($i=0; $i<sizeof($broadcasts); $i++) {
			$senderID = $broadcasts[$i]['sender_id'];
			if ($senderID != null && trim($senderID) != "null" && trim($senderID) != "") {
				$broadcasts[$i]['sender'] = $this->db->query("SELECT * FROM `users` WHERE `id`=".$senderID)->row_array();
			} else {
				$broadcasts[$i]['sender'] = null;
			}
		}
		echo json_encode($broadcasts);
	}
	
	public function get_open_broadcasts() {
		$userID = intval($this->input->post('user_id'));
		$broadcasts = $this->db->query("SELECT * FROM `broadcasts` WHERE `user_id`=".$userID." AND `closed`=0 ORDER BY `date` DESC")->result_array();
		for ($i=0; $i<sizeof($broadcasts); $i++) {
			$senderID = $broadcasts[$i]['sender_id'];
			if ($senderID != null && trim($senderID) != "null" && trim($senderID) != "") {
				$broadcasts[$i]['sender'] = $this->db->query("SELECT * FROM `users` WHERE `id`=".$senderID)->row_array();
			} else {
				$broadcasts[$i]['sender'] = null;
			}
		}
		echo json_encode($broadcasts);
	}
	
	public function add_broadcast() {
		$broadcastID = $this->input->post('broadcast_id');
		$userID = intval($this->input->post('user_id'));
		$senderID = intval($this->input->post('sender_id'));
		$groupID = intval($this->input->post('group_id'));
		$type = $this->input->post('type');
		$title = $this->input->post('title');
		$messagePlain = $this->input->post('message_plain');
		$messageBold = $this->input->post('message_bold');
		$closed = intval($this->input->post('closed'));
		$date = Util::get_local_date();
		$this->db->insert("broadcasts", array(
			"broadcast_id" => $broadcastID,
			"user_id" => $userID,
			"sender_id" => $senderID,
			"group_id" => $groupID,
			"type" => $type,
			"message_plain" => $messagePlain,
			"message_bold" => $messageBold,
			"closed" => $closed,
			"date" => $date
		));
		echo "Broadcast ID: ".$broadcastID.", date: ".$date."\n";
	}
	
	public function delete_broadcast() {
		$id = intval($this->input->post('id'));
		$this->db->query("DELETE FROM `broadcasts` WHERE `id`=".$id);
	}
	
	public function close_broadcast() {
		$id = intval($this->input->post('id'));
		$this->db->query("UPDATE `broadcasts` SET `closed`=1 WHERE `id`=".$id);
	}
	
	public function get_panduan_haji() {
		$panduan = $this->db->query("SELECT * FROM `panduan_haji`")->result_array();
		echo json_encode(array(
			"video_path" => $this->db->query("SELECT * FROM `settings`")->row_array()['panduan_haji_video_path'],
			"panduan" => $panduan
		));
	}
	
	public function get_petunjuk_ihram() {
		$type = $this->input->post('type');
		echo json_encode($this->db->query("SELECT * FROM `petunjuk_ihram` WHERE `type`='" . $type . "'")->result_array());
	}
	
	public function get_cara_memakai_ihram() {
		echo json_encode(array(
			"male" => $this->db->query("SELECT * FROM `cara_memakai_ihram` WHERE `gender`='male'")->result_array(),
			"female" => $this->db->query("SELECT * FROM `cara_memakai_ihram` WHERE `gender`='female'")->result_array()
		));
	}
	
	public function get_bacaan_talbiyah() {
		echo json_encode($this->db->query("SELECT * FROM `bacaan_talbiyah`")->row_array());
	}
	
	public function get_perjalanan_haji() {
		$perjalananHaji = $this->db->query("SELECT * FROM `perjalanan_haji`")->row_array();
		echo json_encode($perjalananHaji);
	}
	
	public function get_barang_rekomendasi() {
		$barang = $this->db->query("SELECT * FROM `barang_rekomendasi` ")->result_array();
		$description = $this->db->query("SELECT * FROM `settings`")->row_array()['barang_rekomendasi_description'];
		echo json_encode(array(
			"description" => $description,
			"barang" => $barang
		));
	}
	
	public function get_sholat_sunnah() {
		$sholats = $this->db->query("SELECT * FROM `sholat_sunnah`")->result_array();
		for ($i=0; $i<sizeof($sholats); $i++) {
			$sholats[$i]['hukum'] = $this->db->query("SELECT * FROM `hukum_sholat` WHERE `sholat_sunnah_id`=" . $sholats[$i]['id'])->row_array();
			$sholats[$i]['waktu'] = $this->db->query("SELECT * FROM `waktu_sholat` WHERE `sholat_sunnah_id`=" . $sholats[$i]['id'])->row_array();
			$sholats[$i]['rakaat'] = $this->db->query("SELECT * FROM `rakaat_sholat` WHERE `sholat_sunnah_id`=" . $sholats[$i]['id'])->row_array();
			$sholats[$i]['doa'] = $this->db->query("SELECT * FROM `doa_sholat` WHERE `sholat_sunnah_id`=" . $sholats[$i]['id'])->row_array();
			$sholats[$i]['keutamaan'] = $this->db->query("SELECT * FROM `keutamaan_sholat` WHERE `sholat_sunnah_id`=" . $sholats[$i]['id'])->row_array();
		}
		echo json_encode($sholats);
	}
	
	public function get_doa_harian() {
		$doaHarian = $this->db->query("SELECT * FROM `doa_harian`")->result_array();
		echo json_encode($doaHarian);
	}
	
	public function check_users_registered() {
		$myUserID = intval($this->input->post('my_user_id'));
		$users = json_decode($this->input->post('users'), true);
		$checkedUsers = [];
		for ($i=0; $i<sizeof($users); $i++) {
			$user = $users[$i];
			$phone = $user['phone'];
			$email = $user['email'];
			$phone = str_replace(" ", "", $phone);
			$phone = str_replace("-", "", $phone);
			$phone = str_replace("(", "", $phone);
			$phone = str_replace(")", "", $phone);
			$filteredUsers = $this->db->query("SELECT * FROM `users` WHERE `phone`='" . $phone . "' OR `email`='" . $email . "'")->result_array();
			if (sizeof($filteredUsers) > 0) {
				if (intval($filteredUsers[0]['id']) != $myUserID) {
					array_push($checkedUsers, $filteredUsers[0]);
				}
			}
		}
		echo json_encode($checkedUsers);
	}
	
	public function get_group_messages() {
		$userID = intval($this->input->post('user_id'));
		$groupID = intval($this->input->post('group_id'));
		$start = intval($this->input->post('start'));
		$length = intval($this->input->post('length'));
		$_messages = $this->db->query("SELECT * FROM `group_messages` WHERE `group_id`=" . $groupID . " ORDER BY `date` DESC LIMIT ".$start.",".$length)->result_array();
		$messages = [];
		for ($i=0; $i<sizeof($_messages); $i++) {
			$deletedMessages = $this->db->query("SELECT * FROM `deleted_group_messages` WHERE `group_message_id`=".$_messages[$i]['id']." AND `user_id`=".$userID)->result_array();
			if (sizeof($deletedMessages) > 0) {
				$_messages[$i]['deleted'] = 1;
			} else {
				$_messages[$i]['deleted'] = 0;
			}
			array_push($messages, $_messages[$i]);
		}
		for ($i=0; $i<sizeof($messages); $i++) {
			$status = "unsent";
			$statuses = $this->db->query("SELECT * FROM `read_group_message_statuses` WHERE `user_id`=".$userID." AND `group_id`=".$groupID." AND `group_message_id`=".$messages[$i]['id'])
				->result_array();
			if (sizeof($statuses) > 0) {
				$status = $statuses[0]['status'];
			}
			$messages[$i]['status'] = $status;
			$messages[$i]['user'] = $this->db->query("SELECT * FROM `users` WHERE `id`=".$messages[$i]['user_id'])->row_array();
		}
		echo json_encode($messages);
	}
	
	public function update_message_status() {
		$messageID = intval($this->input->post('message_id'));
		$status = $this->input->post('status');
		$this->db->query("UPDATE `read_message_statuses` SET `status`='" . $status . "' WHERE `message_id`=" . $messageID);
	}
	
	public function update_group_message_status() {
		$userID = intval($this->input->post('user_id'));
		$groupID = intval($this->input->post('group_id'));
		$messageID = intval($this->input->post('message_id'));
		$status = $this->input->post('status');
		$statuses = $this->db->query("SELECT * FROM `read_group_message_statuses` WHERE `user_id`=".$userID." AND `group_id`=".$groupID." AND `group_message_id`=".$messageID)->num_rows();
		if ($statuses > 0) {
			$this->db->query("UPDATE `read_group_message_statuses` SET `status`='" . $status . "' WHERE `user_id`=".$userID." AND `group_id`=".$groupID." AND `group_message_id`=".$messageID);
		} else {
			$this->db->insert("read_group_message_statuses", array(
				"user_id" => $userID,
				"group_id" => $groupID,
				"group_message_id" => $messageID,
				"status" => $status
			));
		}
	}
	
	public function update_group_chat_messages_status() {
		$userID = intval($this->input->post('user_id'));
		$groupID = intval($this->input->post('group_id'));
		$status = $this->input->post('status');
		$this->db->query("UPDATE `read_group_message_statuses` SET `status`='".$status."' WHERE `user_id`=".$userID." AND `group_id`=".$groupID);
		$groupMessages = $this->db->query("SELECT * FROM `group_messages` WHERE `group_id`=".$groupID." AND `user_id`=".$userID)->result_array();
		for ($i=0; $i<sizeof($groupMessages); $i++) {
			$groupMessage = $groupMessages[$i];
			$statuses = $this->db->query("SELECT * FROM `read_group_message_statuses` WHERE `user_id`=".$userID." AND `group_id`=".$groupID." AND `group_message_id`=".$groupMessage['id'])
				->num_rows();
			if ($statuses <= 0) {
				$this->db->insert("read_group_message_statuses", array(
					"user_id" => $userID,
					"group_id" => $groupID,
					"group_message_id" => $messageID,
					"status" => $status
				));
			}
		}
	}
	
	private function get_group_by_id_($groupId, $userID) {
		$groups = $this->db->query("SELECT * FROM `groups` WHERE `id`=" . $groupId)->result_array();
		for ($i=0; $i<sizeof($groups); $i++) {
			$groups[$i]['sent_messages'] = $this->db->query("SELECT * FROM `read_group_message_statuses` WHERE `user_id`=" . $userID . " AND `group_id`=" . $groups[$i]['id'] . " AND `status`='sent'")->num_rows();
			$members = [];
			$groupMembers = $this->db->query("SELECT * FROM `group_members` WHERE `group_id`=" . $groups[$i]['id'] . " AND `exited`=0")->result_array();
			for ($j=0; $j<sizeof($groupMembers); $j++) {
				$member = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $groupMembers[$j]['user_id'])->row_array();
				$isAdmin = 0;
				if ($this->db->query("SELECT * FROM `group_admins` WHERE `group_id`=".$groups[$i]['id']." AND `user_id`=".$groupMembers[$j]['user_id'])->num_rows()>0) {
					$isAdmin = 1;
				}
				$member['role'] = $this->db->query("SELECT * FROM `roles` WHERE `id`=".$groupMembers[$j]['role_id'])->row_array();
				$member['is_admin'] = $isAdmin;
				array_push($members, $member);
			}
			$groups[$i]['members'] = $members;
			$groups[$i]['group_members'] = $groupMembers;
			$groups[$i]['user'] = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $groups[$i]['user_id'])->row_array();
			$groups[$i]['attachments'] = $this->db->query("SELECT * FROM `group_messages` WHERE `group_id`=" . $groups[$i]['id'] . " AND `message_type`!='text'")->result_array();
			$unreadMessages = 0;
			$groupMessages = $this->db->query("SELECT * FROM `group_messages` WHERE `group_id`=".$groups[$i]['id'])->result_array();
			for ($j=0; $j<sizeof($groupMessages); $j++) {
				$status = "unsent";
				$readMessageStatuses = $this->db->query("SELECT * FROM `read_group_message_statuses` WHERE `user_id`=".$userID." AND `group_id`=".$groups[$i]['id']." AND `group_message_id`=".$groupMessages[$j]['id'])->result_array();
				if (sizeof($readMessageStatuses) > 0) {
					$status = $readMessageStatuses[0]['status'];
				} else {
					$status = "unsent";
				}
				if ($status != "read") {
					$unreadMessages++;
				}
			}
			$groups[$i]['unread_messages'] = $unreadMessages;
		}
		$groupMembers = [];
		$groups2 = $this->db->query("SELECT * FROM `groups` WHERE `user_id`=" . $userID)->result_array();
		if (sizeof($groups2) > 0) {
			$groupMembers = $this->db->query("SELECT * FROM `group_members` WHERE `user_id`=" . $userID . " AND `user_id` NOT IN (SELECT `user_id` FROM `groups` WHERE `id`=" . $groups2[0]['id'] . ") AND `group_id`!=" . $groups2[0]['id'] . " AND `exited`=0")->result_array();
		} else {
			$groupMembers = $this->db->query("SELECT * FROM `group_members` WHERE `user_id`=" . $userID . " AND `exited`=0")->result_array();
		}
		for ($i=0; $i<sizeof($groupMembers); $i++) {
			$group = $this->db->query("SELECT * FROM `groups` WHERE `id`=" . $groupMembers[$i]['group_id'])->row_array();
			$group['sent_messages'] = $this->db->query("SELECT * FROM `read_group_message_statuses` WHERE `user_id`=" . $userID . " AND `group_id`=" . $groupMembers[$i]['group_id'] . " AND `status`='sent'")->num_rows();
			$members = [];
			$groupMembers_ = $this->db->query("SELECT * FROM `group_members` WHERE `group_id`=" . $groupMembers[$i]['group_id'] . " AND `exited`=0")->result_array();
			for ($j=0; $j<sizeof($groupMembers_); $j++) {
				$member = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $groupMembers_[$j]['user_id'])->row_array();
				$isAdmin = 0;
				if ($this->db->query("SELECT * FROM `group_admins` WHERE `group_id`=".$groupMembers[$i]['group_id']." AND `user_id`=".$groupMembers_[$j]['user_id'])->num_rows()>0) {
					$isAdmin = 1;
				}
				$member['role'] = $this->db->query("SELECT * FROM `roles` WHERE `id`=".$groupMembers_[$j]['role_id'])->row_array();
				$member['is_admin'] = $isAdmin;
				array_push($members, $member);
			}
			$group['members'] = $members;
			$group['group_members'] = $groupMembers_;
			$group['user'] = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $groupMembers[$i]['user_id'])->row_array();
			$group['attachments'] = $this->db->query("SELECT * FROM `group_messages` WHERE `group_id`=" . $groupMembers[$i]['group_id'] . " AND `message_type`!='text'")->result_array();
			$unreadMessages = 0;
			$groupMessages = $this->db->query("SELECT * FROM `group_messages` WHERE `group_id`=".$groupMembers[$i]['group_id'])->result_array();
			for ($j=0; $j<sizeof($groupMessages); $j++) {
				$status = "unsent";
				$readMessageStatuses = $this->db->query("SELECT * FROM `read_group_message_statuses` WHERE `user_id`=".$userID." AND `group_id`=".$groupMembers[$i]['group_id']." AND `group_message_id`=".$groupMessages[$j]['id'])->result_array();
				if (sizeof($readMessageStatuses) > 0) {
					$status = $readMessageStatuses[0]['status'];
				} else {
					$status = "unsent";
				}
				if ($status != "read") {
					$unreadMessages++;
				}
			}
			$group['unread_messages'] = $unreadMessages;
			array_push($groups, $group);
		}
		return $groups;
	}
	
	public function get_group_by_id() {
		$groupId = intval($this->input->post('group_id'));
		$userID = intval($this->input->post('user_id'));
		echo json_encode($this->get_group_by_id_($groupId, $userID));
	}
	
	public function update_group_member_role() {
		$groupID = intval($this->input->post('group_id'));
		$userID = intval($this->input->post('user_id'));
		$myUserID = intval($this->input->post('my_user_id'));
		$roleID = intval($this->input->post('role_id'));
		$this->db->query("UPDATE `group_members` SET `role_id`=" . $roleID . " WHERE `group_id`=" . $groupID . " AND `user_id`=" . $userID);
		/*Util::send_message_to_topic_no_notification($this, "group_".$groupID,
			json_encode(array(
				"type" => "role_changed",
				"group_id" => $groupID
			)));*/
		$user = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $userID)->row_array();
		Util::send_message_no_notification($this, intval($user['id']), json_encode(array(
			"type" => "group_member_role_updated",
			"group_id" => "".$groupID
		)));
		/*$groupMembers = $this->db->query("SELECT * FROM `group_members` WHERE `group_id`=".$groupID." AND `exited`=0")->result_array();
		for ($i=0; $i<sizeof($groupMembers); $i++) {
			$groupMember = $this->db->query("SELECT * FROM `users` WHERE `id`=".$groupMembers[$i]['user_id'])->row_array();
			if (intval($groupMember['id']) != $myUserID) {
				Util::send_message_no_notification($this, intval($groupMember['id']), json_encode(array(
					"type" => "role_changed",
					"group_id" => $groupID
				)));
			}
		}*/
	}
	
	public function add_group_admin() {
		$id = intval($this->input->post('id'));
		$userID = intval($this->input->post('user_id'));
		$group = $this->db->query("SELECT * FROM `groups` WHERE `id`=".$id)->row_array();
		$groupAdmins = $this->db->query("SELECT * FROM `group_admins` WHERE `group_id`=".$id." AND `user_id`=".$userID)->num_rows();
		if ($groupAdmins <= 0) {
			$this->db->insert("group_admins", array(
				"group_id" => $id,
				"user_id" => $userID
			));
		}
		$user = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $userID)->row_array();
		$lang = $user['lang_code'];
		$title = "Anda kini telah menjadi admin grup ".$group['title'];
		$body = "Klik untuk lihat info grup terbaru";
		if ($lang == "en") {
			$title = "You have been added as group admin of ".$group['title'];
			$body = "Click to view latest grup info";
		}
		$payload = json_encode(array(
			"type" => "group_admin_added",
			"group_id" => "".$id
		));
		Util::send_message_no_notification($this, intval($user['id']), $payload);
	}
	
	public function remove_group_admin() {
		$id = intval($this->input->post('id'));
		$userID = intval($this->input->post('user_id'));
		$removerUserID = intval($this->input->post('remover_user_id'));
		$date = Util::get_local_date();
		$group = $this->db->query("SELECT * FROM `groups` WHERE `id`=".$id)->row_array();
		$this->db->query("DELETE FROM `group_admins` WHERE `group_id`=".$id." AND `user_id`=".$userID);
		$user = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $userID)->row_array();
		$lang = $user['lang_code'];
		$title = "Anda telah dihapus sebagai admin grup ".$group['title'];
		$body = "Klik untuk lihat info grup terbaru";
		if ($lang == "en") {
			$title = "You have been removed as grup admin of ".$group['title'];
			$body = "Click to view latest grup info";
		}
		/*$payload = json_encode(array(
			"type" => "group_info",
			"click_action" => "FLUTTER_NOTIFICATION_CLICK",
			"screen_path" => "/group_info",
			"screen_name" => "GroupInfo",
			"group_id" => "".$id
		));
		Util::insert_notification($this, intval($user['id']), false, null, "info", $title, $body, $payload, $date);
		Util::send_message($this, intval($user['id']), $title, $body, $payload);*/
		$payload = json_encode(array(
			"type" => "group_admin_removed",
			"group_id" => "".$id,
			"remover_user_id" => "".$removerUserID
		));
		Util::send_message_no_notification($this, intval($user['id']), $payload);
	}
	
	public function mute_group_notifications() {
		$groupID = intval($this->input->post('group_id'));
		$userID = intval($this->input->post('user_id'));
		$mutes = $this->db->query("SELECT * FROM `notification_mutes` WHERE `group_id`=" . $groupID . " AND `user_id`=" . $userID)->num_rows();
		if ($mutes > 0) {
			$this->db->query("UPDATE `notification_mutes` SET `muted`=1 WHERE `group_id`=" . $groupID . " AND `user_id`=" . $userID);
		} else {
			$this->db->insert("notification_mutes", array(
				"group_id" => $groupID,
				"user_id" => $userID,
				"muted" => 1
			));
		}
	}
	
	public function unmute_group_notifications() {
		$groupID = intval($this->input->post('group_id'));
		$userID = intval($this->input->post('user_id'));
		$mutes = $this->db->query("SELECT * FROM `notification_mutes` WHERE `group_id`=" . $groupID . " AND `user_id`=" . $userID)->num_rows();
		if ($mutes > 0) {
			$this->db->query("UPDATE `notification_mutes` SET `muted`=0 WHERE `group_id`=" . $groupID . " AND `user_id`=" . $userID);
		} else {
			$this->db->insert("notification_mutes", array(
				"group_id" => $groupID,
				"user_id" => $userID,
				"muted" => 0
			));
		}
	}
	
	public function get_settings() {
		$settings = $this->db->query("SELECT * FROM `settings`")->result_array();
		echo json_encode($settings);
	}
	
	public function get_setting_by_name() {
		$name = $this->input->post('name');
		$setting = $this->db->query("SELECT * FROM `settings` WHERE `name`='" . $name . "'")->row_array();
		echo json_encode($setting);
	}
	
	public function get_user_setting_by_name() {
		$name = $this->input->post('name');
		$userID = intval($this->input->post('user_id'));
		$setting = $this->db->query("SELECT * FROM `user_settings` WHERE `user_id`=".$userID." AND `name`='" . $name . "'")->row_array();
		echo json_encode($setting);
	}
	
	public function get_active_groups() {
		$userID = intval($this->input->post('user_id'));
		$date = Util::get_local_date();
		$groups = $this->db->query("SELECT * FROM `groups` WHERE `user_id`=" . $userID)->result_array();
		$groupMembers = [];
		$groups2 = $this->db->query("SELECT * FROM `groups` WHERE `user_id`=" . $userID)->result_array();
		if (sizeof($groups2) > 0) {
			$groupMembers = $this->db->query("SELECT * FROM `group_members` WHERE `user_id`=" . $userID . " AND `user_id` NOT IN (SELECT `user_id` FROM `groups` WHERE `id`=" . $groups2[0]['id'] . ") AND `group_id`!=" . $groups2[0]['id'] . " AND `exited`=0")->result_array();
		} else {
			$groupMembers = $this->db->query("SELECT * FROM `group_members` WHERE `user_id`=" . $userID . " AND `exited`=0")->result_array();
		}
		for ($i=0; $i<sizeof($groupMembers); $i++) {
			$group = $this->db->query("SELECT * FROM `groups` WHERE `id`=" . $groupMembers[$i]['group_id'])->row_array();
			array_push($groups, $group);
		}
		$activeGroups = [];
		for ($i=0; $i<sizeof($groups); $i++) {
			$group = $groups[$i];
			if ($group != NULL) {
				$groupID = intval($group['id']);
				$rundowns = $this->db->query("SELECT * FROM `rundowns` WHERE `group_id`=" . $groupID . " AND `date_start`<='" . $date . "' AND `date_end`>'" . $date . "'")->result_array();
				if (sizeof($rundowns) > 0) {
					array_push($activeGroups, $group);
				}
			}
		}
		echo json_encode($activeGroups);
	}
	
	public function add_schedule() {
		$userID = intval($this->input->post('user_id'));
		$groupID = intval($this->input->post('group_id'));
		$dateStart = $this->input->post('date_start');
		$dateEnd = $this->input->post('date_end');
		$timeStart = $this->input->post('time_start');
		$timeEnd = $this->input->post('time_end');
		$placeName = $this->input->post('place_name');
		$description = $this->input->post('description');
		$isRepeating = intval($this->input->post('is_repeating'));
		$isRange = intval($this->input->post('is_range'));
		$targetUserIDs = json_decode($this->input->post('target_user_ids'));
		$this->db->insert('schedules', array(
			'user_id' => $userID,
			'group_id' => $groupID,
			'date_start' => $dateStart,
			'date_end' => $dateEnd,
			'time_start' => $timeStart,
			'time_end' => $timeEnd,
			'place_name' => $placeName,
			'description' => $description,
			'is_repeating' => $isRepeating,
			'is_range' => $isRange,
			'target_user_ids' => json_encode($targetUserIDs)
		));
		$scheduleID = intval($this->db->insert_id());
		$schedule = $this->db->query("SELECT * FROM `schedules` WHERE `id`=".$scheduleID)->row_array();
		echo json_encode($schedule);
		for ($i=0; $i<sizeof($targetUserIDs); $i++) {
			$targetUserID = $targetUserIDs[$i];
			$user = $this->db->query("SELECT * FROM `users` WHERE `id`=".$targetUserID)->row_array();
			Util::send_message_no_notification($this, intval($user['id']), json_encode(array(
				"type" => "create_schedule",
				"schedule" => $schedule
			)));
		}
		Util::send_message_to_topic_no_notification($this, "group_".$groupID, json_encode(array(
			"type" => "schedule_refresh"
		)));
	}
	
	public function save_schedule() {
		$scheduleID = intval($this->input->post('schedule_id'));
		$userID = intval($this->input->post('user_id'));
		$groupID = intval($this->input->post('group_id'));
		$dateStart = $this->input->post('date_start');
		$dateEnd = $this->input->post('date_end');
		$timeStart = $this->input->post('time_start');
		$timeEnd = $this->input->post('time_end');
		$placeName = $this->input->post('place_name');
		$description = $this->input->post('description');
		$isRange = intval($this->input->post('is_range'));
		$targetUserIDs = json_decode($this->input->post('target_user_ids'));
		$this->db->where("id", $scheduleID);
		$this->db->update('schedules', array(
			'user_id' => $userID,
			'group_id' => $groupID,
			'date_start' => $dateStart,
			'date_end' => $dateEnd,
			'time_start' => $timeStart,
			'time_end' => $timeEnd,
			'place_name' => $placeName,
			'description' => $description,
			'is_range' => $isRange,
			'target_user_ids' => json_encode($targetUserIDs)
		));
		$schedule = $this->db->query("SELECT * FROM `schedules` WHERE `id`=".$scheduleID)->row_array();
		echo json_encode($schedule);
		Util::send_message_to_topic_no_notification($this, "group_".$groupID,
			json_encode(array(
				"type" => "schedule_updated",
				"schedule" => $schedule
			)));
		/*$groupMembers = $this->db->query("SELECT * FROM `group_members` WHERE `group_id`=".$groupID." AND `exited`=0")->result_array();
		for ($i=0; $i<sizeof($groupMembers); $i++) {
			$groupMember = $this->db->query("SELECT * FROM `users` WHERE `id`=".$groupMembers[$i]['user_id'])->row_array();
			Util::send_message_no_notification($this, intval($groupMember['id']), json_encode(array(
				"type" => "schedule_updated",
				"schedule" => $schedule
			)));
		}*/
		Util::send_message_to_topic_no_notification($this, "group_".$groupID, json_encode(array(
			"type" => "schedule_refresh"
		)));
	}
	
	public function delete_schedule() {
		$scheduleID = intval($this->input->post('schedule_id'));
		$groupID = intval($this->input->post('group_id'));
		$this->db->query("DELETE FROM `schedules` WHERE `id`=".$scheduleID);
		Util::send_message_to_topic_no_notification($this, "group_".$groupID, json_encode(array(
			"type" => "schedule_refresh"
		)));
	}
	
	public function finish_schedule() {
		$scheduleID = intval($this->input->post('id'));
		$userID = intval($this->input->post('user_id'));
		$groupID = intval($this->input->post('group_id'));
		$date = Util::get_local_date();
		$confirms = $this->db->query("SELECT * FROM `schedule_confirmations` WHERE `schedule_id`=".$scheduleID." AND `user_id`=".$userID)->result_array();
		if (sizeof($confirms) <= 0) {
			$this->db->insert("schedule_confirmations", array(
				"schedule_id" => $scheduleID,
				"user_id" => $userID,
				"last_confirm_date" => $date
			));
		} else {
			$this->db->query("UPDATE `schedule_confirmations` SET `last_confirm_date`='".$date."' WHERE `schedule_id`=".$scheduleID." AND `user_id`=".$userID);
		}
		Util::send_message_to_topic_no_notification($this, "group_".$groupID, json_encode(array(
			"type" => "schedule_refresh"
		)));
	}
	
	public function update_user_info() {
		$id = intval($this->input->post('id'));
		$name = $this->input->post('name');
		$phone = $this->input->post('phone');
		$profilePictureChanged = intval($this->input->post('profile_picture_changed'));
		$this->db->where("id", $id);
		$this->db->update("users", array(
			"name" => $name,
			"phone" => $phone
		));
		if ($profilePictureChanged == 1) {
			$config = array(
				'upload_path' => './userdata/',
				'allowed_types' => "*",
				'overwrite' => TRUE,
				'max_size' => 10240
			);
			$this->load->library('upload', $config);
			if ($this->upload->do_upload('profile_picture')) {
				$this->db->where("id", $id);
				$this->db->update("users", array(
					"photo" => $this->upload->data()['file_name']
				));
			}
		}
		$user = $this->db->query("SELECT * FROM `users` WHERE `id`=".$id)->row_array();
		echo json_encode($user);
	}
	
	public function get_premium_subscriptions() {
		$subscriptions = $this->db->get("premium_subscriptions")->result_array();
		echo json_encode($subscriptions);
	}
	
	public function add_place() {
		$userID = intval($this->input->post('user_id'));
		$groupID = intval($this->input->post('group_id'));
		$placeName = $this->input->post('place_name');
		$address = $this->input->post('address');
		$radius = intval($this->input->post('radius'));
		$latitude = doubleval($this->input->post('latitude'));
		$longitude = doubleval($this->input->post('longitude'));
		$markerIconID = intval($this->input->post('marker_icon_id'));
		$photoUploaded = intval($this->input->post('photo_uploaded'));
		$date = Util::get_local_date();
		$config = array(
			'upload_path' => './userdata/',
			'allowed_types' => "*",
			'overwrite' => TRUE,
			'max_size' => 10240
		);
		$group = $this->db->query("SELECT * FROM `groups` WHERE `id`=".$groupID)->row_array();
		$this->load->library('upload', $config);
		$this->db->insert('places', array(
			'user_id' => $userID,
			'group_id' => $groupID,
			'place_name' => $placeName,
			'address' => $address,
			'radius' => $radius,
			'marker_icon_id' => $markerIconID,
			'latitude' => $latitude,
			'longitude' => $longitude
		));
		$placeID = intval($this->db->insert_id());
		if ($photoUploaded == 1) {
			if ($this->upload->do_upload('photo')) {
				$photoPath = $this->upload->data()['file_name'];
				$this->db->where("id", $placeID);
				$this->db->update("places", array(
					"photo" => $photoPath
				));
			}
		}
		$user = $this->db->query("SELECT * FROM `users` WHERE `id`=".$userID)->row_array();
		/*Util::send_message_to_topic_no_notification($this, "group_".$groupID,
			json_encode(array(
				"type" => "places_refresh",
				"user_id" => "".$userID,
				"group_id" => "".$groupID
			)));*/
		$payload = json_encode(array(
				"type" => "place_added",
				"place_id" => "".$placeID,
				"user_id" => "".$userID,
				"group_id" => "".$groupID
			));
		Util::insert_notification($this, 0, "info", true, "group_".$groupID, $group['title'], $placeName." ".StringDB::get_with_lang_code("id", "text2")." ".$user['name'], $payload, $date);
		Util::send_message_to_topic($this, "group_".$groupID, $group['title'], $placeName." ".StringDB::get_with_lang_code("id", "text2")." ".$user['name'], $payload);
	}
	
	public function save_place() {
		$placeID = intval($this->input->post('place_id'));
		$userID = intval($this->input->post('user_id'));
		$groupID = intval($this->input->post('group_id'));
		$placeName = $this->input->post('place_name');
		$address = $this->input->post('address');
		$radius = intval($this->input->post('radius'));
		$latitude = doubleval($this->input->post('latitude'));
		$longitude = doubleval($this->input->post('longitude'));
		$markerIconID = intval($this->input->post('marker_icon_id'));
		$photoUploaded = intval($this->input->post('photo_uploaded'));
		$config = array(
			'upload_path' => './userdata/',
			'allowed_types' => "*",
			'overwrite' => TRUE,
			'max_size' => 10240
		);
		$this->load->library('upload', $config);
		$this->db->where("id", $placeID);
		$this->db->update('places', array(
			'user_id' => $userID,
			'group_id' => $groupID,
			'place_name' => $placeName,
			'address' => $address,
			'radius' => $radius,
			'marker_icon_id' => $markerIconID,
			'latitude' => $latitude,
			'longitude' => $longitude
		));
		if ($photoUploaded == 1) {
			if ($this->upload->do_upload('photo')) {
				$photoPath = $this->upload->data()['file_name'];
				$this->db->where("id", $placeID);
				$this->db->update("places", array(
					"photo" => $photoPath
				));
			}
		}
		$place = $this->db->query("SELECT * FROM `places` WHERE `id`=" . $placeID)->row_array();
		$place['user'] = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $place['user_id'])->row_array();
		$place['marker_icon'] = $this->db->query("SELECT * FROM `marker_icons` WHERE `id`=".$place['marker_icon_id'])->row_array();
		echo json_encode($place);
	}
	
	public function get_marker_icons() {
		$icons = $this->db->query("SELECT * FROM `marker_icons`")->result_array();
		echo json_encode($icons);
	}
	
	public function get_places() {
		$userID = intval($this->input->post('user_id'));
		$groupID = intval($this->input->post('group_id'));
		$places = $this->db->query("SELECT * FROM `places` WHERE `group_id`=".$groupID)->result_array();
		$groupMembers = $this->db->query("SELECT * FROM `group_members` WHERE `group_id`=".$groupID." AND `exited`=0")->result_array();
		for ($i=0; $i<sizeof($places); $i++) {
			$places[$i]['user'] = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $places[$i]['user_id'])->row_array();
			$places[$i]['marker_icon'] = $this->db->query("SELECT * FROM `marker_icons` WHERE `id`=".$places[$i]['marker_icon_id'])->row_array();
			$inRadiusUsers = [];
			for ($j=0; $j<sizeof($groupMembers); $j++) {
				$groupMember = $this->db->query("SELECT * FROM `users` WHERE `id`=".$groupMembers[$j]['user_id'])->row_array();
				if ($groupMember != NULL) {
					$distance = Util::get_distance(doubleval($groupMember['lat']), doubleval($groupMember['lng']), doubleval($places[$i]['latitude']), doubleval($places[$i]['longitude']));
					if ($distance <= doubleval($places[$i]['radius'])) {
						array_push($inRadiusUsers, $groupMember);
					}
				}
			}
			$places[$i]['in_radius_users'] = $inRadiusUsers;
		}
		echo json_encode($places);
	}
	
	public function get_place_by_id() {
		$placeID = intval($this->input->post('id'));
		$place = $this->db->query("SELECT * FROM `places` WHERE `id`=".$placeID)->row_array();
		if ($place != NULL) {
			$place['user'] = $this->db->query("SELECT * FROM `users` WHERE `id`=" . $place['user_id'])->row_array();
			$place['marker_icon'] = $this->db->query("SELECT * FROM `marker_icons` WHERE `id`=".$place['marker_icon_id'])->row_array();
		}
		echo json_encode($place);
	}
	
	public function delete_place() {
		$placeID = intval($this->input->post('place_id'));
		$userID = intval($this->input->post('user_id'));
		$groupID = intval($this->input->post('group_id'));
		$date = Util::get_local_date();
		$place = $this->db->query("SELECT * FROM `places` WHERE `id`=".$placeID)->row_array();
		$this->db->query("DELETE FROM `places` WHERE `id`=".$placeID);
		/*Util::send_message_to_topic_no_notification($this, "group_".$groupID,
			json_encode(array(
				"type" => "places_refresh",
				"user_id" => "".$userID,
				"group_id" => "".$groupID
			)));*/
		$group = $this->db->query("SELECT * FROM `groups` WHERE `id`=".$groupID)->row_array();
		$user = $this->db->query("SELECT * FROM `users` WHERE `id`=".$userID)->row_array();
		$payload = json_encode(array(
				"type" => "place_deleted",
				"place" => $place,
				"user_id" => "".$userID,
				"group_id" => "".$groupID
			));
		Util::insert_notification($this, 0, true, "group_".$groupID, "info",  $group['title'], $place['place_name']." ".StringDB::get_with_lang_code("id", "text3")." ".$user['name'], $payload, $date);
		Util::send_message_to_topic($this, "group_".$groupID, $group['title'], $place['place_name']." ".StringDB::get_with_lang_code("id", "text3")." ".$user['name'], $payload);
	}
	
	public function clear_chat() {
		$groupID = intval($this->input->post('group_id'));
		$this->db->query("DELETE FROM `group_messages` WHERE `group_id`=".$groupID);
	}
	
	public function get_roles() {
		$groupType = $this->input->post('group_type');
		$roles = $this->db->query("SELECT * FROM `roles` WHERE `group_type`='".$groupType."'")->result_array();
		echo json_encode($roles);
	}
	
	public function get_schedule_by_id() {
		$id = intval($this->input->post('id'));
		$schedule = $this->db->query("SELECT * FROM `schedules` WHERE `id`=".$id)->row_array();
		$schedule['group'] = $this->db->query("SELECT * FROM `groups` WHERE `id`=".$schedule['group_id'])->row_array();
		echo json_encode($schedule);
	}
	
	public function send_message() {
		$userID = intval($this->input->post('user_id'));
		$title = $this->input->post('title');
		$body = $this->input->post('body');
		$payload = $this->input->post('payload');
		$date = Util::get_local_date();
		$user = $this->db->query("SELECT * FROM `users` WHERE `id`=".$userID)->row_array();
		if ($user != NULL) {
			Util::insert_notification($this, intval($user['id']), false, null, "info", $title, $body, $payload, $date);
			Util::send_message($this, intval($user['id']), $title, $body, $payload);
		}
	}
	
	public function send_pushy_message() {
		$userID = intval($this->input->post('user_id'));
		$title = $this->input->post('title');
		$body = $this->input->post('body');
		$payload = $this->input->post('payload');
		$date = Util::get_local_date();
		$user = $this->db->query("SELECT * FROM `users` WHERE `id`=".$userID)->row_array();
		Util::insert_notification($this, $userID, false, null, "info", $title, $body, $payload, $date);
		if ($user != NULL) {
			Util::send_pushy_message($this, $user['pushy_token'], $title, $body, $payload);
		}
	}
	
	public function send_message_to_topic() {
		$topic = $this->input->post('topic');
		$title = $this->input->post('title');
		$body = $this->input->post('body');
		$payload = $this->input->post('payload');
		$date = Util::get_local_date();
		Util::insert_notification($this, 0, true, $topic, "info", $title, $body, $payload, $date);
		Util::send_message_to_topic($this, $topic, $title, $body, $payload);
	}
	
	public function send_pushy_message_to_topic() {
		$topic = $this->input->post('topic');
		$title = $this->input->post('title');
		$body = $this->input->post('body');
		$payload = $this->input->post('payload');
		$date = Util::get_local_date();
		Util::insert_notification($this, 0, true, $topic, "info", $title, $body, $payload, $date);
		Util::send_pushy_message($this, "/topics/".$topic, $title, $body, $payload);
	}
	
	public function send_message_no_notification() {
		$userID = intval($this->input->post('user_id'));
		$payload = $this->input->post('payload');
		$user = $this->db->query("SELECT * FROM `users` WHERE `id`=".$userID)->row_array();
		Util::send_message_no_notification($this, intval($user['id']), $payload);
	}
	
	public function send_pushy_message_no_notification() {
		$userID = intval($this->input->post('user_id'));
		$payload = $this->input->post('payload');
		$user = $this->db->query("SELECT * FROM `users` WHERE `id`=".$userID)->row_array();
		Util::send_pushy_message_no_notification($this, $user['pushy_token'], $payload);
	}
	
	public function update_user_device_id() {
		$userID = intval($this->input->post('user_id'));
		$deviceID = $this->input->post('device_id');
		$this->db->query("UPDATE `users` SET `device_id`='".$deviceID."' WHERE `id`=".$userID);
	}
	
	public function get_user_by_email() {
		$email = $this->input->post('email');
		$user = $this->db->query("SELECT * FROM `users` WHERE `email`='".$email."'")->row_array();
		echo json_encode($user);
	}
	
	public function get_user_by_facebook_uid() {
		$facebookUID = $this->input->post('facebook_uid');
		$user = $this->db->query("SELECT * FROM `users` WHERE `facebook_uid`='".$facebookUID."'")->row_array();
		echo json_encode($user);
	}
	
	private function request_all_members_location_update_($groupID, $date) {
		$this->db->query("UPDATE `groups` SET `last_check_date`='".$date."' WHERE `id`=".$groupID);
		/*Util::send_message_to_topic_no_notification($this, "group_".$groupID,
			json_encode(array(
				"type" => "request_update_location"
			)));*/
	}
	
	public function request_all_members_location_update() {
		$groupID = intval($this->input->post('group_id'));
		$date = Util::get_local_date();
		$group = $this->db->query("SELECT * FROM `groups` WHERE `id`=".$groupID)->row_array();
		$settings = $this->db->query("SELECT * FROM `settings` LIMIT 1")->row_array();
		$minRequestLocationUpdateDelay = doubleval($settings['min_request_location_update_delay']);
		if ($group != NULL) {
			if ($group['last_check_date'] == NULL || $group['last_check_date'].trim() == "null" || $group['last_check_date'].trim() == "") {
				$this->request_all_members_location_update_(intval($group['id']), $date);
			} else {
				$lastCheckDate = new DateTime($group['last_check_date'].trim());
				$currentDate = new DateTime($date);
				$diffMilliseconds = $lastCheckDate->diff($currentDate)->format("%s")*1000;
				if ($diffMilliseconds >= $minRequestLocationUpdateDelay) {
					$this->request_all_members_location_update_(intval($group['id']), $date);
				}
			}
		}
	}
	
	public function update_user_premium_status() {
		$userID = intval($this->input->post('user_id'));
		$premium = intval($this->input->post('premium'));
		$this->db->where("id", $userID);
		$this->db->update("users", array(
			"premium" => $premium
		));
	}
	
	private function get_group_unread_messages_count($userID, $groupID) {
		$unreadMessages = 0;
		$groupMessages = $this->db->query("SELECT * FROM `group_messages` WHERE `group_id`=".$groupID)->result_array();
		for ($i=0; $i<sizeof($groupMessages); $i++) {
			$status = "unsent";
			$readMessageStatuses = $this->db->query("SELECT * FROM `read_group_message_statuses` WHERE `user_id`=".$userID." AND `group_id`=".$groupID." AND `group_message_id`=".$groupMessages[$i]['id'])->result_array();
			if (sizeof($readMessageStatuses) > 0) {
				$status = $readMessageStatuses[0]['status'];
			} else {
				$status = "unsent";
			}
			if ($status != "read") {
				$unreadMessages++;
			}
		}
		return $unreadMessages;
	}
	
	private function get_unread_messages_count($chatID, $receiverID) {
		$unreadMessages = $this->db->query("SELECT * FROM `read_message_statuses` WHERE `chat_id`=".$chatID." AND `receiver_id`=".$receiverID." AND `status`!='read'")->num_rows();
		return $unreadMessages;
	}
	
	public function clear_group_message_statuses() {
		$userID = intval($this->input->post('user_id'));
		$groupID = intval($this->input->post('group_id'));
		$groupMessages = $this->db->query("SELECT * FROM `group_messages` WHERE `group_id`=".$groupID)->result_array();
		for ($i=0; $i<sizeof($groupMessages); $i++) {
			$statuses = $this->db->query("SELECT * FROM `read_group_message_statuses` WHERE `user_id`=".$userID." AND `group_id`=".$groupID." AND `group_message_id`=".$groupMessages[$i]['id'])->num_rows();
			if ($statuses > 0) {
				$this->db->query("UPDATE `read_group_message_statuses` SET `status`='read' WHERE `user_id`=".$userID." AND `group_id`=".$groupID." AND `group_message_id`=".$groupMessages[$i]['id']);
			} else {
				$this->db->insert("read_group_message_statuses", array(
					"user_id" => $userID,
					"group_id" => $groupID,
					"group_message_id" => $groupMessages[$i]['id']
				));
			}
		}
		$this->db->query("UPDATE `read_group_message_statuses` SET `status`='read' WHERE `user_id`=".$userID." AND `group_id`=".$groupID);
		$groups = $this->get_groups_($userID);
		$totalUnreadMessages = 0;
		for ($i=0; $i<sizeof($groups); $i++) {
			$totalUnreadMessages += intval($groups[$i]['unread_messages']);
		}
		echo $totalUnreadMessages;
	}
	
	public function clear_message_statuses() {
		$chatID = intval($this->input->post('chat_id'));
		$receiverID = intval($this->input->post('receiver_id'));
		$this->db->query("UPDATE `read_message_statuses` SET `status`='read' WHERE `chat_id`=".$chatID." AND `receiver_id`=".$receiverID);
	}
	
	public function add_call_log() {
		$callerID = intval($this->input->post('caller_id'));
		$receiverID = intval($this->input->post('receiver_id'));
		$type = $this->input->post('type');
		$direction = $this->input->post('direction');
		$date = Util::get_local_date();
		$this->db->insert("call_logs", array(
			"caller_id" => $callerID,
			"receiver_id" => $receiverID,
			"type" => $type,
			"direction" => $direction,
			"date" => $date
		));
	}
	
	public function get_call_logs() {
		$userID = intval($this->input->post('user_id'));
		$logs = $this->db->query("SELECT * FROM `call_logs` WHERE `caller_id`=".$userID." OR `receiver_id`=".$userID." ORDER BY `date` DESC")->result_array();
		for ($i=0; $i<sizeof($logs); $i++) {
			$logs[$i]['caller'] = $this->db->query("SELECT * FROM `users` WHERE `id`=".$logs[$i]['caller_id'])->row_array();
			$logs[$i]['receiver'] = $this->db->query("SELECT * FROM `users` WHERE `id`=".$logs[$i]['receiver_id'])->row_array();
		}
		echo json_encode($logs);
	}
	
	public function get_ice_servers() {
		$iceServers = json_decode(Util::get_setting($instance, "ice_servers"), true);
		echo json_encode($iceServers);
	}
	
	public function get_panduan_umroh_bab() {
		$panduanUmroh = $this->db->query("SELECT * FROM `panduan_umroh_bab`")->result_array();
		echo json_encode($panduanUmroh);
	}
	
	public function get_panduan_umroh_subbab() {
		$bab = intval($this->input->post('bab'));
		$panduanUmroh = $this->db->query("SELECT * FROM `panduan_umroh_subbab` WHERE `panduan_umroh_bab`=".$bab)->result_array();
		echo json_encode($panduanUmroh);
	}
	
	public function get_panduan_umroh() {
		$subBab = intval($this->input->post('subbab'));
		$panduanUmroh = $this->db->query("SELECT * FROM `panduan_umroh` WHERE `panduan_umroh_subbab`=".$subBab)->result_array();
		echo json_encode($panduanUmroh);
	}
	
	public function get_privacy_policy() {
		echo Util::get_setting($this, "privacy_policy_path");
	}
	
	public function get_user_settings() {
		$userID = intval($this->input->post('user_id'));
		$userSettings = $this->db->query("SELECT * FROM `user_settings` WHERE `user_id`=".$userID)->result_array();
		echo json_encode($userSettings);
	}
	
	public function send_amqp_message() {
		$messagingMethod = $this->input->post('messaging_method');
		$target = $this->input->post('target');
		$title = $this->input->post('title');
		$body = $this->input->post('body');
		$payload = $this->input->post('payload');
		// $target = user_{USER_ID}, user_1, user_2, user_3
		Util::send_amqp_message($this, $messagingMethod, $target, $title, $body, $payload);
	}
	
	public function send_amqp_message_to_topic() {
		$messagingMethod = $this->input->post('messaging_method');
		$target = $this->input->post('target');
		$title = $this->input->post('title');
		$body = $this->input->post('body');
		$payload = $this->input->post('payload');
		$userIDs = json_decode($this->input->post('user_ids'), true);
		// $target = user_{USER_ID}, user_1, user_2, user_3
		//echo "Messaging method: ".$messagingMethod.", user IDs: ".json_encode($userIDs).", target: ".$target.", title: ".$title.", body: ".$body.", payload: ".$payload."\n";
		Util::send_amqp_message_to_user_ids($this, $messagingMethod, $target, $userIDs, $title, $body, $payload);
	}
	
	public function send_amqp_message_remote() {
		$target = $this->input->post('target');
		$title = $this->input->post('title');
		$body = $this->input->post('body');
		$payload = $this->input->post('payload');
		// $target = user_{USER_ID}, user_1, user_2, user_3
		Util::send_amqp_message_remote($this, $target, $title, $body, $payload);
	}
	
	public function send_amqp_message_no_notification() {
		$messagingMethod = $this->input->post('messaging_method');
		$target = $this->input->post('target');
		$payload = $this->input->post('payload');
		// $target = user_{USER_ID}, user_1, user_2, user_3
		Util::send_amqp_message_no_notification($this, $messagingMethod, $target, $payload);
	}
	
	public function send_amqp_message_in_date_no_notification() {
		$messagingMethod = $this->input->post('messaging_method');
		$target = $this->input->post('target');
		$payload = $this->input->post('payload');
		// $target = user_{USER_ID}, user_1, user_2, user_3
		Util::send_amqp_message_in_date_no_notification($this, $messagingMethod, $target, $payload);
	}
	
	public function send_amqp_message_to_topic_no_notification() {
		$messagingMethod = $this->input->post('messaging_method');
		$target = $this->input->post('target');
		$payload = $this->input->post('payload');
		$userIDs = json_decode($this->input->post('user_ids'), true);
		// $target = user_{USER_ID}, user_1, user_2, user_3
		//echo "Messaging method: ".$messagingMethod.", user IDs: ".json_encode($userIDs).", target: ".$target.", payload: ".$payload."\n";
		Util::send_amqp_message_to_user_ids_no_notification($this, $messagingMethod, $target, $userIDs, $payload);
	}
	
	public function send_amqp_message_no_notification_remote() {
		$target = $this->input->post('target');
		$payload = $this->input->post('payload');
		// $target = user_{USER_ID}, user_1, user_2, user_3
		Util::send_amqp_message_no_notification_remote($this, $target, $payload);
	}
	
	public function subscribe_to_rabbitmq_topic() {
		$userID = intval($this->input->post('user_id'));
		$topic = $this->input->post('topic');
		$topics = $this->db->query("SELECT * FROM `rabbitmq_topics` WHERE `user_id`=".$userID." AND `topic`='".$topic."'")->result_array();
		if (sizeof($topics) <= 0) {
			$this->db->insert("rabbitmq_topics", array(
				"user_id" => $userID,
				"topic" => $topic
			));
		}
	}
	
	public function unsubscribe_from_rabbitmq_topic() {
		$userID = intval($this->input->post('user_id'));
		$topic = $this->input->post('topic');
		$topics = $this->where(array("user_id" => $userID, "topic" => $topic))->delete("rabbitmq_topics");
	}
	
	public function get_notifications() {
		$userID = intval($this->input->post('user_id'));
		$topics = json_decode($this->input->post('topics'), true);
		$sqlTopics = "(";
		for ($i=0; $i<sizeof($topics); $i++) {
			$sqlTopics .= ("'".$topics[$i]."', ");
		}
		if (Util::endsWith($sqlTopics, ", ")) {
			$sqlTopics = substr($sqlTopics, 0, strlen($sqlTopics)-2);
		}
		$sqlTopics .= ")";
		$notifications = $this->db->query("SELECT * FROM `notifications` WHERE `user_id`=".$userID." OR `topic` IN ".$sqlTopics." ORDER BY `date` DESC")->result_array();
		echo json_encode($notifications);
	}
	
	public function update_user_settings() {
		$userID = intval($this->input->post('user_id'));
		$settings = json_decode($this->input->post('settings'), true);
		for ($i=0; $i<sizeof($settings); $i++) {
			$setting = $settings[$i];
			$userSettings = $this->db->query("SELECT * FROM `user_settings` WHERE `user_id`=".$userID." AND `name`='".$setting['name']."'")->result_array();
			if (sizeof($userSettings) > 0) {
				$this->db->query("UPDATE `user_settings` SET `value`='".$setting['value']."' WHERE `user_id`=".$userID." AND `name`='".$setting['name']."'");
			} else {
				$this->db->insert("user_settings", array(
					"user_id" => $userID,
					"name" => $setting['name'],
					"value" => $setting['value']
				));
			}
		}
	}
	
	public function update_notification_ringtone() {
		$userID = intval($this->input->post('user_id'));
		$groupChatRingtoneChanged = intval($this->input->post('group_chat_ringtone_changed'));
		$privateMessageRingtoneChanged = intval($this->input->post('private_message_ringtone_changed'));
		if ($groupChatRingtoneChanged == 1) {
			$config = array(
				'upload_path' => './userdata/',
				'allowed_types' => "*",
				'overwrite' => TRUE,
				'max_size' => 10240
			);
			$this->load->library('upload', $config);
			if ($this->upload->do_upload('group_chat_ringtone')) {
				$sha1 = sha1_file("userdata/".$this->upload->data()['file_name']);
				$userSettings = $this->db->query("SELECT * FROM `user_settings` WHERE `user_id`=".$userID." AND `name`='group_chat_ringtone'")->result_array();
				if (sizeof($userSettings) > 0) {
					$this->db->query("UPDATE `user_settings` SET `value`='".$this->upload->data()['file_name']."' WHERE `user_id`=".$userID." AND `name`='group_chat_ringtone'");
				} else {
					$this->db->insert("user_settings", array(
						"user_id" => $userID,
						"name" => "group_chat_ringtone",
						"value" => $this->upload->data()['file_name']
					));
				}
				$userSettings = $this->db->query("SELECT * FROM `user_settings` WHERE `user_id`=".$userID." AND `name`='group_chat_ringtone_sha1'")->result_array();
				if (sizeof($userSettings) > 0) {
					$this->db->query("UPDATE `user_settings` SET `value`='".$sha1."' WHERE `user_id`=".$userID." AND `name`='group_chat_ringtone_sha1'");
				} else {
					$this->db->insert("user_settings", array(
						"user_id" => $userID,
						"name" => "group_chat_ringtone_sha1",
						"value" => $sha1
					));
				}
			}
		}
		if ($privateMessageRingtoneChanged == 1) {
			$config = array(
				'upload_path' => './userdata/',
				'allowed_types' => "*",
				'overwrite' => TRUE,
				'max_size' => 10240
			);
			$this->load->library('upload', $config);
			if ($this->upload->do_upload('private_message_ringtone')) {
				$sha1 = sha1_file("userdata/".$this->upload->data()['file_name']);
				$userSettings = $this->db->query("SELECT * FROM `user_settings` WHERE `user_id`=".$userID." AND `name`='private_message_ringtone'")->result_array();
				if (sizeof($userSettings) > 0) {
					$this->db->query("UPDATE `user_settings` SET `value`='".$this->upload->data()['file_name']."' WHERE `user_id`=".$userID." AND `name`='private_message_ringtone'");
				} else {
					$this->db->insert("user_settings", array(
						"user_id" => $userID,
						"name" => "private_message_ringtone",
						"value" => $this->upload->data()['file_name']
					));
				}
				$userSettings = $this->db->query("SELECT * FROM `user_settings` WHERE `user_id`=".$userID." AND `name`='private_message_ringtone_sha1'")->result_array();
				if (sizeof($userSettings) > 0) {
					$this->db->query("UPDATE `user_settings` SET `value`='".$sha1."' WHERE `user_id`=".$userID." AND `name`='private_message_ringtone_sha1'");
				} else {
					$this->db->insert("user_settings", array(
						"user_id" => $userID,
						"name" => "private_message_ringtone_sha1",
						"value" => $sha1
					));
				}
			}
		}
	}
	
	public function update_user_notification_channel() {
		$userID = intval($this->input->post('user_id'));
		$channelID = $this->input->post('channel_id');
		$this->db->query("UPDATE `users` SET `notification_channel_id`='".$channelID."' WHERE `id`=".$userID);
	}
	
	public function update_user_group_notification_channel() {
		$userID = intval($this->input->post('user_id'));
		$groupID = intval($this->input->post('group_id'));
		$channelID = $this->input->post('channel_id');
		$this->db->query("DELETE FROM `group_notification_channels` WHERE `user_id`=".$userID." AND `group_id`=".$groupID);
		$this->db->insert("group_notification_channels", array(
			"user_id" => $userID,
			"group_id" => $groupID,
			"channel_id" => $channelID
		));
	}
	
	public function get_help() {
		echo json_encode($this->db->query("SELECT * FROM `settings` WHERE `name`='help_path'")->row_array());
	}
	
	public function get_faq() {
		echo json_encode($this->db->query("SELECT * FROM `settings` WHERE `name`='faq_path'")->row_array());
	}
	
	public function contact_us() {
		$userID = intval($this->input->post('user_id'));
		$adminEmails = json_decode(Util::get_setting($this, "admin_emails"), true);
		$name = $this->input->post('name');
		$email = $this->input->post('email');
		$subject = $this->input->post('subject');
		$message = $this->input->post('message');
    	$headers = 'From: '.$email."\r\n" .
                 'Reply-To: '.$email. "\r\n" .
                 'X-Mailer: PHP/' . phpversion();
        for ($i=0; $i<sizeof($adminEmails); $i++) {
        	mail($adminEmails[$i], $subject, $message, $headers);
	    }
	}
	
	public function clear_broadcast_histories() {
		$userID = intval($this->input->post('user_id'));
		$this->db->query("DELETE FROM `broadcasts` WHERE `user_id`=".$userID);
	}
}
