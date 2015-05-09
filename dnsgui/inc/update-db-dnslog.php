<?php
$scriptTime = microtime(true);

require('global-var-inc.php');

global $dbfile;
global $dnslogfile;
global $tblBlk;
global $tblDns;
global $colUrl;
global $colT1;
global $colT2;
global $colHit;
global $colIp;
global $colOp;
global $eol;



//CREATE TABLE "dnslog"("url" varchar(256) primary key not null, "t1" varchar(16), "t2" varchar(16), "ip" varchar(16), "hit" int not null, "op" int not null);

$db = null;
$newCount = 0;
$updateCount = 0;

$line = Array();
$entry = Array();
$logsize = Array();



try {

$db = new PDO('sqlite:' . $dbfile);
echo "==============================================={$eol}";
echo "\tupdate-db-dnslog.php Messages{$eol}";
echo "==============================================={$eol}";

echo "SUCESSFULLY OPEN DATABASE FILE!{$eol}";

}
catch(PDOException $e){
	echo "FAIL TO OPEN DATABASE FILE!{$eol}" . $e->getMessage();
	exit();
}


exec("ls -vsh {$dnslogfile}", $logsize);
exec("grep 'query' {$dnslogfile}", $line);

$n = count($line);

echo "ls: {$logsize[0]}{$eol}line count: {$n} lines{$eol}";

for($i=0; $i<$n; $i++){

    $words = preg_split( '/[\s]+/' , $line[$i]);

    $processLine = FALSE;

	if(substr($words[4],0,5)=='query'){

		 $tm = "$words[0] $words[1] $words[2]";
		 $url = $words[5];
		 $ip = $words[7];

		 if(strpos($url, '.')==FALSE) $processLine=FALSE;
		 else if($ip == '192.168.1.8') $processLine=FALSE;
		 else if(substr($url, 0, -5)=='.arpa') $processLine=FALSE;
		 else if(substr($url, 0, -5)=='._tcp') $processLine=FALSE;
		 else if(substr($url, 0, 4) == 'www.'){

			$url = substr($url, 4);
			$processLine = TRUE;
		 }
		 else $processLine = TRUE;
    }

    if($processLine){

		$entryCount = count($entry);

		$found = FALSE;

		for($j = 0; $j<$entryCount; $j++){

			if($entry[$j]['url']==$url){
				$entry[$j]['tm']  = $tm;
				$entry[$j]['ip'] = $ip;
				$entry[$j]['hit']++;
				$found = TRUE;
				break;
			}
		}

		if($found == FALSE){
			$entry[$entryCount]['url'] = $url;
			$entry[$entryCount]['ip']  = $ip;
			$entry[$entryCount]['tm']  = $tm;
			$entry[$entryCount]['hit'] = 1;
		}
	}

	$line[$i] = null;
}



$entryCount = count($entry);

for($j = 0; $j<$entryCount; $j++){

	$url = $entry[$j]['url'];
	$ip  = $entry[$j]['ip'];
	$tm  = $entry[$j]['tm'];
	$hit = $entry[$j]['hit'];


	$q2  = "SELECT {$colHit} FROM {$tblDns} WHERE {$colUrl}='{$url}' LIMIT 1";

	$res = $db->query($q2);

	if($res==false){
		die(var_export($db->errorinfo(), TRUE));
	}


	$row = $res->fetch();

	if($row != FALSE){
		$c = $row['hit'] + $hit;
	}
	else $c=$hit;

	$row = null;
	$res = null;

	if($c > $hit){

		$q3  = "UPDATE {$tblDns} SET {$colHit}={$c}, {$colT2}='{$tm}', {$colIp}='{$ip}'  WHERE {$colUrl}='{$url}'";

		$rowEffected = $db->exec($q3);

		if($rowEffected > 0){
			$updateCount++;
		}
		else{
			echo "Line number: {$i}{$eol}";
			print_r($db->errorInfo());
			echo "{$eol}{$q3}{$eol}";
		}
	}
	else{

		$q1  = "INSERT INTO {$tblDns}({$colUrl}, {$colT1}, {$colT2}, {$colIp}, {$colHit}, {$colOp}) VALUES('{$url}', '{$tm}', '{$tm}', '{$ip}', {$c}, 0)";

		$rowEffected = $db->exec($q1);

		if($rowEffected > 0){
			$newCount++;
		}
		else{
			echo "Line number: {$i}{$eol}";
			print_r($db->errorInfo());
			echo "{$eol}{$q3}{$eol}";
		}
	}
}

echo "new record = {$newCount}{$eol}record updated = {$updateCount}{$eol}";



if($newCount>0){

	ini_set('max_execution_time', 180);

	$qRestColOp = "UPDATE {$tblDns} SET {$colOp}=0";

	$qSubQuery = "SELECT {$tblBlk}.{$colOp} FROM {$tblBlk} WHERE {$tblDns}.{$colUrl} LIKE '%' || {$tblBlk}.{$colUrl}";
	$qUpdateColOp = "UPDATE {$tblDns} SET {$colOp}=({$qSubQuery}) WHERE EXISTS ({$qSubQuery})";

	$db->exec($qRestColOp);
	$db->exec($qUpdateColOp);

	echo "Updated {$colOp} based on {$tblBlk} table.{$eol}";

}

$scriptTime = round((microtime(true)-$scriptTime),4);

echo "php script runtime: {$scriptTime} seconds{$eol}";
echo "==============================================={$eol}";

?>

