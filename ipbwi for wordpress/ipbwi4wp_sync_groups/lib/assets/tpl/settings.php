<?php
	if(current_user_can('activate_plugins')){
		$settings_default = $this->get_settings_default();
?>
<div id="ipbwi4wp_settings">
	<div id="ipbwi4wp_logo"><img src="<?php echo IPBWI4WP_PLUGIN_URL; ?>lib/assets/img/ipbwi-logo.png" /></div>
	<div id="ipbwi4wp_thankyou">
		<h2><?php _e('Sync Groups', 'ipbwi4wp_sync_groups'); ?></h2>
		<p><?php _e('Synchronize WP roles with IPS community user groups.', 'ipbwi4wp_sync_groups'); ?></p>
	</div>
	<?php
		try{
			$groups = $this->core->ipbwi4wp->group->ipb_list();
		}catch(Throwable $t){
			echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage();
		}
	?>
	<form action="#" method="post" id="ipbwi4wp_global_settings">
		<h2><?php _e('Map WP Roles to IPS Member Groups', 'ipbwi4wp_sync_groups'); ?></h2>
		<table width="100%">
		<?php
			echo '
			<div class="ipbwi4wp_setting ipbwi4wp_setting_'.$this->settings_default['basic']['IPB_GROUPS_MAPPING']['type'].'">
				<div class="ipbwi4wp_setting_name">'.$this->settings_default['basic']['IPB_GROUPS_MAPPING']['name'].'</div>
				<div class="ipbwi4wp_setting_desc">'.$this->settings_default['basic']['IPB_GROUPS_MAPPING']['desc'].'</div>
				<div class="ipbwi4wp_setting_value">';

				foreach(get_editable_roles() as $key => $data){
					echo '<tr><td>'.$data['name'].':</td><td><select name="basic[IPB_GROUPS_MAPPING][value]['.$key.']">';
					echo '<option value="">Select an IPS group</option>';
					foreach($groups as $group_id => $group){
						echo '<option value="'.$group_id.'"'.((isset($this->settings['basic']['IPB_GROUPS_MAPPING']['value'][$key]) && $this->settings['basic']['IPB_GROUPS_MAPPING']['value'][$key] == $group_id) ? ' selected="selected"' : '').'>'.$group['g_name'].'</option>';
					}
					echo '</select></td></tr>';
				}
			echo '</div></div>';
		?>
		</table>
		<input type="hidden" name="ipbwi4wp_sync_groups_settings" value="1" />
		<div style="clear:both;"><input type="submit" value="<?php echo _e('Save Settings', 'ipbwi4wp_sync_groups'); ?>" /></div>
	</form>
</div>
<?php
	}
?>