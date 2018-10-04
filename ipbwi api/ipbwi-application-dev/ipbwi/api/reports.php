<?php
/**
 * @brief		IPBWI reports API
 * @author		<a href='https://ipbwi.com'>IPBWI</a>
 * @copyright	(c) 2016 Matthias Reuter
 * @license		all rights reserved
 * @package		IPS Community Suite
 * @subpackage	IPBWI
 * @since		23 Mar 2016
 * @version		4.0.3
 */

namespace IPS\ipbwi\api;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * @brief	IPBWI reports API
 */
class _reports extends \IPS\Api\Controller
{
	protected $ipbwiValidClasses					= array(
		'post'				=> 'IPS\forums\Topic\Post',
		'message'			=> 'IPS\core\Messenger\Message',
		'status'			=> 'IPS\core\Statuses\Status'
	);
	
	/**
	 * GET /ipbwi/reports
	 * Get list of reports
	 *
	 * @apiparam		string		$section			either forum, topic, post or a custom value
	 * @apiparam		int			$section_id			either forum id, topic id or post id
	 * @apiparam		string		$status				either new, review, complete or a custom value
	 * @return			array
	 * @apiresponse		object		$index				report index array
	 * @apiresponse		object		$reports			report event array
	 * @apiresponse		object		$comments			report index array
	 */
	public function GETindex()
	{
		$output										= array();
		
		if(\IPS\Request::i()->section){
			$section								= array('class=?', (string)\IPS\Request::i()->section);
			$section_id								= array('content_id=?', intval(\IPS\Request::i()->section_id));
		}else{
			$section								= false;
		}
		
		if(\IPS\Request::i()->status == 'new'){
			$status									= array('status=?',1);
		}elseif(\IPS\Request::i()->status == 'review'){
			$status									= array('status=?',2);
		}elseif(\IPS\Request::i()->status == 'complete'){
			$status									= array('status=?',3);
		}elseif(\IPS\Request::i()->status){
			$status									= array('status=?',intval(\IPS\Request::i()->status));
		}else{
			$status									= false;
		}
		
		if($section && $status){
			$where									= array($section,$section_id,$status);
		}elseif($section){
			$where									= array($section,$section_id);
		}elseif($status){
			$where									= $status;
		}else{
			$where									= false;
		}

		// Grab the index
		$index										= iterator_to_array( \IPS\Db::i()->select( '*', 'core_rc_index', $where));
		$output['index']							= $index[0];
		// get reports, too
		$output['reports']							= iterator_to_array( \IPS\Db::i()->select( '*', 'core_rc_reports', array( 'rid=?', $index[0]['id'] ), 'date_reported' ) );
		// and comments
		$output['comments']							= iterator_to_array( \IPS\Db::i()->select( '*', 'core_rc_comments', array( 'rid=?', $index[0]['id'] ), 'comment_date' ) );

		return new \IPS\Api\Response(200, $output);
	}
	
	/**
	 * GET /ipbwi/reports/{id}
	 * Get information about a specific report
	 *
	 * @reqapiparam		int			$id					ID Number
	 * @throws			1C292/2		INVALID_ID			The report ID does not exist
	 * @return			array
	 * @apiresponse		object		$index				report index array
	 * @apiresponse		object		$reports			report event array
	 * @apiresponse		object		$comments			report index array
	 */
	public function GETitem( $id )
	{
		try
		{
			$index									= \IPS\Db::i()->select("*",'core_rc_index',array('core_rc_index.id=?',$id))->first();
			
			if ( !$index['id'] )
			{
				throw new \OutOfRangeException;
			}
			
			$output['index']						= $index;
			// get reports, too
			$output['reports']						= iterator_to_array( \IPS\Db::i()->select( '*', 'core_rc_reports', array( 'rid=?', $index['id'] ), 'date_reported' ) );
			// and comments
			$output['comments']						= iterator_to_array( \IPS\Db::i()->select( '*', 'core_rc_comments', array( 'rid=?', $index['id'] ), 'comment_date' ) );
			
			return new \IPS\Api\Response( 201, $output);
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C292/2', 404 );
		}
	}
	
