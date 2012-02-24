<div class="teamchess-table" id="teamchess-player-table">

<!-- BEGIN HEADER -->
<p class="teamchess-header">
	{name}s lagmatcher
</p>
<p>
	Totalt {p} poäng på {m} matcher = {percent}%
</p>
<!-- END HEADER -->

<table>
<tr class="teamchess-tablehead">
	<th width="10%" class="t_center"> </th>
	<th width="20%" class="t_left">Datum</th>
	<th width="32%" class="t_left">Mot</th>
	<th width="13%" class="t_left">Serie</th>
	<th width="10%" class="t_center">Bord</th>
	<th width="15%" class="t_center">Resultat</th>
</tr>

<!-- BEGIN MATCH -->
<tr class="teamchess-tablerow-{odd_even}">
	<td class="t_center">
		<a href="{list_url}&listtype=match&id={id}">{id}</a>
	</td>
	<td class="t_left">{date}</td>
	<td class="t_left">{opponent}</td>
	<td class="t_left">{league}</td>
	<td class="t_center">{team}:{table}</td>
	<td class="t_center">{result}</td>
</tr>
<!-- END MATCH   -->

</table>

<!-- BEGIN SUMMARY -->
<p>
	Totalt {p} poäng på {m} matcher = {percent}%
</p>
<!-- END SUMMARY   -->

</div>