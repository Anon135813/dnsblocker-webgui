<?php

require('global-var-inc.php');

// This function:
//         * readd the dnsmasq logfile.
//         * Processes all the dns query log:
//                * filters unwanted or bad queries
//                * calculates hit-count for each url in query
//                * calculates op value for each url in query
//         * enters the processed query log into dnslog table of db

function ImportDnsmasqLog(){

	// attempt to increase the php scripts execution time limit.
	// sometimes it may take as long as 60 seconds to process and
	// enter data from logfile to db. It depends on many factors such as
	// logfile size, blocklist table size, dnslog table size, if db
	// stored in sd card or not etc. Please note Apache server also
	// imposes its own limit outside php's own execution time.

	ini_set('max_execution_time', 300);

	$scriptTime = microtime(true);

	global $hostaddress;
	global $dnslogfile;
	global $dbfile;
	global $tblBlk;
	global $tblDns;
	global $colUrl;
	global $colT1;
	global $colT2;
	global $colHit;
	global $colIp;
	global $colOp;
	global $eol;

	$a1 = null;
	$a2 = null;
	$a3 = null;
	$db = null;

	$msg = '';
	$line = Array();
	$entry = Array();
	$newCount = 0;
	$updateCount = 0;

	$msg .= "==============================================={$eol}";
	$msg .= "\t update-db-dnslog.php Messages{$eol}";
	$msg .= "==============================================={$eol}";

	// Get log file size via ls -lh
	exec("ls -lh {$dnslogfile}", $a1);
	$a1  = preg_split('/[\s]+/', $a1[0]);
	$a1 = $a1[4];

	// Get log file total line count via wc -l
	exec("wc -l {$dnslogfile}", $a2);
	$a2 = preg_split('/[\s]+/', $a2[0]);
	$a2 = $a2[0];

	// Read query lines via grep
	exec("grep 'query' {$dnslogfile}", $line);

	// log file query lines count
	$a3 = count($line);


	$msg .= "Logfile Size: {$a1}{$eol}";
	$msg .= "Line Count (Total Lines): {$a2} Lines{$eol}";
	$msg .= "Line Count (Query Lines): {$a3} Lines{$eol}";


	// go through all the query lines from the logfile
	// generate a array of queris (url, hit, t1, t2).
	// if finds repeadted url, precalculate hit counts.
	// precalculating hit count will reduce the number of
	// insert statement and reduce slow database interaction.

	for($i=0; $i<$a3; $i++){

		$words = preg_split( '/[\s]+/' , $line[$i]);

		$processLine = FALSE;

		if(substr($words[4],0,5)=='query'){

			 $tm  = "$words[0] $words[1] $words[2]";
			 $url = $words[5];
			 $ip  = $words[7];

			 // below conditions are FILTERS.
			 // they mearly filters out unnecessory dnsrequest from
			 // getting into the database.

			 // not a real dns request.
			 if(strpos($url, '.')==FALSE) $processLine=FALSE;

			 //	these are dnsmasq hots own request.
			 else if($ip == $hostaddress) $processLine=FALSE;

			 // these are LAN only requests, notihng to do with internet
			 else if(substr($url, -5)=='.arpa') $processLine=FALSE;
			 else if(substr($url, -5)=='._tcp') $processLine=FALSE;

			 // strip out 'www.' if present at the begining of address.
			 // this will reduce some unnecessory duplicate entries.
			 else if(substr($url, 0, 4) == 'www.'){
				$url = substr($url, 4);
				$processLine = TRUE;
			 }

			 // precess all other lines
			 else $processLine = TRUE;
		}

		// Process the line if it is deemed as a valid query by above filters.
		if($processLine){

			// current number of entries in the array
			$entryCount = count($entry);

			$found = FALSE;

			// check to see if url already exist in the array
			for($j = 0; $j<$entryCount; $j++){

				// if url already exist, increase hit count, update t2 & ip
				if($entry[$j]['url']==$url){
					$entry[$j]['tm'] = $tm;
					$entry[$j]['ip'] = $ip;
					$entry[$j]['hit']++;
					$found = TRUE;
					break;
				}
			}

			// if its a new url and does not already exist in the array
			// enter the url and corresponding details (tm = both t1 & t2),
			// hit, ip
			if($found == FALSE){
				$entry[$entryCount]['url'] = $url;
				$entry[$entryCount]['ip']  = $ip;
				$entry[$entryCount]['tm']  = $tm;
				$entry[$entryCount]['hit'] = 1;
			}
		}

		// signal php that $line[$i] is no longer required,
		// free mem if possible.
		$line[$i] = null;
	}



	try {
		$db = new PDO('sqlite:' . $dbfile);
		$msg .= "SUCESSFULLY OPEN DATABASE FILE!{$eol}";
	}
	catch(PDOException $e){
		echo "FAIL TO OPEN DATABASE FILE!{$eol}" . $e->getMessage();
		exit();
	}


	// get the final entry count
	$entryCount = count($entry);

	// Check if entries already exsit in the database.
	// if exist update the hit, t2, ip
	// if does not exist enter the new entry (url, hit, t1, t2, ip)
	for($j = 0; $j<$entryCount; $j++){

		$url = $entry[$j]['url'];
		$ip  = $entry[$j]['ip'];
		$tm  = $entry[$j]['tm'];
		$hit = $entry[$j]['hit'];

		// sql query for checking if entry exist
		$q1  = "SELECT {$colHit}, COUNT({$colUrl}) AS 'EntryFound' FROM {$tblDns} WHERE {$colUrl}='{$url}' LIMIT 1";

		$res = $db->query($q1);

		if($res==false){
			// SOMETHING WRONG. SCRIPT SHOULD NOT PROCEED
			print_r($db->errorInfo());
			echo "{$eol}QUERY STRING: {$q2}{$eol}";
			exit();
		}


		$row = $res->fetch();

		// if entry exist in the database
		// accumulate hit-cout total hit = hit from db + hit from log
		if($row['EntryFound']>0){

			$row = null;
			$res = null;

			$hit =  $hit + $row['hit'];

			$q2  = "UPDATE {$tblDns} SET {$colHit}={$hit}, {$colT2}='{$tm}', {$colIp}='{$ip}'  WHERE {$colUrl}='{$url}'";

			$rowEffected = $db->exec($q2);

			if($rowEffected > 0){
				$updateCount++;
			}
			else{
				// SOMETHING WRONG. SCRIPT SHOULD NOT PROCEED
				print_r($db->errorInfo());
				echo "{$eol}QUERY STRING: {$q2}{$eol}";
				exit();
			}

		}
		// else entry doesnt exist, prepare and enter a new entry
		// total hit = hit from log
		// check blocklist table to see if any op is applicable to this url
		else{

			// hint php for memory cleanup and free resources
			$row = null;
			$res = null;

			// check the blocklist table to see if any op is applicable.
			$q2  = "SELECT COUNT({$colUrl}) AS 'EntryFound', {$colOp}, {$colUrl} FROM {$tblBlk} WHERE '{$url}' LIKE '%' || {$colUrl} LIMIT 1";

			$res = $db->query($q2);

			if($res==FALSE){
				// SOMETHING WRONG. SCRIPT SHOULD NOT PROCEED
				print_r($db->errorInfo());
				echo "{$eol}QUERY STRING: {$q2}{$eol}";
				exit();
			}

			$row = $res->fetch();

			if($row['EntryFound']>0) $op = $row['op'];
			else $op = 0;

			// hint php for memory cleanup and free resources
			$row = null;
			$res = null;

			// Enter new entry into the dnslog table of the database
			$q3  = "INSERT INTO {$tblDns}({$colUrl}, {$colT1}, {$colT2}, {$colIp}, {$colHit}, {$colOp}) VALUES('{$url}', '{$tm}', '{$tm}', '{$ip}', {$hit}, {$op})";

			$rowEffected = $db->exec($q3);

			if($rowEffected > 0){
				$newCount++;
			}
			else{
				// SOMETHING WRONG. SCRIPT SHOULD NOT PROCEED
				print_r($db->errorInfo());
				echo "{$eol}QUERY STRING: {$q2}{$eol}";
				exit();
			}

		}
	}


	$msg .= "New Record Entred = {$newCount}{$eol}Existing Record Updated = {$updateCount}{$eol}";

	$scriptTime = round((microtime(true)-$scriptTime),4);

	$msg .= "Time Spend To Process Logs: {$scriptTime} Seconds{$eol}";
	$msg .= "==============================================={$eol}";

	// Send all the messages as return
	return $msg;

}




// SQL SCHEMA FOR dnslog table
// CREATE TABLE "dnslog"("url" varchar(256) primary key not null, "t1" varchar(16), "t2" varchar(16), "ip" varchar(16), "hit" int not null, "op" int not null);

/*

	// FOLLWOING SECTION WILL BE USED IN A FUTURE FUCTION TO RECALCULATE OP

	if($newCount>0){

		ini_set('max_execution_time', 180);

		$qRestColOp = "UPDATE {$tblDns} SET {$colOp}=0";

		$qSubQuery = "SELECT {$tblBlk}.{$colOp} FROM {$tblBlk} WHERE {$tblDns}.{$colUrl} LIKE '%' || {$tblBlk}.{$colUrl}";
		$qUpdateColOp = "UPDATE {$tblDns} SET {$colOp}=({$qSubQuery}) WHERE EXISTS ({$qSubQuery})";

		$db->exec($qRestColOp);
		$db->exec($qUpdateColOp);

		echo "Updated {$colOp} based on {$tblBlk} table.{$eol}";

	}
*/





?>

