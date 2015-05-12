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
global $phpsudotaskfile;
global $dnslogfile;



if(isset($_GET['a'])){

	if($_GET['a']==1){
		//restart dnsmasq daemon
		exec("sudo {$phpsudotaskfile} --restart-dnsmasq");
		sleep(1);
	}
	else if($_GET['a']==2){
		// write conf
		exec("sudo {$phpsudotaskfile} --write-blcoklist-conf");
	}
	else if($_GET['a']==4){
		require('./inc/update-blocklist-conf-files.php');
		ExportConfAutolist();
	}
	else if($_GET['a']==5){
		require('./inc/update-blocklist-conf-files.php');
		ExportConfCustomlist();
	}
	else if($_GET['a']==6){
		require('./inc/update-blocklist-conf-files.php');
		ExportConfBothlist();
	}

	//else if($_GET['a']==3){
		//reboot dnsmasq
		//exec("sudo {$phpsudotaskfile} --status-lighttpd");
	//}

	header('Location: index.php');
	exit();
}

?>
<!DOCTYPE html>
<html>
<head>
<title>DNSMASQ STATUS</title>
<link rel="stylesheet" type="text/css" media="all" href="./css/dnsblocker-webgui-style-01.css" />
</head>
<body>
<div class="box1">
<ul class="ctrl1">
	<li><a href="?a=1">Restart dnsmasq daemon</a></li>
	<li><a href="?a=4">Regenerate auto-list conf file</a></li>
	<li><a href="?a=5">Regenerate custom-list conf file</a></li>
	<li><a href="?a=6">Regenerate both (custom and auto list) conf file</a></li>
</ul>
<?php

function KeyValTbl($keyValArray){

	$imx = count($keyValArray[0]);

	$b = '<table class="t2">';

	for($i=0; $i<$imx; $i++){

		$b .= '<tr><td class="lb">';
		$b .= $keyValArray[1][$i];
		$b .= ':</td><td>';
		$b .= $keyValArray[0][$i];
		$b .= '</td></tr>';
		$b .= "\n";
	}

	$b .= '<table class="t1">';

	return $b;

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

	return KeyValTbl(Array($a, $e));
}


$a = Array();
$b = Array();

exec("sudo {$phpsudotaskfile} --status-dnsmasq", $b);

exec('ps -eo fname,pid,stime,etime,cputime,pmem | grep dnsmasq', $a);

echo mkTbl($a[0], $b[0]);

$a = null;
$b = null;

exec("sudo {$phpsudotaskfile} --status-lighttpd", $b);

exec('ps -eo fname,pid,stime,etime,cputime,pmem | grep lighttpd', $a);

echo mkTbl($a[0], $b[0]);

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

$d[0] = basename($a[8]);
$d[1] = $a[4];
$d[2] = $a1[0] . ' lines';
$d[3] = "{$a[5]} {$a[6]} {$a[7]}";
$d[4] = dirname($a[8]);

$a  = null;
$a1 = null;
$b  = null;

$e  = Array('DNS Log database', 'Database file size', 'Database Last Updated', 'Database file path');
$f  = Array('dnsmasq Log file', 'Log file size', 'Log file line-count', 'Last log written', 'Log file path');

echo KeyValTbl(Array($c, $e));
echo KeyValTbl(Array($d, $f));


?>
</div>
</body>
</html>
