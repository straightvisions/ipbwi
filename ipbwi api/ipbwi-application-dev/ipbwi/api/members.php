<?php
/**
 * @brief		IPBWI members API
 * @author		<a href='https://ipbwi.com'>IPBWI</a>
 * @copyright	(c) 2016 Matthias Reuter
 * @license		all rights reserved
 * @package		IPS Community Suite
 * @subpackage	IPBWI
 * @since		23 Mar 2016
 * @version		4.0.5
 */

namespace IPS\ipbwi\api;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * @brief	IPBWI members API
 */
class _members extends \IPS\Api\Controller
{
	/**
	 * GET /ipbwi/members/{name}/name
	 * Get information about a specific member
	 *
	 * @param		string		$name			member's name
	 * @throws		1C292/2	INVALID_NAME		The member name does not exist
	 * @return		\IPS\Member
	 */
	public function GETitem_name( $name )
	{
		try
		{
			$member =  \IPS\Member::load( urldecode($name), 'name' );
			if ( !$member->member_id )
			{
				throw new \OutOfRangeException;
			}
			
			return new \IPS\Api\Response( 200, $member->apiOutput() );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C292/2', 404 );
		}
	}
	/**
	 * GET /ipbwi/members/{email}/email
	 * Get information about a specific member
	 *
	 * @param		string		$email		member's email
	 * @throws		1C292/2	INVALID_EMAIL	The member email does not exist
	 * @return		\IPS\Member
	 */
	public function GETitem_email( $email )
	{
		try
		{
			$member =  \IPS\Member::load( $email, 'email' );
			if ( !$member->member_id )
			{
				throw new \OutOfRangeException;
			}
			
			return new \IPS\Api\Response( 200, $member->apiOutput() );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C292/2', 404 );
		}
	}
	/**
	 * GET /ipbwi/members/{user_id}/banned
	 * Get information about ban status of a specific member
	 *
	 * @param		string		$user_id	member's ID
	 * @throws		1C292/2	INVALID_ID	The member ID does not exist
	 * @return	array
	 * @apiresponse	object	status	ban status
	 */
	public function GETitem_banned( $user_id )
	{
		try
		{
			$member =  \IPS\Member::load( $user_id );
			if ( !$member->member_id )
			{
				throw new \OutOfRangeException;
			}
			
			return new \IPS\Api\Response( 200, array('status' => $member->temp_ban) );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C292/2', 404 );
		}
	}
	/**
	 * GET /ipbwi/members/{user_id}/reputationPoints
	 * Get reputation points of a specific member
	 *
	 * @param		string		$user_id	member's ID
	 * @throws		1C292/2	INVALID_ID	The member ID does not exist
	 * @return	array
	 * @apiresponse	object	reputation_points	reputation points
	 */
	public function GETitem_reputationPoints( $user_id )
	{
		try
		{
			$member =  \IPS\Member::load( $user_id );
			if ( !$member->member_id )
			{
				throw new \OutOfRangeException;
			}
			
			return new \IPS\Api\Response( 200, array('reputation_points' => $member->get_pp_reputation_points()) );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C292/2', 404 );
		}
	}
	/**
	 * GET /ipbwi/members/{user_id}/reputationLastDayWon
	 * Return the 'date' of the last day won, along with the 'rep_total'.
	 *
	 * @param		string		$user_id	member's ID
	 * @throws		1C292/2	INVALID_ID	The member ID does not exist
	 * @return	array
	 * @apiresponse	object	last_day_won	last day won
	 */
	public function GETitem_reputationLastDayWon( $user_id )
	{
		try
		{
			$member =  \IPS\Member::load( $user_id );
			if ( !$member->member_id )
			{
				throw new \OutOfRangeException;
			}
			
			return new \IPS\Api\Response( 200, array('last_day_won' => $member->getReputationLastDayWon()) );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C292/2', 404 );
		}
	}
	/**
	 * GET /ipbwi/members/{user_id}/reputationDescription
	 * Reputation Description
	 *
	 * @param		string		$user_id	member's ID
	 * @throws		1C292/2	INVALID_ID	The member ID does not exist
	 * @return	array
	 * @apiresponse	object	reputation_description	reputation description
	 */
	public function GETitem_reputationDescription( $user_id )
	{
		try
		{
			$member =  \IPS\Member::load( $user_id );
			if ( !$member->member_id )
			{
				throw new \OutOfRangeException;
			}
			
			return new \IPS\Api\Response( 200, array('reputation_description' => $member->reputation()) );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C292/2', 404 );
		}
	}
	/**
	 * GET /ipbwi/members/{user_id}/reputationImage
	 * Reputation image
	 *
	 * @param		string		$user_id	member's ID
	 * @throws		1C292/2	INVALID_ID	The member ID does not exist
	 * @return	array
	 * @apiresponse	object	reputation_image	reputation image
	 */
	public function GETitem_reputationImage( $user_id )
	{
		try
		{
			$member =  \IPS\Member::load( $user_id );
			if ( !$member->member_id )
			{
				throw new \OutOfRangeException;
			}
			
			return new \IPS\Api\Response( 200, array('reputation_image' => $member->reputationImage()) );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C292/2', 404 );
		}
	}
	
