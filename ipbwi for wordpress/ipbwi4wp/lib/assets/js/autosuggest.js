jQuery(document).ready(function() {
	jQuery("input#REASSIGN_TO").suggest(ajaxurl + "?action=svsuggestusers", { delay: 500, minchars: 2 });
});