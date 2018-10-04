<?php
	declare(strict_types=1);
	
	/**
	 * @author			Matthias Reuter
	 * @package			sso
	 * @copyright		2007-2016 Matthias Reuter
	 * @link			http://ipbwi.com/
	 * @since			4.0
	 * @license			This is no free software. See license.txt or https://ipbwi.com
	 */
	class ipbwi_sso extends ipbwi{
		public $ipbwi			= null;
		/**
		 * @desc			Loads other classes of package
		 * @author			Matthias Reuter
		 * @since			4.0
		 * @ignore
		 */
		public function __construct(ipbwi $ipbwi){
			$this->ipbwi	= $ipbwi; // loads common classes
		}
		/**
		 * @desc			Build parameters for curl queries
		 * @param	array	$parameters assosiative array of key => value
		 * @return	string	Query in HTTP URL format
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		private function buildParameters(array $parameters){
			$default = array(
				'key'		=> ipbwi_IPS_CONNECT_MASTER_KEY,
				'url'		=> ipbwi_IPS_CONNECT_SLAVE_URL,
			);
						
			return http_build_query(array_merge($default,$parameters));
		}
		/**
		 * @desc			Process curl query
		 * @param	string	Query in HTTP URL format
		 * @return	string	curl result returning transfer
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		private function process(string $query){
			$curl_url		= ipbwi_IPS_CONNECT_BASE_URL.'applications/core/interface/ipsconnect/ipsconnect.php?'.$query;
			$ch				= curl_init($curl_url);
			// get response
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			
			// load custom options
			if(file_exists('curl_custom.php')){
				$module	= 'sso';
				require('curl_custom.php');
			}
			
			// run query
			$result			= curl_exec($ch);
			$decoded		= json_decode($result,true);
			
			if(is_array($decoded)){
				return $decoded;
			}else{
				$status		= curl_getinfo($ch);
				if($status['http_code'] == 404){
					throw new Exception('<div class="ipbwi_api_error">IPBWI API Error: The CURL request <strong>'.$this->curl_url.'</strong> in module <strong>'.$this->module.'</strong> was not successful. Status Code: '.$status['http_code'].'</div>');
					return false;
				}
			}
		}
		/**
		 * @desc			This method is intended to allow a slave application to verify the settings of the master (i.e. when the master key is first provided) and to "register" with the master installation.  This allows the master installation to propagate requests to slave applications later.
		 * @return	bool	true on success, otherwise Exception/false
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function verifySettings(){
			$result		= $this->process($this->buildParameters(array('do' => 'verifySettings', 'ourKey' => ipbwi_IPS_CONNECT_SLAVE_KEY)));
			
			if($result['status'] != 'SUCCESS') {
				throw new Exception($result['status']);
			}else{
				return true;
			}
		}
		/**
		 * @desc			Call this method in order to fetch a user's password salt - necessary for allowing the local application to hash the user's credentials properly before sending them to the master.
		 * @param	int		$idType What type of ID is being passed (a value of 1 indicates the id is a display name, a value of 2 indicates the id is an email address and a value of 3 indicates the value could be either a display name OR an email address)
		 * @param	string	$id The user ID
		 * @return	string	salt string on success, otherwise Exception
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function fetchSalt(int $idType, string $id){
			$result		= $this->process($this->buildParameters(array('do' => 'fetchSalt', 'idType' => $idType, 'id' => $id, 'key' => md5(ipbwi_IPS_CONNECT_MASTER_KEY.$id))));

			if($result['status'] != 'SUCCESS') {
				throw new Exception($result['status']);
			}else{
				return $result['pass_salt'];
			}
		}
		/**
		 * @desc			This method authenticates a user and logs the user into all applications on the IPS Connect network.
		 * @param	int		$idType What type of ID is being passed (a value of 1 indicates the id is a display name, a value of 2 indicates the id is an email address and a value of 3 indicates the value could be either a display name OR an email address)
		 * @param	int		$id The user ID
		 * @param	string	$password The encrypted password
		 * @return	array	Exception on failure, otherwise an array with the following fields:
							connect_status: VALIDATING if the account is still validating or SUCCESS otherwise
							email: The member's email address
							name: The member's display name
							connect_id: The member's unique integer ID on the master installation
							connect_revalidate_url: If the member is VALIDATING, the URL that any slave application's should send the user to in order to complete their validation
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function login(int $idType, string $id, string $password){
			$result		= $this->process($this->buildParameters(array('do' => 'login', 'idType' => $idType, 'id' => $id, 'password' => $password, 'key' => md5(ipbwi_IPS_CONNECT_MASTER_KEY.$id))));

			if($result['status'] != 'SUCCESS') {
				throw new Exception($result['status']);
			}else{
				return $result;
			}
		}
		/**
		 * @desc			When a user logs in to a slave application successfully, they will be redirected to the crossLogin method of the master application in order to be logged in to it and all other slave applications on the network.  This is necessary to work around cross-domain cookie restrictions.  The master install will need to redirect the user to each slave's crossLogin method, and will also need to log the user in to the master application, before returning the user to the originating URL (the original slave application the user logged in to).
		 * @param	int		$id The member's unique integer ID on the master installation
		 * @param	string	$returnTo a URL to return the user to once the user has been logged on.
		 * @return	void	None, the user will be redirected to the returnTo URL
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function crossLogin(int $id, string $returnTo){
			$query = $this->buildParameters(array('do' => 'crossLogin', 'id' => $id, 'returnTo' => $returnTo, 'key' => md5(ipbwi_IPS_CONNECT_MASTER_KEY.$id)));
			header('location: '.ipbwi_IPS_CONNECT_BASE_URL.'applications/core/interface/ipsconnect/ipsconnect.php?'.$query);
			die();
		}
		/**
		 * @desc			API calls to the logout method are designed to log the user out of the master application as well as all of the slave installations.
		 * @param	int		$id The member's unique integer ID on the master installation
		 * @param	string	$returnTo a URL to return the user to once the user has been logged on.
		 * @return	void	None, the user will be redirected to the returnTo URL
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function logout(int $id, string $returnTo){
			$query = $this->buildParameters(array('do' => 'logout', 'id' => $id, 'returnTo' => $returnTo, 'key' => md5(ipbwi_IPS_CONNECT_MASTER_KEY.$id)));
			header('location: '.ipbwi_IPS_CONNECT_BASE_URL.'applications/core/interface/ipsconnect/ipsconnect.php?'.$query);
			die();
		}
		/**
		 * @desc			Register the user on all installations in the Connect network
		 * @param	string	$name: The member's name
		 * @param	string	$email: The member's email address
		 * @param	string	$pass_hash: The member's password hash
		 * @param	string	$pass_salt: The member's password salt
		 * @param	string	$revalidateUrl: The URL to send the user to if they are validating and attempt to login to any other site in the connect network
		 * @return	int		$connect_id: The member's unique integer ID on the connect network on success, otherwise Exception
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function register(string $name, string $email, string $pass_hash, string $pass_salt, string $revalidateUrl = NULL){
			$result		= $this->process($this->buildParameters(array('do' => 'register', 'name' => $name, 'email' => $email, 'pass_hash' => $pass_hash, 'pass_salt' => $pass_salt, 'revalidateUrl' => $revalidateUrl)));

			if(isset($result['status']) && $result['status'] != 'SUCCESS'){
				throw new Exception($result['status']);
			}elseif(isset($result['connect_id'])){
				return $result['connect_id'];
			}else{
				throw new Exception(var_export($result,true));
			}
		}
		/**
		 * @desc			Call this method in order to mark a user's account as validated. If a user account is marked as awaiting validation and the user validates, this should be called to ensure the user account is marked as validated across the entire network.
		 * @param	int		$id: The unique user ID of the user account
		 * @return	bool	true on success, otherwise Exception
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function validate(int $id){
			$result		= $this->process($this->buildParameters(array('do' => 'validate', 'id' => $id, 'key' => md5(ipbwi_IPS_CONNECT_MASTER_KEY.$id))));

			if($result['status'] != 'SUCCESS') {
				throw new Exception($result['status']);
			}else{
				return true;
			}
		}
		/**
		 * @desc			Call this method in order to delete a user account. THERE IS NO UNDOING THIS ACTION.
		 * @param	int		$id: The unique user ID of the user account
		 * @return	bool	true on success, otherwise Exception
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function delete(int $id){
			$result		= $this->process($this->buildParameters(array('do' => 'delete', 'id' => $id, 'key' => md5(ipbwi_IPS_CONNECT_MASTER_KEY.$id))));

			if($result['status'] != 'SUCCESS') {
				throw new Exception($result['status']);
			}else{
				return true;
			}
		}
		/**
		 * @desc			Call this method in order to ban or unban a user account
		 * @param	int		$id: The unique user ID of the user account
		 * @param	int		$status: A value of 1 will ban the user account while a value of 0 will unban the user account
		 * @return	bool	true on success, otherwise Exception
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function ban(int $id, int $status){
			$result		= $this->process($this->buildParameters(array('do' => 'ban', 'id' => $id, 'status' => $status, 'key' => md5(ipbwi_IPS_CONNECT_MASTER_KEY.$id))));

			if($result['status'] != 'SUCCESS') {
				throw new Exception($result['status']);
			}else{
				return true;
			}
		}
		/**
		 * @desc			Call this method in order to merge two distinct user accounts into one. THERE IS NO UNDOING THIS ACTION.
		 * @param	int		$id: The unique user ID of the account you wish to keep
		 * @param	int		$remote: The unique user ID of the account you wish to remove
		 * @return	bool	true on success, otherwise Exception
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function merge(int $id, int $remote){
			$result		= $this->process($this->buildParameters(array('do' => 'merge', 'id' => $id, 'remote' => $remote, 'key' => md5(ipbwi_IPS_CONNECT_MASTER_KEY.$id))));

			if($result['status'] != 'SUCCESS') {
				throw new Exception($result['status']);
			}else{
				return true;
			}
		}
		/**
		 * @desc			Call this method in order to check if an email exists at the master application. This can be useful to prevent a user who has already registered elsewhere on the Connect network from registering again on a local site, when they should instead login.
		 * @param	string	$email The email address to check
		 * @return	bool	true on success, otherwise Exception/false
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function checkEmail(string $email){
			$result		= $this->process($this->buildParameters(array('do' => 'checkEmail', 'email' => $email)));

			if($result['status'] != 'SUCCESS') {
				throw new Exception($result['status']);
			}else{
				return boolval($result['used']);
			}
		}
		/**
		 * @desc			Call this method in order to check if a username exists at the master application. This can be useful to prevent a user who has already registered elsewhere on the Connect network from registering again on a local site, when they should instead login. It is not necessary to enforce uniqueness of display names in your application if your application has a need to allow multiple user accounts with the same display name to exist, however you should never allow logging in by 'display name' if this is the case.
		 * @param	string	$name The name to check
		 * @return	bool	true on success, otherwise Exception/false
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function checkName(string $name){
			$result		= $this->process($this->buildParameters(array('do' => 'checkName', 'name' => $name)));

			if($result['status'] != 'SUCCESS') {
				throw new Exception($result['status']);
			}else{
				return boolval($result['used']);
			}
		}
		/**
		 * @desc			This method is called when an existing user's email address should be updated to a new value.
		 * @param	string	$email The new email address to use
		 * @param	string	$id Unique user ID provided by the master application to a previous login or registration call
		 * @return	bool	true on success, otherwise Exception/false
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function changeEmail(string $email, int $id){
			$result		= $this->process($this->buildParameters(array('do' => 'changeEmail', 'email' => $email, 'id' => $id, 'key' => md5(ipbwi_IPS_CONNECT_MASTER_KEY.$id))));

			if($result['status'] != 'SUCCESS') {
				throw new Exception($result['status']);
			}else{
				return true;
			}
		}
		/**
		 * @desc			This method is called when a user has updated their password
		 * @param	string	$pass_salt Password salt
		 * @param	string	$pass_hash Password hash
		 * @param	int		$id Unique user ID provided by the master application to a previous login or registration call
		 * @return	bool	true on success, otherwise Exception/false
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function changePassword(string $pass_salt, string $pass_hash, int $id){
			$result		= $this->process($this->buildParameters(array('do' => 'changePassword', 'pass_salt' => $pass_salt, 'pass_hash' => $pass_hash, 'id' => $id, 'key' => md5(ipbwi_IPS_CONNECT_MASTER_KEY.$id))));

			if($result['status'] != 'SUCCESS') {
				throw new Exception($result['status']);
			}else{
				return true;
			}
		}
		/**
		 * @desc			This method is called when an existing user has changed their display name at a local installation
		 * @param	string	$name The new name to use
		 * @param	int		$id Unique user ID provided by the master application to a previous login or registration call
		 * @return	bool	true on success, otherwise Exception/false
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function changeName(string $name, int $id){
			$result		= $this->process($this->buildParameters(array('do' => 'changeName', 'name' => $name, 'id' => $id, 'key' => md5(ipbwi_IPS_CONNECT_MASTER_KEY.$id))));

			if($result['status'] != 'SUCCESS') {
				throw new Exception($result['status']);
			}else{
				return true;
			}
		}
		/**
		* Generate a salt
		*
		* @return	string
		*/
		public function generateSalt(){
			$salt = '';
			for($i=0; $i<22; $i++){
				do{
					$chr = rand(48, 122);
				}
				while(in_array($chr, range(58, 64)) or in_array($chr, range(91, 96)));
				$salt .= chr($chr);
			}
			return $salt;
		}
		/**
		 * @desc			Properly encrypt password
		 * @param	string	$login user login
		 * @param	string	$loginType  // 1 for display name, 2 for email, 3 if it could be either
		 * @param	string	$password plaintext password
		 * @return	mixed	encrypted password string on success otherwise false
		 * @author			Matthias Reuter
		 * @since			4.1.3
		 */
		public function encrypt_password($login,$loginType,$password){
			try{
				$salt						= $this->fetchSalt($loginType, $login);
				
				if(strlen($salt) == 22){
					$encrytedPassword		= crypt($password, '$2a$13$'.$salt);
				}else{
					$password				= str_replace("&", "&amp;", $password);
					$password				= str_replace("<!--", "&#60;&#33;--", $password);
					$password				= str_replace("-->", "--&#62;", $password);
					$password				= str_ireplace("<script", "&#60;script", $password);
					$password				= str_replace(">", "&gt;", $password);
					$password				= str_replace("<", "&lt;", $password);
					$password				= str_replace('"', "&quot;", $password);
					$password				= str_replace("\n", "<br />", $password);
					$password				= str_replace("$", "&#036;", $password);
					$password				= str_replace("!", "&#33;", $password);
					$password				= str_replace("'", "&#39;", $password);
					$password				= str_replace("\\", "&#092;", $password);	
							
					$encrytedPassword		= md5(md5($salt).md5($password));
				}
				
				return $encrytedPassword;
			}catch(Exception $e){
				return false;
			}
		}
	}
?>