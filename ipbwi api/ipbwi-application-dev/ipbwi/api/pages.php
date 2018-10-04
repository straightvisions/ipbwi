<?php
/**
 * @brief		IPBWI pages API
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
 * @brief	IPBWI pages API
 */
class _pages extends \IPS\Api\Controller
{
	/**
	 * GET /ipbwi/pages
	 * Get list of databases from IP.pages
	 *
	 * @return			array
	 * @apiresponse		object		$index				databases index array
	 */
	public function GETindex()
	{
		try
		{
			$databases = iterator_to_array( \IPS\Db::i()->select( '*', 'cms_databases'));
			return new \IPS\Api\Response(200, $databases);
		}
		catch (Exception $e)
		{
			throw new \IPS\Api\Exception( 'ERROR', $e->getMessage(), 500 );
		}
	}

	/**
	 * GET /ipbwi/pages/{database_id}/image/{record_id}
	 * Get image on a record
	 *
	 * @reqapiparam		int			$database_id		Database ID Number
	 * @reqapiparam		int			$record_id			Record ID Number
	 * @return			array
	 * @apiresponse		string		$url				image url
	 */
	public function GETitem_image($database_id, $record_id)
	{
		// Load database
		try
		{
			$database		= \IPS\cms\Databases::load($database_id);
			$recordClass	= '\IPS\cms\Records' . $database->id;
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_DATABASE', '2T306/C', 404 );
		}
		
		$record				= $recordClass::load($record_id);
		$filename			= $record->record_image;
		$file = \IPS\File::get( 'core_Records', $filename );
		
		return new \IPS\Api\Response(200, $file->url->__toString());
	}
	/**
	 * GET /ipbwi/pages/{database_id}/topicid/{record_id}
	 * Get topicid on a record
	 *
	 * @reqapiparam		int			$database_id		Database ID Number
	 * @reqapiparam		int			$record_id			Record ID Number
	 * @return			array
	 * @apiresponse		int			$topicid			topicid
	 */
	public function GETitem_topicid($database_id, $record_id)
	{
		// Load database
		try
		{
			$database		= \IPS\cms\Databases::load($database_id);
			$recordClass	= '\IPS\cms\Records' . $database->id;
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_DATABASE', '2T306/C', 404 );
		}
		
		$record				= $recordClass::load($record_id);
		$tid				= $record->record_topicid;
		
		return new \IPS\Api\Response(200, $tid);
	}
}