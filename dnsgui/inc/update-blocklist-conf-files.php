<?php

require('global-var-inc.php');

global $dbfile;
global $adlistfile;
global $adlistCustomfile;

global $tblBlk;
global $tblDns;
global $colUrl;
global $colT1;
global $colT2;
global $colHit;
global $colIp;
global $colOp;
global $eol;


$db = null;
$newCount = 0;
$updateCount = 0;


$line = Array();
$entry = Array();
$logsize = Array();



try {

$db = new PDO('sqlite:' . $dbfile);

echo "SUCESSFULLY OPEN DATABASE FILE!{$eol}";

}
catch(PDOException $e){
	echo "FAIL TO OPEN DATABASE FILE!{$eol}" . $e->getMessage();
	exit();
}



$q1  = "SELECT A.{$colUrl} FROM {$tblBlk} AS A WHERE A.{$colOp}=2 ORDER BY A.{$colUrl} ASC";

$res = $db->query($q1);

if($res==false){
	die(var_export($db->errorinfo(), TRUE));
}

db2conf($res, $adlistCustomfile);


$q1  = "SELECT A.{$colUrl} FROM {$tblBlk} AS A WHERE A.{$colOp}=1 ORDER BY A.{$colUrl} ASC";

$res = $db->query($q1);

if($res==false){
	die(var_export($db->errorinfo(), TRUE));
}

db2conf($res, $adlistfile);


function db2conf($res, $fn){

	$hostip = '192.168.1.8';

	$row = $res->fetch();

	if($row!=FALSE){

		$f = fopen($fn, 'w');

		if($f==FALSE){

			echo "Unable to open file: {$fn}";
			exit();
		}
	}


	while($row){

		$s = "address=/{$row['url']}/$hostip\n";
		fwrite($f, $s);
		$row = $res->fetch();
	}

	if($f!=FALSE) fclose($f);

}


?>
