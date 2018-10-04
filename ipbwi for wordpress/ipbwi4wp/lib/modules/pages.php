<?php
	/**
	 * @author			Matthias Reuter
	 * @package			pages
	 * @copyright		2007-2016 Matthias Reuter
	 * @link			http://ipbwi.com/
	 * @since			4.0
	 * @license			This is no free software. See license.txt or https://ipbwi.com
	 */
	class ipbwi4wp_pages extends ipbwi4wp{
		public $ipbwi4wp			= NULL;

		/**
		 * @desc			Loads other classes of package
		 * @author			Matthias Reuter
		 * @since			4.0
		 * @ignore
		 */
		public function __construct($ipbwi4wp){
			$this->ipbwi4wp			= isset($ipbwi4wp->ipbwi4wp) ? $ipbwi4wp->ipbwi4wp : $ipbwi4wp; // loads common classes
		}
		public function get_databases($renew=false){
			try{
				if($renew || !$this->ipbwi4wp->cache->get('pages_databases','all')){
					return $this->ipbwi4wp->cache->save('pages_databases', 'all', $this->ipbwi4wp->ipbwi->extended->pages_databases());
				}else{
					return $this->ipbwi4wp->cache->get('pages_databases', 'all');
				}
			}catch(Throwable $t){ return false; }
		}
		public function get_records($database_id,$page=1,$renew=false){
			try{
				if($renew || !$this->ipbwi4wp->cache->get('pages_records',$database_id.'_'.$page)){
					$get_params		= array(
						'page'		=> $page
					);
					return $this->ipbwi4wp->cache->save('pages_records', $database_id.'_'.$page, $this->ipbwi4wp->ipbwi->extended->pages_records($database_id,NULL,NULL,NULL,$get_params));
				}else{
					return $this->ipbwi4wp->cache->get('pages_records', $database_id.'_'.$page);
				}
			}catch(Throwable $t){ return false; }
		}
		public function get_record_comments($database_id,$record_id,$page=1,$renew=false){
			try{
				if($renew || !$this->ipbwi4wp->cache->get('pages_record_comments',$database_id.'_'.$record_id.'_'.$page)){
					$get_params		= array(
						'page'		=> $page
					);
					return $this->ipbwi4wp->cache->save('pages_record_comments', $database_id.'_'.$record_id.'_'.$page, $this->ipbwi4wp->ipbwi->extended->pages_record_comments($database_id,$record_id,NULL,NULL,NULL,$get_params));
				}else{
					return $this->ipbwi4wp->cache->get('pages_record_comments', $database_id.'_'.$record_id.'_'.$page);
				}
			}catch(Throwable $t){ return false; }
		}
	}