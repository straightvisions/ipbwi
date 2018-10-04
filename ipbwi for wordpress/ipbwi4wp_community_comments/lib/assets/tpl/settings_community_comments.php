<?php
	if(current_user_can('activate_plugins')){
		$settings_default = $this->ipbwi4wp_community_comments->settings->get_settings_default();
?>
<div id="ipbwi4wp_settings">
	<div id="ipbwi4wp_logo"><img src="<?php echo IPBWI4WP_PLUGIN_URL; ?>lib/assets/img/ipbwi-logo.png" /></div>
	<div id="ipbwi4wp_thankyou">
		<h2><?php _e('Community Comments', 'ipbwi4wp_community_comments'); ?></h2>
		<p><?php _e('Replace WordPress comments with IPS community comments.', 'ipbwi4wp_community_comments'); ?></p>
	</div>
	<form action="#" method="post" id="ipbwi4wp_global_settings">
		<table width="100%">
		<?php
			echo '
			<div class="ipbwi4wp_setting ipbwi4wp_setting_'.$this->settings_default['basic']['IPB_DEFAULT_FORUM']['type'].'">
				<div class="ipbwi4wp_setting_name">'.$this->settings_default['basic']['IPB_DEFAULT_FORUM']['name'].'</div>
				<div class="ipbwi4wp_setting_desc">'.$this->settings_default['basic']['IPB_DEFAULT_FORUM']['desc'].'</div>
				<div class="ipbwi4wp_setting_value"><select name="basic[IPB_DEFAULT_FORUM][value]"><option value="">'.__('Choose a forum...', 'ipbwi4wp_community_comments').'</option>'.$this->ipbwi4wp_community_comments->settings->get_forums_hierarchically_dropdown(false,$this->settings['basic']['IPB_DEFAULT_FORUM']['value']).'</select></div>
			</div>';

			echo '
			<div class="ipbwi4wp_setting ipbwi4wp_setting_'.$this->settings_default['basic']['IPB_DEFAULT_USER']['type'].'">
				<div class="ipbwi4wp_setting_name">'.$this->settings_default['basic']['IPB_DEFAULT_USER']['name'].'</div>
				<div class="ipbwi4wp_setting_desc">'.$this->settings_default['basic']['IPB_DEFAULT_USER']['desc'].'</div>
				<div class="ipbwi4wp_setting_value"><input type="text" name="basic[IPB_DEFAULT_USER][value]" value="'.$this->settings['basic']['IPB_DEFAULT_USER']['value'].'" /></div>
			</div>';
			
			echo '
			<div class="ipbwi4wp_setting ipbwi4wp_setting_'.$this->settings_default['basic']['IPB_HIDE_NEW_POSTS']['type'].'">
				<div class="ipbwi4wp_setting_name">'.$this->settings_default['basic']['IPB_HIDE_NEW_POSTS']['name'].'</div>
				<div class="ipbwi4wp_setting_desc">'.$this->settings_default['basic']['IPB_HIDE_NEW_POSTS']['desc'].'</div>
				<div class="ipbwi4wp_setting_value">
					<select name="basic[IPB_HIDE_NEW_POSTS][value]">
						<option value="0">'.__('No Moderation required', 'ipbwi4wp_community_comments').'</option>
						<option value="1"'.($this->settings['basic']['IPB_HIDE_NEW_POSTS']['value'] == 1 ? ' selected="selected"' : '').'>'.__('Hide Guest posts', 'ipbwi4wp_community_comments').'</option>
						<option value="2"'.($this->settings['basic']['IPB_HIDE_NEW_POSTS']['value'] == 2 ? ' selected="selected"' : '').'>'.__('Hide Guest and Member posts', 'ipbwi4wp_community_comments').'</option>
					</select>
				</div>
			</div>';

			echo '
			<div class="ipbwi4wp_setting ipbwi4wp_setting_'.$this->settings_default['basic']['IPB_SHOW_LEGACY_WP_POSTS']['type'].'">
				<div class="ipbwi4wp_setting_name">'.$this->settings_default['basic']['IPB_SHOW_LEGACY_WP_POSTS']['name'].'</div>
				<div class="ipbwi4wp_setting_desc">'.$this->settings_default['basic']['IPB_SHOW_LEGACY_WP_POSTS']['desc'].'</div>
				<div class="ipbwi4wp_setting_value"><input type="checkbox" name="basic[IPB_SHOW_LEGACY_WP_POSTS][value]" value="'.$this->settings['basic']['IPB_SHOW_LEGACY_WP_POSTS']['value'].'"'.((isset($this->settings['basic']['IPB_SHOW_LEGACY_WP_POSTS']['value']) && $this->settings['basic']['IPB_SHOW_LEGACY_WP_POSTS']['value'] == 1) ? ' checked="checked"' : '').' /></div>
			</div>';
		?>
		</table>
		<input type="hidden" name="ipbwi4wp_community_comments_settings" value="1" />
		<div style="clear:both;"><input type="submit" value="<?php echo _e('Save Settings', 'ipbwi4wp_community_comments'); ?>" /></div>
	</form>
</div>
<?php
	}
?>