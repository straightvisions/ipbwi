<?php
	if(current_user_can('activate_plugins')){
		$settings_default = $this->ipbwi4wp_pages_import->settings->get_settings_default();
?>
<div id="ipbwi4wp_settings">
	<div id="ipbwi4wp_logo"><img src="<?php echo IPBWI4WP_PLUGIN_URL; ?>lib/assets/img/ipbwi-logo.png" /></div>
	<div id="ipbwi4wp_thankyou">
		<h2><?php _e('Pages Import', 'ipbwi4wp_pages_import'); ?></h2>
		<p><?php _e('Just import all your IP.pages articles to your WordPress site.', 'ipbwi4wp_pages_import'); ?></p>
	</div>
	<h2>Status</h2>
	<?php
		try{
			$databases										= $this->ipbwi4wp_pages_import->ipbwi4wp->pages->get_databases();
			$databases_count								= 0;
			if(is_array($databases) && count($databases) > 0 && isset($databases[0]['database_id'])){
				foreach($databases as $database){
					$r											= $this->ipbwi4wp_pages_import->ipbwi4wp->pages->get_records($database['database_id']);
					if(isset($r['totalResults'])){
						$records[$database['database_id']]		= $r;
						$databases_count						= $databases_count+1;
					}
					
					if(isset($this->ipbwi4wp_pages_import->settings->settings['import']['IPB_PAGES_DATABASE']['value']) && intval($this->ipbwi4wp_pages_import->settings->settings['import']['IPB_PAGES_DATABASE']['value']) > 0 && $database['database_id'] == $this->ipbwi4wp_pages_import->settings->settings['import']['IPB_PAGES_DATABASE']['value']){
						$database_selected						= $database;
					}
				}
			}
			echo '<p><strong>'.__('Total Databases:', 'ipbwi4wp').'</strong> '.$databases_count.'</p>';
			
			if(isset($this->ipbwi4wp_pages_import->settings->settings['import']['IPB_PAGES_DATABASE']['value']) && intval($this->ipbwi4wp_pages_import->settings->settings['import']['IPB_PAGES_DATABASE']['value']) > 0){
				echo '<p><strong>'.__('Database selected:', 'ipbwi4wp').'</strong> '.$database_selected['database_key'].'</p>';
				echo '<p><strong>'.__('Total Records:', 'ipbwi4wp').'</strong> '.$records[$database_selected['database_id']]['totalResults'].'</p>';
				echo '<p><strong>'.__('Records per import cycle:', 'ipbwi4wp').'</strong> '.$records[$database_selected['database_id']]['perPage'].'</p>';
				echo '<p><strong>'.__('Total cycles:', 'ipbwi4wp').'</strong> '.$records[$database_selected['database_id']]['totalPages'].'</p>';
			}
			
		}catch(Throwable $t){
			echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage();
		}
	?>
	<form action="#" method="post" id="ipbwi4wp_global_settings">
		<h2><?php _e('Import Settings', 'ipbwi4wp_pages_import'); ?></h2>
		<?php
			echo '
			<div class="ipbwi4wp_setting ipbwi4wp_setting_'.$this->settings_default['import']['IPB_PAGES_DATABASE']['type'].'">
				<div class="ipbwi4wp_setting_name">'.$this->settings_default['import']['IPB_PAGES_DATABASE']['name'].'</div>
				<div class="ipbwi4wp_setting_desc">'.$this->settings_default['import']['IPB_PAGES_DATABASE']['desc'].'</div>
				<div class="ipbwi4wp_setting_value"><select name="import[IPB_PAGES_DATABASE][value]">';
				foreach($databases as $data){
					if(isset($records[$data['database_id']]['totalResults'])){
						echo '<option value="">select Database...</option>';
						echo '<option value="'.$data['database_id'].'" '.((isset($this->ipbwi4wp_pages_import->settings->settings['import']['IPB_PAGES_DATABASE']['value']) && intval($this->ipbwi4wp_pages_import->settings->settings['import']['IPB_PAGES_DATABASE']['value']) > 0 && $this->ipbwi4wp_pages_import->settings->settings['import']['IPB_PAGES_DATABASE']['value'] == $data['database_id']) ? 'selected="selected"' : '').'>'.$data['database_key'].' ('.$records[$data['database_id']]['totalResults'].' Records)</option>';
					}
				}
			echo '</select></div></div>';

			echo '
			<div class="ipbwi4wp_setting ipbwi4wp_setting_'.$this->settings_default['import']['IPB_PAGES_POST_TYPE']['type'].'">
				<div class="ipbwi4wp_setting_name">'.$this->settings_default['import']['IPB_PAGES_POST_TYPE']['name'].'</div>
				<div class="ipbwi4wp_setting_desc">'.$this->settings_default['import']['IPB_PAGES_POST_TYPE']['desc'].'</div>
				<div class="ipbwi4wp_setting_value"><select name="import[IPB_PAGES_POST_TYPE][value]">';
				foreach(get_post_types() as $data){
					echo '<option value="'.$data.'" '.((isset($this->ipbwi4wp_pages_import->settings->settings['import']['IPB_PAGES_POST_TYPE']['value']) && strlen($this->ipbwi4wp_pages_import->settings->settings['import']['IPB_PAGES_POST_TYPE']['value']) > 0 && $this->ipbwi4wp_pages_import->settings->settings['import']['IPB_PAGES_POST_TYPE']['value'] == $data) ? 'selected="selected"' : '').'>'.$data.'</option>';
				}
			echo '</select>
			<p>If post type "post" is set, categories from IP.pages will be created and associated with imported records within WordPress.</p>
			</div>
			</div>';
		?>
		<input type="hidden" name="ipbwi4wp_pages_import_settings" value="1" />
		<div style="clear:both;"><input type="submit" value="<?php echo _e('Save Settings', 'ipbwi4wp_pages_import'); ?>" /></div>
	</form>
<?php
	if(isset($this->ipbwi4wp_pages_import->settings->settings['import']['IPB_PAGES_DATABASE']['value']) && intval($this->ipbwi4wp_pages_import->settings->settings['import']['IPB_PAGES_DATABASE']['value']) > 0){
?>
	<h2><?php _e('Start Import', 'ipbwi4wp_pages_import'); ?></h2>
	<p><?php _e('Please do not close browser windows or tab until import has been finished.', 'ipbwi4wp_pages_import'); ?></p>
	<div style="clear:both;">
		<input type="submit" value="<?php echo _e('Start Import', 'ipbwi4wp_pages_import'); ?>" id="ipbwi4wp_pages_import_start" />
		<?php if(get_transient('ipbwi4wp_pages_import_pages_completed') > 1 && get_transient('ipbwi4wp_pages_import_pages_completed') < $records[$database_selected['database_id']]['totalPages']){ ?>
		<input type="submit" value="<?php echo _e('Continue Import from Cycle', 'ipbwi4wp_pages_import'); ?> <?php echo get_transient('ipbwi4wp_pages_import_pages_completed'); ?>" id="ipbwi4wp_pages_import_continue" />
		<?php } ?>
	</div>
	<h2>301 redirects</h2>
	<p><?php _e('Your SEO department will thank us for these 301 redirect lists we are generating during import.', 'ipbwi4wp_pages_import'); ?></p>
	<h3>.htaccess</h3>
	<pre><code class="apacheconf" id="ipbwi4wp_pages_import_htaccess"><?php echo get_transient('ipbwi4wp_pages_import_pages_301_htaccess'); ?></code></pre>
	<h3>nginx</h3>
	<pre><code class="apacheconf" id="ipbwi4wp_pages_import_nginx"><?php echo get_transient('ipbwi4wp_pages_import_pages_301_nginx'); ?></code></pre>
	<div id="ipbwi4wp_pages_import_status">
		<h2>Import Status</h2>
		<div id="ipbwi4wp_pages_import_status_progress_bar"></div>
		<div id="ipbwi4wp_pages_import_status_progress_text"><div class="left"></div><div class="center"></div><div class="right"></div></div>
		<div id="ipbwi4wp_pages_import_status_progress_log"></div>
	</div>
<?php
	}
?>
</div>
<?php
	}
?>