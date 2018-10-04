<?php
	/**
	 * @author			Matthias Reuter
	 * @package			hooks
	 * @copyright		2007-2016 Matthias Reuter
	 * @link			http://ipbwi.com/
	 * @since			4.0
	 * @license			This is no free software. See license.txt or https://ipbwi.com
	 */
	class ipbwi4wp_user_import_ajax extends ipbwi4wp_user_import{
		public $ipbwi4wp_user_import			= NULL;
		
		/**
		 * @desc			Loads other classes of package
		 * @author			Matthias Reuter
		 * @since			4.0
		 * @ignore
		 */
		public function __construct($ipbwi4wp_user_import){
			$this->ipbwi4wp_user_import				= isset($ipbwi4wp_user_import->ipbwi4wp_user_import) ? $ipbwi4wp_user_import->ipbwi4wp_user_import : $ipbwi4wp_user_import; // loads common classes
		}
		/**
		 * @desc			initialize ajax
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			4.0
		 */
		public function init(){
			if(current_user_can('activate_plugins')){
				$this->ipbwi4wp_user_import->ipbwi4wp->sso_by_ipb->request = true;

				$result			= '';
				$members		= $this->ipbwi4wp_user_import->ipbwi4wp->member->ipb_list(false,false,$_POST['page']);
				foreach($members['results'] as $member){
					$role			= $this->ipbwi4wp_user_import->settings->settings['import']['IPB_GROUPS_MAPPING']['value'][$member['primaryGroup']['id']];
					$status			= $this->ipbwi4wp_user_import->ipbwi4wp->sso_by_ipb->register($member['id'],$member['name'],$member['email'],false,false,$role);
					$result			.= '<div>'.__('Member', 'ipbwi4wp_user_import').' <strong>'.$member['name'].'</strong> (#'.$member['id'].'): ';
					if($status === true){
						$result			.= __('already exists in WordPress.', 'ipbwi4wp_user_import');
					}elseif(is_wp_error($status)){
						$result			.= __('error occured:', 'ipbwi4wp_user_import').' <ul>';
						foreach($status->get_error_messages() as $error){
							$result			.= '<li>'.$error.'</li>';
						}
						$result			.= '</ul>';
					}else{
						$result			.= __('successfully created.', 'ipbwi4wp_user_import');
					}
					$result			.= '</div>';
				}
				echo $result;
				
				if($members['totalPages'] >= $_POST['page']){
					set_transient('ipbwi4wp_user_import_pages_completed', intval($_POST['page'])+1);
				}else{
					set_transient('ipbwi4wp_user_import_pages_completed', 1);
				}
			}else{
				__('You are not allowed to do this.', 'ipbwi4wp_user_import');
			}
			wp_die();
		}
	}
?>