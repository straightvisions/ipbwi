//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class ipbwi_hook_ipbwi_dispatcher_admin extends _HOOK_CLASS_
{


	/**
	 * Base CSS
	 *
	 * @return	void
	 */
	static public function baseCss()
	{
		\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'ipbwi.css', 'ipbwi' ) );

		return call_user_func_array( 'parent::baseCss', func_get_args() );
	}

}
