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
global $phpsudotaskfile;
global $dnslogfile;
global $sessionPath;
global $adlistfile;
global $adlistCustomfile;
global $dnslogfile;

session_save_path($sessionPath);

if(isset($_GET['a'])){

	$msg = '';

	if($_GET['a']==1){

		//restart dnsmasq daemon
		$msg  = "Task: Restart dnsmasq daemon{$eol}";
		exec("sudo {$phpsudotaskfile} --restart-dnsmasq", $s);
		if(is_array($s)) { $msg .= implode("\n",$s); }

	}
	else if($_GET['a']==4){
		// Export Auto-List

		require('./inc/blocklist-conf-inc.php');
		$msg  = "Task: Export Auto-List to file: {$adlistfile}{$eol}";
		$msg .= ExportConfAutolist();

	}
	else if($_GET['a']==5){
		// Export Custom-List

		require('./inc/blocklist-conf-inc.php');
		$msg  = "Task: Export Custom-List to file: {$adlistCustomfile}{$eol}";
		$msg .= ExportConfCustomlist();
	}
	else if($_GET['a']==6){
		// Export Both Auto-List and Custom-List

		require('./inc/blocklist-conf-inc.php');
		$msg  = "Task 1: Export Auto-List to file: {$adlistfile}{$eol}";
		$msg .= "Task 2: Export Custom-List to file: {$adlistCustomfile}{$eol}";
		$msg .= ExportConfBothlist();
	}
	else if($_GET['a']==7){
		// Import Auto-List

		require('./inc/blocklist-conf-inc.php');
		$msg  = "Task: Import Auto-List from file: {$adlistfile}{$eol}";
		$msg .= ImportConfAutolist();
	}
	else if($_GET['a']==8){
		// Import Custom-List

		require('./inc/blocklist-conf-inc.php');
		$msg  = "Task: Import Custom-List from file: {$adlistCustomfile}{$eol}";
		$msg .= ImportConfCustomlist();
	}
	else if($_GET['a']==9){

		// Import Both Auto-List and Custom-List
		require('./inc/blocklist-conf-inc.php');
		$msg  = "Task 1: Import Auto-List from file: {$adlistfile}{$eol}";
		$msg .= "Task 2: Import Custom-List from file: {$adlistCustomfile}{$eol}";
		$msg .= ImportConfBothlist();
	}
	else if($_GET['a']==10){
		// Import DNS Log

		require('./inc/update-db-dnslog-inc.php');
		$msg  = "Task: Import dnsmasq's log from file: {$dnslogfile}{$eol}";
		$msg .= ImportDnsmasqLog();
		$f = fopen($dnslogfile, 'w');
		if($f!=FALSE){
			fwrite($f, $msg);
			fclose($f);
		}
	}

	if(session_id()==''){

		// start a new session
		session_start();

		$_SESSION['msg']=$msg;
		$_SESSION['viewCount']=0;
	}

	// Sending reloading header causes the client browser to
	// reload a fresh index.php page and cleans up the unwanted url
	// params from browser url-bar. session veriables will ensure that
	// user is displayed with the output from the task excuted above
	// when the new index.php page loads.

	header('Location: index.php');
	exit();

}

?>
<!DOCTYPE html>
<html>
<head>
<meta name = "viewport" content = "width = 510">
<title>SYSTEM SUMMARY</title>
<link rel="stylesheet" type="text/css" media="all" href="./css/dnsblocker-webgui-style-01.css" />
</head>
<body>
<div class="box1">
<?php echo TopNavHtml(1); ?>
<div class="TopNav01">
	<ul>
		<li><p>dnsmasq Control:</p></li>
		<li><a href="?a=1">Restart</a></li>
	</ul>
</div>
<div class="TopNav01">
	<ul>
		<li><p>Export conf:</p></li>
		<li><a href="?a=4">Auto-List</a></li>
		<li><a href="?a=5">Custom-List</a></li>
		<li><a href="?a=6">Both List</a></li>
	</ul>
</div>
<div class="TopNav01">
	<ul>
		<li><p>Import conf:</p></li>
		<li><a href="?a=7">Auto-List</a></li>
		<li><a href="?a=8">Custom-List</a></li>
		<li><a href="?a=9">Both List</a></li>
	</ul>
