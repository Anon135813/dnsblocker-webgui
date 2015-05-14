<?php

require('./inc/global-var-inc.php');

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

function f1($a, $b){

	echo '<option value="';
	echo $b;
	echo '"';

	if($_GET[$a]==$b) echo ' selected="selected"';

	echo '>';

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
<form action="viewlog.php">
<table class="tnav">
<tr>
	<td class="l">Show:</td>
	<td class="v">
		<select name="a">
			<?php f1('a', 1); ?>All</option>
			<?php f1('a', 2); ?>Only Unblocked</option>
			<?php f1('a', 3); ?>Only Blocked</option>
			<?php f1('a', 4); ?>Only Blocked by Auto list</option>
			<?php f1('a', 5); ?>Only Blocked by Custom list</option>
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
* OP Column value legend (1 = Blocked by Auto-list, 2 = Blocked by Custom-list)<br/>
<?php

//CREATE TABLE "dnslog"("url" varchar(256) primary key not null, "t1" varchar(16), "t2" varchar(16), "ip" varchar(16), "hit" int not null, "op" int not null);

$db = null;
$eol = "\n";
$line = Array();
$adUrl = Array();
$adCustom = Array();
$logsize = Array();
$alter = FALSE;

try {

	$db = new PDO('sqlite:' . $dbfile);

	//echo "SUCESSFULLY OPEN DATABASE FILE!{$eol}<br/><br/>OP VALUE LEGEND<br/>1 = AUTO LIST<br/>2 = CUSTOM LIST<br/><br/>";

}
catch(PDOException $e){
	echo "FAIL TO OPEN DATABASE FILE!{$eol}" . $e->getMessage();
	exit();
}

// SELECT fields
$qField = "SELECT * FROM {$tblDns} AS A";

// WHERE condition for unblocked only(goes after $qCount)
$qUnblockedOnly = "A.{$colOp}=0";

// WHERE condition for blocked custom list only(goes after $qCount)
$qBlockedCustom = "A.{$colOp}=2";

// WHERE condition for blocked auto list only(goes after $qCount)
$qBlockedAuto = "A.{$colOp}=1";

// WHERE condition for blocked auto list only(goes after $qCount)
$qBlockedOnly = "(A.{$colOp}=1 OR A.{$colOp}=2)";

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
     if($_GET['d'] == 1) $col="A.{$colHit} {$ord}, REVERSE(A.{$colUrl}) {$ord}";
else if($_GET['d'] == 2) $col="A.{$colOp} {$ord}, A.{$colHit} {$ord}, REVERSE(A.{$colUrl}) {$ord}";
else if($_GET['d'] == 3) $col="REVERSE(A.{$colUrl}) {$ord}";
else if($_GET['d'] == 4) $col="LENGTH(A.{$colUrl}) {$ord}";
else if($_GET['d'] == 5) $col="A.{$colUrl} {$ord}";
else if($_GET['d'] == 6) $col="STRTIME(A.{$colT1}) {$ord}";
else if($_GET['d'] == 7) $col="STRTIME(A.{$colT2}) {$ord}";
else if($_GET['d'] == 8) $col="A.{$colIp} {$ord}, REVERSE(A.{$colUrl}) {$ord}";
else $col="A.{$colHit}";


$qOrder = "ORDER BY $col";


if($_GET['b'] == 1){

	if($_GET['a']==1) $qCount = "WHERE (A.{$colHit} > $n)";
	else $qCount = "WHERE (A.{$colHit} > $n) AND";

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
	else $q = "{$qField} {$qOrder} {$qLimit}";

}

$db->sqliteCreateFunction('REVERSE', 'strrev', 1);
$db->sqliteCreateFunction('STRTIME', 'strtotime', 1);

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

