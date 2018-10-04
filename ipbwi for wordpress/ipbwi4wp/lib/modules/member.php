<?php
	/**
	 * @author			Matthias Reuter
	 * @package			member
	 * @copyright		2007-2016 Matthias Reuter
	 * @link			http://ipbwi.com/
	 * @since			4.0
	 * @license			This is no free software. See license.txt or https://ipbwi.com
	 */
	class ipbwi4wp_member extends ipbwi4wp{
		public $ipbwi4wp			= NULL;
		public $wp_current_user_id	= NULL;
		/**
		 * @desc			Loads other classes of package
		 * @author			Matthias Reuter
		 * @since			4.0
		 * @ignore
		 */
		public function __construct($ipbwi4wp){
			$this->ipbwi4wp				= isset($ipbwi4wp->ipbwi4wp) ? $ipbwi4wp->ipbwi4wp : $ipbwi4wp; // loads common classes
			$this->wp_current_user_id	= get_current_user_id();
		}
		/**
		 * @desc			list IPB members
		 * @param	string	$sortBy What to sort by. Can be 'joined', 'name' or leave unspecified for ID
		 * @param	string	$sortDir Sort direction. Can be 'asc' or 'desc' - defaults to 'asc'
		 * @param	string	$page Page number
		 * @param	bool	$renew Perform new query and renew cache
		 * @return	array	member information
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function ipb_list($sortBy=false, $sortDir=false, $page=1, $renew=false){
			if($renew || !$this->ipbwi4wp->cache->get('members',$sortBy.$sortDir.$page)){
				$params				= array(
					'sortBy'		=> $sortBy,
					'sortDir'		=> $sortDir,
					'page'			=> $page
				);
				return $this->ipbwi4wp->cache->save('members', $sortBy.$sortDir.$page, $this->ipbwi4wp->ipbwi->core->members(NULL,NULL,NULL,$params));
			}else{
				return $this->ipbwi4wp->cache->get('members', $sortBy.$sortDir.$page);
			}
		}
		/**
		 * @desc			retrieve IPB member information
		 * @param	int		$id IP.board member ID
		 * @param	bool	$renew Perform new query and renew cache
		 * @return	array	member information
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function ipb_get($id,$renew=false){
			if($renew || !$this->ipbwi4wp->cache->get('ipb_member',$id)){
				try{
					$user		= $this->ipbwi4wp->ipbwi->core->members($id);
				}catch(Throwable $t){ $this->ipbwi4wp->alert->add($t->getMessage()); return false; }
				
				if(isset($user['id'])){
					$this->ipbwi4wp->cache->save('ipb_member', $user['id'], $user);
					$this->ipbwi4wp->cache->save('ipb_member_name', $user['name'], $user);
					$this->ipbwi4wp->cache->save('ipb_member_email', $user['email'], $user);
				}
				return $user;
			}else{
				return $this->ipbwi4wp->cache->get('ipb_member', $id);
			}
		}
		/**
		 * @desc			retrieve IPB member information
		 * @param	string	$name IP.board member name
		 * @param	bool	$renew Perform new query and renew cache
		 * @return	array	member information
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function ipb_get_by_name($name, $renew=false){
			if($renew || !$this->ipbwi4wp->cache->get('ipb_member_name',$name)){
				try{
					$user		= $this->ipbwi4wp->ipbwi->extended->members('name',$name);
				}catch(Throwable $t){ $this->ipbwi4wp->alert->add($t->getMessage()); return false; }
				
				if(isset($user['id'])){
					$this->ipbwi4wp->cache->save('ipb_member', $user['id'], $user);
					$this->ipbwi4wp->cache->save('ipb_member_name', $user['name'], $user);
					$this->ipbwi4wp->cache->save('ipb_member_email', $user['email'], $user);
				}
				return $user;
			}else{
				return $this->ipbwi4wp->cache->get('ipb_member_name', $name);
			}
		}
		/**
		 * @desc			retrieve IPB member information
		 * @param	string	$email IP.board member email
		 * @param	bool	$renew Perform new query and renew cache
		 * @return	array	member information
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function ipb_get_by_email($email, $renew=false){
			if($renew || !$this->ipbwi4wp->cache->get('ipb_member_email',$email)){
				try{
					$user		= $this->ipbwi4wp->ipbwi->extended->members('email',$email);
				}catch(Throwable $t){ $this->ipbwi4wp->alert->add($t->getMessage()); return false; }
				
				if(isset($user['id'])){
					$this->ipbwi4wp->cache->save('ipb_member', $user['id'], $user);
					$this->ipbwi4wp->cache->save('ipb_member_name', $user['name'], $user);
					$this->ipbwi4wp->cache->save('ipb_member_email', $user['email'], $user);
				}
				return $user;
			}else{
				return $this->ipbwi4wp->cache->get('ipb_member_email', $email);
			}
		}
		/**
		 * @desc			get member's ban status
		 * @param	string	$id member's WP ID
		 * @return	bool	true when if banned otherwise false
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function ipb_is_banned($id){
			try{
				$response = $this->ipbwi4wp->ipbwi->extended->members('banned',$id);
				}catch(Throwable $t){ $this->ipbwi4wp->alert->add($t->getMessage()); return false; }
			
			if($response['status'] == 0){
				return false;
			}else{
				return true;
			}
		}
		/**
		 * @desc			check whether member exists in IPB
		 * @param	string	$id member's WP ID
		 * @return	bool	true when exists otherwise false
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function ipb_exists($id){
			try{
				$status		= $this->ipbwi4wp->ipbwi->sso->checkemail($this->wp_get_email_by_id($id));
				}catch(Throwable $t){ $this->ipbwi4wp->alert->add($t->getMessage()); return false; }
			
			if($status){
				return true;
			}else{
				return false;
			}
		}
		/**
		 * @desc			check whether member exists in WP
		 * @param	int		$id member's IPB ID
		 * @return	bool	true when exists otherwise false
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function ipb_get_email_by_id($id){
			$member			= $this->ipb_get($id);
			return $member['email'];
		}
		/**
		 * @desc			get the username from IPB via IPB user ID
		 * @param	int		$id member's IPB ID
		 * @return	bool	member's name
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function ipb_get_username_by_id($id){
			$member			= $this->ipb_get($id);
			return $member['name'];
		}
		/**
		 * @desc			check whether member exists in WP
		 * @param	int		$email member's email address
		 * @return	bool	true when exists otherwise false
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function wp_exists($id){
			if(get_user_by('email',$id)){
				return true;
			}else{
				return false;
			}
		}
		/**
		 * @desc			get the email from WP via WP user ID
		 * @param	int		$id member's WP ID
		 * @return	bool	member's email
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function wp_get_email_by_id($id){
			$wp_user		=  get_user_by('ID',$id);

			if($wp_user){
				return $wp_user->user_email;
			}else{
				return false;
			}
		}
		/**
		 * @desc			get wp user object via email
		 * @param	string	$email member's email
		 * @return	object	member's info object
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function wp_get_user_by_email($email){
			return get_user_by('email',$email);
		}
		/**
		 * @desc			get the WP user object by IPB ID
		 * @param	int		$id member's IPB ID
		 * @return	object	member's info object
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function wp_get_user_by_ipb_id($id){
			return $this->wp_get_user_by_email($this->ipb_get_email_by_id($id));
		}
		/**
		 * @desc			get WP user ID from IPB user ID
		 * @param	int		$id member's IPB ID
		 * @return	int		member's WP user ID
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function ipb_user_id_to_wp_user_id($id){
			$wp_user		= $this->wp_get_user_by_ipb_id($id);
			
			if($wp_user){
				return $wp_user->ID;
			}else{
				return false;
			}
		}
		/**
		 * @desc			get IPB user ID from WP user ID
		 * @param	int		$id member's WP user ID
		 * @return	int		member's IPB ID
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function wp_user_id_to_ipb_user_id($id){
			try{
				$user		= $this->ipb_get_by_email($this->wp_get_email_by_id($id));
				}catch(Throwable $t){ $this->ipbwi4wp->alert->add($t->getMessage()); return false; }

			if(isset($user['id'])){
				return $user['id'];
			}else{
				return false;
			}
		}
		/**
		 * @desc			get WP user ID from WP login name
		 * @param	string	$loginname WP login name
		 * @return	int		member's WP user ID
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function wp_loginname_to_wp_user_id($loginname){
			$wp_user		= get_user_by('login',$loginname);
			
			if($wp_user){
				return $wp_user->ID;
			}else{
				return false;
			}
		}
	}
?>