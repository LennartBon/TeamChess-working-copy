<div class="teamchess-table" id="teamchess-single-match-table">

<!-- BEGIN HEADER -->
<p class="teamchess-header">
	{num}.&nbsp;Vallentuna ({team})&nbsp;&ndash;&nbsp;{opponent}, {league}, {date}
</p>
<!-- END HEADER -->

<!-- BEGIN LINKS -->
<p class="txt" align="left"{style}>
	<a href="{list_url}&listtype=match&id={prev}">Föregående match</a>
	&nbsp;|&nbsp;
	<a href="{list_url}&listtype=match&id={next}">Nästa match</a>
	&nbsp;|&nbsp;
	<a href="{list_url}&listtype=matchlist">Alla matcher</a>
</p>
<!-- END LINKS -->

<table>
<tr class="teamchess-tablehead">
	<th width="10%" class="t_center">Bord</th>
	<th width="60%" class="t_left">Spelare</th>
	<th width="10%" class="t_center">Ranking</th>
	<th width="20%" class="t_center">Resultat</th>
</tr>

<!-- BEGIN TABLE -->
<tr class="teamchess-tablerow-{odd_even}">
	<td class="t_center">{table}</td>
	<td class="t_left"><a href="{list_url}&listtype=player&id={id}">{player}</a></td>
	<td class="t_center">{rating}</td>
	<td class="t_center">{result}</td>
</tr>
<!-- END TABLE   -->

<!-- BEGIN SUMMARY -->
<tr>
	<td>&nbsp;</td>
	<td class="t_right">Snitt</td>
	<td class="t_center">{rating_average}</td>
	<td class="t_center">{team_result}</td>
</tr>
<!-- END SUMMARY   -->

</table>
</div>
