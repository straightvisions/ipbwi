<?php
/**
 * @brief		IPBWI menu API
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
 * @brief	IPBWI menu API
 */
class _menu extends \IPS\Api\Controller
{
	/**
	 * GET /ipbwi/menu
	 * Get list of menus
	 *
	 * @return	array
	 * @apiresponse	object	menu	multidimensional array with menu entries
	 */
	public function GETindex()
	{
		$menu										= $this->advancedInfo(\IPS\core\FrontNavigation::i()->frontNavigation());

		return new \IPS\Api\Response(200, $menu);
	}
	protected function advancedInfo($level){
		foreach($level as $pos => $item){
			if(isset($item['id'])){
				if(\IPS\Application::appIsEnabled($item['app'])){
					$class							= 'IPS\\'.$item['app'].'\extensions\core\FrontNavigation\\'.$item['extension'];
					$m								= new $class(json_decode($item['config'], true), $item['id'], $item['permissions']);
					
					// get link
					$level[$pos]['link']			= (string) $m->link();
					
					// get title
					$level[$pos]['title']			= $m->title();
				}
			}elseif(is_array($item)){
				$level[$pos]						= $this->advancedInfo($item);
			}else{
				// nothing to do here
			}
		}
		return $level;
	}
}