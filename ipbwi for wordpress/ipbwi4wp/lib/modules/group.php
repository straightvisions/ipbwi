<?php
	/**
	 * @author			Matthias Reuter
	 * @package			group
	 * @copyright		2007-2016 Matthias Reuter
	 * @link			http://ipbwi.com/
	 * @since			4.0
	 * @license			This is no free software. See license.txt or https://ipbwi.com
	 */
	class ipbwi4wp_group extends ipbwi4wp{
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
		 * @desc			list IPB groups
		 * @param	bool	$renew Perform new query and renew cache
		 * @return	array	member information
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function ipb_list($renew=false){
			if($renew || !$this->ipbwi4wp->cache->get('groups', 'all')){
				return $this->ipbwi4wp->cache->save('groups', 'all', $this->ipbwi4wp->ipbwi->extended->groups());
			}else{
				return $this->ipbwi4wp->cache->get('groups', 'all');
			}
		}
	}
?>