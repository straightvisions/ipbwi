<?php
	if(!function_exists('wp_hash_password')){
		function wp_hash_password($password){
			if($GLOBALS['ipbwi4wp']->sso_by_ipb->request === NULL){
				$GLOBALS['ipbwi4wp']->sso_by_wp->set_new_password($password);
			}
			global $wp_hasher;
			if(empty($wp_hasher)){
				require_once(ABSPATH.WPINC.'/class-phpass.php');
				// By default, use the portable hash from phpass
				$wp_hasher = new PasswordHash(8, true);
			}
			return $wp_hasher->HashPassword(trim($password));
		}
	}else{
		$reflFunc = new ReflectionFunction('wp_hash_password');
		//error_log($reflFunc->getFileName().':'.$reflFunc->getStartLine(), 3, IPBWI4WP_DIR.'log.txt');
		// @todo: add error notice
	}
?>