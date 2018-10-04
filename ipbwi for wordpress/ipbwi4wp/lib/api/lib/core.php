<?php
	declare(strict_types=1);
	
	/**
	 * @author			Matthias Reuter
	 * @package			core
	 * @copyright		2007-2016 Matthias Reuter
	 * @link			http://ipbwi.com/
	 * @since			4.0
	 * @license			This is no free software. See license.txt or https://ipbwi.com
	 */
	class ipbwi_core extends ipbwi{
		public $ipbwi						= null;
		public $url_rewrite					= true;
		public $key_in_url					= false;
		public $module						= 'core';
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
				$get_parameters['key']		= ipbwi_IPS_REST_API_KEY;
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
				curl_setopt($ch, CURLOPT_USERPWD, ipbwi_IPS_REST_API_KEY);
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
		 * @desc			Get basic information about the community.
		 * @return	array	community information
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function hello(){
			$result = $this->process('/core/hello');
			return $result;
		}
		/**
		 * @desc			Member Interface
		 * @param	int		$id The user ID
		 * @param	array	$post_parameters assosiative array of key => value, see IP.board ACP -> System -> REST API -> API Reference
		 * @param	bool	$delete command to delete data if any found with given id
		 * @param	array	$get_parameters assosiative array of key => value, see IP.board ACP -> System -> REST API -> API Reference
		 * @return	array	Response as associative array, otherwise array with fields errorCode and errorMessage, see IP.board ACP -> System -> REST API -> API Reference
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function members(int $id=NULL, array $post_parameters=NULL, bool $delete=NULL, array $get_parameters=NULL){
			if($id){
				$result = $this->process('/core/members/'.$id, $post_parameters, $delete);
			}else{
				$result = $this->process('/core/members', $post_parameters, NULL, $get_parameters);
			}
			return $result;
		}
		/**
		 * @desc			Posts Interface
		 * @param	int		$id The post ID
		 * @param	array	$post_parameters assosiative array of key => value, see IP.board ACP -> System -> REST API -> API Reference
		 * @param	bool	$delete command to delete data if any found with given id
		 * @param	array	$get_parameters assosiative array of key => value, see IP.board ACP -> System -> REST API -> API Reference
		 * @return	array	Response as associative array, otherwise array with fields errorCode and errorMessage, see IP.board ACP -> System -> REST API -> API Reference
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function posts(int $id=NULL, array $post_parameters=NULL, bool $delete=NULL, array $get_parameters=NULL){
			if($id){
				$result = $this->process('/forums/posts/'.$id, $post_parameters, $delete);
			}else{
				$result = $this->process('/forums/posts', $post_parameters, NULL, $get_parameters);
			}
			return $result;
		}
		/**
		 * @desc			Topics Interface
		 * @param	int		$id The topic ID
		 * @param	array	$post_parameters assosiative array of key => value, see IP.board ACP -> System -> REST API -> API Reference
		 * @param	bool	$delete command to delete data if any found with given id
		 * @param	array	$get_parameters assosiative array of key => value, see IP.board ACP -> System -> REST API -> API Reference
		 * @return	array	Response as associative array, otherwise array with fields errorCode and errorMessage, see IP.board ACP -> System -> REST API -> API Reference
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function topics(int $id=NULL, array $post_parameters=NULL, bool $delete=NULL, array $get_parameters=NULL){
			if($id){
				$result = $this->process('/forums/topics/'.$id, $post_parameters, $delete, $get_parameters);
			}else{
				$result = $this->process('/forums/topics', $post_parameters, NULL, $get_parameters);
			}
			return $result;
		}
		/**
		 * @desc			Topic Posts
		 * @param	int		$id The topic ID
		 * @param	array	$get_parameters assosiative array of key => value, see IP.board ACP -> System -> REST API -> API Reference
		 * @return	array	Response as associative array, otherwise array with fields errorCode and errorMessage, see IP.board ACP -> System -> REST API -> API Reference
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function topic_posts(int $id=NULL, array $get_parameters=NULL){
			$result = $this->process('/forums/topics/'.$id.'/posts', NULL, NULL, $get_parameters);
			return $result;
		}
	}
?>