<?php
	/**
	 * @author			Matthias Reuter
	 * @package			hooks
	 * @copyright		2007-2017 Matthias Reuter
	 * @link			http://ipbwi.com/
	 * @since			4.0
	 * @license			This is no free software. See license.txt or https://ipbwi.com
	 */
	class ipbwi4wp_sync_groups_hooks extends ipbwi4wp_sync_groups{
		public $ipbwi4wp_sync_groups			= NULL;
		
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
		 * @desc			initialize actions and filters
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function init(){
			$this->actions();
			$this->filters();
		}
		/**
		 * @desc			initialize actions
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function actions(){
			add_action('admin_menu', array($this->core->settings, 'get_settings_menu'));
			add_action('admin_enqueue_scripts', array($this->core->settings, 'acp_style'));
			add_action('set_user_role', array($this->core->groups, 'set_user_role'), 10, 3);
			add_action('remove_user_role', array($this->core->groups, 'remove_user_role'), 10, 2);
			add_action('add_user_role', array($this->core->groups, 'add_user_role'), 10, 2);
			
			// IPS hooks
			if(
				(isset($_REQUEST['id']) && isset($_REQUEST['key']) && $_REQUEST['key'] == md5(md5(get_site_url()).$_REQUEST['id'])) // ID delivered
				|| (isset($_REQUEST['key']) && $_REQUEST['key'] == md5(get_site_url())) // no ID delivered
			){
				$this->core->groups->ips_hooks();
			}
		}
		/**
		 * @desc			initialize filters
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function filters(){
			add_filter('plugin_action_links', array($this->core->settings,'plugin_action_links'), 10, 5);
		}
	}
?>