<?php

	declare(strict_types=1);
	
	require_once('../ipbwi.php');
	echo '<pre>';
	
	/* // basic board information
	try{var_dump($ipbwi->core->hello());}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
	*/
	
	/* // get info about a specific member
	try{var_dump($ipbwi->core->members(1));}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
	*/
	
	/* // get members list
	try{var_dump($ipbwi->core->members());}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
	*/
	
	/* // trying to get a member who possibly not exists
	try{var_dump($ipbwi->core->members(123456789));}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
	*/
	
	/* // trying to get a member with wrong variable type
	try{var_dump($ipbwi->core->members('a'));}catch(Throwable $t){ echo 'Type Error, line '.$t->getLine().': ' .$t->getMessage(); }
	*/
	
	echo '</pre>';

?>