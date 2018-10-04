<?php
/**
 * @brief		IPBWI topics API
 * @author		<a href='https://ipbwi.com'>IPBWI</a>
 * @copyright	(c) 2016 Matthias Reuter
 * @license		all rights reserved
 * @package		IPS Community Suite
 * @subpackage	IPBWI
 * @since		23 Mar 2016
 * @version		4.1.1
 */

namespace IPS\ipbwi\api;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * @brief	IPBWI topics API
 */
class _topics extends \IPS\Api\Controller
{
	/**
	 * GET /ipbwi/topics/{id}
	 * Get information about a specific topic
	 *
	 * @param		int		$id			ID Number
	 * @throws		1C292/2	INVALID_ID	The topic ID does not exist
	 * @return		\IPS\forums\Topic
	 */
	public function GETitem( $id )
	{
		try
		{
			$topic = \IPS\forums\Topic::load( $id );
			if ( !$topic->tid )
			{
				throw new \OutOfRangeException;
			}
			
			return new \IPS\Api\Response( 200, $topic->apiOutput());
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C292/2', 404 );
		}
	}
}