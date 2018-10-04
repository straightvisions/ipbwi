<?php
	/**
	 * @desc			Please edit this configuration file to get your ipbwi installation work.
	 * @author			Matthias Reuter
	 * @package			IPBWI
	 * @copyright		2007-2016 Matthias Reuter
	 * @license			This is no free software. See license.txt or https://ipbwi.com
	 * @since			4.0
	 * @link			http://ipbwi.com
	 */
	 
	// The base URL to your IP.board installation. Must include a trailing slash.
	if(!defined('ipbwi_IPS_CONNECT_BASE_URL')){
		define('ipbwi_IPS_CONNECT_BASE_URL','');
	}
	// Master KEY as shown in Login Handler Overview in IP.board's ACP
	if(!defined('ipbwi_IPS_CONNECT_MASTER_KEY')){
		define('ipbwi_IPS_CONNECT_MASTER_KEY','');
	}
	// Slave URL
	if(!defined('ipbwi_IPS_CONNECT_SLAVE_URL')){
		define('ipbwi_IPS_CONNECT_SLAVE_URL','');
	}
	// Slave Unique Key
	if(!defined('ipbwi_IPS_CONNECT_SLAVE_KEY')){
		define('ipbwi_IPS_CONNECT_SLAVE_KEY','');
	}
	// REST API KEY
	if(!defined('ipbwi_IPS_REST_API_KEY')){
		define('ipbwi_IPS_REST_API_KEY','');
	}
	// ACTIVATE HOOKS
	if(!defined('ipbwi_ACTIVATE_HOOKS')){
		define('ipbwi_ACTIVATE_HOOKS',false);
	}
?>