</div>
<div class="TopNav01">
	<ul>
		<li><p>Import Log:</p></li>
		<li><a href="?a=10">Import</a></li>
	</ul>
</div>
<br/>
<?php

if(session_id()==''){

	session_start();

	if(isset($_SESSION['msg']) && strlen($_SESSION['msg'])>0 && $_SESSION['viewCount']==0){

			echo 'Output:<div class="tx">';
			echo $_SESSION['msg'];
			echo '</div><br/>';

			$_SESSION['viewCount']++;
	}

	session_unset();
	session_destroy();
}

function strtime($t){

	$b=preg_split('/[\-:]/', $t);

	$i = count($b);

	$s = '';

	if($i > 0) $s = $b[$i-1] . ' seconds';
	if($i > 1) $s = $b[$i-2] . ' minutes ' . $s;
	if($i > 2) $s = $b[$i-3] . ' hours ' . $s;
	if($i > 3) $s = $b[$i-4] . ' days ' . $s;


	return $s;

}

function mkTbl($a, $c){

	$a=preg_split('/[\s]+/', $a);

	$a[3]= strtime($a[3]);
	$a[5] .= ' MB';
	$a[6]  = $c;

	$e = Array('Process', 'PID', 'Started On', 'Running since', 'Total CPU-Time', 'Memory used', 'Service Status');

	return KeyValTblHtml($e, $a);
}


$a = Array();
$b = Array();

exec("sudo {$phpsudotaskfile} --status-dnsmasq", $b);

exec('ps -eo fname,pid,stime,etime,cputime,pmem | grep dnsmasq', $a);

echo mkTbl($a[0], $b[0]);
echo "{$eol}<br/>{$eol}";

$a = null;
$b = null;

exec("sudo {$phpsudotaskfile} --status-lighttpd", $b);

exec('ps -eo fname,pid,stime,etime,cputime,pmem | grep lighttpd', $a);

echo mkTbl($a[0], $b[0]);
echo "{$eol}<br/>{$eol}";

$a = null;
$b = null;


exec("ls -lh {$dnslogfile}", $a);
exec("wc -l {$dnslogfile}", $a1);
exec("ls -lh {$dbfile}", $b);

$a  = preg_split('/[\s]+/', $a[0]);
$a1 = preg_split('/[\s]+/', $a1[0]);
$b  = preg_split('/[\s]+/', $b[0]);

$c[0] = basename($b[8]);
$c[1] = $b[4];
$c[2] = "{$b[5]} {$b[6]} {$b[7]}";
$c[3] = dirname($b[8]);


try {

	$db = new PDO('sqlite:' . $dbfile);

	$q = "SELECT COUNT(*) AS 'RowCount' FROM {$tblDns}";
	$res = $db->query($q);
	if($res != FALSE){
		$row = $res->fetch();
		$c[4] = "{$row['RowCount']} rows";
	}
	else{
		$c[4] = 'unavailable';
	}

	$res = null;
	$row = null;

	$q = "SELECT COUNT(*) AS 'RowCount' FROM {$tblBlk}";
	$res = $db->query($q);
	if($res != FALSE){
		$row = $res->fetch();
		$c[5] = "{$row['RowCount']} rows";
	}
	else{
		$c[5] = 'unavailable';
	}

	$res = null;
	$row = null;
	$db  = null;

}
catch(PDOException $e){
	//	echo "FAIL TO OPEN DATABASE FILE!{$eol}" . $e->getMessage();
	//	exit();
}


$d[0] = basename($a[8]);
$d[1] = $a[4];
$d[2] = $a1[0] . ' lines';
$d[3] = "{$a[5]} {$a[6]} {$a[7]}";
$d[4] = dirname($a[8]);

$a  = null;
$a1 = null;
$b  = null;

$e  = Array('DNS Log Database', 'Database File Size', 'Database Last Updated', 'Database File Path', "{$tblDns} Table Size", "{$tblBlk} Table Size");
$f  = Array('dnsmasq Log File', 'Log File Size', 'Log File Line-Count', 'Last Log Written', 'Log File Path');

echo KeyValTblHtml($e, $c);
echo "{$eol}<br/>{$eol}";
echo KeyValTblHtml($f, $d);







?>
</div>
</body>
</html>
