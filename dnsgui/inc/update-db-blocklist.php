<?php

require('global-var-inc.php');

//CREATE TABLE "blocklist"("url" varchar(256) primary key not null, "op" int not null);

global $adlistfile;
global $adlistCustomfile;


global $dbfile;
global $tblBlk;
global $colUrl;
global $colOp;
global $eol;


$db = null;
$adUrl = Array();
$adCustom = Array();

$adCustom = file($adlistCustomfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$adCustomCount = count($adCustom);

$adUrl = file($adlistfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$adUrlCount = count($adUrl);


try {

$db = new PDO('sqlite:' . $dbfile);

echo "SUCESSFULLY OPEN DATABASE FILE!{$eol}";

}
catch(PDOException $e){
	echo "FAIL TO OPEN DATABASE FILE!{$eol}" . $e->getMessage();
	exit();
}

$newEntry = 0;
for($i=0; $i<$adCustomCount; $i++){

	if($adCustom[$i][0]=='#'){
		$adCustom[$i] = null;
		continue;
	}

	$url = substr($adCustom[$i], 9, strlen($adCustom[$i]) - 21);

	$q1  = "INSERT INTO {$tblBlk}({$colUrl}, {$colOp}) VALUES('{$url}', 2)";

	$rowEffected = $db->exec($q1);

	if($rowEffected > 0) $newEntry++;
	else echo "Ignored entry: {$url}, op=2{$eol}";

    $adCustom[$i] = null;
    flush();
    ob_flush();

}

echo "sucessfully entered {$newEntry} entries from custom list.{$eol}";

//exit();

$newEntry = 0;

for($i=0; $i<$adUrlCount; $i++){

	if($adUrl[$i][0]=='#'){
		$adUrl[$i] = null;
		continue;
	}

	$url = substr($adUrl[$i], 9, strlen($adUrl[$i]) - 21);

	$q1  = "INSERT INTO {$tblBlk}({$colUrl}, {$colOp}) VALUES('{$url}', 1)";

	$rowEffected = $db->exec($q1);

	if($rowEffected > 0) $newEntry++;
	else echo "Ignored entry: {$url}, op=1{$eol}";

    $adUrl[$i] = null;
    flush();
	ob_flush();
}

echo "sucessfully entered {$newEntry} entries from auto list.{$eol}";

?>
