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

if(isset($_GET['a']) == FALSE || $_GET['a']<1 || $_GET['a']>3)  $_GET['a']=3;
if(isset($_GET['c']) == FALSE || $_GET['c']<6 || $_GET['c']>19) $_GET['c']=11;
if(isset($_GET['d']) == FALSE || $_GET['d']<1 || $_GET['d']>4)  $_GET['d']=3;
if(isset($_GET['e']) == FALSE || $_GET['e']<1 || $_GET['e']>2)  $_GET['e']=1;

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
		return $row['ENTRYCOUNT'];
	}

	$res = null;
	$row = null;

	return '0';
}

function Fetch02($q){

	global $db;


	$res = $db->query($q);
	if($res != FALSE){

		$row = $res->fetch();

		$a[0] = $row['URLCOUNT'];
		$a[1] = $row['HITCOUNTSUM'];

		return $a;
	}

	$res = null;
	$row = null;

	$a[0] = '0';
	$a[1] = '0';
	return $a;
}


// Generate Stats from DB

$a = Array('Auto-List', 'Custom-List', 'Total');

$q = "SELECT COUNT(*) AS 'ENTRYCOUNT' FROM {$tblBlk} WHERE {$colOp}=1";
$a0 = Fetch01($q) . ' entries';

$q = "SELECT COUNT(*) AS 'ENTRYCOUNT' FROM {$tblBlk} WHERE {$colOp}=2";
$a1 = Fetch01($q) . ' entries';

$aTotal = ($a0 + $a1) . ' entries';

$q = "SELECT COUNT({$colUrl}) AS 'URLCOUNT', SUM({$colHit}) AS HITCOUNTSUM FROM {$tblDns} WHERE {$colOp}=1";
$b = Fetch02($q);

$q = "SELECT COUNT({$colUrl}) AS 'URLCOUNT', SUM({$colHit}) AS HITCOUNTSUM FROM {$tblDns} WHERE {$colOp}=2";
$c = Fetch02($q);


$e = Array(
		Array($a0, 		'Blocked:',          $b[0]  . ' url',          $b[1]  . ' hits'),
		Array($a1, 		'Blocked:',		     $c[0]  . ' url',          $c[1]  . ' hits'),
		Array($aTotal,  'Blocked:',	  ($b[0]+$c[0]) . ' url',   ($b[1]+$c[1]) . ' hits')
);

$DbStatTbl[0]  = "Block List Summary:";
$DbStatTbl[0] .= KeyValTblHtml($a, $e, 'vlr', 4);


$a = Array('Auto-List entry', 'Custom-List entry');
$b = Array('<p class="blk">1</p>', '<p class="blk">2</p>');
$DbStatTbl[1]  = "* OP Column Legend:";
$DbStatTbl[1] .= KeyValTblHtml($a, $b);


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
<title>BLOCK LISTS</title>
<link rel="stylesheet" type="text/css" media="all" href="./css/dnsblocker-webgui-style-01.css" />
</head>
<body>
<div class="box1">
<?php echo TopNavHtml(3); ?>
<form action="viewlist.php">
<table class="tnav">
<tr>
	<td class="l">Show:</td>
	<td class="v">
		<select name="a">
			<?php f1('a', 1); ?>All</option>
			<?php f1('a', 2); ?>Auto-list</option>
			<?php f1('a', 3); ?>Custom-list</option>
		</select>
	</td>
</tr>
<tr>
	<td class="l">Number of Lines</td>
	<td class="v">
		<select name="c">
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
			<?php f1('d', 1); ?>URL (By domain)</option>
			<?php f1('d', 2); ?>URL (By length)</option>
			<?php f1('d', 3); ?>URL (By alphabetic)</option>
			<?php f1('d', 4); ?>OP</option>
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
	<td class="stattbl"><?php echo $DbStatTbl[1]; ?></td>
</tr>
<tr>
	<td class="stattbl"><?php echo $DbStatTbl[0]; ?></td>
</tr>
</table>
Block List Entries:<br/>
<?php


$line = Array();
$adUrl = Array();
$adCustom = Array();
$logsize = Array();
$alter = FALSE;

// SELECT fields
$qField = "SELECT * FROM {$tblBlk}";

// WHERE condition for blocked custom list only(goes after $qCount)
$qBlockedCustom = "{$colOp}=2";

// WHERE condition for blocked auto list only(goes after $qCount)
$qBlockedAuto = "{$colOp}=1";

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
	 if($_GET['d'] == 1) $col="REVERSE({$colUrl}) {$ord}";
else if($_GET['d'] == 2) $col="LENGTH({$colUrl}) {$ord}";
else if($_GET['d'] == 3) $col="{$colUrl} {$ord}";
else if($_GET['d'] == 4) $col="{$colOp} {$ord}, REVERSE({$colUrl}) {$ord}";
else $col="REVERSE({$colUrl}) {$ord}";


$qOrder = "ORDER BY $col";


$n++;
$qLimit = "LIMIT $n";

	 if($_GET['a'] == 1) $q = "{$qField} {$qOrder} {$qLimit}";
else if($_GET['a'] == 2) $q = "{$qField} WHERE {$qBlockedAuto} {$qOrder} {$qLimit}";
else if($_GET['a'] == 3) $q = "{$qField} WHERE {$qBlockedCustom} {$qOrder} {$qLimit}";
else $q = "{$qField} {$qOrder} {$qLimit}";


$db->sqliteCreateFunction('REVERSE', 'strrev', 1);

// echo "<pre>{$q}</pre>"; exit();

$res = $db->query($q);

if($res==false){
	die(var_export($db->errorinfo(), TRUE));
}

echo '<table class="tbl">';
echo "<tr><td>OP *</td><td>URL</td><td>Actions</td></tr>{$eol}";

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

	// OP COLUMN
	$o .= '<td class="cnt"><p class="blk">';
	$o .= $row['op'];
	$o .= '</p></td>';


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

	$o .= "</td></tr>{$eol}";

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

