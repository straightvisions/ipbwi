<?php
	/*
	Plugin Name: IPBWI for WordPress v4
	Plugin URI: https://ipbwi.com
	Description: Single Sign On for WordPress and IP.board
	Version: 4.1.3
	Author: Matthias Reuter
	Author URI: https://straightvisions.com
	*/

	define('IPBWI4WP_DIR',WP_PLUGIN_DIR.'/'.dirname(plugin_basename(__FILE__)).'/');
	define('IPBWI4WP_PLUGIN_BASENAME',plugin_basename(__FILE__));
	define('IPBWI4WP_PLUGIN_URL',plugins_url( '' , __FILE__ ).'/');
	define('IPBWI4WP_VERSION', 4013);
	define('IPBWI4WP_PHP_MIN', 7);

	class ipbwi4wp{
		public $ipbwi				= NULL;
		/**
		 * @desc			Load's requested libraries dynamicly
		 * @param	string	$name library-name
		 * @return			class object of the requested library
		 * @author			Matthias Reuter
		 * @since			4.0
		 * @ignore
		 */
		public function __get($name){
			if(file_exists(IPBWI4WP_DIR.'lib/modules/'.$name.'.php')){
				require_once(IPBWI4WP_DIR.'lib/modules/'.$name.'.php');
				$classname			= 'ipbwi4wp_'.$name;
				$this->$name		= new $classname($this);
				return $this->$name;
			}else{
				throw new Exception('Class '.$name.' could not be loaded (tried to load class-file '.IPBWI4WP_DIR.'lib/'.$name.'.php'.')');
			}
		}
		/**
		 * @desc			initialize plugin
		 * @author			Matthias Reuter
		 * @since			4.0
		 * @ignore
		 */
		public function __construct(){
			// language settings
			load_textdomain('ipbwi4wp', WP_LANG_DIR.'/plugins/ipbwi4wp-'.apply_filters('plugin_locale', get_locale(), 'ipbwi4wp').'.mo');
			load_plugin_textdomain('ipbwi4wp', false, dirname(plugin_basename(__FILE__)).'/lib/assets/lang/');
			
			if(!self::wp_compatible_version()){
				return;
			}
			if(!self::php_compatible_version()){
				return;
			}
			
			add_action('plugins_loaded', array($this, 'init'));
		}
		public function init(){
			$this->settings->init();							// load settings
			
			require_once(IPBWI4WP_DIR.'lib/api/ipbwi.php');		// load API
			$this->ipbwi			= $ipbwi;
			
			if($this->settings->settings['advanced']['NO_URL_REWRITE']['value'] == 1){
				$this->ipbwi->core->url_rewrite						= false;
				$this->ipbwi->extended->url_rewrite					= false;
			}
			if($this->settings->settings['advanced']['NO_AUTH_HEADER']['value'] == 1){
				$this->ipbwi->core->key_in_url						= true;
				$this->ipbwi->extended->key_in_url					= true;
			}
			
			do_action('ipbwi_sso_loaded');
			
			$this->hooks->init();								// load hooks
			
			do_action('ipbwi_sso_hooks_loaded');
		}
		public function status(){
			try{
				if(isset($this->settings->settings['ipbwi4wp_settings']) && $this->settings->settings['ipbwi4wp_settings'] == 1){
					return true;
				}else{
					return false;
				}
			}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); return false; }
			catch(Exception $e){ echo 'Type Error, line '.$e->getLine().': ' .$e->getMessage(); return false; }
		}
		static function activation_check(){
			if(!self::wp_compatible_version()){
				deactivate_plugins(IPBWI4WP_PLUGIN_BASENAME);
				wp_die(__( 'IPBWI for WordPress v4 requires WordPress 4.0 or higher!', 'ipbwi4wp'));
			}
			if(!self::php_compatible_version()){
				deactivate_plugins(IPBWI4WP_PLUGIN_BASENAME);
				wp_die(__( 'This version of IPBWI for WordPress v4 requires PHP '.IPBWI4WP_PHP_MIN.' or higher! You may upgrade PHP on your server or choose the PHP 5 compatible release of IPBWI for WordPress.', 'ipbwi4wp'));
			}
		}
		static function wp_compatible_version(){
			return version_compare($GLOBALS['wp_version'], '4.4', '>');
		}
		static function php_compatible_version(){
			return version_compare(phpversion(), IPBWI4WP_PHP_MIN, '>');
		}
	}

	$GLOBALS['ipbwi4wp']			= new ipbwi4wp();
	
	if(file_exists(IPBWI4WP_DIR.'lib/pluggable.php')){
		require_once(IPBWI4WP_DIR.'lib/pluggable.php');
	}

	register_activation_hook(__FILE__, array('ipbwi4wp', 'activation_check'));
	
?>