<?php
	if(current_user_can('activate_plugins')){
		$settings_default = $this->ipbwi4wp->settings->get_settings_default();
?>
<div id="ipbwi4wp_settings">
	<div id="ipbwi4wp_logo"><img src="<?php echo IPBWI4WP_PLUGIN_URL; ?>lib/assets/img/ipbwi-logo.png" /></div>
	<div id="ipbwi4wp_thankyou">
		<h2><?php _e('Unleash the full power!', 'ipbwi4wp'); ?></h2>
		<p><?php _e('We provide additional feature extensions on a modular level.', 'ipbwi4wp'); ?></p>
		<div id="ipbwi4wp_extensions">
			<div><?php echo (is_plugin_active('ipbwi4wp_user_import/ipbwi4wp_user_import.php') ? '' : '<a href="https://straightvisions.com/product/ipbwi4wp-ips-user-to-wordpress-import/" target="_blank" title="More Information" class="not_installed">'); ?><img src="<?php echo IPBWI4WP_PLUGIN_URL; ?>lib/assets/img/extension_user_import.png" width="200" height="200" /><?php echo (is_plugin_active('ipbwi4wp_user_import/ipbwi4wp_user_import.php') ? '' : '</a>'); ?></div>
			<div><?php echo (is_plugin_active('ipbwi4wp_pages_import/ipbwi4wp_pages_import.php') ? '' : '<a href="https://straightvisions.com/product/ipbwi4wp-ip-pages-to-wordpress-import/" target="_blank" title="More Information" class="not_installed">'); ?><img src="<?php echo IPBWI4WP_PLUGIN_URL; ?>lib/assets/img/extension_pages_import.png" width="200" height="200" /><?php echo (is_plugin_active('ipbwi4wp_pages_import/ipbwi4wp_pages_import.php') ? '' : '</a>'); ?></div>
			<div><?php echo (is_plugin_active('ipbwi4wp_digimember/ipbwi4wp_digimember.php') ? '' : '<a href="https://straightvisions.com/product/ipbwi4wp-digimember/" target="_blank" title="More Information" class="not_installed">'); ?><img src="<?php echo IPBWI4WP_PLUGIN_URL; ?>lib/assets/img/extension_digimember.png" width="200" height="200" /><?php echo (is_plugin_active('ipbwi4wp_digimember/ipbwi4wp_digimember.php') ? '' : '</a>'); ?></div>
		</div>
	</div>
</div>
<?php
	}
?>