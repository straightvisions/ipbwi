<?php
	if(current_user_can('activate_plugins')){
		$settings_default = $this->ipbwi4wp_user_import->settings->get_settings_default();
?>
<div id="ipbwi4wp_settings">
	<div id="ipbwi4wp_logo"><img src="<?php echo IPBWI4WP_PLUGIN_URL; ?>lib/assets/img/ipbwi-logo.png" /></div>
	<div id="ipbwi4wp_thankyou">
		<h2><?php _e('User Import', 'ipbwi4wp_user_import'); ?></h2>
		<p><?php _e('Just import all your IP.board user accounts to your WordPress site. Map IPB user groups to WP roles.', 'ipbwi4wp_user_import'); ?></p>
	</div>
	<h2>Status</h2>
	<?php
		try{
			$members = $this->ipbwi4wp_user_import->ipbwi4wp->member->ipb_list();
			echo '<p><strong>'.__('Total Members:', 'ipbwi4wp').'</strong> '.$members['totalResults'].'</p>';
			echo '<p><strong>'.__('Members per import cycle:', 'ipbwi4wp').'</strong> '.$members['perPage'].'</p>';
			echo '<p><strong>'.__('Total Cycles:', 'ipbwi4wp').'</strong> '.$members['totalPages'].'</p>';
		}catch(Throwable $t){
			echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage();
		}
		try{
			$groups = $this->ipbwi4wp_user_import->ipbwi4wp->group->ipb_list();
			echo '<p><strong>'.__('Total Groups:', 'ipbwi4wp').'</strong> '.count($groups).'</p>';
		}catch(Throwable $t){
			echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage();
		}
		if(function_exists('wp_get_sites')){
			echo '<p><strong>'.__('Total Sites:', 'ipbwi4wp').'</strong> '.count(wp_get_sites()).'</p>';
		}
	?>
	<form action="#" method="post" id="ipbwi4wp_global_settings">
		<h2><?php _e('Map User Groups to WP Roles', 'ipbwi4wp_user_import'); ?></h2>
		<table width="100%">
		<?php
			echo '
			<div class="ipbwi4wp_setting ipbwi4wp_setting_'.$this->settings_default['import']['IPB_GROUPS_MAPPING']['type'].'">
				<div class="ipbwi4wp_setting_name">'.$this->settings_default['import']['IPB_GROUPS_MAPPING']['name'].'</div>
				<div class="ipbwi4wp_setting_desc">'.$this->settings_default['import']['IPB_GROUPS_MAPPING']['desc'].'</div>
				<div class="ipbwi4wp_setting_value">';
				foreach($groups as $key => $data){
					echo '<tr><td>'.$data['g_name'].':</td><td><select name="import[IPB_GROUPS_MAPPING][value]['.$key.']">';
					wp_dropdown_roles(isset($this->settings['import']['IPB_GROUPS_MAPPING']['value'][$key]) ? $this->settings['import']['IPB_GROUPS_MAPPING']['value'][$key] : 'subscriber');
					echo '</select></td></tr>';
				}
			echo '</div></div>';
		?>
		</table>
		<input type="hidden" name="ipbwi4wp_user_import_settings" value="1" />
		<div style="clear:both;"><input type="submit" value="<?php echo _e('Save Settings', 'ipbwi4wp_user_import'); ?>" /></div>
	</form>
	<h2><?php _e('Start Import', 'ipbwi4wp_user_import'); ?></h2>
	<p><?php _e('Please do not close browser windows or tab until import has been finished.', 'ipbwi4wp_user_import'); ?></p>
	<div style="clear:both;">
		<input type="submit" value="<?php echo _e('Start Import', 'ipbwi4wp_user_import'); ?>" id="ipbwi4wp_user_import_start" />
		<?php if(get_transient('ipbwi4wp_user_import_pages_completed') > 1 && get_transient('ipbwi4wp_user_import_pages_completed') < $members['totalPages']){ ?>
		<input type="submit" value="<?php echo _e('Continue Import from Cycle', 'ipbwi4wp_user_import'); ?> <?php echo get_transient('ipbwi4wp_user_import_pages_completed'); ?>" id="ipbwi4wp_user_import_continue" />
		<?php } ?>
	</div>
	<div id="ipbwi4wp_user_import_status">
		<h2>Import Status</h2>
		<div id="ipbwi4wp_user_import_status_progress_bar"></div>
		<div id="ipbwi4wp_user_import_status_progress_text"><div class="left"></div><div class="center"></div><div class="right"></div></div>
		<div id="ipbwi4wp_user_import_status_progress_log"></div>
	</div>
</div>
<?php
	}
?>