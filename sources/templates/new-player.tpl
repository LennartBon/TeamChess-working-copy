<form action="" method="post" onsubmit="" id="new-player-form">

<!-- BEGIN NONCEFIELD -->
{nonce_field}
<!-- END   NONCEFIELD -->

<table>
	<tr>
		<td><label for="xFirst">Förnamn</label></td>
		<td><input type="text" id="xFirst" name="firstname" size="50" /></td>
	</tr>
	<tr>
		<td><label for="xLast">Efternamn</label></td>
		<td><input type="text" id="xLast" name="lastname" size="50" /></td>
	</tr>
	<tr>
		<td><label for="xSign">Kortnamn</label></td>
		<td><input type="text" id="xSign" name="signature" size="10" /></td>
	</tr>
	<tr>
		<td><label for="xSSF">SSF-id</label></td>
		<td><input type="text" id="xSSF" name="ssfid" size="10" /></td>
	</tr>
	<tr>
		<td><label for="xFide">FIDE-id</label></td>
		<td><input type="text" id="xFide" name="fideid" size="10" /></td>
	</tr>
	<tr>
		<td><label for="xInfo">Kommentar</label></td>
		<td><input type="text" id="xInfo" name="playerinfo" size="50" /></td>
	</tr>
	<tr>
		<td><label for="xPrimary">Primärregistrerad</label></td>
		<td><input type="checkbox" id="xPrimary" name="primarymember" value="1" checked="checked"/></td>
	</tr>

	<tr><td colspan="2">&nbsp;</td></tr>

	<tr>
		<td align="center">
			<button type="submit" id="Submitter" value="OK">Registrera spelaren</button>
		</td>
		<td align="center">
			<button type="reset" id="Resetter" value="Cancel">Rensa formuläret</button>
		</td>
	</tr>

</table>

</form>
