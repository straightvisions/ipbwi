<?php


namespace IPS\ipbwi\modules\admin\advanced;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * hooks
 */
class _hooks extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		
		parent::execute();
	}

	/**
	 * ...
	 *
	 * @return	void
	 */
	protected function manage()
	{
		// This is the default method if no 'do' parameter is specified
		$form = new \IPS\Helpers\Form;
		
		$form->add(new \IPS\Helpers\Form\YesNo('ipbwi_hooks_activate_default', \IPS\Settings::i()->ipbwi_hooks_activate_default));
		$form->add(new \IPS\Helpers\Form\YesNo('ipbwi_hooks_target_slaves', \IPS\Settings::i()->ipbwi_hooks_target_slaves));
		$form->add(new \IPS\Helpers\Form\Url('ipbwi_hooks_target_custom', \IPS\Settings::i()->ipbwi_hooks_target_custom));
		$form->add(new \IPS\Helpers\Form\Email('ipbwi_hooks_log_email', \IPS\Settings::i()->ipbwi_hooks_log_email));

		if($values = $form->values()){
			$form->saveAsSettings();
			\IPS\Session::i()->log('acplogs__ipbwi_settings');
		}

		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('IPBWI Advanced - Hook Settings');
		\IPS\Output::i()->output = $form;
	}
	public function send($do,$output){
		$queryString												= array(
			'do'													=> $do,
			'key'													=> '_SERVER_',
		);
		$post_data													= $output;
		
		// Custom URL notification
		if(isset(\IPS\Settings::i()->ipbwi_hooks_target_custom) && mb_strlen(\IPS\Settings::i()->ipbwi_hooks_target_custom) > 0){
			$this->call(\IPS\Settings::i()->ipbwi_hooks_target_custom,$queryString);
		}
		// Slaves notification
		if(isset(\IPS\Settings::i()->ipbwi_hooks_target_slaves) && \IPS\Settings::i()->ipbwi_hooks_target_slaves == 1){
			$this->callSlaves($queryString,$post_data);
		}
	}
	protected function callSlaves($queryString,$post_data){
		// Only do this if we're not a slave that is being called
		if( !\IPS\Request::i()->slaveCall )
		{
			$select	= \IPS\Db::i()->select( '*', 'core_ipsconnect_slaves', array( 'slave_enabled=?', 1 ) );

			if( $select->count() )
			{
				foreach( $select as $row )
				{
					
					if( $row['slave_url'] != \IPS\Request::i()->url )
					{
						$key	= '';

						if( isset( $queryString['key'] ) )
						{
							if( $queryString['key'] == '_SERVER_' )
							{
								$key	= $row['slave_key'];
							}
						}
						$queryString								= array_merge( $queryString, array( 'slaveCall' => 1, 'key' => $key ?: ( ( isset( $queryString['key'] ) ) ? $queryString['key'] : '' ) ) );
						
						$this->call($row['slave_url'],$queryString,$post_data,$row['slave_id']);
					}
					$queryString['key']								= '_SERVER_';
				}
			}
		}
	}
	protected function call($url,$queryString,$post_data,$slave_id){
		try
		{
			$failed													= false;
			$response = \IPS\Http\Url::external($url)
				->setQueryString($queryString)
				->request()
				->post(array('data' => $post_data))
				->decodeJson();
		}
		catch( \RuntimeException $e )
		{
			\IPS\Db::i()->insert( 'core_ipsconnect_queue', array( 'slave_id' => $slave_id, 'request_url' => http_build_query( array_merge( $queryString, array( 'slaveCall' => 1, 'key' => $key ?: ( ( isset( $queryString['key'] ) ) ? $queryString['key'] : '' ) ) ) ) ) );

			// Make sure task is enabled
			\IPS\Db::i()->update( 'core_tasks', array( 'enabled' => 1 ), "`key`='connect'" );
			
			// Hook trigger notification
			if(isset(\IPS\Settings::i()->ipbwi_hooks_log_email) && mb_strlen(\IPS\Settings::i()->ipbwi_hooks_log_email) > 0){
				mail(\IPS\Settings::i()->ipbwi_hooks_log_email,'IPBWI IPS Hook FAILED - Query: '.$url,var_export($queryString,true));
				mail(\IPS\Settings::i()->ipbwi_hooks_log_email,'IPBWI IPS Hook FAILED - Response: '.$url,var_export($e,true)."\n\r\n\r".var_export($response,true));
				
				error_log(var_export(\IPS\Http\Url::external($url)->setQueryString($queryString)->request(),true));
			}
			$failed													= true;
		}
		if(!$failed){
			// Hook trigger notification
			if(isset(\IPS\Settings::i()->ipbwi_hooks_log_email) && mb_strlen(\IPS\Settings::i()->ipbwi_hooks_log_email) > 0){
				mail(\IPS\Settings::i()->ipbwi_hooks_log_email,'IPBWI IPS Hook SUCCESS: '.$url,json_encode($queryString));
			}
		}
	}
	
	// Create new methods with the same name as the 'do' parameter which should execute it
}