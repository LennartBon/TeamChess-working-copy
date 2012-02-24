function showTableRows () {
	var lastRow = jQuery(this).val();
	if ('otherSize' == lastRow) {
		jQuery('#xNewTeamSize').prop('disabled', false);
		lastRow = showTableRows.currentRowCount;
	} else {
		jQuery('#xNewTeamSize').val('');
		jQuery('#xNewTeamSize').prop('disabled', 'disabled');
	}
	jQuery('#new-match-tables tr').hide();
	jQuery('#new-match-tables').find('tr:lt('+lastRow+')').show();
	showTableRows.currentRowCount = parseInt (lastRow);
}

jQuery(document).ready(function () {
	jQuery('#xTables, #xNewTeamSize').change(showTableRows);
	jQuery('#xTables').change;
});
