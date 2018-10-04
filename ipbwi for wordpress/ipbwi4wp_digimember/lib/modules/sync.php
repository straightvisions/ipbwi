<?php
	/**
	 * @author			Matthias Reuter
	 * @package			hooks
	 * @copyright		2007-2016 Matthias Reuter
	 * @link			http://ipbwi.com/
	 * @since			4.0
	 * @license			This is no free software. See license.txt or https://ipbwi.com
	 */
	class ipbwi4wp_digimember_sync extends ipbwi4wp_digimember{
		public $ipbwi4wp_digimember															= NULL;
		public $ipb_user_id																	= NULL;
		public $ipb_user_info																= NULL;
		public $ipb_secondary_groups														= array();
		public $suffix																		= '';
		public $params																		= NULL;
		public $wp_user																		= NULL;
		
		/**
		 * @desc			Loads other classes of package
		 * @author			Matthias Reuter
		 * @since			4.0
		 * @ignore
		 */
		public function __construct($ipbwi4wp_digimember){
			$this->ipbwi4wp_digimember														= isset($ipbwi4wp_digimember->ipbwi4wp_digimember) ? $ipbwi4wp_digimember->ipbwi4wp_digimember : $ipbwi4wp_digimember; // loads common classes
		}
		public function get_ipb_group_id($user_id,$product_id){
			// members exists in IPB?
			if(!$this->ipbwi4wp_digimember->ipbwi4wp->member->ipb_exists($user_id)){
				return false;
			}
			
			$this->get_ipb_user_by_wp_user_id($user_id);
			return $this->get_group_id_by_product_id($product_id);
		}
		public function get_group_id_by_product_id($product_id){
			if(isset($this->ipbwi4wp_digimember->settings->settings['digimember']['IPB_GROUPS_MAPPING']['value'][$product_id]) && intval($this->ipbwi4wp_digimember->settings->settings['digimember']['IPB_GROUPS_MAPPING']['value'][$product_id]) > 0){
				return $this->ipbwi4wp_digimember->settings->settings['digimember']['IPB_GROUPS_MAPPING']['value'][$product_id];
			}else{
				return false;
			}
		}
		public function get_ipb_user_by_wp_user_id($user_id){
			$this->ipb_user_id																= $this->ipbwi4wp_digimember->ipbwi4wp->member->wp_user_id_to_ipb_user_id($user_id);
			$this->ipb_user_info															= $this->ipbwi4wp_digimember->ipbwi4wp->member->ipb_get($this->ipb_user_id);
			
			if(!isset($this->ipb_secondary_groups[$this->ipb_user_id])){
				foreach($this->ipb_user_info['secondaryGroups'] as $secondaries){
					$this->ipb_secondary_groups[$this->ipb_user_id][]						= $secondaries['id'];
				}
				if(isset($this->ipb_secondary_groups[$this->ipb_user_id]) && is_array($this->ipb_secondary_groups[$this->ipb_user_id])){
					$this->ipb_secondary_groups[$this->ipb_user_id]							= array_values(array_unique($this->ipb_secondary_groups[$this->ipb_user_id]));
				}
			}
		}
		/**
		 * @desc			digimember_purchase
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function digimember_purchase($user_id, $product_id, $order_id, $reason){
			$ipb_group_id																	= $this->get_ipb_group_id($user_id,$product_id);

			if($ipb_group_id){
				if($reason == 'order_paid'){
					$this->add_group($ipb_group_id);
				}
				if($reason == 'order_cancelled'){
					$this->remove_group($ipb_group_id);
				}
				if($reason == 'payment_missing'){
					$this->remove_group($ipb_group_id);
				}else{
					
				}
			}
		}
		public function username_taken_by_other_wp_user(){
			$username = $this->params['firstname'].' '.$this->params['lastname'].$this->suffix;
			if(username_exists($username) && username_exists($username) != $this->wp_user->ID){
				return true;
			}else{
				return false;
			}
		}
		
		public function username_taken_by_this_wp_user(){
			$username = $this->params['firstname'].' '.$this->params['lastname'].$this->suffix;
			if(username_exists($username) && username_exists($username) == $this->wp_user->ID){
				return true;
			}else{
				return false;
			}
		}
		public function username_is_placeholder(){
			if($this->params['firstname'] == 'first' || $this->params['lastname'] == 'last'){
				return true;
			}else{
				return false;
			}
		}
		public function current_displayname_is_not_email(){
			if($this->wp_user->display_name != $this->wp_user->user_email){
				return true;
			}else{
				return false;
			}
		}
		public function current_login_is_not_displayname(){
			if($this->wp_user->user_login != $this->wp_user->display_name){
				return true;
			}else{
				return false;
			}
		}
		public function use_current_displayname(){
			if(
				$this->current_displayname_is_not_email() &&
				$this->current_login_is_not_displayname() &&
				!username_exists($this->wp_user->display_name)
			){
				return true;
			}else{
				return false;
			}
		}
		public function use_given_firstname_lastname(){
			if(
				!$this->username_is_placeholder() &&
				!$this->username_taken_by_other_wp_user()
			){
				return true;
			}else{
				return false;
			}
		}
		public function generate_username(){
			if($this->use_current_displayname()){
				$username																= $this->wp_user->display_name;
			}elseif($this->use_given_firstname_lastname()){
				$username																= $this->params['firstname'].' '.$this->params['lastname'].$this->suffix;
			}elseif(
				!$this->username_is_placeholder() &&
				$this->username_taken_by_other_wp_user()
			){
				$this->suffix															= intval($this->suffix)+1;
				$username																= $this->generate_username();
			}else{
				$username																= $this->params['username'];
			}
			return $username;
		}
		public function update_ipb_username($username){
			// update user account in IPB which has been created prior via DigiMember
			$user																		= $this->ipbwi4wp_digimember->ipbwi4wp->member->ipb_get_by_name($username);
			if($user){
				$wp_user_id																= $this->ipbwi4wp_digimember->ipbwi4wp->member->ipb_user_id_to_wp_user_id($user['id']);
				
				if($wp_user_id){ // taken
					if($wp_user_id == $this->wp_user->ID){ // by this user
						return $username; // nothing todo in IPB, just return username
					}else{ // by another user
						// generate new username
						$this->suffix														= intval($this->suffix)+1;
						$username															= $this->update_ipb_username($this->generate_username());
					}
					
				}else{ // free
					// just continue update
				}
			}
			
			try{
				$this->ipbwi4wp_digimember->ipbwi4wp->ipbwi->sso->changeName($username,$this->ipbwi4wp_digimember->ipbwi4wp->member->wp_user_id_to_ipb_user_id($this->wp_user->ID));
			}catch(Throwable $t){
				if($t->getMessage() == 'USERNAME_IN_USE'){
					$this->suffix														= intval($this->suffix)+1;
					$username															= $this->update_ipb_username($this->generate_username());
				}else{
					$this->ipbwi4wp_digimember->ipbwi4wp->alert->add($t->getMessage());
				}
			}
			catch(Exception $e){
				if($e->getMessage() == 'USERNAME_IN_USE'){
					$this->suffix														= intval($this->suffix)+1;
					$username															= $this->update_ipb_username($this->generate_username());
				}else{
					$this->ipbwi4wp_digimember->ipbwi4wp->alert->add($e->getMessage());
				}
			}
			
			return $username;
		}
		public function digimember_welcome_mail_placeholder_values($params, $user, $product, $order_id){
			global $wpdb;
			$this->params																= $params;
			$this->wp_user																= get_userdata($user->ID);
			$this->params['username']													= $this->update_ipb_username($this->generate_username());

			//$this->ipbwi4wp_digimember->ipbwi4wp->sso_by_wp->set_new_password($this->params['password']);
			//$this->ipbwi4wp_digimember->ipbwi4wp->sso_by_wp->register($user->ID);
			
			// set userlogin
			$wpdb->update($wpdb->users, array('user_login' => $this->params['username']), array('ID' => $this->wp_user->ID));
			clean_user_cache($this->wp_user->ID);
			
			// in welcome mail, the email as recommened for login will be used
			$this->params['username']													= $this->wp_user->user_email;
			
			return $this->params;
		}
		public function requests($r){
			if(isset($r['page']) && $r['page'] == 'digimember_orders'){
				if(isset($r['action']) && isset($r['ids'])){
					$ids																= explode(',',$r['ids']);
					if($r['action'] == 'activate'){
						foreach($ids as $id){
							$this->activate_by_order_id(intval($id));
						}
					}elseif($r['action'] == 'deactivate'){
						foreach($ids as $id){
							$this->deactivate_by_order_id(intval($id));
						}
					}elseif($r['action'] == 'trash'){
						foreach($ids as $id){
							$this->deactivate_by_order_id(intval($id));
						}
					}elseif($r['action'] == 'restore'){
						foreach($ids as $id){
							$this->restore_by_order_id(intval($id));
						}
					}
				}elseif(isset($r['id']) && isset($r['ncore_is_active'.$r['id']])){
					if($r['ncore_is_active'.$r['id']] == 'Y'){
						$this->activate_by_order_id(intval($r['id']));
					}else{
						$this->deactivate_by_order_id(intval($r['id']));
					}
				}
			}elseif(isset($r['dm_ipn'])){
				//var_dump($r); die('end');
			}
		}
		public function activate_by_order_id($id){
			$order																	= $this->digimember_getOrder($id);
			$ipb_group_id															= $this->get_ipb_group_id($order->user_id,$order->product_id);
			$this->add_group($ipb_group_id);
		}
		public function deactivate_by_order_id($id){
			$order																	= $this->digimember_getOrder($id);
			$ipb_group_id															= $this->get_ipb_group_id($order->user_id,$order->product_id);
			$this->remove_group($ipb_group_id);
		}
		public function add_group($ipb_group_id){
			$this->ipb_secondary_groups[$this->ipb_user_id][]						= $ipb_group_id;
			$this->ipbwi4wp_digimember->ipbwi4wp->ipbwi->extended->updateSecondaryGroups($this->ipb_user_id, $this->ipb_secondary_groups[$this->ipb_user_id]);
		}
		public function remove_group($ipb_group_id){
			if(isset($this->ipb_secondary_groups[$this->ipb_user_id]) && is_array($this->ipb_secondary_groups[$this->ipb_user_id])){
				$this->ipb_secondary_groups[$this->ipb_user_id]						= array_values(array_unique(array_diff($this->ipb_secondary_groups[$this->ipb_user_id],(array)$ipb_group_id)));
				$this->ipbwi4wp_digimember->ipbwi4wp->ipbwi->extended->updateSecondaryGroups($this->ipb_user_id, $this->ipb_secondary_groups[$this->ipb_user_id]);
			}
		}
		private function digimember_getOrder($order_id){
			global $wpdb;
			$order																	= $wpdb->get_row('SELECT user_id,product_id,is_active FROM '.$wpdb->prefix.'digimember_user_product WHERE id="'.$order_id.'"');
			return $order;
		}
		private function restore_by_order_id($id){
			$order																	= $this->digimember_getOrder($id);
			if($order->is_active == 'Y'){
				// make sure group is added
				$this->activate_by_order_id($id);
			}else{
				// make sure group is removed
				$this->deactivate_by_order_id($id);
			}
		}
		public function ipbwi4wp_user_import_update_username($name,$ipb_id){ // generate new username following DigiMember module standard
			global $wpdb;
			$this->suffix															= '';
			
			// get WP user object
			$this->wp_user															= $this->ipbwi4wp_digimember->ipbwi4wp->member->wp_get_user_by_ipb_id($ipb_id);
			if($this->wp_user){
				// retrieve firstname / lastname from digimember
				$this->params['firstname']											= get_user_meta($this->wp_user->ID, 'first_name', true);
				$this->params['lastname']											= get_user_meta($this->wp_user->ID, 'last_name', true);
				$this->params['username']											= $name;
				
				// set username in WP and IPB
				$name																= $this->update_ipb_username($this->params['firstname'].' '.$this->params['lastname']);
				$wpdb->update($wpdb->users, array('user_login' => $name), array('ID' => $this->wp_user->ID));
				clean_user_cache($this->wp_user->ID);
				
				// return new name
				return $name;
			}else{
				return $name;
			}
			
		}
	}
?>