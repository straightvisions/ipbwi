//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class ipbwi_hook_ipbwi_member extends _HOOK_CLASS_
{

	/**
	 * Set Photo
	 *
	 * @param	string	$photo	Photo location
	 * @return	void
	 */
	public function set_pp_main_photo( $photo )
	{
		$return						=  call_user_func_array( 'parent::set_pp_main_photo', func_get_args() );
      
        if(isset(\IPS\Settings::i()->ipbwi_hooks_activate_default) && \IPS\Settings::i()->ipbwi_hooks_activate_default == 1 && isset($this) && is_object($this) && method_exists($this, 'apiOutput')){
            $hooks					= new \IPS\ipbwi\modules\admin\advanced\hooks;
            $hooks->send(__METHOD__,array('photo_path' => $photo, 'member' => $this->apiOutput()));
        }
      
		return $return;
	}

	/**
	 * [ActiveRecord] Save Changed Columns
	 *
	 * @return	void
	 * @note	We have to be careful when upgrading in case we are coming from an older version
	 */
	public function save()
	{
		// trigger this hook only if something has changed
		if(count($this->changed) > 1 ||
		(count($this->changed) == 1 && !isset($this->changed['last_activity']))
		){
			$result						= \IPS\Db::i()->select('mgroup_others','core_members',array('member_id=?',$this->apiOutput()['id']));
			$groups_orig				= $result->first();

			$return						= call_user_func_array( 'parent::save', func_get_args() );

			if(isset(\IPS\Settings::i()->ipbwi_hooks_activate_default) && \IPS\Settings::i()->ipbwi_hooks_activate_default == 1 && isset($this) && is_object($this) && method_exists($this, 'apiOutput')){
				$hooks					= new \IPS\ipbwi\modules\admin\advanced\hooks;
				$hooks->send(__METHOD__,array('member' => $this->apiOutput(), 'groups_orig' => $groups_orig));
			}
		}else{
			$return						= call_user_func_array( 'parent::save', func_get_args() );
		}
      
		return $return;
	}

}
