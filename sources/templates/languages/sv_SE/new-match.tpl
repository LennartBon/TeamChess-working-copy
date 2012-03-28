<form action="" method="post" onsubmit="" id="new-match-form">

<!-- BEGIN NONCEFIELD -->
{nonce_field}
<!-- END   NONCEFIELD -->

<label for="xOpp">Motståndare:</label>
<input type="text" id="xOpp" name="opponent" size="50" />

<table id="new-match-data">
	<tr>
		<td><label for="xDate">Datum:</label></td>
		<td><input type="text" id="xDate" name="matchdate" size="20" /></td>
		<td><label for="xLeague">Serie:</label></td>
		<td><input type="text" id="xLeague" name="league" size="20" /></td>
	</tr>

	<tr>
		<td><label for="xTeam">Lag:</label></td>
		<td>
			<select id="xTeam" name="team">
<!-- BEGIN TEAMMENU -->
				<option value="{team}">{team}</option>
<!-- END   TEAMMENU -->
				<option value="otherTeam">Annat-></option>
			</select>
			<input type="text" id="xNewTeam" name="newteam" size="15" />
		</td>
		<td><label for="xTables">Antal bord:</label></td>
		<td>
			<select id="xTables" name="tables">
<!-- BEGIN TEAMSIZEMENU -->
				<option value="{teamsize}"{selected}>{teamsize}</option>
<!-- END   TEAMSIZEMENU -->
				<option value="otherSize">Annat-></option>
			</select>
			<input type="text" id="xNewTeamSize" name="newteamsize" size="5" disabled="disabled" />
		</td>
	</tr>

</table>

<hr />

<table id="new-match-tables">
<!-- BEGIN TABLEMENU -->
	<tr align="center" id="new-match-table{n}">
		<td>
			Bord {n}:
		</td>
		<td>
			<select name="Table{n}">
				<option value="---" selected="selected">---</option>
<!-- BEGIN PLAYERMENU -->
				<option value="{id}">{name}</option>
<!-- END   PLAYERMENU -->
			</select>
		</td>
		<td>
			<input type="text" name="Signature{n}" size="6" />
		</td>
		<td>
			<select name="Result{n}">
				<option value="0" selected="selected">0</option>
				<option value="1">½</option>
				<option value="2">1</option>
			</select>
		</td>
	</tr>
<!-- END   TABLEMENU -->

	<tr class="TCH_alwaysShow">
		<td colspan="4" align="center">
			&nbsp;
		</td>
	</tr>
	<tr class="TCH_alwaysShow">
		<td colspan="2" align="center">
			<button type="submit" id="Submitter" value="OK">Registrera matchen</button>
		</td>
		<td colspan="2" align="center">
			<button type="reset" id="Resetter" value="Cancel">Rensa formuläret</button>
		</td>
	</tr>

</table>
</form>