	/**
	 * POST /ipbwi/reports
	 * Create a report
	 *
	 * @reqapiparam		string		content_type		class name including namespace (like "IPS\forums\Topic\Post") or supported shorties: post, message or status
	 * @reqapiparam		int			content_id			id of the content object
	 * @apiparam		string		report_message		Content of the report
	 * @apiparam		int			member_id			id of the reporting member
	 * @return			array
	 * @apiresponse		object		$index				report index array
	 * @apiresponse		object		$reports			report event array
	 * @apiresponse		object		$comments			report index array
	 */
	public function POSTindex()
	{
		$member_id = \IPS\Request::i()->member_id;
		\IPS\Member::$loggedInMember			= \IPS\Member::load($member_id);
		
		if($this->ipbwiValidClasses[\IPS\Request::i()->content_type]){
			$content_type_class					= $this->ipbwiValidClasses[\IPS\Request::i()->content_type];
		}else{
			$content_type_class					= \IPS\Request::i()->content_type;
		}
		
		$content								= $content_type_class::load(\IPS\Request::i()->content_id);
		
		try
		{
			$r									= $content->report(\IPS\Request::i()->report_message);
			$output['index']					= array(
				'id'							=> $r->id,
				'class'							=> $r->class,
				'content_id'					=> $r->content_id,
				'perm_id'						=> $r->perm_id,
				'status'						=> $r->status,
				'num_reports'					=> $r->num_reports,
				'num_comments'					=> $r->num_comments,
				'first_report_by'				=> $r->first_report_by,
				'first_report_date'				=> $r->first_report_date,
				'last_updated'					=> $r->last_updated,
				'author'						=> $r->author,
			);
			
			// get reports, too
			$output['reports']					= iterator_to_array( \IPS\Db::i()->select( '*', 'core_rc_reports', array( 'rid=?', $r->id ), 'date_reported' ) );
			// and comments
			$output['comments']					= iterator_to_array( \IPS\Db::i()->select( '*', 'core_rc_comments', array( 'rid=?', $r->id ), 'comment_date' ) );
			
			return new \IPS\Api\Response( 201, $output );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '2C292/7', 404 );
		}
	}
	
	/**
	 * POST /ipbwi/reports/{id}
	 * Edit a report
	 *
	 * @apiparam	string					class					Indicates the type of content that was reported
	 * @apiparam	int						content_id				The ID number of the content that was reported.
	 * @apiparam	int						perm_id					The ID number from the core_permission_index table which indicates who can view this report.
	 * @apiparam	int						status					1 = New report. 2 = Under Review. 3 = Complete.
	 * @apiparam	int						num_reports				Number of reports received.
	 * @apiparam	int						num_comments			Number of comments moderators have made on this report.
	 * @apiparam	int						first_report_by			The ID number of the member who submitted the first report.
	 * @apiparam	datetime				first_report_date		Unix timestamp of when the first report was submitted.
	 * @apiparam	datetime				last_updated			Unix timestamp of the last time a comment or report was made (for read/unread marking)
	 * @apiparam	int						author					The ID number of the user who submitted the reported content.
	 * @return			array
	 * @apiresponse		object		$index				report index array
	 * @apiresponse		object		$reports			report event array
	 * @apiresponse		object		$comments			report index array
	 */
	public function POSTitem( $id )
	{
		try
		{
			$report								= \IPS\core\Reports\Report::load($id);
			$index								= array(
													'id',
													'class',
													'content_id',
													'perm_id',
													'status',
													'num_reports',
													'num_comments',
													'first_report_by',
													'first_report_date',
													'last_updated',
													'author'
												);
			
			foreach($index as $field){
				$report->$field					= \IPS\Request::i()->$field;
			}
			
			$report->save();
			
			$output['index']					= \IPS\Db::i()->select("*",'core_rc_index',array('core_rc_index.id=?',$id))->first();
			// get reports, too
			$output['reports']					= iterator_to_array( \IPS\Db::i()->select( '*', 'core_rc_reports', array( 'rid=?', $id ), 'date_reported' ) );
			// and comments
			$output['comments']					= iterator_to_array( \IPS\Db::i()->select( '*', 'core_rc_comments', array( 'rid=?', $id ), 'comment_date' ) );
			
			return new \IPS\Api\Response( 200, $output );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '2C292/7', 404 );
		}
	}
	
	/**
	 * DELETE /ipbwi/reports/{id}
	 * Deletes a report
	 *
	 * @param		int		$id			ID Number
	 * @throws		1C292/3	INVALID_ID	The report ID does not exist
	 * @return	array
	 * @apiresponse	object	status	SUCCESS
	 */
	public function DELETEitem( $id )
	{
		try
		{
			$report = \IPS\Db::i()->select("*",'core_rc_index',array('core_rc_index.id=?',$id))->join('core_rc_reports','core_rc_reports.rid=core_rc_index.id')->first();
			
			if ( !$report['id'] )
			{
				throw new \OutOfRangeException;
			}
			\IPS\Db::i()->delete( 'core_rc_index', array( 'id=?', $id ) );
			\IPS\Db::i()->delete( 'core_rc_reports', array( 'rid=?', $id ) );
			\IPS\Db::i()->delete( 'core_rc_comments', array( 'rid=?', $id ) );
			
			return new \IPS\Api\Response( 200, array('status' => 'SUCCESS') );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '1C292/2', 404 );
		}
	}
}