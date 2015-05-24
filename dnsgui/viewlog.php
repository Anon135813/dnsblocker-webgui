<?php

require('./inc/global-var-inc.php');
require('./inc/common-html-inc.php');

global $dbfile;
global $tblBlk;
global $tblDns;
global $colUrl;
global $colT1;
global $colT2;
global $colHit;
global $colIp;
global $colOp;


// start of php blcok1

if(isset($_GET['a']) == FALSE || $_GET['a']<1 || $_GET['a']>6)  $_GET['a']=1;
if(isset($_GET['b']) == FALSE || $_GET['b']<1 || $_GET['b']>3)  $_GET['b']=2;
if(isset($_GET['c']) == FALSE || $_GET['c']<1 || $_GET['c']>20) $_GET['c']=11;
if(isset($_GET['d']) == FALSE || $_GET['d']<1 || $_GET['d']>9)  $_GET['d']=7;
if(isset($_GET['e']) == FALSE || $_GET['e']<1 || $_GET['e']>3)  $_GET['e']=2;

$db = null;

try {
	$db = new PDO('sqlite:' . $dbfile);
}
catch(PDOException $e){
	echo "FAIL TO OPEN DATABASE FILE!{$eol}" . $e->getMessage();
	exit();
}


// Generating Database statistics

function Fetch01($q){

	global $db;

	$res = $db->query($q);
	if($res != FALSE){
		$row = $res->fetch();
		return $row['ENTRYCOUNT'] . ' entries';
	}

	$res = null;
	$row = null;

	return 'unavailable';
}

function Fetch02($q){

	global $db;


	$res = $db->query($q);
	if($res != FALSE){

		$row = $res->fetch();

		$a[0] = $row['URLCOUNT'] . ' url';
		$a[1] = $row['HITCOUNTSUM'] . ' hits';

		return $a;
	}

	$res = null;
	$row = null;

	$a[0] = 'unavailable';
	$a[1] = 'unavailable';
	return $a;
}


// Generate Stats from DB


$a = Array('Auto-List', 'Custom-List', 'Total');
$b = null;

$q = "SELECT COUNT(*) AS 'ENTRYCOUNT' FROM {$tblBlk} WHERE {$colOp}=1";
$b[0] = Fetch01($q);

$q = "SELECT COUNT(*) AS 'ENTRYCOUNT' FROM {$tblBlk} WHERE {$colOp}=2";
$b[1] = Fetch01($q);

$b[2] = ($b[0] + $b[1]) . ' entries';

$DbStatTbl[0]  = "Summary of Table {$tblBlk}:";
$DbStatTbl[0] .= KeyValTblHtml($a, $b, 'vlr');



$a = Array('Auto-List blocked', 'Custom-List blocked', 'Total blocked', 'Unblocked', 'Total');
$b = null;

$q = "SELECT COUNT({$colUrl}) AS 'URLCOUNT', SUM({$colHit}) AS HITCOUNTSUM FROM {$tblDns} WHERE {$colOp}=1";
$b[0] = Fetch02($q);

$q = "SELECT COUNT({$colUrl}) AS 'URLCOUNT', SUM({$colHit}) AS HITCOUNTSUM FROM {$tblDns} WHERE {$colOp}=2";
$b[1] = Fetch02($q);

$b[2][0] = ($b[0][0] + $b[1][0]) . ' url';
$b[2][1] = ($b[0][1] + $b[1][1]) . ' hits';

$q = "SELECT COUNT({$colUrl}) AS 'URLCOUNT', SUM({$colHit}) AS HITCOUNTSUM FROM {$tblDns} WHERE {$colOp}=0";
$b[3] = Fetch02($q);

$b[4][0] = ($b[3][0] + $b[2][0]) . ' url';
$b[4][1] = ($b[3][1] + $b[2][1]) . ' hits';

$DbStatTbl[2]  = "Log Summary:";
$DbStatTbl[2] .= KeyValTblHtml($a, $b, 'vlr', 2);


