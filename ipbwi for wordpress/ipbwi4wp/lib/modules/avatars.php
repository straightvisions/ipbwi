<?php
	/**
	 * @author			Matthias Reuter
	 * @package			avatars
	 * @copyright		2007-2016 Matthias Reuter
	 * @link			http://ipbwi.com/
	 * @since			4.0
	 * @license			This is no free software. See license.txt or https://ipbwi.com
	 */
	class ipbwi4wp_avatars extends ipbwi4wp{
		public $ipbwi4wp			= NULL;

		/**
		 * @desc			Loads other classes of package
		 * @author			Matthias Reuter
		 * @since			4.0
		 * @ignore
		 */
		public function __construct($ipbwi4wp){
			$this->ipbwi4wp			= isset($ipbwi4wp->ipbwi4wp) ? $ipbwi4wp->ipbwi4wp : $ipbwi4wp; // loads common classes
		}
		public function ipb_get_avatar($avatar, $id_or_email, $size=false, $default=false, $alt=false){
			$user					= false;
			
			if(is_numeric($id_or_email)){
				$id					= (int)$id_or_email;
				$user				= get_user_by('id' , $id);
			}elseif(is_object($id_or_email)){
				if(!empty($id_or_email->user_id)){
					$id				= (int)$id_or_email->user_id;
					$user			= get_user_by('id' , $id);
				}
			}else{
				$user				= get_user_by('email', $id_or_email);	
			}
			
			if($user && is_object($user)){
				if(is_array($size)){
					$size			= $size['width'];
					$alt			= $size['alt'];
				}
				
				$ipb_user			= $this->ipbwi4wp->member->ipb_get($this->ipbwi4wp->member->wp_user_id_to_ipb_user_id($user->data->ID));
				if(isset($ipb_user['photoUrl'])){
					$avatar				= '<img alt="'.$alt.'" src="'.$ipb_user['photoUrl'].'" class="avatar avatar-'.$size.' photo" height="'.$size.'" width="'.$size.'" />';
				}
			}

			return $avatar;
		}
	}