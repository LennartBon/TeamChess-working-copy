<?php

	require_once	'DB.php';
	require_once	'HTML/Template/IT.php';
	include_once	'db-userparams.inc';
	
	$dsn = 'mysql://' . $db_user . ':' . $db_passwd . '@' . $db_host . '/' . $db_base;
	$options = array(
		'debug'       => 2,
		'portability' => DB_PORTABILITY_ALL,
	);
	
	$db =& DB::connect($dsn, $options);
	if (PEAR::isError($db)) {
		 die("C - " . $db->getMessage());
	}
	
	$matchlista = new HTML_Template_IT ("./mallar");
	$matchlista->loadTemplatefile ("matchlista.tpl", true, true);

	$motst	= $_GET["motst"];
	$f_date	= $_GET["f_date"];
	$l_date	= $_GET["l_date"];
	$serie	= $_GET["serie"];
	$lag	= $_GET["lag"];

	$wh = 0;		// Flagga
	$wh_string = "";
	
	$q  = "select * from matcher";
	
	if ($motst != "")
	{
		if ($wh > 0) $wh_string .= " and ";
		$wh_string .= "motst like '{$motst}%'";
		$wh += 1;
	}
	if ($f_date != "")
	{
		if ($wh > 0) $wh_string .= " and ";
		$wh_string .= "datum >= '{$f_date}'";
		$wh += 1;
	}
	if ($l_date != "")
	{
		if ($wh > 0) $wh_string .= " and ";
		$wh_string .= "datum <= '{$l_date}'";
		$wh += 1;
	}
	if ($serie != "")
	{
		if ($wh > 0) $wh_string .= " and ";
		$wh_string .= "serie like '{$serie}%'";
		$wh += 1;
	}
	if ($lag != "")
	{
		if ($wh > 0) $wh_string .= " and ";
		$wh_string .= "lag = '{$lag}'";
		$wh += 1;
	}

	if ($wh > 0)
	{
		$q .= " where " . $wh_string;
	}
	
	$q .= " order by datum, lag";

	$result1 =& $db->query ($q);
	if (DB::isError ($result1))
		die ("Q1 - " . $result1->getMessage());
	
	while ($row1 = $result1->fetchRow (DB_FETCHMODE_ASSOC))
	{
		$n = $row1["nr"];
		
		$q  = "select count(resultat) as antal, sum(resultat)/2 as res";
		$q .= " from bord";
		$q .= " where match_nr={$n}";
	
		$result2 =& $db->query ($q);
		if (DB::isError ($result2))
			die ("Q2 - " . $result2->getMessage());
			
		$row2 = $result2->fetchRow (DB_FETCHMODE_ASSOC);

		$vsk_resultat = $row2["res"] + 0;						// ** Hack! **
		$motst_resultat = $row2["antal"] - $vsk_resultat;
		$res_string = $vsk_resultat . "&nbsp;&ndash;&nbsp;" . $motst_resultat;
	
		$matchlista->setCurrentBlock ("LAGMATCH");
		$matchlista->setVariable ("nr", $row1["nr"]);
		$matchlista->setVariable ("motst", $row1["motst"]);
		$matchlista->setVariable ("datum", $row1["datum"]);
		$matchlista->setVariable ("resultat", $res_string);
		$matchlista->setVariable ("serie", $row1["serie"]);
		$matchlista->setVariable ("lag", $row1["lag"]);
		$matchlista->parseCurrentBlock ();
	}

	$matchlista->show();
?>
