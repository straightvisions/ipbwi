<?php
	/**
	 * @author			Matthias Reuter
	 * @package			hooks
	 * @copyright		2007-2016 Matthias Reuter
	 * @link			http://ipbwi.com/
	 * @since			4.0
	 * @license			This is no free software. See license.txt or https://ipbwi.com
	 */
	class ipbwi4wp_hooks extends ipbwi4wp{
		public $ipbwi4wp			= NULL;
		
		/**
		 * @desc			Loads other classes of package
		 * @author			Matthias Reuter
		 * @since			4.0
		 * @ignore
		 */
		public function __construct($ipbwi4wp){
			$this->ipbwi4wp				= isset($ipbwi4wp->ipbwi4wp) ? $ipbwi4wp->ipbwi4wp : $ipbwi4wp; // loads common classes
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
			if(is_admin()){
				add_action('admin_notices', array($this->ipbwi4wp->alert,'admin_notices'));
			}
			
			add_action('admin_menu', array($this->ipbwi4wp->settings, 'get_settings_menu'));
			add_action('admin_enqueue_scripts', array($this->ipbwi4wp->settings,'acp_style'));
			add_action('wp_ajax_svsuggestusers', array($this->ipbwi4wp->settings,'suggest_users'));
			if($this->ipbwi4wp->status()){
				add_action('rest_api_init', function(){
					register_rest_route('ipbwi4wp', '/v4', array(
						'methods'	=> 'GET',
						'callback'	=> array($this->ipbwi4wp->sso_by_ipb,'wp_rest_api_extension'),
					));
				});
				
				// sso by wp
				add_action('wp_logout', array($this->ipbwi4wp->sso_by_wp, 'logout'));
				add_action('user_register', array($this->ipbwi4wp->sso_by_wp, 'register'), 10, 1);
				add_action('profile_update', array($this->ipbwi4wp->sso_by_wp, 'update'), 10, 2);
				add_action('delete_user', array($this->ipbwi4wp->sso_by_wp, 'delete'), 10, 1);
				add_action('wpmu_delete_user', array($this->ipbwi4wp->sso_by_wp, 'delete'), 10, 1);
				add_action('password_reset', array($this->ipbwi4wp->sso_by_wp, 'update_password'), 10, 2);
			}
		}
		/**
		 * @desc			initialize filters
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function filters(){
			add_filter('plugin_action_links', array($this->ipbwi4wp->settings,'plugin_action_links'), 10, 5);
			
			if($this->ipbwi4wp->status()){
				// sso by wp
				add_filter('authenticate', array($this->ipbwi4wp->sso_by_wp, 'login'), 99999999, 3);
				
				// sso by wooCommerce
				add_filter('woocommerce_registration_redirect', array($this->ipbwi4wp->sso_by_wp, 'woocommerce_registration_redirect'), 10, 1);
				
				// avatars
				add_filter('pre_get_avatar', array($this->avatars,'ipb_get_avatar'), 99 , 5);
				add_filter('get_avatar', array($this->avatars,'ipb_get_avatar'), 99 , 5);
			}
		}
	}
?>