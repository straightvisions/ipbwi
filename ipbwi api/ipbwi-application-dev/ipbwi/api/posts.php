<?php
/**
 * @brief		IPBWI posts API
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
 * @brief	IPBWI posts API
 */
class _posts extends \IPS\Api\Controller
{
	/**
	 * GET /ipbwi/posts/{id}/reputation
	 * get reputation count
	 *
	 * @param			int		$id							ID Number
	 * @throws			1C292/2	INVALID_ID					The post ID does not exist
	 * @return			array
	 * @apiresponse		int		$count						reputation count
	 */
	public function GETitem_reputation( $id )
	{
		try
		{
			$post					= \IPS\forums\Topic\Post::load( $id );
			if ( !$post->pid )
			{
				throw new \OutOfRangeException;
			}
			
			$count					= $post->reputation();
			
			return new \IPS\Api\Response( 200, array('count' => $count));
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C292/2', 404 );
		}
	}
	/**
	 * GET /ipbwi/posts/{id}/canGiveReputation
	 * Can give reputation? This method is also ran to check if a member can "unrep"
	 *
	 * @param			int		$id							ID Number
	 * @reqapiparam		int		$type						1 for positive, -1 for negative
	 * @reqapiparam		int		$user_id					sender's member ID
	 * @throws			1C292/2	INVALID_ID					The post ID does not exist
	 * @return			array
	 * @apiresponse		bool	$can						true or false
	 */
	public function GETitem_canGiveReputation( $id )
	{
		try
		{
			$post					= \IPS\forums\Topic\Post::load( $id );
			
			if ( !$post->pid )
			{
				throw new \OutOfRangeException;
			}
			$member					=  \IPS\Member::load( \IPS\Request::i()->user_id );
			if ( !$member->member_id )
			{
				throw new \OutOfRangeException;
			}
			
			$type					= intval( \IPS\Request::i()->type ) === 1 ? 1 : -1;
			$can					= $post->canGiveReputation( $type, $member );
			
			return new \IPS\Api\Response( 200, array('can' => $can));
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C292/2', 404 );
		}
	}
	
	
	/**
	 * GET /ipbwi/posts/{id}/reputationGiven
	 * Has reputation been given by a particular member?
	 *
	 * @param			int		$id							ID Number
	 * @reqapiparam		int		$user_id					sender's member ID
	 * @throws			1C292/2	INVALID_ID					The post ID oder member ID does not exist
	 * @return			array
	 * @apiresponse		int		$repGiven					1 = Positive rep given. -1 = Negative rep given. 0 = No rep given
	 */
	public function GETitem_reputationGiven( $id )
	{
		try
		{
			$post					= \IPS\forums\Topic\Post::load( $id );
			if ( !$post->pid )
			{
				throw new \OutOfRangeException;
			}
			$member =  \IPS\Member::load( \IPS\Request::i()->user_id );
			if ( !$member->member_id )
			{
				throw new \OutOfRangeException;
			}
			return new \IPS\Api\Response( 200, array('repGiven' => $post->repGiven($member)));
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C292/2', 404 );
		}
	}
	/**
	 * POST /ipbwi/posts/{id}/reputation
	 * Give reputation
	 *
	 * @param		int		$id			ID Number
	 * @reqapiparam		int		$type					1 for positive, -1 for negative
	 * @reqapiparam		int		$user_id				sender's member ID
	 * @throws			1C292/2	INVALID_ID				The post ID does not exist
	 * @return			array
	 * @apiresponse		int		$count					reputation count
	 */
	public function POSTitem_reputation( $id )
	{
		try
		{
			$post = \IPS\forums\Topic\Post::load( $id );
			if ( !$post->pid )
			{
				throw new \OutOfRangeException;
			}
			
			$member =  \IPS\Member::load( \IPS\Request::i()->user_id );
			if ( !$member->member_id )
			{
				throw new \OutOfRangeException;
			}
			
			$type = intval( \IPS\Request::i()->type ) === 1 ? 1 : -1;
			$post->giveReputation( $type, $member );
			
			return $this->GETitem_reputation( $id );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C292/2', 404 );
		}
	}
}