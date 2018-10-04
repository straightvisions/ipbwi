<?php
	/**
	 * @author			Matthias Reuter
	 * @package			settings
	 * @copyright		2007-2016 Matthias Reuter
	 * @link			http://ipbwi.com/
	 * @since			4.0
	 * @license			This is no free software. See license.txt or https://ipbwi.com
	 */
	class ipbwi4wp_user_import_settings extends ipbwi4wp_user_import{
		public $ipbwi4wp_user_import					= NULL;
		public $settings_default						= false;
		public $settings								= false;
		
		/**
		 * @desc			Loads other classes of package and defines available settings
		 * @author			Matthias Reuter
		 * @since			4.0
		 * @ignore
		 */
		public function __construct($ipbwi4wp_user_import){
			$this->ipbwi4wp_user_import					= isset($ipbwi4wp_user_import->ipbwi4wp_user_import) ? $ipbwi4wp_user_import->ipbwi4wp_user_import : $ipbwi4wp_user_import; // loads common classes
			
			$this->settings_default						= array(
				'ipbwi4wp_user_import_settings'			=> 0,
				'import'								=> array(
					'IPB_GROUPS_MAPPING'				=> array(
						'name'							=> __('Groups to Roles Mapping', 'ipbwi4wp_user_import'),
						'type'							=> 'select_rel',
						'placeholder'					=> '',
						'desc'							=> __('Map IPB Member Groups to WP Roles for import.', 'ipbwi4wp_user_import'),
						'value'							=> '',
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
			if(isset($_POST['ipbwi4wp_user_import_settings'])){
				if($_POST['ipbwi4wp_user_import_settings'] == 1){
					$options = get_option('ipbwi4wp_user_import');
					
					if($options && is_array($options)){
						$data						= array_replace_recursive($this->settings_default,$options,$_POST);
						$data						= $this->remove_inactive_checkbox_fields($data);
						$this->settings				= $data;
					}else{
						$data						= array_replace_recursive($this->settings_default,$_POST);
						$data						= $this->remove_inactive_checkbox_fields($data);
						$this->settings				= $data;
					}
					
					update_option('ipbwi4wp_user_import',$this->settings, true);
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
							$data[$group_name][$field_name]['value'] = (isset($_POST[$group_name][$field_name]['value']) ? 1 : 0);
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
				$this->settings = array_replace_recursive($this->settings_default,(array)get_option('ipbwi4wp_user_import'));
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
				'IPBWI4WP',													// parent slug
				__('User Import', 'ipbwi4wp_user_import'),					// page title
				__('User Import', 'ipbwi4wp'),								// menu title
				'activate_plugins',											// capability
				'IPBWI4WP_user_import',										// menu slug
				array($this,'get_tpl_user_import')							// callable function
			);
		}
		/**
		 * @desc			output SSO settings template file
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function get_tpl_user_import(){
			require_once($this->ipbwi4wp_user_import->path.'lib/assets/tpl/user_import.php');
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
			if($this->ipbwi4wp_user_import->basename == $plugin_file){
				$links				= array(
										'user_settings'			=> '<a href="admin.php?page=IPBWI4WP_user_import">'.__('User Import', 'ipbwi4wp_user_import').'</a>',
										'support'				=> '<a href="https://straightvisions.com/community/" target="_blank">'.__('Support', 'ipbwi4wp_user_import').'</a>',
										'documentation'			=> '<a href="https://ipbwi.com/ipbwi-for-wordpress/" target="_blank">'.__('Documentation', 'ipbwi4wp_user_import').'</a>',
				);
				$actions			= array_merge($links, $actions);
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
			if($hook == 'ipbwi4wp_page_IPBWI4WP_user_import'){
				wp_enqueue_style('ipbwi4wp_acp_style', IPBWI4WP_PLUGIN_URL.'lib/assets/css/acp.css');
				wp_enqueue_script('jquery-ui-core');
				wp_enqueue_script('jquery-ui-widget');
				wp_enqueue_script('jquery-ui-progressbar');
				wp_enqueue_script('ipbwi4wp_user_import',$this->ipbwi4wp_user_import->url.'lib/assets/js/ipbwi4wp_user_import.js');
				wp_enqueue_style('plugin_name-admin-ui-css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css');
				
				$members								= $this->ipbwi4wp_user_import->ipbwi4wp->member->ipb_list();
				wp_localize_script('ipbwi4wp_user_import', 'ipbwi4wp_user_import_vars', array(
					'page'								=> get_transient('ipbwi4wp_user_import_pages_completed') ? get_transient('ipbwi4wp_user_import_pages_completed') : 1,
					'pages_total'						=> $members['totalPages'],
					'members_total'						=> $members['totalResults'],
					'members_per_cycle'					=> $members['perPage']
				));
			}
		}
	}
?>