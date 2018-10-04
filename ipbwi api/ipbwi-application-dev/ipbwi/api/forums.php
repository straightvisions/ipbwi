<?php
/**
 * @brief		IPBWI forums API
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
 * @brief	IPBWI forums API
 */
class _forums extends \IPS\Api\Controller
{
	/**
	 * GET /ipbwi/forums
	 * Get list of forums
	 *
	 * @return	array
	 * @apiresponse	object	forums	name and ibf_forums_forums table fields
	 */
	public function GETindex()
	{
		foreach(\IPS\Db::i()->select("*",'forums_forums')->join('core_sys_lang_words',"word_key=CONCAT( 'forums_forum_', id )") as $forum){
			$forums[$forum['id']]	= $forum;
		}
					
		return new \IPS\Api\Response(200, (array)$forums);
	}
	
	/**
	 * GET /ipbwi/forums/{id}
	 * Get information about a specific forum
	 *
	 * @param		int		$id			ID Number
	 * @throws		1C292/2	INVALID_ID	The forum ID does not exist
	 * @return	array
	 * @apiresponse	object	forums	name and ibf_forums_forums table fields
	 */
	public function GETitem( $id )
	{
		try
		{
			$forum = \IPS\Db::i()->select("*",'forums_forums',array('id=?',$id))->join('core_sys_lang_words',"word_key=CONCAT( 'forums_forum_', id )")->first();
			
			return new \IPS\Api\Response( 200, $forum );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C292/2', 404 );
		}
	}
	
	/**
	 * POST /ipbwi/forums
	 * Create a forum
	 *
	 * @apiparam	string	name			forum name
	 * @apiparam	object	data			ibf_forums_forums table fields
	 * @return	array
	 * @apiresponse	object	forums	name and ibf_forums_forums table fields
	 */
	public function POSTindex()
	{
		$forum = new \IPS\forums\Forum;
		
		foreach(json_decode(\IPS\Request::i()->data) as $field => $value){
			$forum->$field = $value;
		}
		
		$forum->save();
		
		\IPS\Lang::saveCustom( 'core', "forums_forum_{$forum->id}", \IPS\Request::i()->name);
		
		$forum_saved = \IPS\Db::i()->select("*",'forums_forums',array('id=?',$forum->id))->join('core_sys_lang_words',"word_key=CONCAT( 'forums_forum_', id )")->first();
				
		return new \IPS\Api\Response( 201, $forum_saved);
	}
	
	/**
	 * POST /ipbwi/forums/{id}
	 * Edit a forum
	 *
	 * @apiparam	string	name			forum name
	 * @apiparam	object	data			ibf_forums_forums table fields
	 * @param		int		$id				ID Number
	 * @throws		2C292/7	INVALID_ID		The forum ID provided is not valid
	 * @return	array
	 * @apiresponse	object	forums	name and ibf_forums_forums table fields
	 */
	public function POSTitem( $id )
	{
		try
		{
			$forum = \IPS\forums\Forum::load( $id );
			if ( !$forum->id )
			{
				throw new \OutOfRangeException;
			}

			foreach(json_decode(\IPS\Request::i()->data) as $field => $value){
				$forum->$field = $value;
			}
			
			$forum->save();
			
			\IPS\Lang::saveCustom( 'core', "forums_forum_{$forum->id}", \IPS\Request::i()->name);
			
			$forum_saved = \IPS\Db::i()->select("*",'forums_forums',array('id=?',$forum->id))->join('core_sys_lang_words',"word_key=CONCAT( 'forums_forum_', id )")->first();

			return new \IPS\Api\Response( 200, $forum_saved );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '2C292/7', 404 );
		}
	}
	
	/**
	 * DELETE /ipbwi/forums/{id}
	 * Deletes a forum
	 *
	 * @param		int		$id			ID Number
	 * @throws		1C292/3	INVALID_ID	The forum ID does not exist
	 * @return	array
	 * @apiresponse	object	status	SUCCESS
	 */
	public function DELETEitem( $id )
	{
		try
		{
			$forum = \IPS\forums\Forum::load( $id );
			if ( !$forum->id )
			{
				throw new \OutOfRangeException;
			}
			
			$forum->delete();
			
			return new \IPS\Api\Response( 200, array('status' => 'SUCCESS') );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C292/2', 404 );
		}
	}
}