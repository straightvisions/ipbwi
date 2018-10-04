<?php
	declare(strict_types=1);
	
	/**
	 * @desc			This file loads all IPBWI functions. Include this file to your
	 * 					php-scripts and load the ipbwi-class to use the functions.
	 * @author			Matthias Reuter
	 * @package			IPBWI
	 * @copyright		2007-2016 Matthias Reuter
	 * @link			http://ipbwi.com
	 * @since			4.0
	 * @license			This is no free software. See license.txt or https://ipbwi.com
	 */
	 
	// load config file
	require_once('config.php');

	// check if PHP version is 7 or higher
	if(version_compare(PHP_VERSION,'7.0.0','<')){
		throw new Exception('<p>ERROR: You need PHP 7 or higher to use IPBWI. Your current version is '.PHP_VERSION.'</p>');
	}
	class ipbwi{
		const 				VERSION			= '4.1.8';
		const 				TITLE			= 'IPBWI';
		const 				PROJECT_LEADER	= 'Matthias Reuter';
		const 				WEBSITE			= 'http://ipbwi.com/';
		public				$path			= false;
		
		/**
		 * @desc			Load's requested libraries dynamicly
		 * @param	string	$name library-name
		 * @return			class object of the requested library
		 * @author			Matthias Reuter
		 * @since			2.0
		 * @ignore
		 */
		public function __get(string $name): ipbwi{
			if(file_exists($this->path.'lib/'.$name.'.php')){
				require_once($this->path.'lib/'.$name.'.php');
				$classname					= 'ipbwi_'.$name;
				$this->$name				= new $classname($this);
				return $this->$name;
			}else{
				throw new Exception('Class '.$name.' could not be loaded (tried to load class-file '.$this->path.'lib/'.$name.'.php'.')');
			}
		}
		public function __construct(){
			$this->path						= dirname(__FILE__).'/';
			
			if(ipbwi_ACTIVATE_HOOKS && isset($_REQUEST['do'])){
				$this->hooks->init();
			}
		}
	}
	
	// start class
	if(!isset($ipbwi) || !is_object($ipbwi)){
		$ipbwi								= new ipbwi();
	}else{
		throw new Exception('<p>Error: You have to include and load IPBWI once only.</p>');
	}
?>