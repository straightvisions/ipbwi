<?php
/**
 * @brief		IPBWI hello API
 * @author		<a href='https://ipbwi.com'>IPBWI</a>
 * @copyright	(c) 2016 Matthias Reuter
 * @license		all rights reserved
 * @package		IPS Community Suite
 * @subpackage	IPBWI
 * @since		23 Mar 2016
 * @version		4.0.1
 */

namespace IPS\ipbwi\api;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * @brief	IPBWI hello API
 */
class _hello extends \IPS\Api\Controller
{
	/**
	 * GET /ipbwi/hello
	 * Get basic information about IPBWI-API-Application.
	 *
	 * @return	array
	 * @apiresponse	int		app_id				The IPBWI API Application ID
	 * @apiresponse	string	app_version			The IPBWI API Application version number
	 * @apiresponse	int		app_long_version	The IPBWI API Application long version number
	 * @apiresponse	string	app_directory		The IPBWI API Application directory name

	 */
	public function GETindex()
	{
		$appInfo = \IPS\Db::i()->select( '*', 'core_applications', array('app_directory=?', 'ipbwi' ) )->first();

		return new \IPS\Api\Response(200, array(
			'app_id'			=> $appInfo['app_id'],
			'app_version'		=> $appInfo['app_version'],
			'app_long_version'	=> $appInfo['app_long_version'],
			'app_directory'		=> $appInfo['app_directory'],
		));
	}
}