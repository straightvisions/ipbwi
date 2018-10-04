<?php
	/**
	 * @author			Matthias Reuter
	 * @package			comments
	 * @copyright		2007-2017 Matthias Reuter
	 * @link			http://ipbwi.com/
	 * @since			4.0
	 * @license			This is no free software. See license.txt or https://ipbwi.com
	 */
	class ipbwi4wp_community_comments_comments extends ipbwi4wp_community_comments{
		public $ipbwi4wp_community_comments					= NULL;
		public $topic_id									= false;
		public $post_id										= false;
		
		/**
		 * @desc			Loads other classes of package
		 * @author			Matthias Reuter
		 * @since			4.0
		 * @ignore
		 */
		public function __construct($ipbwi4wp_community_comments){
			$this->ipbwi4wp_community_comments				= isset($ipbwi4wp_community_comments->ipbwi4wp_community_comments) ? $ipbwi4wp_community_comments->ipbwi4wp_community_comments : $ipbwi4wp_community_comments; // loads common classes
		}
		public function topic_exists($post_id){
			if($this->topic_id){
				return true;
			}else{
				$tid										= get_post_meta($post_id, 'ipbwi_cc_topic_id', true);
				if(intval($tid) > 0){
					$this->topic_id							= $tid;
					return true;
				}else{
					return false;
				}
			}
		}
		public function create_topic(){
			if(!$this->topic_exists($_REQUEST['comment_post_ID'])){
				if(intval($this->ipbwi4wp_community_comments->settings->settings['basic']['IPB_DEFAULT_USER']['value']) > 0){
					$author_id								= intval($this->ipbwi4wp_community_comments->settings->settings['basic']['IPB_DEFAULT_USER']['value']);
					$author_name							= '';
				}elseif(is_user_logged_in()){
					$author_id								= intval($this->ipbwi4wp_community_comments->ipbwi4wp->member->wp_user_id_to_ipb_user_id(get_current_user_id()));
					$author_name							= '';
				}else{ // guest
					$author_id								= 0;
					$author_name							= $_REQUEST['author'];
				}
			
				$params										= apply_filters('ipbwi4wp_community_comments_create_topic', array(
					'forum'									=> $this->ipbwi4wp_community_comments->settings->settings['basic']['IPB_DEFAULT_FORUM']['value'],
					'author'								=> $author_id,
					'title'									=> get_the_title($_REQUEST['comment_post_ID']),
					'post'									=> sprintf(__('Discussion about %1$s. %2$sRead full article%2$s', 'ipbwi4wp_community_comments'),get_the_title($_REQUEST['comment_post_ID']),'<a href="'.get_permalink($_REQUEST['comment_post_ID']).'">','</a>'),
					'author_name'							=> $author_name,
					'ip_address'							=> $_SERVER['REMOTE_ADDR'],
					'hidden'								=> $this->hidden
				));
				try{
					$topic									= $this->ipbwi4wp_community_comments->ipbwi4wp->ipbwi->core->topics(NULL,$params);
					$this->topic_id							= $topic['id'];
					add_post_meta($_REQUEST['comment_post_ID'], 'ipbwi_cc_topic_id', $this->topic_id, true);
				}catch(Throwable $t){ $this->ipbwi4wp_community_comments->ipbwi4wp->alert->add($t->getMessage()); return false; }
				catch(Exception $e){ $this->ipbwi4wp_community_comments->ipbwi4wp->alert->add($e->getMessage()); return false; }
			}
		}
		public function hidden(){
				if(intval($this->ipbwi4wp_community_comments->settings->settings['basic']['IPB_HIDE_NEW_POSTS']['value']) == 0){
					$this->hidden							= 0;
				}elseif(intval($this->ipbwi4wp_community_comments->settings->settings['basic']['IPB_HIDE_NEW_POSTS']['value']) == 1 && !is_user_logged_in()){
					$this->hidden							= 1;
				}else{
					$this->hidden							= 1;
				}
		}
		public function create(){
			if(isset($_REQUEST['comment']) && isset($_REQUEST['comment_post_ID']) && comments_open($_REQUEST['comment_post_ID'])){
				if(get_option('comment_registration') && !is_user_logged_in()){
					printf( __( 'You must be %1$slogged in%2$s to post a comment.', 'ipbwi4wp_community_comments' ), '<a href="<?php echo wp_login_url(get_permalink()); ?>">', '</a>' );
					die();
					return; // login required
				}else{
					$this->hidden();
					$this->create_topic();
					
					if(is_user_logged_in()){
						$author_id							= intval($this->ipbwi4wp_community_comments->ipbwi4wp->member->wp_user_id_to_ipb_user_id(get_current_user_id()));
						$author_name						= '';
					}else{ // guest
						$author_id							= 0;
						$author_name						= $_REQUEST['author'];
					}
					
					// create post
					$params									= apply_filters('ipbwi4wp_community_comments_create_post', array(
						'topic'								=> $this->topic_id,
						'author'							=> $author_id,
						'post'								=> $_REQUEST['comment'],
						'author_name'						=> $author_name,
						'ip_address'						=> $_SERVER['REMOTE_ADDR'],
						'hidden'							=> $this->hidden
					));
					
					try{
						$post								= $this->ipbwi4wp_community_comments->ipbwi4wp->ipbwi->core->posts(NULL,$params);
						$this->post_id						= $post['id'];
					}catch(Throwable $t){ $this->ipbwi4wp_community_comments->ipbwi4wp->alert->add($t->getMessage()); return false; }
					catch(Exception $e){ $this->ipbwi4wp_community_comments->ipbwi4wp->alert->add($e->getMessage()); return false; }
					
					return $this->post_id;
				}
			}
		}
		public function get_comments_number($count, $post_id){
			if(empty($this->ipbwi4wp_community_comments->settings->settings['basic']['IPB_SHOW_LEGACY_WP_POSTS']['value']) || $this->ipbwi4wp_community_comments->settings->settings['basic']['IPB_SHOW_LEGACY_WP_POSTS']['value'] != 1) {
				$count										= 0;
			}
			if(!$this->topic_exists($post_id)){
				return $count+0;
			}else{
				$posts				= get_transient('ipbwi4wp_cc_posts_calc_'.$this->topic_id);
				
				if(!$posts){
					$posts			= $GLOBALS['ipbwi4wp_community_comments']->ipbwi4wp->ipbwi->core->topic_posts($this->topic_id,array('hidden' => 0));
					set_transient('ipbwi4wp_cc_posts_calc_'.$this->topic_id, $posts, 60*60);
				}
				return $count+$posts['totalResults']-1;
			}
		}
		public function comments_template(){
			global $post;
			
			if(!(is_singular() && (have_comments() || 'open' == $post->comment_status))){
				return;
			}
			if(file_exists(get_stylesheet_directory().'/ipbwi/community_comments.php')){
				return get_stylesheet_directory().'/ipbwi/community_comments.php';
			}else{
				return $this->ipbwi4wp_community_comments->path.'lib/assets/tpl/community_comments.php';
			}
		}
	}
?>