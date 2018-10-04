<?php
/**
 * @brief		IPBWI groups API
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
 * @brief	IPBWI groups API
 */
class _groups extends \IPS\Api\Controller
{
	/**
	 * GET /ipbwi/groups
	 * Get list of groups
	 *
	 * @return	array
	 * @apiresponse	object	groups	name and ibf_core_groups table fields
	 */
	public function GETindex()
	{
		foreach ( \IPS\Member\Group::groups() as $k => $v )
		{
			$groups[ $k ] = array_merge(array('g_name' => $v->get_name()),(array)$v);
		}
		
		return new \IPS\Api\Response(200, $groups);
	}
	
	/**
	 * GET /ipbwi/groups/{id}
	 * Get information about a specific group
	 *
	 * @param		int		$id			ID Number
	 * @throws		1C292/2	INVALID_ID	The group ID does not exist
	 * @return	array
	 * @apiresponse	object	groups	name and ibf_core_groups table fields
	 */
	public function GETitem( $id )
	{
		try
		{
			$group = \IPS\Member\Group::load( $id );
			if ( !$group->g_id )
			{
				throw new \OutOfRangeException;
			}
			
			return new \IPS\Api\Response( 200, array_merge(array('g_name' => $group->get_name()),$group->data()) );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C292/2', 404 );
		}
	}
	
	/**
	 * POST /ipbwi/groups
	 * Create a group
	 *
	 * @reqapiparam	string	name			group name
	 * @reqapiparam	object	data			ibf_core_groups table fields
	 * @return	array
	 * @apiresponse	object	groups	name and ibf_core_groups table fields
	 */
	public function POSTindex()
	{
		$group = new \IPS\Member\Group;
		
		foreach(json_decode(\IPS\Request::i()->data) as $field => $value){
			$group->$field = $value;
		}
		
		$group->save();
		
		\IPS\Lang::saveCustom( 'core', "core_group_{$group->g_id}", \IPS\Request::i()->name);
				
		return new \IPS\Api\Response( 201, array_merge(array('g_name' => $group->get_name()),$group->data()) );
	}
	
	/**
	 * POST /ipbwi/groups/{id}
	 * Edit a group
	 *
	 * @reqapiparam	string	name			group name
	 * @reqapiparam	object	data			ibf_core_groups table fields
	 * @param		int		$id				ID Number
	 * @throws		2C292/7	INVALID_ID		The group ID provided is not valid
	 * @return	array
	 * @apiresponse	object	groups	name and ibf_core_groups table fields
	 */
	public function POSTitem( $id )
	{
		try
		{
			$group = \IPS\Member\Group::load( $id );
			
			foreach(json_decode(\IPS\Request::i()->data) as $field => $value){
				$group->$field = $value;
			}
			
			$group->save();
			
			\IPS\Lang::saveCustom( 'core', "core_group_{$group->g_id}", \IPS\Request::i()->name);

			return new \IPS\Api\Response( 200, array_merge(array('g_name' => $group->get_name()),$group->data()) );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '2C292/7', 404 );
		}
	}
	
	/**
	 * DELETE /ipbwi/groups/{id}
	 * Deletes a group
	 *
	 * @param		int		$id			ID Number
	 * @throws		1C292/3	INVALID_ID	The group ID does not exist
	 * @return	array
	 * @apiresponse	object	status	SUCCESS
	 */
	public function DELETEitem( $id )
	{
		try
		{
			$group = \IPS\Member\Group::load( $id );
			if ( !$group->g_id )
			{
				throw new \OutOfRangeException;
			}
			
			$group->delete();
			
			return new \IPS\Api\Response( 200, array('status' => 'SUCCESS') );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C292/2', 404 );
		}
	}
}