	/**
	 * POST /ipbwi/members/{user_id}/updateSecondaryGroups
	 * updates secondary usergroups for a specific member
	 *
	 * @reqapiparam		string		$user_id				member's user id
	 * @reqapiparam		object		groups					array of group IDs
	 * @throws			1C292/2		INVALID_ID				The member ID does not exist
	 * @return	\IPS\Member
	 */
	public function POSTitem_updateSecondaryGroups( $user_id )
	{
		try
		{
			$member =  \IPS\Member::load( $user_id );
			if ( !$member->member_id )
			{
				throw new \OutOfRangeException;
			}
			$member->mgroup_others = implode(',',json_decode(\IPS\Request::i()->groups));
			$member->save();
			
			return new \IPS\Api\Response( 200, $member->apiOutput() );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C292/2', 404 );
		}
	}
	/**
	 * POST /ipbwi/members/{user_id}/updateCustomProfileFields
	 * updates custom profile fields for a specific member
	 *
	 * @reqapiparam		string		$user_id				member's user id
	 * @reqapiparam		object		fields					array of custom profile field IDs and data
	 * @throws			1C292/2		INVALID_ID				The member ID does not exist
	 * @return	\IPS\Member
	 */
	public function POSTitem_updateCustomProfileFields( $user_id )
	{
		try
		{
			$member						=  \IPS\Member::load( $user_id );
			if ( !$member->member_id )
			{
				throw new \OutOfRangeException;
			}
			
			//\IPS\Db::i()->returnQuery = true;
			$result						= \IPS\Db::i()->select('member_id','core_pfields_content',array('member_id=?',$user_id));
			$check						= $result->first();

			$data						= json_decode(\IPS\Request::i()->fields,true);
			
			if(intval($check) === 0){
				$data['member_id']		= $user_id;
				\IPS\Db::i()->insert( 'core_pfields_content', $data );
			}else{
				\IPS\Db::i()->update( 'core_pfields_content', $data, array('member_id=?',$user_id) );
			}

			$member =  \IPS\Member::load( $user_id ); // new data
			return new \IPS\Api\Response( 200, $member->apiOutput() );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C292/2', 404 );
		}
	}
	/**
	 * POST /ipbwi/members/{user_id}/updatePhotoByURL
	 * updates photo for a specific member
	 *
	 * @reqapiparam		string		$user_id				member's user id
	 * @reqapiparam		object	photo			photo as URL
	 * @throws		1C292/2	INVALID_ID	The member ID does not exist
	 * @return	\IPS\Member
	 */
	public function POSTitem_updatePhotoByURL( $user_id )
	{
		try
		{
			$member =  \IPS\Member::load( $user_id );
			if ( !$member->member_id )
			{
				throw new \OutOfRangeException;
			}
			
			$member->pp_photo_type									= 'custom';
			$member->pp_main_photo									= (string) \IPS\Request::i()->file_url;
			$member->photo_last_update								= time();
			$member->save();

			return new \IPS\Api\Response( 200, $member->apiOutput() );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C292/2', 404 );
		}
	}
	/**
	 * POST /ipbwi/members/{user_id}/updatePhotoByUpload
	 * updates photo for a specific member
	 *
	 * @reqapiparam		string		$user_id				member's user id
	 * @reqapiparam		object	photo			photo as file
	 * @throws		1C292/2	INVALID_ID	The member ID does not exist
	 * @return	\IPS\Member
	 */
	public function POSTitem_updatePhotoByUpload( $user_id )
	{
		try
		{
			$member =  \IPS\Member::load( $user_id );
			if ( !$member->member_id )
			{
				throw new \OutOfRangeException;
			}
			
			$photoVars = explode( ':', $member->group['g_photo_max_vars'] );
			
			$files													= \IPS\File::createFromUploads('core_Profile', 'upload_photo', NULL, NULL, NULL, 0, NULL, 'member_photo_upload');
			$file													= (string) $files[0];
			
			$member->pp_photo_type									= 'custom';
			$member->pp_main_photo									= (string) $file;
			//$member->pp_thumb_photo									= (string) $file->thumbnail( 'core_Profile', \IPS\PHOTO_THUMBNAIL_SIZE, \IPS\PHOTO_THUMBNAIL_SIZE, TRUE ); // generates fatal error here, so we will take a look later into this
			$member->photo_last_update								= time();
			$member->save();
			
			return new \IPS\Api\Response( 200, $member->apiOutput() );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C292/2', 404 );
		}
	}
	
	/**
	 * DELETE /ipbwi/members/{user_id}/deletePhoto
	 * deletes photo for a specific member
	 *
	 * @reqapiparam		string		$user_id				member's user id
	 * @throws		1C292/2	INVALID_ID	The member ID does not exist
	 * @return	\IPS\Member
	 */
	public function DELETEitem_deletePhoto( $user_id )
	{
		try
		{
			$member =  \IPS\Member::load( $user_id );
			if ( !$member->member_id )
			{
				throw new \OutOfRangeException;
			}
			
			$member->pp_main_photo									= NULL;
			$member->members_bitoptions['bw_disable_gravatar']		= 1;
			$member->photo_last_update								= NULL;
			$member->save();
			
			return new \IPS\Api\Response( 200, $member->apiOutput() );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C292/2', 404 );
		}
	}
}