<?php
/**
 * @brief		IPBWI sql API
 * @author		<a href='https://ipbwi.com'>IPBWI</a>
 * @copyright	(c) 2016 Matthias Reuter
 * @license		all rights reserved
 * @package		IPS Community Suite
 * @subpackage	IPBWI
 * @since		23 Mar 2016
 * @version		4.0.3
 */

namespace IPS\ipbwi\api;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * @brief	IPBWI sql API
 */
class _sql extends \IPS\Api\Controller
{
	/**
	 * GET /ipbwi/sql
	 * Performs a SQL query in IPS database and returns result as array. No SQL security checks are performed, so make sure to deliver save queries only.
	 *
	 * @reqapiparam		string		$post_vars				MySQL Query String
	 * @return			array
	 * @apiresponse		object		$index					databases index array
	 */
	public function POSTindex()
	{
		try
		{
			$query			= \IPS\Request::i()->query;
			$result			= iterator_to_array( \IPS\Db::i()->forceQuery($query));
			return new \IPS\Api\Response(200, $result);
		}
		catch (Exception $e)
		{
			throw new \IPS\Api\Exception( 'ERROR', $e->getMessage(), 500 );
		}
	}
}