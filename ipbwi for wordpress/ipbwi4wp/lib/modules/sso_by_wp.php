<?php
	/**
	 * @author			Matthias Reuter
	 * @package			sso_by_wp
	 * @copyright		2007-2016 Matthias Reuter
	 * @link			http://ipbwi.com/
	 * @since			4.0
	 * @license			This is no free software. See license.txt or https://ipbwi.com
	 */
	class ipbwi4wp_sso_by_wp extends ipbwi4wp{
		public $ipbwi4wp			= NULL;
		private $redirect_url		= NULL;
		private $new_password		= NULL;
		private $allow_registration	= false;

		/**
		 * @desc			Loads other classes of package
		 * @author			Matthias Reuter
		 * @since			4.0
		 * @ignore
		 */
		public function __construct($ipbwi4wp){
			$this->ipbwi4wp				= isset($ipbwi4wp->ipbwi4wp) ? $ipbwi4wp->ipbwi4wp : $ipbwi4wp; // loads common classes
			
			if(is_ssl()){
				$protocol				= 'https://';
			}else{
				$protocol				= 'http://';
			}
			if(isset($_REQUEST['redirect_to'])){
				$this->redirect_url		= $_REQUEST['redirect_to'];
			}elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == 'logout'){
				$this->redirect_url		= get_home_url().'/wp-login.php?loggedout=true';
			}else{
				$this->redirect_url		= $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			}
		}
		/**
		 * @desc			Handle Logout
		 * @return	void	nothing as user will be redirected
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function logout(){
			$returnTo					= apply_filters('ipbwi_sso_wp_logout_destination_url',$this->redirect_url);
			$id							= $this->ipbwi4wp->member->wp_user_id_to_ipb_user_id($this->ipbwi4wp->member->wp_current_user_id);
			
			if($id){
				$this->ipbwi4wp->ipbwi->sso->logout($id, $returnTo);
			}
		}
		/**
		 * @desc			Handle Login
		 * @return	void	nothing as user will be redirected
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function login($user, $username, $password){
			// bypass wrong username / password error
			if(
				!is_wp_error($user) ||
				(
					is_wp_error($user) &&
					count($user->errors) == 1 &&
					isset($user->errors['incorrect_password'])
				)
			){
				if(strlen($username) > 0 && strlen($password) > 0){
					// check if user exists in WP
					if(!is_email($username)){
						$user					= get_user_by('login',$username);
					}else{
						$user					= get_user_by('email',$username);
					}
					try{
						if(!$user){ // user does not exist in WP yet, take a look to IP.board
							return $this->login_wp_no_account($username, $password);
						}else{ // user already exists in WP
							return $this->login_wp_has_account($user, $username, $password);
						}
					}catch(Throwable $t){ return new WP_Error('ipbwi_error', __('<strong>ERROR</strong>: '.$t->getMessage())); }
				}else{
					return $user;
				}
			}else{
				return $user;
			}
		}
		public function crossLogin($user_login, $user){
			$returnTo					= apply_filters('ipbwi_sso_wp_login_destination_url', $this->redirect_url);
			$id							= $this->ipbwi4wp->member->wp_user_id_to_ipb_user_id($user->ID);

			$this->ipbwi4wp->ipbwi->sso->crossLogin($id, $returnTo);
		}
		/**
		 * @desc			Handle Registration
		 * @param	int		WP member ID
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function register($wp_user_id){
			$ipb_user_id				= $this->ipbwi4wp->member->wp_user_id_to_ipb_user_id($wp_user_id);

			if($ipb_user_id){
				// user already exists in IPB, so do nothing here
			}elseif(!$this->new_password){
				// we were not able to retrieve the password upon registration. We will not create an IP.board account yet, this will be rescheduled upon next login via WP
				//die('no pw');
			}else{
				$user					= get_userdata($wp_user_id); // get user object

				// create IPB user
				$salt					= $this->ipbwi4wp->ipbwi->sso->generateSalt();
				$crypted_pw				= crypt($this->new_password, '$2a$13$'.$salt);
				$id						= $this->ipbwi4wp->ipbwi->sso->register($user->data->user_login, $user->data->user_email, $crypted_pw, $salt);
			}
		}
		/**
		 * @desc			Handle Login after Registration via WooCommerce
		 * @param	int		WP member ID
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function woocommerce_registration_redirect($redirect_to){
			$id							= $this->ipbwi4wp->member->wp_user_id_to_ipb_user_id(get_current_user_id());

			$this->ipbwi4wp->ipbwi->sso->crossLogin($id, $redirect_to);
		}
		/**
		 * @desc			Handle Update
		 * @param	int		WP member ID
		 * @param	object	Old user data object
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function update($wp_user_id,$user_old){
			try{
				$ipb_user					= $this->ipbwi4wp->member->ipb_get_by_name($user_old->data->user_login);
				$ipb_user_id				= $ipb_user['id'];
				
				if($ipb_user_id){
					// user already exists in IPB, so update there
					$user					= get_user_by('ID',$wp_user_id); // get user object
					
					if($user->data->user_email != $user_old->data->user_email){
						$result				= $this->ipbwi4wp->ipbwi->sso->changeEmail($user->data->user_email, $ipb_user_id);
					}
					if($this->new_password){
						$salt				= $this->ipbwi4wp->ipbwi->sso->fetchSalt(2,$user->data->user_email);
						$crypted_pw			= $this->ipbwi4wp->ipbwi->sso->encrypt_password($user->data->user_email, 2, $this->new_password);
						
						$result				= $this->ipbwi4wp->ipbwi->sso->changePassword($salt, $crypted_pw, $ipb_user_id);
					}
				}elseif(!$this->new_password){
					// we were not able to retrieve the password upon registration. We will not create an IP.board account yet, this will be rescheduled upon next login via WP
					// die('no pw');
				}else{
					$user					= get_user_by('ID',$wp_user_id); // get user object
					
					// create IPB user
					$salt					= $this->ipbwi4wp->ipbwi->sso->fetchSalt(2,$user->data->user_email);
					$crypted_pw				= $this->ipbwi4wp->ipbwi->sso->encrypt_password($user->data->user_email, 2, $this->new_password);
					
					$id						= $this->ipbwi4wp->ipbwi->sso->register($user->data->user_login, $user->data->user_email, $crypted_pw, $salt);
				}
			}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); return false; }
		}
		/**
		 * @desc			Login if no WP account exists
		 * @param	string	username
		 * @param	string	password
		 * @return	object	WP user information object or WP error object
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		private function login_wp_no_account($username, $password){
			try{
				if(strlen($username) == 0){
					return;
				}
				if(!is_email($username)){
					$ipb_user				= $this->ipbwi4wp->member->ipb_get_by_name($username);
				}else{
					$ipb_user				= $this->ipbwi4wp->member->ipb_get_by_email($username);
				}
				if(isset($ipb_user['id']) && !$this->ipbwi4wp->member->ipb_is_banned($ipb_user['id'])){
					if($ipb_user && isset($ipb_user['email'])){ // user exists in IPB, so try to authenticate
						$salt					= $this->ipbwi4wp->ipbwi->sso->fetchSalt(2,$ipb_user['email']);
						$crypted_pw				= $this->ipbwi4wp->ipbwi->sso->encrypt_password($ipb_user['email'], 2, $password);
						$result					= $this->ipbwi4wp->ipbwi->sso->login(2, $ipb_user['email'], $crypted_pw);
						if($result){ // authentication successfull, create user and perform login
							$this->allow_registration	= true;
							$wp_user_id			= wp_create_user($ipb_user['name'], $password, $ipb_user['email']); // create user in WP
							$user				= get_user_by('ID',$wp_user_id); // get user object
							if(function_exists('add_user_to_blog')){
								add_user_to_blog(get_current_blog_id(),$user->ID, get_option('default_role')); // add user to this blog, too, if WPMU is active
							}
							add_action('wp_login', array($this->ipbwi4wp->sso_by_wp,'crossLogin'), 10, 2); // we'll perform the login redirect later once WP has fulfilled login process
							return $user;
						}else{
							return $this->incorrect_password($username);
						}
					}else{ // user does not exist in WP nor IPB
						if(!is_email($username)){
							return $this->invalid_username();
						}else{
							return $this->invalid_email();
						}
					}
				}
			}catch(Throwable $t){ return new WP_Error('ipbwi_error', __('<strong>ERROR</strong>: '.$t->getMessage())); }
		}
		/**
		 * @desc			Login if WP account exists
		 * @param	object	WP user object
		 * @param	string	username
		 * @param	string	password
		 * @return	object	WP user information object or WP error object
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		private function login_wp_has_account($user, $username, $password){
			try{
				if(function_exists('is_user_member_of_blog') && function_exists('add_user_to_blog') && function_exists('get_current_blog_id') && !is_user_member_of_blog($user->ID)){
					add_user_to_blog(get_current_blog_id(),$user->ID, get_option('default_role'));
				}

				$id								= $this->ipbwi4wp->member->wp_user_id_to_ipb_user_id($user->ID);
				
				if(!$id){ // user does not exist yet in IP.board - we'll gonna change that
					$salt						= $this->ipbwi4wp->ipbwi->sso->generateSalt();
					$crypted_pw					= crypt($password, '$2a$13$'.$salt);
					try{
						$id						= $this->ipbwi4wp->ipbwi->sso->register($user->data->user_login, $user->data->user_email, $crypted_pw, $salt);
					}catch(Throwable $t){ return new WP_Error('ipbwi_error', __('<strong>ERROR</strong>: '.$t->getMessage())); }
				}
				$salt							= $this->ipbwi4wp->ipbwi->sso->fetchSalt(2,$user->data->user_email);
				$crypted_pw						= $this->ipbwi4wp->ipbwi->sso->encrypt_password($user->data->user_email, 2, $password);

				if($id){
					// make a login attempt
					try{
						$result					= $this->ipbwi4wp->ipbwi->sso->login(2, $user->data->user_email, $crypted_pw);
					}catch(Throwable $t){
						// If the password does not work on IPB, but does work on WordPress
						if($t->getMessage() === 'WRONG_AUTH' && wp_check_password($password, $user->user_pass, $user->ID)){ // These guys are legit, but have different passwords on WP and IPB
							$this->update_password($user, $password); // Let's update their IPB password to match
							$this->login_wp_has_account($user, $username, $password); // And try to log into IPB again

							return $user;
						}
						
						return new WP_Error('ipbwi_error', __('<strong>ERROR</strong>: '.$t->getMessage()));
					}

					if($result['status'] == 'SUCCESS' && $result['connect_id'] == $id){
						wp_set_password($password, $user->ID); // set password in WP as we've got a confirmation that this one is correct by IPB master
						add_action('wp_login', array($this->ipbwi4wp->sso_by_wp,'crossLogin'), 10, 2); // we'll perform the login redirect later once WP has fulfilled login process
						
						return $user;
					}else{
						return $this->incorrect_password($username);
					}
				}else{
					return $this->invalid_username();
				}
			}catch(Throwable $t){ return new WP_Error('ipbwi_error', __('<strong>ERROR</strong>: '.$t->getMessage())); }
		}
		/**
		 * @desc			Invalid username
		 * @return	object	WP error object
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		private function invalid_username(){
			return new WP_Error('invalid_username',
				__('<strong>ERROR</strong>: Invalid username.').
				' <a href="' . wp_lostpassword_url() . '">'.
				__('Lost your password?').
				'</a>'
			);
		}
		/**
		 * @desc			Invalid email
		 * @return	object	WP error object
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		private function invalid_email(){
			return new WP_Error('invalid_email',
				__('<strong>ERROR</strong>: Invalid email address.').
				' <a href="' . wp_lostpassword_url() . '">'.
				__('Lost your password?').
				'</a>'
			);
		}
		/**
		 * @desc			Incorrect password
		 * @return	object	WP error object
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		private function incorrect_password($username){
			return new WP_Error('incorrect_password',
				sprintf(
					/* translators: %s: user name */
					__('<strong>ERROR</strong>: The password you entered for the username %s is incorrect.'),
					'<strong>' . $username . '</strong>'
				).
				' <a href="' . wp_lostpassword_url() . '">'.
					__('Lost your password?').
				'</a>'
			);
		}
		/**
		 * @desc			cache password for later use
		 * @param	string	password
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function set_new_password($password){
			$this->new_password			= $password;
		}
		/**
		 * @desc			delete or ban user account
		 * @param	int		WP user ID
		 * @return	object	status
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function delete($user_id){
			$id = $this->ipbwi4wp->member->wp_user_id_to_ipb_user_id($user_id);
			if($id){
				if($this->ipbwi4wp->settings->settings['advanced']['ALLOW_DELETE']['value'] == 1){
					return $this->ipbwi4wp->ipbwi->sso->delete($id);
				}else{
					return $this->ipbwi4wp->ipbwi->sso->ban($id,1);
				}
			}
		}
		/**
		 * @desc			update password
		 * @param	int		WP user object
		 * @param	string	new password
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function update_password($user, $new_pass){
			$id					= $this->ipbwi4wp->member->wp_user_id_to_ipb_user_id($user->ID);
			if($id){
				$salt			= $this->ipbwi4wp->ipbwi->sso->fetchSalt(2,$user->data->user_email);
				$crypted_pw		= $this->ipbwi4wp->ipbwi->sso->encrypt_password($user->data->user_email, 2, $new_pass);
				$result			= $this->ipbwi4wp->ipbwi->sso->changePassword($salt, $crypted_pw, $id);
			}
		}
	}
?>