$a = Array('Blocked by Auto-List', 'Blocked by Custom-List', 'Unblocked');
$b[0] = '<p class="blk">1</p>';
$b[1] = '<p class="blk">2</p>';
$b[2] = '<p class="ublk"></p>';
$DbStatTbl[3]  = "* OP Column Legend:";
$DbStatTbl[3] .= KeyValTblHtml($a, $b);


function f1($a, $b){

	echo '<option value="';
	echo $b;
	echo '"';

	if($_GET[$a]==$b) echo ' selected="selected"';

	echo '>';

}

function top_domain($url){

	$urlParts = explode('.', $url);
	$i = count($urlParts);

	if($i > 1){

		$m = strlen($urlParts[$i-1]);

		if($m==2){

			if($i>=3){

				$m = strlen($urlParts[$i-2]);

				if($m<5){

					return $urlParts[$i-3] . '.' . $urlParts[$i-2] . '.' . $urlParts[$i-1];
				}
				else return $urlParts[$i-2] . '.' . $urlParts[$i-1];

			}
			else return $url;
		}
		else if($m==3){

			return $urlParts[$i-2] . '.' . $urlParts[$i-1];

		}
		else{

			if($i>=3) return $urlParts[$i-3] . '.' . $urlParts[$i-2] . '.' . $urlParts[$i-1];
			else return $url;
		}

	}

	return $url;

}


// end of php blcok1
?>
<!DOCTYPE html>
<html>
<head>
<title>DNS LOGS</title>
<link rel="stylesheet" type="text/css" media="all" href="./css/dnsblocker-webgui-style-01.css" />
</head>
<body>
<div class="box1">
<?php echo TopNavHtml(2); ?>
<form action="viewlog.php">
<table class="tnav">
<tr>
	<td class="l">Show:</td>
	<td class="v">
		<select name="a">
			<?php f1('a', 1); ?>All</option>
			<?php f1('a', 2); ?>Only Unblocked</option>
			<?php f1('a', 3); ?>Only Blocked</option>
			<?php f1('a', 4); ?>Only Blocked by Auto-list</option>
			<?php f1('a', 5); ?>Only Blocked by Custom-list</option>
			<?php f1('a', 6); ?>Group By Base Domain Name</option>
		</select>
	</td>
</tr>
<tr>
	<td class="l">Limit by:</td>
	<td class="v">
		<select name="b">
			<?php f1('b', 1); ?>Minimum hit-count</option>
			<?php f1('b', 2); ?>Number of lines</option>
		</select>
	</td>
</tr>

<tr>
	<td class="l">Limit amount:</td>
	<td class="v">
		<select name="c">
			<?php f1('c', 1); ?>1</option>
			<?php f1('c', 2); ?>2</option>
			<?php f1('c', 3); ?>3</option>
			<?php f1('c', 4); ?>4</option>
			<?php f1('c', 5); ?>5</option>
			<?php f1('c', 6); ?>10</option>
			<?php f1('c', 7); ?>20</option>
			<?php f1('c', 8); ?>30</option>
			<?php f1('c', 9); ?>40</option>
			<?php f1('c', 10); ?>50</option>
			<?php f1('c', 11); ?>100</option>
			<?php f1('c', 12); ?>200</option>
			<?php f1('c', 13); ?>300</option>
			<?php f1('c', 14); ?>400</option>
			<?php f1('c', 15); ?>500</option>
			<?php f1('c', 16); ?>1000</option>
			<?php f1('c', 17); ?>1500</option>
			<?php f1('c', 18); ?>2000</option>
			<?php f1('c', 19); ?>3000</option>
		</select>
	</td>
</tr>
<tr>
	<td class="l">Sort by column:</td>
	<td class="v">
		<select name="d">
			<?php f1('d', 1); ?>Hit-Count</option>
			<?php f1('d', 2); ?>OP</option>
			<?php f1('d', 3); ?>URL (By domain)</option>
			<?php f1('d', 4); ?>URL (By length)</option>
			<?php f1('d', 5); ?>URL (By alphabetic)</option>
			<?php f1('d', 6); ?>First Time (T1)</option>
			<?php f1('d', 7); ?>Last Time (T2)</option>
			<?php f1('d', 8); ?>Last IP</option>
		</select>
	</td>
