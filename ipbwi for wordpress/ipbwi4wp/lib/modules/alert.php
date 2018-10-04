<?php
	/**
	 * @author			Matthias Reuter
	 * @package			alert
	 * @copyright		2016 Matthias Reuter
	 * @link			http://ipbwi.com/
	 * @since			4.0.6
	 * @license			This is no free software. See license.txt or https://ipbwi.com
	 */
	class ipbwi4wp_alert extends ipbwi4wp{
		public $ipbwi4wp			= NULL;
		private $alerts				= array();

		/**
		 * @desc			Loads other classes of package
		 * @author			Matthias Reuter
		 * @since			4.0
		 * @ignore
		 */
		public function __construct($ipbwi4wp){
			$this->ipbwi4wp			= isset($ipbwi4wp->ipbwi4wp) ? $ipbwi4wp->ipbwi4wp : $ipbwi4wp; // loads common classes
		}
		public function __destruct(){
			echo $this->getAllOutput();
			$this->alerts			= array();
		}
		public function add($message,$type='error'){
			$count					= $this->getCount();
			
			$this->alerts[$count]	= array(
				'msg'				=> $message,
				'type'				=> $type
			);
			
			return $count;
		}
		public function remove($id){
			unset($this->alerts[$id]);
		}
		public function update($id,$message,$type){
			$this->alerts[$id]		= array(
				'msg'				=> $message,
				'type'				=> $type
			);
		}
		public function get($id){
			return $this->alerts[$id];
		}
		public function getAll(){
			return $this->alerts;
		}
		public function getOutput($id){
			$output					= '<div class="ipbwi4wp_alert ipbwi4wp_alert_'.$this->alerts[$id]['type'].' ipbwi4wp_alert_'.$id.' notice notice-'.$this->alerts[$id]['type'].'">'.$this->alerts[$id]['msg'].'</div>';
			unset($this->alerts[$id]);
			return $output;
		}
		public function getAllOutput(){
			if($this->getCount() > 0){
				$output					= '<div class="ipbwi4wp_alerts">';
				if($this->getCount() > 0){
					foreach($this->alerts as $id => $data){
						$output			.= $this->getOutput($id);
					}
				}
				$output					.= '</div>';
				return $output;
			}else{
				return false;
			}
		}
		public function getCount(){
			return count($this->alerts);
		}
		public function admin_notices(){
			echo $this->getAllOutput();
			$this->alerts			= array();
		}
	}