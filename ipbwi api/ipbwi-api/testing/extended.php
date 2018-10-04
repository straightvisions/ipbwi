<?php

	require_once('../ipbwi.php');
	echo '<pre>';
	
	// basic ipbwi information
	#try{var_dump($ipbwi->extended->hello());}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
	
	// groups
	
	/* // list all groups
	try{var_dump($ipbwi->extended->groups());}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
	*/
	
	/* // get group info
	try{var_dump($ipbwi->extended->groups(1));}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
	*/

	/* // create new group
	try{
		var_dump($ipbwi->extended->groups(false,array('name' => 'new', 'data' => array('g_view_board' => 1, 'g_mem_info' => 0))));
	}catch(Throwable $t){
		echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage();
	}
	*/
	
	/* // update group
	try{
		var_dump($ipbwi->extended->groups(114,array('name' => 'wubwub', 'data' => array('g_view_board' => 1, 'g_mem_info' => 0))));
	}catch(Throwable $t){
		echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage();
	}
	*/

	/* // delete group
	#try{var_dump($ipbwi->extended->groups(116,NULL,1));}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
	*/
	
	// forums
	
	/* // list all forums
	try{var_dump($ipbwi->extended->forums());}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
	*/
	
	/* // get forum info
	try{var_dump($ipbwi->extended->forums(148));}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
	*/
	
	/* // create new forum
	try{
		var_dump($ipbwi->extended->forums(false,array('name' => 'new', 'data' => array('permission_showtopic' => 1, 'forum_allow_rating' => 1))));
	}catch(Throwable $t){
		echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage();
	}
	*/
	
	/* // update forum
	try{
		var_dump($ipbwi->extended->forums(294,array('name' => 'wubwub', 'data' => array('permission_showtopic' => 1, 'forum_allow_rating' => 0))));
	}catch(Throwable $t){
		echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage();
	}
	*/
	
	/* // delete forum
	#try{var_dump($ipbwi->extended->forums(293,NULL,1));}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
	*/
	
	/* // list all reports
	try{var_dump($ipbwi->extended->reports());}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
	*/
	
	/* // list reports from one Post only
	try{var_dump($ipbwi->extended->reports(NULL,NULL,NULL,array('section' => 'IPS\forums\Topic\Post', 'section_id' => 398154)));}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
	*/
	
	/* // get report info
	try{var_dump($ipbwi->extended->reports(10));}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
	*/
	
	/* // create new report
	try{
		var_dump($ipbwi->extended->reports(false,array('content_type' => 'post', 'content_id' => 398154, 'report_message' => 'this content is reportworthy', 'member_id' => 1)));
	}catch(Throwable $t){
		echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage();
	}*/
	
	/* // update report
	try{
		var_dump($ipbwi->extended->reports(10,array('name' => 'wubwub', 'data' => array('g_view_board' => 1, 'g_mem_info' => 0))));
	}catch(Throwable $t){
		echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage();
	}*/
	
	
	/* // delete report
	#try{var_dump($ipbwi->extended->reports(10,NULL,1));}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
	*/
	
	/* // get all pages databases
	try{var_dump($ipbwi->extended->pages_databases());}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
	*/
	
	/* // get records from databases
	try{
		foreach($ipbwi->extended->pages_databases() as $database){
			var_dump($ipbwi->extended->pages_records($database['database_id']));
		}
	}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
	*/
	
	// get the image from a record
	//try{var_dump($ipbwi->extended->pages_record_image(1,1));}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
	
	/* // get menu
	try{var_dump($ipbwi->extended->menu());}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
	*/
	
	/* // reputation - member specific
	try{var_dump($ipbwi->extended->members_reputationPoints(1));}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
	try{var_dump($ipbwi->extended->members_reputationLastDayWon(1));}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
	try{var_dump($ipbwi->extended->members_reputationDescription(1));}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
	try{var_dump($ipbwi->extended->members_reputationImage(1));}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
	*/
	
	/* // reputation - post specific
	$post_id = 385672;
	$member_id = 33818;
	
	try{var_dump($ipbwi->extended->posts_canGiveReputation($post_id, array('type' => 1, 'user_id' => $member_id)));}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
	try{var_dump($ipbwi->extended->posts_reputation($post_id));}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
	try{var_dump($ipbwi->extended->posts_reputation($post_id, array('type' => 1, 'user_id' => $member_id)));}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
	try{var_dump($ipbwi->extended->posts_reputationGiven($post_id, array('user_id' => $member_id)));}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
	*/
	
	echo '</pre>';

?>