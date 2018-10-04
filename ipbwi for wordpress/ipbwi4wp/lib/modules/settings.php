<?php
	/**
	 * @author			Matthias Reuter
	 * @package			settings
	 * @copyright		2007-2016 Matthias Reuter
	 * @link			http://ipbwi.com/
	 * @since			4.0
	 * @license			This is no free software. See license.txt or https://ipbwi.com
	 */
	class ipbwi4wp_settings extends ipbwi4wp{
		public $ipbwi4wp			= NULL;
		public $settings_default	= false;
		public $settings			= false;
		
		/**
		 * @desc			Loads other classes of package and defines available settings
		 * @author			Matthias Reuter
		 * @since			4.0
		 * @ignore
		 */
		public function __construct($ipbwi4wp){
			$this->ipbwi4wp				= isset($ipbwi4wp->ipbwi4wp) ? $ipbwi4wp->ipbwi4wp : $ipbwi4wp; // loads common classes
			
			$this->settings_default						= array(
				'ipbwi4wp_settings'						=> 0,
				'sso'					=> array(
					'IPS_CONNECT_BASE_URL'				=> array(
						'name'							=> __('IP.board Base URL', 'ipbwi4wp'),
						'type'							=> 'text',
						'placeholder'					=> '',
						'desc'							=> __('The base URL to your IP.board installation.', 'ipbwi4wp'),
						'value'							=> '',
					),
					'IPS_CONNECT_MASTER_KEY'			=> array(
						'name'							=> __('Master IPS Connect Key', 'ipbwi4wp'),
						'type'							=> 'text',
						'placeholder'					=> '',
						'desc'							=> __('Go to IP.board ACP » System » Settings » Login Handlers', 'ipbwi4wp'),
						'value'							=> '',
					),
				), 'rest'								=> array(
					'IPS_REST_API_KEY'					=> array(
						'name'							=> __('IPS REST API Key', 'ipbwi4wp'),
						'type'							=> 'text',
						'placeholder'					=> '',
						'desc'							=> __('Go to IP.board ACP » System » Site Features » REST API', 'ipbwi4wp'),
						'value'							=> '',
					)
				), 'advanced'							=> array(
					'NO_URL_REWRITE'					=> array(
						'name'							=> __('Fallback for not activated URL Rewrite', 'ipbwi4wp'),
						'type'							=> 'checkbox',
						'placeholder'					=> '',
						'desc'							=> __('Activate this, if you have no htaccess based/compatible URL rewrite activated in IPS. In some scenarios, you may decide against this search engine friendly setting in IPS. To allow REST API requests working, activate this checkbox.', 'ipbwi4wp'),
						'value'							=> 0,
					),
					'NO_AUTH_HEADER'					=> array(
						'name'							=> __('Fallback for no Auth Header Support', 'ipbwi4wp'),
						'type'							=> 'checkbox',
						'placeholder'					=> '',
						'desc'							=> __('Some servers are configured to not support HTTP Basic Auth, e.g. when running PHP as CGI binary. IPS will give you a note about that in IPS ACP -> REST API. With activating this setting, you will allow to send the API key as GET parameter.', 'ipbwi4wp'),
						'value'							=> 0,
					),
					'ALLOW_DELETE'						=> array(
						'name'							=> __('Activate synchronized member deletion', 'ipbwi4wp'),
						'type'							=> 'checkbox',
						'placeholder'					=> '',
						'desc'							=> __('To avoid any unwanted data lost, we make member account deletion synchronization as opt in feature. By activating this checkbox, deletion of a member account in IP.board will result in deletion of member in WordPress and vice versa.', 'ipbwi4wp'),
						'value'							=> 0,
					),
					'REASSIGN_TO'						=> array(
						'name'							=> __('Reassign WordPress Posts upon deletion', 'ipbwi4wp'),
						'type'							=> 'text',
						'placeholder'					=> '',
						'desc'							=> __('When Synchronized member deletion is activated and a member is going to be deleted through IP.board, you can set a WP user loginname here to reassign all WP posts to that account rather than deleting them.', 'ipbwi4wp'),
						'value'							=> '',
					),
					'SYNC_USERNAME_TO_PUBLICNAME'		=> array(
						'name'							=> __('Sync IPS username to WP Publicname', 'ipbwi4wp'),
						'type'							=> 'checkbox',
						'placeholder'					=> '',
						'desc'							=> __('Since IPS has dropped separate display name feature in v4, you may want to activate this setting to allow IPBWI for WordPress v4 to set the publicname of a user in WordPress when the username in IPS has been changed.', 'ipbwi4wp'),
						'value'							=> 0,
					)
				), 'network'							=> array(
					'ALL_SITES'							=> array(
						'name'							=> __('Register to all sites', 'ipbwi4wp'),
						'type'							=> 'checkbox',
						'placeholder'					=> '',
						'desc'							=> __('Register new users to whole blog network (all sites) instead of just the current site only.', 'ipbwi4wp'),
						'value'							=> 0,
					)
				),
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
			
			// The base URL to your IP.board installation. Must include a trailing slash.
			if(!defined('ipbwi_IPS_CONNECT_BASE_URL')){
				define('ipbwi_IPS_CONNECT_BASE_URL',$this->get_IPS_CONNECT_BASE_URL());
			}
			// Master KEY as shown in Login Handler Overview in IP.board's ACP
			if(!defined('ipbwi_IPS_CONNECT_MASTER_KEY')){
				define('ipbwi_IPS_CONNECT_MASTER_KEY',$this->get_IPS_CONNECT_MASTER_KEY());
			}
			// Slave URL
			if(!defined('ipbwi_IPS_CONNECT_SLAVE_URL')){
				define('ipbwi_IPS_CONNECT_SLAVE_URL',get_site_url().'/wp-json/ipbwi4wp/v4/');
			}
			// Slave Unique Key
			if(!defined('ipbwi_IPS_CONNECT_SLAVE_KEY')){
				define('ipbwi_IPS_CONNECT_SLAVE_KEY',md5(get_site_url()));
			}
			// REST API KEY
			if(!defined('ipbwi_IPS_REST_API_KEY')){
				define('ipbwi_IPS_REST_API_KEY',$this->get_IPS_REST_API_KEY());
			}
		}
		/**
		 * @desc			update settings
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function set_settings(){
			if(isset($_POST['ipbwi4wp_settings'])){
				if($_POST['ipbwi4wp_settings'] == 1){
					$options = get_option('ipbwi4wp');
					
					if($options && is_array($options)){
						$data						= array_replace_recursive($this->settings_default,$options,$_POST);
						$data						= $this->remove_inactive_checkbox_fields($data);
						$this->settings				= $data;
					}else{
						$data						= array_replace_recursive($this->settings_default,$_POST);
						$data						= $this->remove_inactive_checkbox_fields($data);
						$this->settings				= $data;
					}
					
					update_option('ipbwi4wp',$this->settings, true);
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
				$this->settings = array_replace_recursive($this->settings_default,(array)get_option('ipbwi4wp'));
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
			add_menu_page(
				__('SSO Settings', 'ipbwi4wp'),								// page title
				__('IPBWI4WP', 'ipbwi4wp'),									// menu title
				'activate_plugins',											// capability
				'IPBWI4WP',													// menu slug
				false,														// callable function
				IPBWI4WP_PLUGIN_URL.'lib/assets/img/logo_icon.png'			// icon url
			);
			add_submenu_page(
				'IPBWI4WP',													// parent slug
				__('SSO', 'ipbwi4wp'),										// page title
				__('SSO', 'ipbwi4wp'),										// menu title
				'activate_plugins',											// capability
				'IPBWI4WP',													// menu slug
				array($this,'get_tpl_sso_settings')							// callable function
			);
			add_submenu_page(
				'IPBWI4WP',													// parent slug
				__('Extensions', 'ipbwi4wp'),								// page title
				__('Extensions', 'ipbwi4wp'),								// menu title
				'activate_plugins',											// capability
				'IPBWI4WP_extensions',										// menu slug
				array($this,'get_tpl_extensions')							// callable function
			);
		}
		/**
		 * @desc			output SSO settings template file
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function get_tpl_sso_settings(){
			require_once(IPBWI4WP_DIR.'lib/assets/tpl/settings_sso.php');
		}
		/**
		 * @desc			output Extensions template file
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function get_tpl_extensions(){
			require_once(IPBWI4WP_DIR.'lib/assets/tpl/extensions.php');
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
			if(IPBWI4WP_PLUGIN_BASENAME == $plugin_file){
				$links				= array(
										'user_settings'			=> '<a href="admin.php?page=IPBWI4WP">'.__('SSO Settings', 'ipbwi4wp').'</a>',
										'support'				=> '<a href="https://straightvisions.com/community/" target="_blank">'.__('Support', 'ipbwi4wp').'</a>',
										'documentation'			=> '<a href="https://ipbwi.com/ipbwi-for-wordpress/" target="_blank">'.__('Documentation', 'ipbwi4wp').'</a>',
				);
				$actions			= array_merge($links, $actions);
			}
			return $actions;
		}
		/**
		 * @desc			get IPS connect Base URL
		 * @return	string	IPS connect Base URL or false
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function get_IPS_CONNECT_BASE_URL(){
			if(isset($this->settings['sso']['IPS_CONNECT_BASE_URL']['value']) && strlen($this->settings['sso']['IPS_CONNECT_BASE_URL']['value']) > 0){
				return trailingslashit(trim($this->settings['sso']['IPS_CONNECT_BASE_URL']['value']));
			}else{
				return false;
			}
		}
		/**
		 * @desc			get IPS connect Master Key
		 * @return	string	IPS connect Master Key or false
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function get_IPS_CONNECT_MASTER_KEY(){
			if(isset($this->settings['sso']['IPS_CONNECT_MASTER_KEY']['value']) && strlen($this->settings['sso']['IPS_CONNECT_MASTER_KEY']['value']) > 0){
				return trim($this->settings['sso']['IPS_CONNECT_MASTER_KEY']['value']);
			}else{
				return false;
			}
		}
		/**
		 * @desc			get IPS REST API Key
		 * @return	string	IPS REST API Key or false
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function get_IPS_REST_API_KEY(){
			if(isset($this->settings['rest']['IPS_REST_API_KEY']['value']) && strlen($this->settings['rest']['IPS_REST_API_KEY']['value']) > 0){
				return trim($this->settings['rest']['IPS_REST_API_KEY']['value']);
			}else{
				return false;
			}
		}
		/**
		 * @desc			ACP scripts and styles
		 * @param	string	$hook location in WP Admin
		 * @return	void	
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function acp_style($hook){
			if($hook == 'toplevel_page_IPBWI4WP' || $hook == 'ipbwi4wp_page_IPBWI4WP_extensions'){
				wp_enqueue_style('ipbwi4wp_acp_style', IPBWI4WP_PLUGIN_URL.'lib/assets/css/acp.css');
				wp_enqueue_script('suggest');
				wp_enqueue_script('sv_suggest',IPBWI4WP_PLUGIN_URL.'lib/assets/js/autosuggest.js');
			}
		}
		/**
		 * @desc			get text field for settings template
		 * @param	string	$group settings group
		 * @param	string	$key settings key
		 * @param	array	$data default settings data for current settings field
		 * @return	string	input text field
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		private function get_text($group,$key,$data){
			return '<input name="'.$group.'['.$key.'][value]" type="text" value="'.(isset($this->settings[$group][$key]['value']) ? $this->settings[$group][$key]['value'] : $data['value']).'" id="'.$key.'" />';
		}
		/**
		 * @desc			get checkbox for settings template
		 * @param	string	$group settings group
		 * @param	string	$key settings key
		 * @param	array	$data default settings data for current settings field
		 * @return	string	checkbox field
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		private function get_checkbox($group,$key,$data){
			return '<input name="'.$group.'['.$key.'][value]" type="checkbox" value="'.(isset($this->settings[$group][$key]['value']) ? $this->settings[$group][$key]['value'] : $data['value']).'" '.((isset($this->settings[$group][$key]['value']) && $this->settings[$group][$key]['value'] == 1) ? 'checked="checked"' : '').' id="'.$key.'" />';
		}
		/**
		 * @desc			get form field
		 * @param	string	$group settings group
		 * @param	string	$key settings key
		 * @param	array	$data default settings data for current settings field
		 * @return	string	form field
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		private function get_form_field($group,$key,$data){
			if($data['type'] == 'text'){
				return $this->get_text($group,$key,$data);
			}elseif($data['type'] == 'checkbox'){
				return $this->get_checkbox($group,$key,$data);
			}
		}
		/**
		 * @desc			get form block
		 * @param	string	$group settings group
		 * @param	string	$key settings key
		 * @param	array	$data default settings data for current settings field
		 * @return	string	form block
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function get_form_block($group,$key,$data){
			return '
			<div class="ipbwi4wp_setting ipbwi4wp_setting_'.$data['type'].'">
				<div class="ipbwi4wp_setting_name">'.$data['name'].'</div>
				<div class="ipbwi4wp_setting_desc">'.$data['desc'].'</div>
				<div class="ipbwi4wp_setting_value">'.$this->get_form_field($group,$key,$data).'</div>
			</div>';
		}
		/**
		 * @desc			get suggested users
		 * @return	void	
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function suggest_users(){
			$args = array(
				'orderby'      => 'login',
				'order'        => 'ASC',
				'search'       => '*'.$_GET['q'].'*',
				'number'       => 5,
				'count_total'  => false,
				'fields'       => 'all',
				'who'          => 'authors'
			 );
			$users = get_users($args);
			foreach ($users as $user) {
				echo $user->data->user_login."\n";
			}
			die();
		}
	}
?>