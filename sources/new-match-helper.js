function showTableRows_menu () {
	var lastRow = jQuery(this).val();
	if ('otherSize' == lastRow) {
		jQuery('#xNewTeamSize').prop('disabled', false).focus();
		lastRow = showTableRows.currentRowCount;
	} else {
		jQuery('#xNewTeamSize').prop('disabled', 'disabled').val('');
	}
	showTableRows (lastRow);
}

function showTableRows_fld () {
	var lastRow = jQuery(this).val();
	showTableRows (lastRow);
}

function showTableRows (n) {
	jQuery('#new-match-tables tr').hide();
	jQuery('#new-match-tables').find('tr:lt('+n+')').show();
	showTableRows.currentRowCount = parseInt (n);
	
	jQuery('.TCH_alwaysShow').show();       // Some rows should always be visible
}

jQuery(document).ready(function () {
	jQuery('#xTables').change(showTableRows_menu);
	jQuery('#xNewTeamSize').change(showTableRows_fld);
	
	jQuery('#xTables').change();            // Trigger 'change' to display the right # of rows on page load
});
