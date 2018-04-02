<?php

	require_once(LYYRA_ROOT_FILE_PATH."/lib/modules/Facebook/facebook.php");
	require_once(LYYRA_ROOT_FILE_PATH."/ds/std.inc.php");
	require_once(LYYRA_ROOT_FILE_PATH."/ds/se.inc.php");

	class FacebookIntegration {
	
		private $_key;
		private $_secret;
		private $_facebook;
		private $_user;
		private $_profile;
		
		public function FacebookIntegration() {
			$this->initSettings();
			$this->initSDK();
			$this->initUser();
			$this->addFacebookSettingsToSmarty();
		}
		public function initSettings() {
			global $database;
			$result = $database->database_query("SELECT * FROM se_semods_settings");
			if($database->database_num_rows($result) > 0) {
				$row = (object) $database->database_fetch_assoc($result);
				$this->_key = $row->setting_openidconnect_facebook_api_key;
				$this->_secret = $row->setting_openidconnect_facebook_secret;
			}
		}
		public function initSDK() {
			$this->_facebook = new Facebook(array(
			  'appId'  => $this->_key,
			  'secret' => $this->_secret,
			));
		}
		public function initUser() {
			$this->_user = $this->_facebook->getUser();
		}
		public function addFacebookSettingsToSmarty() {
			global $smarty, $url;
			$smarty->assign('facebookKey', $this->key);
			$smarty->assign('facebookSecret', $this->secret);
			$smarty->assign('facebookChannel', $url->url_base."templates/channel.html");
		}
		public function getAccessToken() {
			return $this->_facebook->getAccessToken();
		}
		public function isThereAnyUserLogged() {
			if($this->_user) {
				try {
					$this->_profile = $this->_facebook->api('/me');
					return true;
				} catch (Exception $e) {
					$result = $e->getResult();
					return false;
				}
			} else {
				return false;
			}
		}
		public function isMapped($facebookId = null) {
			global $database;
			$result = $database->database_query("
				SELECT * FROM se_semods_usersopenid WHERE
				openid_user_key = '".($facebookId == null ? $this->id : $facebookId)."' AND
				openid_service_id = '1'
			");
			if($database->database_num_rows($result) > 0) {
				$row = (object) $database->database_fetch_assoc($result);
				return $row->openid_user_id;
			} else {
				return false;
			}
		}
		public function mapUser($facebookId, $lyyraId) {
			global $database;
			if($facebookId != "" && $lyyraId != "") {
				return $database->database_query("
					INSERT INTO se_semods_usersopenid 
					(openid_user_id, openid_user_key, openid_service_id) 
					VALUES 
					('".$lyyraId."', '".$facebookId."', '1')
					ON DUPLICATE KEY UPDATE openid_user_id = '".$lyyraId."'
				");
			} else {
				return false;
			}
		}
		public function unmapUser($facebookId, $lyyraId) {
			global $database;
			return $database->database_query("DELETE FROM se_semods_usersopenid WHERE openid_user_id = '".$lyyraId."' AND openid_user_key = '".$facebookId."' AND openid_service_id = '1'");
		}
		public function getLoginLink() {
			return $this->_facebook->getLoginUrl();
		}
		public function getLogoutLink() {
			return $this->_facebook->getLogoutUrl();
		}
		public function __get($varName) {
			switch($varName) {
				case "key":
					return $this->_key;
				break;
				case "secret":
					return $this->_secret;
				break;
				case "id":
					return $this->_user;
				break;
				case "profile":
					if($this->_profile) {
						return (object) $this->_profile;
					} else {
						$this->_profile = $this->_facebook->api('/me');
						return (object) $this->_profile;
					}
					return NULL;
				break;
				case "avatar":
					return "https://graph.facebook.com/".$this->id."/picture";
				break;
				case "displayName":
					if($this->profile) {
						return $this->profile->name;
					} else {
						return "";
					}
				break;
				case "user":
					return $this->_user;
				break;
			}
		}
	
	}

?>