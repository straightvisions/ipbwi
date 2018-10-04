<?php
	/**
	 * @author			Matthias Reuter
	 * @package			hooks
	 * @copyright		2007-2016 Matthias Reuter
	 * @link			http://ipbwi.com/
	 * @since			4.0
	 * @license			This is no free software. See license.txt or https://ipbwi.com
	 */
	class ipbwi4wp_pages_import_hooks extends ipbwi4wp_pages_import{
		public $ipbwi4wp_pages_import			= NULL;
		
		/**
		 * @desc			Loads other classes of package
		 * @author			Matthias Reuter
		 * @since			4.0
		 * @ignore
		 */
		public function __construct($ipbwi4wp_pages_import){
			$this->ipbwi4wp_pages_import				= isset($ipbwi4wp_pages_import->ipbwi4wp_pages_import) ? $ipbwi4wp_pages_import->ipbwi4wp_pages_import : $ipbwi4wp_pages_import; // loads common classes
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
			add_action('admin_menu', array($this->ipbwi4wp_pages_import->settings, 'get_settings_menu'));
			add_action('admin_enqueue_scripts', array($this->ipbwi4wp_pages_import->settings, 'acp_style'));
			add_action('wp_ajax_ipbwi4wp_pages_import', array($this->ipbwi4wp_pages_import->ajax, 'init'));
		}
		/**
		 * @desc			initialize filters
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function filters(){
			add_filter('plugin_action_links', array($this->ipbwi4wp_pages_import->settings,'plugin_action_links'), 10, 5);
			add_filter('duplicate_comment_id', array($this->ipbwi4wp_pages_import->settings,'allow_duplicate_comments'), 10, 2);
		}
	}
?>