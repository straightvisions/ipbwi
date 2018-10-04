<?php
	/**
	 * @author			Matthias Reuter
	 * @package			hooks
	 * @copyright		2007-2016 Matthias Reuter
	 * @link			http://ipbwi.com/
	 * @since			4.0
	 * @license			This is no free software. See license.txt or https://ipbwi.com
	 */
	class ipbwi4wp_pages_import_ajax extends ipbwi4wp_pages_import{
		public $ipbwi4wp_pages_import							= NULL;
		private $posts											= array();
		private $comments										= array();
		
		/**
		 * @desc			Loads other classes of package
		 * @author			Matthias Reuter
		 * @since			4.0
		 * @ignore
		 */
		public function __construct($ipbwi4wp_pages_import){
			$this->ipbwi4wp_pages_import						= isset($ipbwi4wp_pages_import->ipbwi4wp_pages_import) ? $ipbwi4wp_pages_import->ipbwi4wp_pages_import : $ipbwi4wp_pages_import; // loads common classes
		}
		private function load_wp_post_by_meta($record_id){
			global $wpdb;
			$post_id											= intval($wpdb->get_var('SELECT post_id FROM '.$wpdb->postmeta.' WHERE meta_key = "_ipbwi4wp_pages_import" AND meta_value = "'.$record_id.'" LIMIT 1'));

			if($post_id > 0){
				$this->posts[$record_id]						= $post_id;
				return $post_id;
			}else{
				return false;
			}
		}
		private function load_wp_comment_by_meta($ipb_comment_id){
			global $wpdb;
			$comment_id											= intval($wpdb->get_var('SELECT comment_id FROM '.$wpdb->commentmeta.' WHERE meta_key = "_ipbwi4wp_pages_import" AND meta_value = "'.$ipb_comment_id.'" LIMIT 1'));

			if($comment_id > 0){
				$this->comments[$ipb_comment_id]				= $comment_id;
				return $comment_id;
			}else{
				return false;
			}
		}
		private function create_comments($record,$page=1){
			if($record['comments'] > 0){
				try{
					$ipb_comments										= $this->ipbwi4wp_pages_import->ipbwi4wp->pages->get_record_comments($this->ipbwi4wp_pages_import->settings->settings['import']['IPB_PAGES_DATABASE']['value'],$record['id'],$page);
				}catch(Throwable $t){ return 0; }
				catch(Exception $e){ return 0; }

				$i													= 0;
				$comment_data										= array();
				foreach($ipb_comments['results'] as $comment){
					$comment_data['comment_post_ID']				= $this->posts[$record['id']];
					$comment_data['comment_content']				= $comment['content'];
					$comment_data['comment_date_gmt']				= $comment['date'];
					$comment_data['comment_date']					= $comment['date'];
				
					$author											= $this->ipbwi4wp_pages_import->ipbwi4wp->member->wp_get_user_by_email($comment['author']['email']);
					if(isset($author->data->ID)){
						$comment_data['user_id']					= $author->data->ID;
						$comment_data['comment_author']				= '';
						$comment_data['comment_author_email']		= '';
					}else{
						$comment_data['comment_author']				= $comment['author']['name'];
						$comment_data['comment_author_email']		= $comment['author']['email'];
					}
					
					if(!$this->load_wp_comment_by_meta($comment['id'])){ // comment does not exist yet
						$comment_id									= wp_new_comment($comment_data);

						if(is_int($comment_id)){
							add_comment_meta($comment_id, '_ipbwi4wp_pages_import', $comment['id']);
							$i++;
						}
					}else{
						$comment_data['comment_ID']					= $this->load_wp_comment_by_meta($comment['id']);
						$comment_id									= wp_update_comment($comment_data);
						$i++;
					}
				}
				
				if($page < $ipb_comments['totalPages']){
					$i												= $i+$this->create_comments($record,$page+1);
				}
				
				return $i;
			}else{
				return 0;
			}
		}
		private function create_comments_from_posts($record,$page=1){
			if($record['comments'] > 0){
				try{
				$topic_id											= $this->ipbwi4wp_pages_import->ipbwi4wp->ipbwi->extended->pages_record_topicid($this->ipbwi4wp_pages_import->settings->settings['import']['IPB_PAGES_DATABASE']['value'], $record['id']);
				
				$posts												= $this->ipbwi4wp_pages_import->ipbwi4wp->ipbwi->core->topics($topic_id,NULL,NULL,array('page' => $page));
				}catch(Throwable $t){ return 0; }
				catch(Exception $e){ return 0; }
				
				$i													= 0;
				$comment_data										= array();
				foreach($posts['results'] as $comment){
					if(
						!$this->load_wp_comment_by_meta($comment['id'])	// comment does not exist yet
						&& ($page == 1 && $i != 0)						// first comment of first page is topic post, so don't import it as comment
					){
						$comment_data['comment_post_ID']			= $this->posts[$record['id']];
						$comment_data['comment_content']			= $comment['content'];
						$comment_data['comment_date_gmt']			= $comment['date'];
						$comment_data['comment_date']				= $comment['date'];
					
						$author										= $this->ipbwi4wp_pages_import->ipbwi4wp->member->wp_get_user_by_email($comment['author']['email']);
						if(isset($author->ID)){
							$comment_data['user_ID']				= $author->ID;
						}else{
							$comment_data['comment_author']			= $comment['author']['name'];
							$comment_data['comment_author_email']	= $comment['author']['email'];
						}

						$comment_id									= wp_new_comment($comment_data);

						if(is_int($comment_id)){
							add_comment_meta($comment_id, '_ipbwi4wp_pages_import', $comment['id']);
							$i++;
						}
					}else{
						$i++;
					}
				}
				
				if($page < $ipb_comments['totalPages']){
					$i												= $i+$this->create_comments_from_posts($record,$page+1);
				}
				
				return $i-1;
			}else{
				return 0;
			}
		}
		private function set_featured_image($record_id){
			$post_id												= $this->posts[$record_id];

			if(!has_post_thumbnail($post_id)){
				try{
					$image_url											= $this->ipbwi4wp_pages_import->ipbwi4wp->ipbwi->extended->pages_record_image($this->ipbwi4wp_pages_import->settings->settings['import']['IPB_PAGES_DATABASE']['value'], $record_id);
				}catch(Throwable $t){ return false; }
				catch(Exception $e){ return false; }
				
				require_once(ABSPATH.'wp-admin/includes/file.php');
				require_once(ABSPATH.'wp-admin/includes/media.php');
				set_time_limit(300);

				$tmp												= download_url($image_url); // Download file to temp location
				preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $image_url, $matches); // Set variables for storage, fix file filename for query strings
				$file_array											= array(
					'name'											=> basename($matches[0]),
					'tmp_name'										=> $tmp
				);

				if(is_wp_error($tmp)){ // If error storing temporarily, unlink
					@unlink($file_array['tmp_name']);
					$file_array['tmp_name']							= '';
					return $tmp;
				}

				$thumbid = media_handle_sideload($file_array, $post_id); // do the validation and storage stuff
				
				if(is_wp_error($thumbid)){ // If error storing permanently, unlink
					@unlink($file_array['tmp_name']);
					return $thumbid;
				}

				return set_post_thumbnail($post_id, $thumbid);
			}else{
				return true;
			}
		}
		/**
		 * @desc			initialize ajax
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function init(){
			if(current_user_can('activate_plugins')){
				$result											= '';
				$redirects_htaccess								= '';
				$redirects_nginx								= '';
				$records										= $this->ipbwi4wp_pages_import->ipbwi4wp->pages->get_records($this->ipbwi4wp_pages_import->settings->settings['import']['IPB_PAGES_DATABASE']['value'],$_POST['page']);

				if(isset($records['totalResults']) && intval($records['totalResults']) > 0){
					$post										= array();
					if($_POST['page'] == 1){
						set_transient('ipbwi4wp_pages_import_pages_301_htaccess', '');
						set_transient('ipbwi4wp_pages_import_pages_301_nginx', '');
					}
					foreach($records['results'] as $record){
						/*if($record['id'] != '908'){
							continue;
						}*/
						
						$result									.= '<div>'.__('Page Records', 'ipbwi4wp_pages_import').' <strong>'.$record['title'].'</strong> (#'.$record['id'].'): ';

						$post['post_title']						= $record['title'];
						$post['post_content']					= $record['description'];
						$post['post_type']						= $this->ipbwi4wp_pages_import->settings->settings['import']['IPB_PAGES_POST_TYPE']['value'];
						$post['post_date']						= $record['date'];

						$author									= $this->ipbwi4wp_pages_import->ipbwi4wp->member->wp_get_user_by_email($record['author']['email']);
						$post['post_author']					= (isset($author->ID) ? $author->ID : false);
						
						if(!$this->load_wp_post_by_meta($record['id'])){ // post does not exist yet
							$post_id							= wp_insert_post($post);
						
							if(is_wp_error($post_id)){
								$result							.= __('error occured:', 'ipbwi4wp_pages_import').' <ul>';
								foreach($post_id->get_error_messages() as $error){
									$result						.= '<li>'.$error.'</li>';
								}
								$result							.= '</ul>';
							}elseif(intval($post_id) === 0){
								$result							.= __('error occured - no valid post ID returned:', 'ipbwi4wp_pages_import').' '.$post_id;
							}else{ // add categories and meta
								add_post_meta($post_id, '_ipbwi4wp_pages_import', $record['id'], true);
							
								// create / assign categories
								$wp_category_ids				= wp_create_categories(array($record['category']['name']), $post_id);
								
								$this->posts[$record['id']]		= $post_id;
								
								$result							.= __('successfully imported.', 'ipbwi4wp_pages_import');
							}
						}else{ // post already exists
							$result								.= __('already exists.', 'ipbwi4wp_pages_import');
						}
						
						$result									.= ' '.($this->create_comments($record)+$this->create_comments_from_posts($record)).' '.__('comments imported.', 'ipbwi4wp_pages_import'); // add comments
						//$post_comments							= $this->create_comments_from_posts($record);
						//$result									.= var_export($post_comments,true);
						
						// add featured image
						$image									= $this->set_featured_image($record['id']);
						if(is_wp_error($image)){
							$result								.= __('error occured:', 'ipbwi4wp_pages_import').' <ul>';
							foreach($image->get_error_messages() as $error){
								$result							.= '<li>'.__('Featured Image not created', 'ipbwi4wp_pages_import').' - '.$error.'</li>';
							}
							$result								.= '</ul>';
						}elseif($image === true){
							$result								.= ' '.__('Featured Image already exists.', 'ipbwi4wp_pages_import');
						}elseif($image > 0){
							$result								.= ' '.__('Featured Image set successfully.', 'ipbwi4wp_pages_import');
						}else{
							$result								.= ' '.__('Featured Image not added, something went wrong.', 'ipbwi4wp_pages_import');
						}
						
						// get post object
						$wp_post								= get_post($this->posts[$record['id']]);
						
						// set date again
						$wp_post->post_date						= $record['date'];
						$wp_post_updated						= wp_update_post($wp_post, $wp_error);
						
						// generate 301 redirects
						if (in_array($wp_post->post_status, array('draft', 'pending', 'auto-draft'))) {
							$my_post							= clone $wp_post;
							$my_post->post_status				= 'published';
							$my_post->post_name					= sanitize_title($my_post->post_name ? $my_post->post_name : $my_post->post_title, $my_post->ID);
							$url_new							= get_permalink($my_post);
						}else{
							$url_new							= get_permalink();
						}
						
						$ips_hello = $this->ipbwi4wp_pages_import->ipbwi4wp->ipbwi->core->hello();
						$url_old								= str_replace($ips_hello['communityUrl'],'/',$record['url']);

						$redirects_htaccess						= $redirects_htaccess.'Redirect 301 '.$url_old.' '.$url_new."\n";
						$redirects_nginx						= $redirects_nginx.'rewrite ^'.$url_old.'$ '.$url_new.' permanent;'."\n";

						$result									.= '</div>';
					}
					
					if($records['totalPages'] >= $_POST['page']){
						set_transient('ipbwi4wp_pages_import_pages_completed', intval($_POST['page'])+1);
					}else{
						set_transient('ipbwi4wp_pages_import_pages_completed', 1);
					}
					
					set_transient('ipbwi4wp_pages_import_pages_301_htaccess', get_transient('ipbwi4wp_pages_import_pages_301_htaccess').$redirects_htaccess);
					set_transient('ipbwi4wp_pages_import_pages_301_nginx', get_transient('ipbwi4wp_pages_import_pages_301_nginx').$redirects_nginx);
					
				}else{ // no content
					$result										= '<div>'.__('No Content in Pages Database', 'ipbwi4wp_pages_import').' #'.$this->ipbwi4wp_pages_import->settings->settings['import']['IPB_PAGES_DATABASE']['value'].' with page '.$_POST['page'].'</div>';
					set_transient('ipbwi4wp_pages_import_pages_completed', 1);
				}
			}else{
				$result											= __('You are not allowed to do this.', 'ipbwi4wp_pages_import');
			}
				
			echo json_encode(array('results' => $result, 'htaccess' => $redirects_htaccess, 'nginx' => $redirects_nginx));
			wp_die();
		}
	}
?>