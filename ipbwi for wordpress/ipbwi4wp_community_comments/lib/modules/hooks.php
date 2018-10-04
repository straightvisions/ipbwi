<?php
	/**
	 * @author			Matthias Reuter
	 * @package			hooks
	 * @copyright		2007-2017 Matthias Reuter
	 * @link			http://ipbwi.com/
	 * @since			4.0
	 * @license			This is no free software. See license.txt or https://ipbwi.com
	 */
	class ipbwi4wp_community_comments_hooks extends ipbwi4wp_community_comments{
		public $ipbwi4wp_community_comments			= NULL;
		
		/**
		 * @desc			Loads other classes of package
		 * @author			Matthias Reuter
		 * @since			4.0
		 * @ignore
		 */
		public function __construct($ipbwi4wp_community_comments){
			$this->ipbwi4wp_community_comments				= isset($ipbwi4wp_community_comments->ipbwi4wp_community_comments) ? $ipbwi4wp_community_comments->ipbwi4wp_community_comments : $ipbwi4wp_community_comments; // loads common classes
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
			add_action('admin_menu', array($this->ipbwi4wp_community_comments->settings, 'get_settings_menu'));
			add_action('admin_enqueue_scripts', array($this->ipbwi4wp_community_comments->settings, 'acp_style'));
		}
		/**
		 * @desc			initialize filters
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function filters(){
			add_filter('plugin_action_links', array($this->ipbwi4wp_community_comments->settings,'plugin_action_links'), 10, 5);
			add_filter('comments_template', array($this->ipbwi4wp_community_comments->comments,'comments_template'));
			add_filter('get_comments_number', array($this->ipbwi4wp_community_comments->comments, 'get_comments_number'), 10, 2);
		}
	}
?>