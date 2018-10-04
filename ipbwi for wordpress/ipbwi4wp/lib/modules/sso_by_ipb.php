<?php
	/**
	 * @author			Matthias Reuter
	 * @package			sso_by_ipb
	 * @copyright		2007-2016 Matthias Reuter
	 * @link			http://ipbwi.com/
	 * @since			4.0
	 * @license			This is no free software. See license.txt or https://ipbwi.com
	 */
	class ipbwi4wp_sso_by_ipb extends ipbwi4wp{
		public $ipbwi4wp	= NULL;
		public $request		= NULL;
		/**
		 * @desc			Loads other classes of package
		 * @author			Matthias Reuter
		 * @since			4.0
		 * @ignore
		 */
		public function __construct($ipbwi4wp){
			$this->ipbwi4wp				= isset($ipbwi4wp->ipbwi4wp) ? $ipbwi4wp->ipbwi4wp : $ipbwi4wp; // loads common classes
		}
		/**
		 * @desc			Handle IPB SSO API Requests
		 * @param	object	$request WP REST object
		 * @return	mixed	nothing or status response
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function wp_rest_api_extension(WP_REST_Request $request){
			//@error_log(var_export($request,true), 3, IPBWI4WP_DIR.'log.txt');
			$this->request		= apply_filters('ipbwi_sso_ipb_request',$request);

			try{
				if(
					(strlen($this->request->get_param('id')) > 0 && $this->request->get_param('key') == md5(md5(get_site_url()).$this->request->get_param('id'))) // ID delivered
					|| $this->request->get_param('key') == md5(get_site_url()) // no ID delivered
				){ // security key check
					if($this->request->get_param('do') == 'register'){
						$this->register($this->request->get_param('id'), $this->request->get_param('name'), $this->request->get_param('email'), $this->request->get_param('pass_hash'), $this->request->get_param('pass_salt'));
					}else{ // security key check
						if($this->request->get_param('do') != 'delete'){
							$this->register($this->request->get_param('id')); // register user to WordPress if not exists yet
						}
						
						if($this->request->get_param('do') == 'crossLogin'){
							$this->login($this->request->get_param('id'));
						}elseif($this->request->get_param('do') == 'logout'){
							$this->logout();
						}elseif($this->request->get_param('do') == 'delete'){
							$this->delete($this->request->get_param('id'));
						}elseif($this->request->get_param('do') == 'changeEmail'){
							$this->change_email($this->request->get_param('id'),$this->request->get_param('email'));
						}elseif($this->request->get_param('do') == 'changeName'){
							$this->change_name($this->request->get_param('id'),$this->request->get_param('name'));
						}elseif($this->request->get_param('do') == 'changePassword'){
							$this->change_password($this->request->get_param('id'),$this->request->get_param('pass_hash'),$this->request->get_param('pass_salt'));
						}elseif($this->request->get_param('do') == 'validate'){
							$this->validate($this->request->get_param('id'));
						}
					}
				}else{
					return __('Security key mismatch.', 'ipbwi4wp');
				}
				
				if($this->request->get_param('returnTo')){
					header('Location: '.$this->request->get_param('returnTo')); 
					die();
				}
			}catch(Throwable $t){
				error_log('Type Error, line '.$t->getLine().': ' .$t->getMessage(), 3, IPBWI4WP_DIR.'log.txt');
				echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage();
			}
			
			return;
		}
		/**
		 * @desc			login a user in WP when sent from IPB
		 * @param	int		$ipb_member_id IP.board member ID
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		private function login($ipb_member_id){
			if($this->register($ipb_member_id)){
				$status = wp_set_auth_cookie($this->ipbwi4wp->member->ipb_user_id_to_wp_user_id($ipb_member_id),true);

				do_action('ipbwi_sso_ipb_login', $status, $this->request);
				return $status;
			}
		}
		/**
		 * @desc			logout a user in WP when sent from IPB
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		private function logout(){
			$status = wp_logout();
			
			do_action('ipbwi_sso_ipb_logout', $status, $this->request);
			return $status;
		}
		/**
		 * @desc			register a user in WP when sent from IPB
		 * @param	int		$ipb_member_id IP.board member ID
		 * @param	int		$name IP.board member name
		 * @param	int		$email IP.board member email
		 * @param	int		$pass_hash IP.board member pass hash
		 * @param	int		$pass_salt IP.board member pass salt
		 * @return	mixed	true if member already exists, user ID if member has been successfully created, WP error object in failure
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function register($ipb_member_id, $name=false, $email=false, $pass_hash=false, $pass_salt=false, $role=false){
			if($name == false){
				$name						= $this->ipbwi4wp->member->ipb_get_username_by_id($ipb_member_id);
			}
			if($email == false){
				$email						= $this->ipbwi4wp->member->ipb_get_email_by_id($ipb_member_id);
			}

			if(!$this->ipbwi4wp->member->wp_exists($email)){ // user does not exist in WP
				$status						= wp_create_user($name, md5(time().$name.$email.$pass_salt), $email); // create WP account with random password
				if(!is_int($status)){
					// maybe user already exists in network but not on this site. Try adding user to this site
					if(function_exists('is_user_member_of_blog') && function_exists('add_user_to_blog') && function_exists('get_current_blog_id')){
						$wp_user_id			= $this->ipbwi4wp->member->wp_loginname_to_wp_user_id($name);
						
						// add user to all blogs
						if($this->ipbwi4wp->settings->settings['network']['ALL_SITES']['value'] == 1 && function_exists('wp_get_sites')){
							foreach(wp_get_sites(array('limit' => 10000)) as $site){
								$status		= add_user_to_blog($site['blog_id'], $wp_user_id, ($role ? $role : get_option('default_role')));
							}
						}else{
							// add user to current blog
							$status			= add_user_to_blog(get_current_blog_id(), $wp_user_id, ($role ? $role : get_option('default_role')));
						}
					}
				}else{
					$user					= new WP_User($status);
					$user->set_role(($role ? $role : get_option('default_role')));
					
					// add user to all blogs
					if($this->ipbwi4wp->settings->settings['network']['ALL_SITES']['value'] == 1 && function_exists('wp_get_sites')){
						foreach(wp_get_sites(array('limit' => 10000)) as $site){
							$status			= add_user_to_blog($site['blog_id'], $user->ID, ($role ? $role : get_option('default_role')));
						}
					}elseif(function_exists('add_user_to_blog')){
						// add user to current blog
						$status				= add_user_to_blog(get_current_blog_id(), $user->ID, ($role ? $role : get_option('default_role')));
					}
				}
			}else{ // user exists in WP, but not in current blog
				$user						= $this->ipbwi4wp->member->wp_get_user_by_ipb_id($ipb_member_id);
				
				// add user to all blogs
				if($this->ipbwi4wp->settings->settings['network']['ALL_SITES']['value'] == 1 && function_exists('wp_get_sites')){
					foreach(wp_get_sites(array('limit' => 10000)) as $site){
						if(!is_user_member_of_blog($user->ID, $site['blog_id'])){ // dont't try to add user if is already member of that blog
							$status				= add_user_to_blog($site['blog_id'], $user->ID, ($role ? $role : get_option('default_role')));
						}elseif($role){ // except a role has been explicetly delivered
							$status				= add_user_to_blog($site['blog_id'], $user->ID, $role);
						}
					}
				}elseif(function_exists('add_user_to_blog') && function_exists('add_user_to_blog') && !is_user_member_of_blog($user->ID)){
					// add user to current blog
					$status					= add_user_to_blog(get_current_blog_id(), $user->ID, ($role ? $role : get_option('default_role')));
				}
				$status						= true; // already exists
			}
			
			do_action('ipbwi_sso_ipb_register', $status, $this->request);
			return $status;
		}
		/**
		 * @desc			delete a user in WP when sent from IPB
		 * @param	int		$ipb_member_id IP.board member ID
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		private function delete($ipb_member_id){
			$wp_user_id					= $this->ipbwi4wp->member->ipb_user_id_to_wp_user_id($ipb_member_id);
			
			if($this->ipbwi4wp->settings->settings['advanced']['ALLOW_DELETE']['value'] == 1){
				include(ABSPATH.'/wp-admin/includes/user.php');
				if(strlen($this->ipbwi4wp->settings->settings['advanced']['REASSIGN_TO']['value']) > 0){
					$reassign_to_id		= $this->ipbwi4wp->member->wp_loginname_to_wp_user_id($this->ipbwi4wp->settings->settings['advanced']['REASSIGN_TO']['value']);
					if($reassign_to_id > 0){
						$status			= wp_delete_user($this->ipbwi4wp->member->ipb_user_id_to_wp_user_id($ipb_member_id),$wp_user_id);
					}else{
						$status			= 'cannot retrieve user ID '.$reassign_to_id.' for reassignment from username '.$this->ipbwi4wp->settings->settings['advanced']['REASSIGN_TO']['value'];
					}
				}else{
					$status				= wp_delete_user($this->ipbwi4wp->member->ipb_user_id_to_wp_user_id($ipb_member_id));
				}
			}else{
				$status					= false;
			}
			
			do_action('ipbwi_sso_ipb_delete', $status, $this->request);
			return $status;
		}
		/**
		 * @desc			change a user's email in WP when sent from IPB
		 * @param	int		$ipb_member_id IP.board member ID
		 * @param	int		$email IP.board member email
		 * @return	mixed	WP user ID on success, otherwise WP error object
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		private function change_email($ipb_member_id, $email){
			// normally, we would use function ipb_user_id_to_wp_user_id, but this uses email address saved in WP which is outdated compared to IPB.
			// so we go step by step by loginname to avoid issues here
			
			$ipb_login					= $this->ipbwi4wp->member->ipb_get_username_by_id($ipb_member_id);
			$wp_user_id					= $this->ipbwi4wp->member->wp_loginname_to_wp_user_id($ipb_login);
			
			if($wp_user_id){
				$userdata				= array(
					'ID'				=> $wp_user_id,
					'user_email'		=> $email
				);
				$status					= wp_update_user($userdata);
			}else{
				$status					= false;
			}
			
			do_action('ipbwi_sso_ipb_change_email', $status, $this->request);
			return $status;
		}
		/**
		 * @desc			change a user's name in WP when sent from IPB
		 * @param	int		$ipb_member_id IP.board member ID
		 * @param	int		$name IP.board member name
		 * @return	mixed	WP user ID on success, otherwise WP error object
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		private function change_name($ipb_member_id, $name){
			global $wpdb;
			$wp_user_id					= $this->ipbwi4wp->member->ipb_user_id_to_wp_user_id($ipb_member_id);
			
			if($wp_user_id){
				$status					= $wpdb->update($wpdb->users, array('user_login' => $name), array('ID' => $wp_user_id));
			}else{
				$status					= false;
			}
			
			do_action('ipbwi_sso_ipb_change_name', $status, $this->request);
			return $status;
		}
		/**
		 * @desc			change a user's password in WP when sent from IPB
		 * @param	int		$ipb_member_id IP.board member ID
		 * @param	int		$pass_hash IP.board member pass hash
		 * @param	int		$pass_salt IP.board member pass salt
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		private function change_password($ipb_member_id, $pass_hash, $pass_salt){
			// we don't need changing user's password in WP yet, as we bypass password checks in WP with IP.board.
			
			$status		= false;
			
			do_action('ipbwi_sso_ipb_change_password', $status, $this->request);
			return $status;
		}
		/**
		 * @desc			validate user in WP when sent from IPB
		 * @param	int		$ipb_member_id IP.board member ID
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		private function validate($ipb_member_id){
			// we don't support validating status in WP yet.
			
			$status		= false;
			
			do_action('ipbwi_sso_ipb_validate', $status, $this->request);
			return $status;
		}

	}
?>