</tr>
<tr>
	<td class="l">Sort order:</td>
	<td class="v">
		<select name="e">
			<?php f1('e', 1); ?>Ascending</option>
			<?php f1('e', 2); ?>Descending</option>
		</select>
	</td>
</tr>
<tr>
	<td></td>
	<td class="v"><input type="submit" value="Apply Changes" /></td>
</tr>
</table>
</form>
<table>
<tr>
	<td class="stattbl"><?php echo $DbStatTbl[2]; ?></td>
	<td class="stattbl"><?php echo $DbStatTbl[3]; ?></td>
	<td class="stattbl"><?php echo $DbStatTbl[0]; ?></td>
</tr>
</table>
DNS Query Log:<br/>
<?php


$line = Array();
$adUrl = Array();
$adCustom = Array();
$logsize = Array();
$alter = FALSE;

// SELECT fields
$qField = "SELECT * FROM {$tblDns}";

// WHERE condition for unblocked only(goes after $qCount)
$qUnblockedOnly = "{$colOp}=0";

// WHERE condition for blocked custom list only(goes after $qCount)
$qBlockedCustom = "{$colOp}=2";

// WHERE condition for blocked auto list only(goes after $qCount)
$qBlockedAuto = "{$colOp}=1";

// WHERE condition for blocked auto list only(goes after $qCount)
$qBlockedOnly = "({$colOp}=1 OR {$colOp}=2)";

// Limit Amount (basically "n-1" if limit is hit or "n" if limit is number)
     if($_GET['c'] == 1) $n=0;
else if($_GET['c'] == 2) $n=1;
else if($_GET['c'] == 3) $n=2;
else if($_GET['c'] == 4) $n=3;
else if($_GET['c'] == 5) $n=4;
else if($_GET['c'] == 6) $n=9;
else if($_GET['c'] == 7) $n=19;
else if($_GET['c'] == 8) $n=29;
else if($_GET['c'] == 9) $n=39;
else if($_GET['c'] == 10) $n=49;
else if($_GET['c'] == 11) $n=99;
else if($_GET['c'] == 12) $n=199;
else if($_GET['c'] == 13) $n=299;
else if($_GET['c'] == 14) $n=399;
else if($_GET['c'] == 15) $n=499;
else if($_GET['c'] == 16) $n=999;
else if($_GET['c'] == 17) $n=1499;
else if($_GET['c'] == 18) $n=1999;
else if($_GET['c'] == 19) $n=2999;
else $n=10;


// ORDER asc/desc
     if($_GET['e'] == 1) $ord='ASC';
else if($_GET['e'] == 2) $ord='DESC';
else $ord='DESC';

// ORDER by column
     if($_GET['d'] == 1) $col="{$colHit} {$ord}, REVERSE({$colUrl}) {$ord}";
else if($_GET['d'] == 2) $col="{$colOp} {$ord}, {$colHit} {$ord}, REVERSE({$colUrl}) {$ord}";
else if($_GET['d'] == 3) $col="REVERSE({$colUrl}) {$ord}";
else if($_GET['d'] == 4) $col="LENGTH({$colUrl}) {$ord}";
else if($_GET['d'] == 5) $col="{$colUrl} {$ord}";
else if($_GET['d'] == 6) $col="STRTIME({$colT1}) {$ord}";
else if($_GET['d'] == 7) $col="STRTIME({$colT2}) {$ord}";
else if($_GET['d'] == 8) $col="{$colIp} {$ord}, REVERSE({$colUrl}) {$ord}";
else $col="{$colHit}";


$qOrder = "ORDER BY $col";


