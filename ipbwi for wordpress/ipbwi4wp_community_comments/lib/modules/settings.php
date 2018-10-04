<?php
	/**
	 * @author			Matthias Reuter
	 * @package			settings
	 * @copyright		2007-2016 Matthias Reuter
	 * @link			http://ipbwi.com/
	 * @since			4.0
	 * @license			This is no free software. See license.txt or https://ipbwi.com
	 */
	class ipbwi4wp_community_comments_settings extends ipbwi4wp_community_comments{
		public $ipbwi4wp_community_comments											= NULL;
		public $settings_default													= false;
		public $settings															= false;
		private $forums																= array();
		private $forums_hierarchically												= array();
		private $forums_hierarchically_dropdown										= '';
		
		/**
		 * @desc			Loads other classes of package and defines available settings
		 * @author			Matthias Reuter
		 * @since			4.0
		 * @ignore
		 */
		public function __construct($ipbwi4wp_community_comments){
			$this->ipbwi4wp_community_comments										= isset($ipbwi4wp_community_comments->ipbwi4wp_community_comments) ? $ipbwi4wp_community_comments->ipbwi4wp_community_comments : $ipbwi4wp_community_comments; // loads common classes
			
			$this->settings_default													= array(
				'ipbwi4wp_community_comments_settings'								=> 0,
				'basic'																=> array(
					'IPB_DEFAULT_FORUM'												=> array(
						'name'														=> __('Default Forum', 'ipbwi4wp_community_comments'),
						'type'														=> 'select',
						'placeholder'												=> '',
						'desc'														=> __('New discussion topics will be created here.', 'ipbwi4wp_community_comments'),
						'value'														=> '',
					),
					'IPB_DEFAULT_USER'												=> array(
						'name'														=> __('Default User', 'ipbwi4wp_community_comments'),
						'type'														=> 'text',
						'placeholder'												=> '',
						'desc'														=> __('IPS user ID - otherwise, currently logged in user or guest name will be used.', 'ipbwi4wp_community_comments'),
						'value'														=> '',
					),
					'IPB_HIDE_NEW_POSTS'											=> array(
						'name'														=> __('Hide new Posts', 'ipbwi4wp_community_comments'),
						'type'														=> 'text',
						'placeholder'												=> '',
						'desc'														=> __('Select whether new comments should be hidden for moderator approval.', 'ipbwi4wp_community_comments'),
						'value'														=> 0,
					),
					'IPB_SHOW_LEGACY_WP_POSTS'										=> array(
						'name'														=> __('Show legacy WordPress posts', 'ipbwi4wp_community_comments'),
						'type'														=> 'checkbox',
						'placeholder'												=> '',
						'desc'														=> __('You may have legacy WP based discussions on your site - activate this settings and these will still be shown on your site below the recent IPS based discussions. Please note that these will not be imported to your IPS based discussion topics and are shown on your WP site for archive purposes only.', 'ipbwi4wp_community_comments'),
						'value'														=> 0,
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
			if(isset($_POST['ipbwi4wp_community_comments_settings'])){
				if($_POST['ipbwi4wp_community_comments_settings'] == 1){
					$options = get_option('ipbwi4wp_community_comments');
					
					if($options && is_array($options)){
						$data														= array_replace_recursive($this->settings_default,$options,$_POST);
						$data														= $this->remove_inactive_checkbox_fields($data);
						$this->settings												= $data;
					}else{
						$data														= array_replace_recursive($this->settings_default,$_POST);
						$data														= $this->remove_inactive_checkbox_fields($data);
						$this->settings												= $data;
					}
					
					update_option('ipbwi4wp_community_comments',$this->settings, true);
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
				$this->settings														= array_replace_recursive($this->settings_default,(array)get_option('ipbwi4wp_community_comments'));
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
				__('Community Comments', 'ipbwi4wp_community_comments'),			// page title
				__('Community Comments', 'ipbwi4wp_community_comments'),			// menu title
				'activate_plugins',													// capability
				'ipbwi4wp_community_comments',										// menu slug
				function(){ require_once($this->ipbwi4wp_community_comments->path.'lib/assets/tpl/settings_community_comments.php'); }
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
			if($this->ipbwi4wp_community_comments->basename == $plugin_file){
				$links																= array(
										'user_settings'								=> '<a href="admin.php?page=ipbwi4wp_community_comments">'.__('Community Comments', 'ipbwi4wp_community_comments').'</a>',
										'support'									=> '<a href="https://straightvisions.com/community/" target="_blank">'.__('Support', 'ipbwi4wp_community_comments').'</a>',
										'documentation'								=> '<a href="https://ipbwi.com/ipbwi-for-wordpress/" target="_blank">'.__('Documentation', 'ipbwi4wp_community_comments').'</a>',
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
			if($hook == 'ipbwi4wp_page_ipbwi4wp_community_comments'){
				wp_enqueue_style('ipbwi4wp_acp_style', IPBWI4WP_PLUGIN_URL.'lib/assets/css/acp.css');
			}
		}
		public function get_forums($flush_cache=false){
			if(count($this->forums) == 0 || $flush_cache){
				return $this->forums												= $this->ipbwi4wp_community_comments->ipbwi4wp->ipbwi->extended->forums();
			}else{
				return $this->forums;
			}
		}
		public function get_forums_hierarchic($flush_cache=false){
			if(count($this->forums_hierarchically) == 0 || $flush_cache){
				$a																	= array();
				$p																	= array();
				
				foreach($this->get_forums($flush_cache) as $key => $value){
					if (!isset($a[$key])){
						// add child to array of all elements
						$a[$key]													= array();
					}
					if (!isset($a[$value['parent_id']])){
						// add parent to array of all elements
						$a[$value['parent_id']]										= array();

						// add reference to master list of parents
						$p[$value['parent_id']]										= &$a[$value['parent_id']];
					}
					if (!isset($a[$value['parent_id']][$key])){
						// add reference to child for this parent
						$a[$value['parent_id']][$key]								= &$a[$key];
					}
				}
				return $this->forums_hierarchically									= $p['-1'];
			}else{
				return $this->forums_hierarchically;
			}
		}
		public function get_forums_hierarchically_dropdown($flush_cache=false,$selected=''){
			if(strlen($this->forums_hierarchically_dropdown) == 0 || $flush_cache){
				return $this->forums_hierarchically_dropdown						= $this->generate_forums_hierarchically_dropdown($this->get_forums_hierarchic($flush_cache),$selected);
			}else{
				return $this->forums_hierarchically_dropdown;
			}
		}
		public function generate_forums_hierarchically_dropdown($level,$selected='',$indent='&nbsp;&nbsp;&nbsp;&nbsp;'){
			$output																	= '';
			foreach($level as $key => $value){
				$output																.= '<option value="'.$key.'"'.(($key == $selected) ? ' selected="selected"' : '').'>'.$indent.$this->forums[$key]['word_default'].'</option>';
				if(is_array($value) && count($value) > 0){
					$output															.= $this->generate_forums_hierarchically_dropdown($value,$selected,$indent.$indent);
				}
			}
			return $output;
		}
	}
?>