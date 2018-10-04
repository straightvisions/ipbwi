<?php
	/**
	 * @author			Matthias Reuter
	 * @package			hooks
	 * @copyright		2007-2016 Matthias Reuter
	 * @link			http://ipbwi.com/
	 * @since			4.0
	 * @license			This is no free software. See license.txt or https://ipbwi.com
	 */
	class ipbwi4wp_digimember_hooks extends ipbwi4wp_digimember{
		public $ipbwi4wp_digimember					= NULL;
		
		/**
		 * @desc			Loads other classes of package
		 * @author			Matthias Reuter
		 * @since			4.0
		 * @ignore
		 */
		public function __construct($ipbwi4wp_digimember){
			$this->ipbwi4wp_digimember				= isset($ipbwi4wp_digimember->ipbwi4wp_digimember) ? $ipbwi4wp_digimember->ipbwi4wp_digimember : $ipbwi4wp_digimember; // loads common classes
		}
		/**
		 * @desc			initialize actions and filters
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function init(){
			$this->actions();
			$this->filters();
			$this->ipbwi4wp_digimember->sync->requests($_REQUEST);
		}
		/**
		 * @desc			initialize actions
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function actions(){
			add_action('admin_menu', array($this->ipbwi4wp_digimember->settings, 'get_settings_menu'));
			add_action('admin_enqueue_scripts', array($this->ipbwi4wp_digimember->settings, 'acp_style'));
			
			// DigiMember
			add_action('digimember_purchase', array($this->ipbwi4wp_digimember->sync, 'digimember_purchase'), 10, 4);
		}
		/**
		 * @desc			initialize filters
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function filters(){
			add_filter('plugin_action_links', array($this->ipbwi4wp_digimember->settings,'plugin_action_links'), 10, 5);
			
			// DigiMember
			add_filter('digimember_welcome_mail_placeholder_values', array($this->ipbwi4wp_digimember->sync, 'digimember_welcome_mail_placeholder_values'), 10, 4);
			
			// User Import
			add_filter('ipbwi4wp_user_import_update_username', array($this->ipbwi4wp_digimember->sync, 'ipbwi4wp_user_import_update_username'), 10, 2);
		}
	}
?>