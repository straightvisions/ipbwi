<?php
	/**
	 * @author			Matthias Reuter
	 * @package			settings
	 * @copyright		2007-2016 Matthias Reuter
	 * @link			http://ipbwi.com/
	 * @since			4.0
	 * @license			This is no free software. See license.txt or https://ipbwi.com
	 */
	class ipbwi4wp_pages_import_settings extends ipbwi4wp_pages_import{
		public $ipbwi4wp_pages_import					= NULL;
		public $settings_default						= false;
		public $settings								= false;
		
		/**
		 * @desc			Loads other classes of package and defines available settings
		 * @author			Matthias Reuter
		 * @since			4.0
		 * @ignore
		 */
		public function __construct($ipbwi4wp_pages_import){
			$this->ipbwi4wp_pages_import				= isset($ipbwi4wp_pages_import->ipbwi4wp_pages_import) ? $ipbwi4wp_pages_import->ipbwi4wp_pages_import : $ipbwi4wp_pages_import; // loads common classes
			
			$this->settings_default						= array(
				'ipbwi4wp_pages_import_settings'		=> 0,
				'import'								=> array(
					'IPB_PAGES_DATABASE'				=> array(
						'name'							=> __('IPS Pages Database', 'ipbwi4wp'),
						'type'							=> 'select',
						'placeholder'					=> '',
						'desc'							=> __('Select a Database for Import', 'ipbwi4wp'),
						'value'							=> '',
					),
					'IPB_PAGES_POST_TYPE'				=> array(
						'name'							=> __('WordPress Post Type', 'ipbwi4wp'),
						'type'							=> 'select',
						'placeholder'					=> '',
						'desc'							=> __('Select a WordPress Post Type for Import', 'ipbwi4wp'),
						'value'							=> 'post',
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
			if(isset($_POST['ipbwi4wp_pages_import_settings'])){
				if($_POST['ipbwi4wp_pages_import_settings'] == 1){
					$options = get_option('ipbwi4wp_pages_import');
					
					if($options && is_array($options)){
						$data						= array_replace_recursive($this->settings_default,$options,$_POST);
						$data						= $this->remove_inactive_checkbox_fields($data);
						$this->settings				= $data;
					}else{
						$data						= array_replace_recursive($this->settings_default,$_POST);
						$data						= $this->remove_inactive_checkbox_fields($data);
						$this->settings				= $data;
					}
					
					update_option('ipbwi4wp_pages_import',$this->settings, true);
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
				$this->settings = array_replace_recursive($this->settings_default,(array)get_option('ipbwi4wp_pages_import'));
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
				__('Pages Import', 'ipbwi4wp_pages_import'),				// page title
				__('Pages Import', 'ipbwi4wp'),								// menu title
				'activate_plugins',											// capability
				'IPBWI4WP_pages_import',									// menu slug
				array($this,'get_tpl_pages_import')							// callable function
			);
		}
		/**
		 * @desc			output SSO settings template file
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function get_tpl_pages_import(){
			require_once($this->ipbwi4wp_pages_import->path.'lib/assets/tpl/pages_import.php');
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
			if($this->ipbwi4wp_pages_import->basename == $plugin_file){
				$links				= array(
										'pages_settings'		=> '<a href="admin.php?page=IPBWI4WP_pages_import">'.__('Pages Import', 'ipbwi4wp_pages_import').'</a>',
										'support'				=> '<a href="https://straightvisions.com/community/" target="_blank">'.__('Support', 'ipbwi4wp_pages_import').'</a>',
										'documentation'			=> '<a href="https://ipbwi.com/ipbwi-for-wordpress/" target="_blank">'.__('Documentation', 'ipbwi4wp_pages_import').'</a>',
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
			if($hook == 'ipbwi4wp_page_IPBWI4WP_pages_import'){
				wp_enqueue_style('ipbwi4wp_acp_style', IPBWI4WP_PLUGIN_URL.'lib/assets/css/acp.css');
				wp_enqueue_script('jquery-ui-core');
				wp_enqueue_script('jquery-ui-widget');
				wp_enqueue_script('jquery-ui-progressbar');
				wp_enqueue_script('ipbwi4wp_pages_import',$this->ipbwi4wp_pages_import->url.'lib/assets/js/ipbwi4wp_pages_import.js');
				wp_enqueue_style('plugin_name-admin-ui-css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css');
				
				$databases										= $this->ipbwi4wp_pages_import->ipbwi4wp->pages->get_databases();
				$records_count									= 0;
				foreach($databases as $database){
					$records[$database['database_id']]			= $this->ipbwi4wp_pages_import->ipbwi4wp->pages->get_records($database['database_id']);
					if(isset($this->settings['import']['IPB_PAGES_DATABASE']['value']) && intval($this->settings['import']['IPB_PAGES_DATABASE']['value']) > 0 && $database['database_id'] == $this->settings['import']['IPB_PAGES_DATABASE']['value']){
						$database_selected						= $database;
					}
				}
				wp_localize_script('ipbwi4wp_pages_import', 'ipbwi4wp_pages_import_vars', array(
					'page'										=> get_transient('ipbwi4wp_pages_import_pages_completed') ? get_transient('ipbwi4wp_pages_import_pages_completed') : 1,
					'pages_total'								=> $records[$database_selected['database_id']]['totalPages'],
					'records_total'								=> $records[$database_selected['database_id']]['totalResults'],
					'records_per_cycle'							=> $records[$database_selected['database_id']]['perPage'],
				));
			}
		}
		public function allow_duplicate_comments($dupe_id, $commentdata){
			return false; // allow duplicate comments
		}
	}
?>