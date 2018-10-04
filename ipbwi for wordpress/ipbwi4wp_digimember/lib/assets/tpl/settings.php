<?php
	if(current_user_can('activate_plugins')){
		$settings_default = $this->get_settings_default();
?>
<div id="ipbwi4wp_settings">
	<div id="ipbwi4wp_logo"><img src="<?php echo IPBWI4WP_PLUGIN_URL; ?>lib/assets/img/ipbwi-logo.png" /></div>
	<div id="ipbwi4wp_thankyou">
		<h2><?php _e('DigiMember', 'digimember'); ?></h2>
		<p><?php _e('Synchronize WP roles set by DigiMember subscriptions. These will be synced with secondary Member groups in IPS.', 'digimember'); ?></p>
	</div>
	<?php
		try{
			$groups = $this->ipbwi4wp_digimember->ipbwi4wp->group->ipb_list();
		}catch(Throwable $t){
			echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage();
		}
	?>
	<form action="#" method="post" id="ipbwi4wp_global_settings">
		<h2><?php _e('Map WP Roles to IPS Member Groups', 'digimember'); ?></h2>
		<table width="100%">
		<?php
			echo '
			<div class="ipbwi4wp_setting ipbwi4wp_setting_'.$this->settings_default['digimember']['IPB_GROUPS_MAPPING']['type'].'">
				<div class="ipbwi4wp_setting_name">'.$this->settings_default['digimember']['IPB_GROUPS_MAPPING']['name'].'</div>
				<div class="ipbwi4wp_setting_desc">'.$this->settings_default['digimember']['IPB_GROUPS_MAPPING']['desc'].'</div>
				<div class="ipbwi4wp_setting_value">';

				foreach(digimember_listProducts() as $product){
					echo '<tr><td>'.$product->name.':</td><td><select name="digimember[IPB_GROUPS_MAPPING][value]['.$product->id.']">';
					echo '<option value="">Select an IPS group</option>';
					foreach($groups as $group_id => $group){
						echo '<option value="'.$group_id.'"'.((isset($this->settings['digimember']['IPB_GROUPS_MAPPING']['value'][$product->id]) && $this->settings['digimember']['IPB_GROUPS_MAPPING']['value'][$product->id] == $group_id) ? ' selected="selected"' : '').'>'.$group['g_name'].' (#'.$group_id.')</option>';
					}
					echo '</select></td></tr>';
				}
			echo '</div></div>';
		?>
		</table>
		<input type="hidden" name="ipbwi4wp_digimember_settings" value="1" />
		<div style="clear:both;"><input type="submit" value="<?php echo _e('Save Settings', 'digimember'); ?>" /></div>
	</form>
</div>
<?php
	}
?>