if($_GET['b'] == 1){

	if($_GET['a']==1) $qCount = "WHERE ({$colHit} > $n)";
	else $qCount = "WHERE ({$colHit} > $n) AND";

		 if($_GET['a'] == 1) $q = "{$qField} {$qCount} {$qOrder}";
	else if($_GET['a'] == 2) $q = "{$qField} {$qCount} {$qUnblockedOnly} {$qOrder}";
	else if($_GET['a'] == 3) $q = "{$qField} {$qCount} {$qBlockedOnly} {$qOrder}";
	else if($_GET['a'] == 4) $q = "{$qField} {$qCount} {$qBlockedAuto} {$qOrder}";
	else if($_GET['a'] == 5) $q = "{$qField} {$qCount} {$qBlockedCustom} {$qOrder}";
	else $q = "{$qField} {$qCount} {$qOrder}";


}
else{

	$n++;
	$qLimit = "LIMIT $n";

		 if($_GET['a'] == 1) $q = "{$qField} {$qOrder} {$qLimit}";
	else if($_GET['a'] == 2) $q = "{$qField} WHERE {$qUnblockedOnly} {$qOrder} {$qLimit}";
	else if($_GET['a'] == 3) $q = "{$qField} WHERE {$qBlockedOnly} {$qOrder} {$qLimit}";
	else if($_GET['a'] == 4) $q = "{$qField} WHERE {$qBlockedAuto} {$qOrder} {$qLimit}";
	else if($_GET['a'] == 5) $q = "{$qField} WHERE {$qBlockedCustom} {$qOrder} {$qLimit}";
	else if($_GET['a'] == 6){

		 $subQ = "SELECT SUM({$colHit}) AS 'hit', '*.' || TOPDOMAIN({$colUrl}) AS 'url', {$colOp} AS 'op', {$colIp} AS 'ip', {$colT1} AS 't1', {$colT2} AS 't2' FROM {$tblDns} GROUP BY TOPDOMAIN({$colUrl}) {$qLimit}";

		 $q = "SELECT * FROM ({$subQ}) {$qOrder}";

	}
	else $q = "{$qField} {$qOrder} {$qLimit}";

}

$db->sqliteCreateFunction('REVERSE', 'strrev', 1);
$db->sqliteCreateFunction('STRTIME', 'strtotime', 1);
$db->sqliteCreateFunction('TOPDOMAIN', 'top_domain', 1);

//if($_GET['a']==6){ echo "<pre>{$q}</pre>"; exit();}

$res = $db->query($q);

if($res==false){
	die(var_export($db->errorinfo(), TRUE));
}

echo '<table class="tbl">';
echo "<tr><td>HIT</td><td>OP *</td><td>URL</td><td>Actions</td><td>FIRST<br/>REQUEST (T1)</td><td>LAST<br/>REQUEST (T2)</td><td>LAST IP</td></tr>{$eol}";

$row = $res->fetch();
while($row){

	if($alter){
		$o= '<tr class="a">';
		$alter=FALSE;
	}
	else{
		$o = '<tr>';
		$alter=TRUE;
	}

	// HIT COLUMN
	$o .= "<td>{$row['hit']}</td>";

	// OP COLUMN
	$o .= '<td class="cnt">';
		if($row['op']>0){

			$o .= '<p class="blk">';
			$o .= $row['op'];
			$o .= '</p>';
		}
		else{

			$o .= '<p class="ublk"></p>';
		}
	$o .= '</td>';


	// URL COLUMN
	$o .= '<td class="u">';
	$o .= htmlentities($row['url']);
	$o .= '</td>';


	// ACTION COLUMN
	$encUrl = urlencode($row['url']);
	$encDblQuote = urlencode('"');


	$o .= '<td class="cnt">';

		$o .= '<a class="res" Title="Google search" href="http://www.google.com/search?q=';
		$o .= $encDblQuote;
		$o .= $encUrl;
		$o .= $encDblQuote;
		$o .= '" target="_blanhk">';
		$o .= '</a>';

		$o .= htmlentities(' ');

		if($row['op']==1 || $row['op']==2) $o .= '<a class="unblk" Title="Unblock this URL" href="modlist.php?a=1&u[]=';
		else $o .= '<a class="blk" Title="Block this URL" href="modlist.php?a=2&u[]=';
		$o .= $encUrl;
		$o .= '" target="_blank">';
		$o .= '</a>';

	$o .= '</td>';

	// T1, T2 AND IP COLUMN
	$o .= "<td>{$row['t1']}</td><td>{$row['t2']}</td><td>{$row['ip']}</td></tr>{$eol}";

	echo $o;

	$row = $res->fetch();
}
echo '</table>';


$row = null;
$res = null;
$db  = null;

?>
</div>
</body>
</html>

