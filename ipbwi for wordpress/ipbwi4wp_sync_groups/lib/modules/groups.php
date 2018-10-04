<?php
	/**
	 * @author			Matthias Reuter
	 * @package			groups
	 * @copyright		2007-2017 Matthias Reuter
	 * @link			http://ipbwi.com/
	 * @since			4.0
	 * @license			This is no free software. See license.txt or https://ipbwi.com
	 */
	class ipbwi4wp_sync_groups_groups extends ipbwi4wp_sync_groups{
		public $ipbwi4wp_sync_groups			= NULL;
		public $ips_hook						= false;
		
		/**
		 * @desc			Loads other classes of package
		 * @author			Matthias Reuter
		 * @since			4.0
		 * @ignore
		 */
		public function __construct($ipbwi4wp_sync_groups){
			$this->core				= isset($ipbwi4wp_sync_groups->ipbwi4wp_sync_groups) ? $ipbwi4wp_sync_groups->ipbwi4wp_sync_groups : $ipbwi4wp_sync_groups; // loads common classes
		}
		/**
		 * @desc			set user role, remove old ones
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function set_user_role($user_id,$role,$old_roles){
			if(!$this->ips_hook){
				if(isset($this->core->settings->settings['basic']['IPB_GROUPS_MAPPING']['value'][$role]) && $ips_group_id = $this->core->settings->settings['basic']['IPB_GROUPS_MAPPING']['value'][$role]){
					$ips_member_id		= $this->core->ipbwi4wp->member->wp_user_id_to_ipb_user_id($user_id);
					$ips_member			= $this->core->ipbwi4wp->ipbwi->core->members($ips_member_id);
					if($ips_group_id == $ips_member['primaryGroup']['id']){
						$this->core->ipbwi4wp->ipbwi->extended->updateSecondaryGroups($ips_member_id, array());
					}else{
						$this->core->ipbwi4wp->ipbwi->extended->updateSecondaryGroups($ips_member_id, array($ips_group_id));
					}
				}
			}
		}
		/**
		 * @desc			remove user role
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function remove_user_role($user_id,$role){
			if(!$this->ips_hook){
				if(isset($this->core->settings->settings['basic']['IPB_GROUPS_MAPPING']['value'][$role]) && $ips_group_id = $this->core->settings->settings['basic']['IPB_GROUPS_MAPPING']['value'][$role]){
					$ips_member_id		= $this->core->ipbwi4wp->member->wp_user_id_to_ipb_user_id($user_id);
					$ips_member			= $this->core->ipbwi4wp->ipbwi->core->members($ips_member_id);
					$ips_groups			= $ips_member['secondaryGroups'];

					if(($key = array_search($ips_group_id, $ips_groups)) !== false) {
						unset($ips_groups[$key]);
					}
					
					$this->core->ipbwi4wp->ipbwi->extended->updateSecondaryGroups($ips_member_id, $ips_groups);
				}
			}
		}
		/**
		 * @desc			add user role
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function add_user_role($user_id,$role){
			if(!$this->ips_hook){
				if(isset($this->core->settings->settings['basic']['IPB_GROUPS_MAPPING']['value'][$role]) && $ips_group_id = $this->core->settings->settings['basic']['IPB_GROUPS_MAPPING']['value'][$role]){
					$ips_member_id		= $this->core->ipbwi4wp->member->wp_user_id_to_ipb_user_id($user_id);
					$ips_member			= $this->core->ipbwi4wp->ipbwi->core->members($ips_member_id);
					$ips_groups			= $ips_member['secondaryGroups'];

					if(($key = array_search($ips_group_id, $ips_groups)) === false) {
						$ips_groups[]	= $ips_group_id;
					}
					
					$this->core->ipbwi4wp->ipbwi->extended->updateSecondaryGroups($ips_member_id, $ips_groups);
				}
			}
		}
		
		public function ips_hooks(){
			if($_REQUEST['do'] == 'IPS\\ipbwi_hook_ipbwi_member::save'){
				$this->ips_hook								= true;
				$data										= $_REQUEST['data'];
				$wp_role_map								= $this->core->settings->settings['basic']['IPB_GROUPS_MAPPING']['value'];
				$WP_user									= new WP_User($this->core->ipbwi4wp->member->ipb_user_id_to_wp_user_id($data['member']['id']));

				if($WP_user){
					$member_secondaries							= array();
					if(isset($data['member']['secondaryGroups']) && count($data['member']['secondaryGroups']) > 0){
						foreach($data['member']['secondaryGroups'] as $group){
							$member_secondaries[]					= $group['id'];
						}
					}
					$member_secondaries_old						= explode(',',$data['groups_orig']);
					
					$removed									= array_diff($member_secondaries_old, $member_secondaries);
					if(count($removed) > 0){
						foreach($removed as $ips_group_id){
							if(intval($ips_group_id) > 0){
								$keys = array_keys($wp_role_map, $ips_group_id);
								if(count($keys) > 0){
									foreach($keys as $key){
										$WP_user->remove_role($key);
									}
								}
							}
						}
					}
					
					$added										= array_diff($member_secondaries, $member_secondaries_old);
					if(count($added) > 0){
						foreach($added as $ips_group_id){
							if(intval($ips_group_id) > 0){
								$keys = array_keys($wp_role_map, $ips_group_id);
								if(count($keys) > 0){
									foreach($keys as $key){
										$WP_user->add_role($key);
									}
								}
							}
						}
					}
				}
				
			}
		}
	}
?>