<?php

require('global-var-inc.php');


function db2conf($res, $fn){

	global $hostaddress;

	$row = $res->fetch();

	if($row!=FALSE){

		$f = fopen($fn, 'w');

		if($f==FALSE){

			echo "Unable to open file: {$fn}";
			exit();
		}
	}


	while($row){

		$s = "address=/{$row['url']}/${hostaddress}\n";
		fwrite($f, $s);
		$row = $res->fetch();
	}

	if($f!=FALSE) fclose($f);

}


function ExportConf($param){

	// if $param is: 1 export auto-list, if 2 export custom-list, if 3 export both list
	if($param<1 || $param>3) return;

	global $dbfile;
	global $adlistfile;
	global $adlistCustomfile;
	global $tblBlk;
	global $colUrl;
	global $colOp;
	global $eol;


	$db = null;

	try {

		$db = new PDO('sqlite:' . $dbfile);
		// echo "SUCESSFULLY OPEN DATABASE FILE!{$eol}";
	}
	catch(PDOException $e){
		echo "FAIL TO OPEN DATABASE FILE!{$eol}" . $e->getMessage();
		exit();
	}

	if($param==1 || $param==3){

		$q1  = "SELECT A.{$colUrl} FROM {$tblBlk} AS A WHERE A.{$colOp}=1 ORDER BY A.{$colUrl} ASC";

		$res = $db->query($q1);

		if($res==false){
			die(var_export($db->errorinfo(), TRUE));
		}

		db2conf($res, $adlistfile);
	}

	if($param==2 || $param==3){

		$q1  = "SELECT A.{$colUrl} FROM {$tblBlk} AS A WHERE A.{$colOp}=2 ORDER BY A.{$colUrl} ASC";

		$res = $db->query($q1);

		if($res==false){
			die(var_export($db->errorinfo(), TRUE));
		}

		db2conf($res, $adlistCustomfile);
	}

	$db = null;
}


function ExportConfAutolist(){   ExportConf(1); }
function ExportConfCustomlist(){ ExportConf(2); }
function ExportConfBothlist(){	 ExportConf(3); }



?>
