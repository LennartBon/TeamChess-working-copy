<div class="teamchess-table" id="teamchess-ratinglist-table">

<!-- BEGIN HEADER -->
<p>Ranking {date}</p>
<!-- END HEADER -->

<table>
<tr class="teamchess-tablehead">
	<th width="5%" class="t_center">&nbsp;</th>
	<th width="40%" class="t_center">Namn</th>
	<th width="15%" class="t_center">Ranking</th>
	<th width="15%" class="t_center">(Föregående)</th>
	<th width="25%" class="t_center">Kommentar</th>
</tr>

<!-- BEGIN NAME -->
<tr class="teamchess-tablerow-{odd_even}">
	<td class="t_left">{place}</td>
	<td class="t_left">
		<a href="{list_url}&listtype=ratingplayer&id={id}">{name}</a>
	</td>
	<td class="t_center">{rating}</td>
	<td class="t_center">({rating_prev})</td>
	<td class="t_center">{comment}</td>
</tr>
<!-- END NAME   -->

</table>
</div>
