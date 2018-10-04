<?php
	if(current_user_can('activate_plugins')){
		$settings_default = $this->ipbwi4wp->settings->get_settings_default();
?>
<div id="ipbwi4wp_settings">
	<div id="ipbwi4wp_logo"><img src="<?php echo IPBWI4WP_PLUGIN_URL; ?>lib/assets/img/ipbwi-logo.png" /></div>
	<div id="ipbwi4wp_thankyou">
		<h2><?php _e('IPBWI at your service', 'ipbwi4wp'); ?></h2>
		<p><?php _e('With your purchase you actively support further development, so expect further improvements and new features in future. We are thrilled to give you the best SSO experience on market between IP.board and WordPress.', 'ipbwi4wp'); ?></p>
		<p><?php _e('This WordPress plugin is designed to give you maximum flexibility by avoiding settings overload. If you need additional support or update save feature development, don\'t hesitate to', 'ipbwi4wp'); ?> <a href="https://straightvisions.com/about-us/contact/" target="_blank"><?php _e('contact us', 'ipbwi4wp'); ?></a>.</p>
	</div>
	<h2>Status</h2>
	<?php
		if(isset($this->settings['ipbwi4wp_settings']) && $this->settings['ipbwi4wp_settings'] == 1){
			if(strpos($this->ipbwi4wp->settings->get_IPS_CONNECT_BASE_URL(),'.php') !== false){
				echo '<p class="ipbwi_error">'.__('Please review your IP.board Base URL. It should not end with a PHP file, instead it should be the index URL of your board.', 'ipbwi4wp').'</p>';
			}elseif($this->ipbwi4wp->settings->get_IPS_CONNECT_BASE_URL() && $this->ipbwi4wp->settings->get_IPS_CONNECT_MASTER_KEY() && $this->ipbwi4wp->settings->get_IPS_REST_API_KEY()){
				try{
					if($this->ipbwi4wp->ipbwi->sso->verifySettings() === true){
						try{
							$current_user = wp_get_current_user();
							echo '<h3>'.__('Admin Account Status', 'ipbwi4wp').'</h3>';
							echo '<p>'.__('WordPress Account Data:', 'ipbwi4wp').' '.$current_user->user_login.' ('.$current_user->user_email.')</p>';

							if($this->ipbwi4wp->member->ipb_exists($current_user->ID)){
								$ipb_user = $this->ipbwi4wp->member->ipb_get_by_email($current_user->user_email);
								if($ipb_user['name'] != $current_user->user_login){
									printf('<p class="ipbwi_error">'.__('Account with same emailaddress found in IP.board, but IP.board username (%1$s) differs from WordPress username (%2$s). This will result in non-working SSO. Please synchronize login-names manually to solve this issue.', 'ipbwi4wp').'</p>',$ipb_user['name'],$current_user->user_login);
								}else{
									printf('<p class="ipbwi_success">'.__('Congratulations, your admin-account (%1$s) with email address (%2$s) in IP.board is synced with this WordPress installation.', 'ipbwi4wp').'</p>',$ipb_user['name'],$ipb_user['email']);
								}
							}else{
								printf('<p class="ipbwi_error">'.__('There is no userracount with email (%1$s) in IP.board. User (%2$s) will be registered in IP.board and all slaves upon next login in this WordPress installation.', 'ipbwi4wp').'</p>',$current_user->user_email,$current_user->user_login);
							}
						}catch(Throwable $t){
							echo '<p>'.__('SSO connection cannot be established. Please correct your settings and try again.', 'ipbwi4wp').'</p>';
							echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage();
						}
					}
				}catch(Throwable $t){
					echo '<p>'.__('SSO connection cannot be established. Please correct your settings and try again.', 'ipbwi4wp').'</p>';
					echo '<p>Type Error, line '.$t->getLine().': ' .$t->getMessage().'</p>';
				}
				
				echo '<h3>'.__('IP.board REST API connection status', 'ipbwi4wp').'</h3>';
				try{
					$ips_hello = $this->ipbwi4wp->ipbwi->core->hello();
					echo '<p><strong>'.__('Community Name:', 'ipbwi4wp').'</strong> '.$ips_hello['communityName'].'</p>';
					echo '<p><strong>'.__('Community URL:', 'ipbwi4wp').'</strong> '.$ips_hello['communityUrl'].'</p>';
					echo '<p><strong>'.__('IPS Version:', 'ipbwi4wp').'</strong> '.$ips_hello['ipsVersion'].'</p>';
				}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
				echo '<h3>'.__('IP.board IPBWI extended REST API connection status', 'ipbwi4wp').'</h3>';
				try{
					$ipbwi_hello = $this->ipbwi4wp->ipbwi->extended->hello();
					echo '<p><strong>'.__('Application ID:', 'ipbwi4wp').'</strong> '.$ipbwi_hello['app_id'].'</p>';
					echo '<p><strong>'.__('Application Version:', 'ipbwi4wp').'</strong> '.$ipbwi_hello['app_version'].'</p>';
					echo '<p><strong>'.__('Application Long Version:', 'ipbwi4wp').'</strong> '.$ipbwi_hello['app_long_version'].'</p>';
					echo '<p><strong>'.__('Application Directory:', 'ipbwi4wp').'</strong> '.$ipbwi_hello['app_directory'].'</p>';
				}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
			}else{
				echo '<p class="ipbwi_warning">'.__('You have not set all settings yet - please fill out the settings form below', 'ipbwi4wp').'</p>';
			}
		}else{ // no settings saved yet
			echo '<p class="ipbwi_warning">'.__('You have not saved any settings yet - please fill out the settings form below', 'ipbwi4wp').'</p>';
		}
	?>
	<form action="#" method="post" id="ipbwi4wp_global_settings">
		<h2><?php _e('SSO Settings', 'ipbwi4wp'); ?></h2>
		<?php
			foreach($settings_default['sso'] as $key => $data){
				echo $this->ipbwi4wp->settings->get_form_block('sso',$key,$data);
			}
		?>
		<h2><?php _e('REST Settings', 'ipbwi4wp'); ?></h2>
		<p><?php _e('In order to get SSO work properly, you need to follow these steps:', 'ipbwi4wp'); ?></p>
		<ol>
			<li><?php _e('Go to IP.board Admin CP » System » Site Features » Applications » Install and upload file ipbwi.tar. This file can be found within the zip-archive of this plugin.', 'ipbwi4wp'); ?></li>
			<li><?php _e('Go to IP.board Admin CP » System » Site Features » REST API and create a new API Key.', 'ipbwi4wp'); ?></li>
			<li><?php _e('Select "Access" for all endpoints and save the new API Key.', 'ipbwi4wp'); ?></li>
			<li><?php _e('Insert API Key in form field below.', 'ipbwi4wp'); ?></li>
		</ol>
		<?php
			foreach($settings_default['rest'] as $key => $data){
				echo $this->ipbwi4wp->settings->get_form_block('rest',$key,$data);
			}
		?>
		<h2><?php _e('Advanced Settings', 'ipbwi4wp'); ?></h2>
		<p><?php _e('You can adjust behavior of IPBWI more granular here.', 'ipbwi4wp'); ?></p>
		<?php
			foreach($settings_default['advanced'] as $key => $data){
				echo $this->ipbwi4wp->settings->get_form_block('advanced',$key,$data);
			}
			if(function_exists('wp_get_sites')){
		?>
		<h2><?php _e('Network Settings', 'ipbwi4wp'); ?></h2>
		<p><?php _e('This site is part of a WordPress Network (MU).', 'ipbwi4wp'); ?></p>
		<?php
				foreach($settings_default['network'] as $key => $data){
					echo $this->ipbwi4wp->settings->get_form_block('network',$key,$data);
				}
			}
		?>
		<input type="hidden" name="ipbwi4wp_settings" value="1" />
		<div style="clear:both;padding-top:20px;"><input type="submit" value="<?php echo _e('Save Settings', 'ipbwi4wp'); ?>" /></div>
	</form>
</div>
<?php
	}
?>