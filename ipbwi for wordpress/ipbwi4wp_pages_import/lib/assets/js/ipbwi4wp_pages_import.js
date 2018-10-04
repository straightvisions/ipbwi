function ipbwi4wp_pages_import(){
	var data = {
		'action': 'ipbwi4wp_pages_import',
		'page': ipbwi4wp_pages_import_vars.page
	};
	
	jQuery.post(ajaxurl, data, function(response){
		$data				= jQuery.parseJSON(response);
		
		jQuery('#ipbwi4wp_pages_import_status_progress_log').append($data.results);
		jQuery('#ipbwi4wp_pages_import_htaccess').append($data.htaccess);
		jQuery('#ipbwi4wp_pages_import_nginx').append($data.nginx);
		
		var percentage		= 100/ipbwi4wp_pages_import_vars.pages_total*ipbwi4wp_pages_import_vars.page;
		
		jQuery('#ipbwi4wp_pages_import_status_progress_bar').progressbar({
			value: percentage
		});
		
		jQuery('#ipbwi4wp_pages_import_status_progress_text .left').html((Math.round(percentage * 100) / 100)+'%');
		jQuery('#ipbwi4wp_pages_import_status_progress_text .center').html(ipbwi4wp_pages_import_vars.page+'/'+ipbwi4wp_pages_import_vars.pages_total+' Cycles');
		jQuery('#ipbwi4wp_pages_import_status_progress_text .right').html(jQuery('#ipbwi4wp_pages_import_status_progress_log').children('div').length+'/'+ipbwi4wp_pages_import_vars.records_total+' Records');
		
		if(parseInt(ipbwi4wp_pages_import_vars.page) < parseInt(ipbwi4wp_pages_import_vars.pages_total)){
			ipbwi4wp_pages_import_vars.page = parseInt(ipbwi4wp_pages_import_vars.page)+1;
			ipbwi4wp_pages_import();
		}
	}).fail(function() {
		ipbwi4wp_pages_import();
	});
}

jQuery(document).ready(function(){
	// progressbar
	jQuery('#ipbwi4wp_pages_import_status_progress_bar').progressbar({
		value: 0
	});
	
	jQuery('#ipbwi4wp_pages_import_start').one('click', function(){
		jQuery('#ipbwi4wp_pages_import_status_progress_log').append('<h2>Import Log</h2>');
		jQuery('#ipbwi4wp_pages_import_start, #ipbwi4wp_pages_import_continue').attr('disabled', 'disabled');
		
		ipbwi4wp_pages_import_vars.page = 1;
		
		jQuery('#ipbwi4wp_pages_import_htaccess').empty();
		jQuery('#ipbwi4wp_pages_import_nginx').empty();
		
		// ajax
		ipbwi4wp_pages_import();
	});
	
	jQuery('#ipbwi4wp_pages_import_continue').one('click', function(){
		jQuery('#ipbwi4wp_pages_import_status_progress_log').append('<h2>Import Log</h2>');
		jQuery('#ipbwi4wp_pages_import_start, #ipbwi4wp_pages_import_continue').attr('disabled', 'disabled');
		
		// ajax
		ipbwi4wp_pages_import();
	});
	
	
});