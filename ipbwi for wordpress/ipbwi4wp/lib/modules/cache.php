<?php
	/**
	 * @author			Matthias Reuter
	 * @package			cache
	 * @copyright		2007-2016 Matthias Reuter
	 * @link			http://ipbwi.com/
	 * @since			4.0
	 * @license			This is no free software. See license.txt or https://ipbwi.com
	 */
	class ipbwi4wp_cache extends ipbwi4wp{
		public $ipbwi4wp	= NULL;
		private $data		= array();
		
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
		 * @desc			retrieve cache
		 * @param	string	$group cache group
		 * @param	string	$id Key to identify a cache field
		 * @return	mixed	Cached item or false if $key does not exist.
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function get($group, $id){
			if(array_key_exists($group, $this->data)){
				return (array_key_exists((string)$id, $this->data[$group])) ? $this->data[$group][$id] : false;
			}else{
				return false;
			}
		}
		/**
		 * @desc			Save/Update cache
		 * @param	string	$group cache group
		 * @param	string	$id Key to identify a cache field
		 * @param	mixed	$data Data being cached
		 * @return	mixed	$data
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function save($group, $id, $data){
			$this->data[$group][$id] = $data;
			return $data;
		}
	}
?>