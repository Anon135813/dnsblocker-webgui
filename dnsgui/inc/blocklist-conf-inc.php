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



function ImportConf($fn, $op){

	ini_set('max_execution_time', 300);

	$scriptTime = microtime(true);


	global $dbfile;
	global $tblBlk;
	global $colUrl;
	global $colOp;
	global $eol;

	$msg = "Starting Import 'op'={$op}, file: {$fn}{$eol}";

	$db = null;
	$lines = null;
	$newEntry = 0;
	$ignoredEntry = 0;

	// read content from file into an array
	$lines = file($fn, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$lineCount = count($lines);


	// calculate offset of url start and end in string
	$pos = FALSE; // start position of url after "address=\"
	$len = FALSE; // str length of "\IP"

	for ($i = 0; $i<$lineCount; $i++){

		if($lines[$i][0]!='#'){

			$pos = strpos($lines[$i], '/');

			if($pos===FALSE){
				$pos = FALSE;
				$len = FALSE;
				continue;
			}

			$len = strrpos($lines[$i], '/');

			if($len===FALSE){
				$pos = FALSE;
				$len = FALSE;
				continue;
			}

			$pos = $pos + 1;

			// $pos = char length of "address=\"
			// strlen($lines[$i]) - $len  = char length of hostip
			// so $len = length of "address=\\hostip"
			$len = $pos + (strlen($lines[$i]) - $len);

			// echo "<pre>line={$lines[$i]}\npos={$pos}\nlen={$len}\n";
			// exit();

			break;
		}
		else continue;
	}

	if($pos === FALSE || $len === FALSE){

		$msg .= "Cant find the char '\\' in file{$eol}";

		return $msg;
	}

	try {
		$db = new PDO('sqlite:' . $dbfile);
		//$msg .= "SUCESSFULLY OPEN DATABASE FILE!{$eol}";
	}
	catch(PDOException $e){
		echo "FAIL TO OPEN DATABASE FILE!{$eol}" . $e->getMessage();
		exit();
	}


	for($i=0; $i<$lineCount; $i++){

		if($lines[$i][0]=='#'){
			$lines[$i] = null;
			continue;
		}

		$url = substr($lines[$i], $pos, strlen($lines[$i]) - $len);

		// Check if its a duplicate entry or a parent domain block entry already exist
		$q1 = "SELECT COUNT({$colUrl}) AS 'ENTRYCOUNT', {$colUrl} FROM {$tblBlk} WHERE '.{$url}' LIKE '%.' || {$colUrl}";

		$res = $db->query($q1);

		if($res==false){
			// SOMETHING WRONG. SCRIPT SHOULD NOT PROCEED
			print_r($db->errorInfo());
			echo "{$eol}QUERY STRING: {$q1}{$eol}";
			exit();
		}



		$row = $res->fetch();

		// if an existing block entry is found
		if($row['ENTRYCOUNT']>0){
			$msg .= "Duplicate or redundent entry. Entry Ignored: [{$url}] Conflicts With: [{$row['url']}]{$eol}";
			$ignoredEntry++;
			continue;
		}

		$q1  = "INSERT INTO {$tblBlk}({$colUrl}, {$colOp}) VALUES('{$url}', {$op})";

		$rowEffected = $db->exec($q1);

		if($rowEffected > 0) $newEntry++;
		else{
			// SOMETHING WRONG. SCRIPT SHOULD NOT PROCEED
			print_r($db->errorInfo());
			echo "{$eol}QUERY STRING: {$q1}{$eol}";
			exit();
		}

		$lines[$i] = null;
	}

	$scriptTime = round((microtime(true)-$scriptTime),4);

	$msg .= "Completed Import 'op'={$op}, file: {$fn}{$eol}";
	$msg .= "Entries Entred: {$newEntry} Entries{$eol}";
	$msg .= "Entries Ignored: {$ignoredEntry} Entries{$eol}";
	$msg .= "Time Spend: {$scriptTime} Seconds{$eol}";

	return $msg;
}



function ImportConfAutolist(){
	global $adlistfile;
	return ImportConf($adlistfile, 1);
}

function ImportConfCustomlist(){
	global $adlistCustomfile;
	return ImportConf($adlistCustomfile, 2);
}

function ImportConfBothlist(){

	global $adlistfile;
	global $adlistCustomfile;

	$msg  = ImportConf($adlistfile, 1);
	$msg .= ImportConf($adlistCustomfile, 2);

	return $msg;
}


?>
