//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class ipbwi_hook_ipbwi_forums_topic_post extends _HOOK_CLASS_
{
	/**
	 * Save Changed Columns
	 *
	 * @return	void
	 */
	public function save()
	{
		$return						= call_user_func_array( 'parent::save', func_get_args());
		
		if(isset(\IPS\Settings::i()->ipbwi_hooks_activate_default) && \IPS\Settings::i()->ipbwi_hooks_activate_default == 1 && isset($this) && is_object($this) && method_exists($this, 'apiOutput')){
			$hooks					= new \IPS\ipbwi\modules\admin\advanced\hooks;
			$hooks->send(__METHOD__,array('item' => $this->apiOutput()));
		}

		return $return;
	}

	/**
	 * Do Moderator Action
	 *
	 * @param	string				$action	The action
	 * @param	\IPS\Member|NULL	$member	The member doing the action (NULL for currently logged in member)
	 * @param	string|NULL			$reason	Reason (for hides)
	 * @return	void
	 * @throws	\OutOfRangeException|\InvalidArgumentException|\RuntimeException
	 */
	public function modAction( $action, \IPS\Member $member=NULL, $reason=NULL )
	{
		if(isset(\IPS\Settings::i()->ipbwi_hooks_activate_default) && \IPS\Settings::i()->ipbwi_hooks_activate_default == 1 && isset($this) && is_object($this) && method_exists($this, 'apiOutput')){
			if($member === NULL){
				$m					= \IPS\Member ::loggedIn();
				$member_output		= $m->apiOutput();
			}elseif(is_object($member)){
				$member_output		= $member->apiOutput();
			}
			
			$hooks					= new \IPS\ipbwi\modules\admin\advanced\hooks;
			$hooks->send(__METHOD__,array('action' => $action, 'member' => $member_output, 'reason' => $reason, 'item' => $this->apiOutput()));
		}
		return call_user_func_array( 'parent::modAction', func_get_args() );
	}

}
