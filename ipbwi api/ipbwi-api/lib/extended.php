<?php
	declare(strict_types=1);
	
	/**
	 * @author			Matthias Reuter
	 * @package			extended
	 * @copyright		2007-2016 Matthias Reuter
	 * @link			http://ipbwi.com/
	 * @since			4.0.1
	 * @license			This is no free software. See license.txt or https://ipbwi.com
	 */
	class ipbwi_extended extends ipbwi{
		public $ipbwi						= null;
		public $url_rewrite					= true;
		public $key_in_url					= false;
		public $module						= 'extended';
		public $curl_url					= '';
		/**
		 * @desc			Loads other classes of package
		 * @author			Matthias Reuter
		 * @since			4.0
		 * @ignore
		 */
		public function __construct($ipbwi){
			$this->ipbwi					= $ipbwi; // loads common classes
		}
		private function failure($response){
			if(is_array($response) && isset($response['errorCode']) && ($response['errorCode'] == '3S290/3' || $response['errorCode'] == 'INVALID_APP' || $response['errorCode'] == 'INVALID_CONTROLLER')){
				return true;
			}else{
				return false;
			}
		}
		private function build_url($endpoint,$get_parameters){
			if($this->key_in_url === true){
				$get_parameters['key']		= trim(ipbwi_IPS_REST_API_KEY);
			}
			
			if($this->url_rewrite){
				$query						= ($get_parameters ? '?'.http_build_query($get_parameters) : '');
				$this->curl_url				= ipbwi_IPS_CONNECT_BASE_URL.'api'.$endpoint.$query;
			}else{
				$query						= ($get_parameters ? '&'.http_build_query($get_parameters) : '');
				$this->curl_url				= ipbwi_IPS_CONNECT_BASE_URL.'api/index.php?'.$endpoint.$query;
			}
			return $this->curl_url;
		}
		private function handle_response($response,$ch){
			$decoded						= json_decode($response,true);

			if(!$this->failure($decoded)){
				return $decoded;
			}else{
				$status						= curl_getinfo($ch);
				//error_log("\n\n".var_export($status,true).': '.var_export($response,true)."\n\n", 3, IPBWI4WP_DIR.'log.txt');
				throw new Exception('<div class="ipbwi_api_error">IPBWI API Error: The CURL request <strong>'.$this->curl_url.'</strong> in module <strong>'.$this->module.'</strong> was not successful. Status Code: '.$status['http_code'].'</div>');
				return false;
			}
		}
		/**
		 * @desc			Process curl query
		 * @param	string	$endpoint endpoint directories
		 * @param	array	$post_parameters assosiative array of key => value
		 * @param	bool	$delete command to delete data if any found
		 * @return	array	curl result returning transfer
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		private function process($endpoint, $post_parameters=NULL, $delete=NULL, $get_parameters=NULL){
			$curl_url						= $this->build_url($endpoint,$get_parameters);
			$ch								= curl_init($curl_url);
			
			// get response
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			// auth
			if($this->key_in_url !== true){
				curl_setopt($ch, CURLOPT_USERPWD, trim(ipbwi_IPS_REST_API_KEY));
				curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			}
			// update
			if($post_parameters){
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_parameters);
			}
			// delete
			if($delete === true){
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
			}
			
			// load custom options
			if(file_exists('curl_custom.php')){
				require('curl_custom.php');
			}
			
			// run query
			$result							= curl_exec($ch);
			$response						= $this->handle_response($result,$ch);
			return $response;
		}
		/**
		 * @desc			Get basic information about the IPBWI application.
		 * @return	array	IPBWI app information
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function hello(){
			$result = $this->process('/ipbwi/hello');
			return $result;
		}
		public function groups(int $id=NULL, array $post_parameters=NULL, bool $delete=NULL){
			if($post_parameters){
				$post_parameters['data']		= json_encode($post_parameters['data']);
			}
			if($id){
				$result = $this->process('/ipbwi/groups/'.$id, $post_parameters, $delete);
			}else{
				$result = $this->process('/ipbwi/groups', $post_parameters);
			}
			return $result;
		}
		public function forums(int $id=NULL, array $post_parameters=NULL, bool $delete=NULL){
			if($post_parameters){
				$post_parameters['data']		= json_encode($post_parameters['data']);
			}
			if($id){
				$result = $this->process('/ipbwi/forums/'.$id, $post_parameters, $delete);
			}else{
				$result = $this->process('/ipbwi/forums', $post_parameters);
			}
			return $result;
		}
		public function reports(int $id=NULL, array $post_parameters=NULL, bool $delete=NULL, array $get_parameters=NULL){
			if($id){
				$result = $this->process('/ipbwi/reports/'.$id, $post_parameters, $delete);
			}else{
				$result = $this->process('/ipbwi/reports', $post_parameters, NULL, $get_parameters);
			}
			return $result;
		}
		public function members(string $field, string $value){
			$result = $this->process('/ipbwi/members/'.urlencode($value).'/'.$field);
			return $result;
		}
		public function members_reputationPoints($id){
			$result						= $this->process('/ipbwi/members/'.$id.'/reputationPoints');
			return $result;
		}
		public function members_reputationLastDayWon($id){
			$result						= $this->process('/ipbwi/members/'.$id.'/reputationLastDayWon');
			return $result;
		}
		public function members_reputationDescription($id){
			$result						= $this->process('/ipbwi/members/'.$id.'/reputationDescription');
			return $result;
		}
		public function members_reputationImage($id){
			$result						= $this->process('/ipbwi/members/'.$id.'/reputationImage');
			return $result;
		}
		public function updateSecondaryGroups($id, $groups){
			$result = $this->process('/ipbwi/members/'.$id.'/updateSecondaryGroups',array('groups' => json_encode($groups)));
			return $result;
		}
		public function updateCustomProfileFields($id, $fields){
			$result = $this->process('/ipbwi/members/'.$id.'/updateCustomProfileFields',array('fields' => json_encode($fields)));
			return $result;
		}
		public function updatePhotoByUpload($id, $file){
			$file	= new CurlFile($file['tmp_name'], $file['type'], $file['name']);
			$result = $this->process('/ipbwi/members/'.$id.'/updatePhotoByUpload', array('upload_photo' => $file));
			return $result;
		}
		public function updatePhotoByURL($id, $file_url){
			$result = $this->process('/ipbwi/members/'.$id.'/updatePhotoByURL',array('file_url' => $file_url));
			return $result;
		}
		public function deletePhoto($id){
			$result = $this->process('/ipbwi/members/'.$id.'/deletePhoto',NULL,true);
			return $result;
		}
		public function pages_databases(){
			$result = $this->process('/ipbwi/pages');
			return $result;
		}
		public function pages_records(int $database_id, int $id=NULL, array $post_parameters=NULL, bool $delete=NULL, array $get_parameters=NULL){
			if($id){
				$result = $this->process('/cms/records/'.$database_id.'/'.$id, $post_parameters, $delete);
			}else{
				$result = $this->process('/cms/records/'.$database_id, $post_parameters, NULL, $get_parameters);
			}
			return $result;
		}
		public function pages_record_comments(int $database_id, int $record_id=NULL, int $id=NULL, array $post_parameters=NULL, bool $delete=NULL, array $get_parameters=NULL){
			if($id){ // get/update/delete a specific comment
				$result = $this->process('/cms/comments/'.$database_id.'/'.$id, $post_parameters, $delete, $get_parameters); // no parameters here
			}elseif($record_id){ // get comments from a record
				$result = $this->process('/cms/records/'.$database_id.'/'.$record_id.'/comments', NULL, NULL, $get_parameters);
			}else{ // get/post comments
				$result = $this->process('/cms/comments/'.$database_id, $post_parameters, NULL, $get_parameters);
			}
			return $result;
		}
		public function pages_record_reviews(int $database_id, int $record_id=NULL, int $id=NULL, array $post_parameters=NULL, bool $delete=NULL, array $get_parameters=NULL){
			if($id){ // get/update/delete a specific review
				$result = $this->process('/cms/reviews/'.$database_id.'/'.$id, $post_parameters, $delete, $get_parameters); // no parameters here
			}elseif($record_id){ // get reviews from a record
				$result = $this->process('/cms/records/'.$database_id.'/'.$record_id.'/reviews', NULL, NULL, $get_parameters);
			}else{ // get/post reviews
				$result = $this->process('/cms/reviews/'.$database_id, $post_parameters, NULL, $get_parameters);
			}
			return $result;
		}
		public function pages_record_image(int $database_id, int $record_id){
			$result = $this->process('/ipbwi/pages/'.$database_id.'/image/'.$record_id.'');
			return $result;
		}
		public function pages_record_topicid($database_id, $record_id){
			$result = $this->process('/ipbwi/pages/'.$database_id.'/topicid/'.$record_id.'');
			return $result;
		}
		public function sql($post_parameters){
			$result = $this->process('/ipbwi/sql', $post_parameters);
			return $result;
		}
		/**
		 * @desc			Topics Interface
		 * @param	int		$id The topic ID
		 * @return	array	Response as associative array, otherwise array with fields errorCode and errorMessage, see IP.board ACP -> System -> REST API -> API Reference
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function topics($id){
			$result = $this->process('/ipbwi/topics/'.$id);
			return $result;
		}
		public function posts_reputation($id, $post_parameters=NULL){
			$result							= $this->process('/ipbwi/posts/'.$id.'/reputation', $post_parameters);
			return $result;
		}
		public function posts_canGiveReputation($id, $get_parameters){
			$result							= $this->process('/ipbwi/posts/'.$id.'/canGiveReputation', NULL, NULL, $get_parameters);
			return $result;
		}
		public function posts_reputationGiven($id, $get_parameters){
			$result							= $this->process('/ipbwi/posts/'.$id.'/reputationGiven', NULL, NULL, $get_parameters);
			return $result;
		}
		
		public function menu($id=NULL, $post_parameters=NULL, $delete=NULL, $get_parameters=NULL){
			if($post_parameters){
				$post_parameters['data']	= json_encode($post_parameters['data']);
			}
			if($id){
				$result						= $this->process('/ipbwi/menu/'.$id, $post_parameters, $delete, $get_parameters);
			}else{
				$result						= $this->process('/ipbwi/menu', $post_parameters, NULL, $get_parameters);
			}
			return $result;
		}
	}
?>