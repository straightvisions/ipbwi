<?php
	/**
	 * @author			Matthias Reuter
	 * @package			settings
	 * @copyright		2007-2017 Matthias Reuter
	 * @link			http://ipbwi.com/
	 * @since			4.0
	 * @license			This is no free software. See license.txt or https://ipbwi.com
	 */
	class ipbwi4wp_sync_groups_settings extends ipbwi4wp_sync_groups{
		public $ipbwi4wp_sync_groups												= NULL;
		public $settings_default													= false;
		public $settings															= false;
		
		/**
		 * @desc			Loads other classes of package and defines available settings
		 * @author			Matthias Reuter
		 * @since			4.0
		 * @ignore
		 */
		public function __construct($ipbwi4wp_sync_groups){
			$this->core																= isset($ipbwi4wp_sync_groups->ipbwi4wp_sync_groups) ? $ipbwi4wp_sync_groups->ipbwi4wp_sync_groups : $ipbwi4wp_sync_groups; // loads common classes
			
			$this->settings_default													= array(
				'ipbwi4wp_sync_groups_settings'										=> 0,
				'basic'																=> array(
					'IPB_GROUPS_MAPPING'											=> array(
						'name'														=> __('Groups to Roles Mapping', 'ipbwi4wp_sync_groups'),
						'type'														=> 'select_rel',
						'placeholder'												=> '',
						'desc'														=> __('Map IPB Member Groups to WP Roles for synchronization.', 'ipbwi4wp_sync_groups'),
						'value'														=> '',
					)
				)
			);
		}
		/**
		 * @desc			initialize settings and set constants for IPBWI API
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function init(){
			// update settings
			$this->set_settings();
			
			// get settings
			$this->get_settings();
		}
		/**
		 * @desc			update settings
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function set_settings(){
			if(isset($_POST['ipbwi4wp_sync_groups_settings'])){
				if($_POST['ipbwi4wp_sync_groups_settings'] == 1){
					$options = get_option('ipbwi4wp_sync_groups');
					
					if($options && is_array($options)){
						$data														= array_replace_recursive($this->settings_default,$options,$_POST);
						$data														= $this->remove_inactive_checkbox_fields($data);
						$this->settings												= $data;
					}else{
						$data														= array_replace_recursive($this->settings_default,$_POST);
						$data														= $this->remove_inactive_checkbox_fields($data);
						$this->settings												= $data;
					}
					
					update_option('ipbwi4wp_sync_groups',$this->settings, true);
				}
			}
		}
		/**
		 * @desc			if checkbox fields are unchecked, update value to 0
		 * @param	int		$data settings data
		 * @return	array	updated settings data
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		private function remove_inactive_checkbox_fields($data){
			foreach($data as $group_name => $group){
				if(is_array($group)){
					foreach($group as $field_name => $field){
						if($field['type'] == 'checkbox'){
							$data[$group_name][$field_name]['value']				= (isset($_POST[$group_name][$field_name]['value']) ? 1 : 0);
						}
					}
				}
			}
			return $data;
		}
		/**
		 * @desc			get settings
		 * @return	array	settings array
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function get_settings(){
			if($this->settings){
				return $this->settings;
			}else{
				$this->settings														= array_replace_recursive($this->settings_default,(array)get_option('ipbwi4wp_sync_groups'));
				return $this->settings;
			}
		}
		/**
		 * @desc			get default settings
		 * @return	array	default settings
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function get_settings_default(){
			return $this->settings_default;
		}
		/**
		 * @desc			define settings menu
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function get_settings_menu(){
			add_submenu_page(
				'IPBWI4WP',															// parent slug
				__('Sync Groups', 'ipbwi4wp_sync_groups'),							// page title
				__('Sync Groups', 'ipbwi4wp_sync_groups'),							// menu title
				'activate_plugins',													// capability
				'ipbwi4wp_sync_groups',												// menu slug
				function(){ require_once($this->core->path.'lib/assets/tpl/settings.php'); }
			);
		}
		/**
		 * @desc			output the plugin action links
		 * @param	array	$actions default plugin action links
		 * @param	string	$plugin_file plugin's file name
		 * @return	array	updated plugin action links
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function plugin_action_links($actions, $plugin_file){
			if($this->core->basename == $plugin_file){
				$links																= array(
										'user_settings'								=> '<a href="admin.php?page=ipbwi4wp_sync_groups">'.__('Sync Groups', 'ipbwi4wp_sync_groups').'</a>',
										'support'									=> '<a href="https://straightvisions.com/community/" target="_blank">'.__('Support', 'ipbwi4wp_sync_groups').'</a>',
										'documentation'								=> '<a href="https://ipbwi.com/ipbwi-for-wordpress/" target="_blank">'.__('Documentation', 'ipbwi4wp_sync_groups').'</a>',
				);
				$actions															= array_merge($links, $actions);
			}
			return $actions;
		}
		/**
		 * @desc			ACP scripts and styles
		 * @param	string	$hook location in WP Admin
		 * @return	void	
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function acp_style($hook){
			if($hook == 'ipbwi4wp_page_ipbwi4wp_sync_groups'){
				wp_enqueue_style('ipbwi4wp_acp_style', IPBWI4WP_PLUGIN_URL.'lib/assets/css/acp.css');
			}
		}
	}
?>