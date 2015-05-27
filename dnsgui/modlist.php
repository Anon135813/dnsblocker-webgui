<?php

require('./inc/global-var-inc.php');

function f2(){

	if(isset($_GET['u'])==FALSE || is_array($_GET['u'])==FALSE) return;

	$u=$_GET['u'];

	$umx = count($u);

	for($i=0; $i<$umx; $i++){

		echo $u[$i];
		echo "\n";

	}

}

function f3(){

	if(isset($_GET['a'])==FALSE){

		return;
	}
	else if($_GET['a']==1){

		 echo '<img src="./img/unblock-icon-color1.png" /> ';
		 echo 'Unblock the follwing URL.<br/>Remove them from all the block lists:';
	}
	else if($_GET['a']==2){

		echo '<img src="./img/block-icon-color1.png" /> ';
		echo 'Block the follwing URL.<br/>Add them into the custom block list:';

	}
	else if($_GET['a']==3){

		echo '<img src="./img/block-icon-color1.png" /> ';
		echo 'Remove the follwing urls from the dns log.<br/>You can use the SQL LIKE wildcards including "%".';
	}

}

function f4(){

	$eol = "\n";

	$h1 = '<tr><td>';


	if(isset($_GET['a'])){

		if($_GET['a']==2){

			$h1 .= $eol;
			$h1 .= '<input type="checkbox" id="chkbxid1" name="a2c" value="1" checked="checked" />';
			$h1 .= $eol;
			$h1 .= '<label for="chkbxid1">if exists in auto-list, move to custom-list</label>';

		}

		$h1 .= $eol;
		$h1 .= '<input type="hidden" name="a1" value="';
		$h1 .= $_GET['a'];
		$h1 .= '" />';

	}
	else{

		$h1 .= $eol;
		$h1 .= '<input type="hidden" name="a1" value="0" />';
	}

	$h1 .= $eol;
	$h1 .= '</td></tr>';

	echo $h1;

}


///// MAIN CODE ////////


if(isset($_POST['submit']) && $_POST['submit']=='Apply Changes'){

	if(isset($_POST['a1']) && isset($_POST['u1']) && strlen($_POST['u1'])>0){


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

		$db = null;

		// NEED CLEANUP
		$urlArray = preg_split('/[\n | \n\r]+/', $_POST['u1']);
		$cleanUrl = $urlArray[0];


		try {

			$db = new PDO('sqlite:' . $dbfile);
			//echo "SUCESSFULLY OPEN DATABASE FILE!{$eol}";
		}
		catch(PDOException $e){
			echo "FAIL TO OPEN DATABASE FILE!{$eol}" . $e->getMessage();
			exit();
		}

		$db->query('PRAGMA synchronous = OFF');
		$db->query('PRAGMA journal_mode = OFF');
		$db->query('BEGIN TRANSACTION');


		if($_POST['a1']==1 || $_POST['a1']==2){

			$q1  = "SELECT * FROM {$tblBlk} AS A WHERE '{$cleanUrl}' LIKE '%' || A.{$colUrl} LIMIT 1";

			$res = $db->query($q1);

			if($res==false){
				die(var_export($db->errorinfo(), TRUE));
			}

			$row = $res->fetch();


			if($_POST['a1']==1){
				// unblock entries

				if($row == FALSE){
					// nothing to remove;
					echo 'nothing to remove';
					exit();

				}

				$q2  = "DELETE FROM {$tblBlk} WHERE {$colUrl}='{$row['url']}'";
				$q3  = "UPDATE {$tblDns} SET {$colOp}=0, {$colHit}=1, {$colT1}={$colT2} WHERE {$colUrl} LIKE '%{$row['url']}'";

				echo "{$row['url']} entry has been removed from the database. if you regenerate the dnsmasq 'conf' files and restart the dnsmasq service, the block will be lifted for this entry.";

			}
			else if($_POST['a1']==2){
				// block entries

				if($row == FALSE){
					// add the entry;
					$q2  = "INSERT INTO {$tblBlk}({$colUrl}, {$colOp}) VALUES('{$cleanUrl}', 2)";
					$q3  = "UPDATE {$tblDns} SET {$colOp}=2, {$colHit}=1, {$colT1}={$colT2} WHERE {$colUrl} LIKE '%{$cleanUrl}'";

				}
				else{

					if($row['op']==2){

						echo 'no change necessory.';
						exit();

					}

					// update entry to custome list
					$q2  = "UPDATE {$tblBlk} SET {$colOp}=2 WHERE {$colUrl}='{$row['url']}'";
					$q3  = "UPDATE {$tblDns} SET {$colOp}=2 WHERE {$colUrl} LIKE '%{$row['url']}'";

				}


				echo "<pre>{$row['url']} entry has been added from the database. if you regenerate the dnsmasq 'conf' files and restart the dnsmasq service, all URL matching the entry will be blocked.</pre>";

			}

			$db->exec($q2);
			$db->exec($q3);

			$row = null;
			$res = null;

		}
		else if($_POST['a1']==3){

			$q1  = "SELECT {$colUrl} FROM {$tblDns} WHERE {$colUrl} LIKE '{$cleanUrl}'";


			$res = $db->query($q1);

			if($res==false){
				die(var_export($db->errorinfo(), TRUE));
			}

			$row = $res->fetch();
			$urlList = '';
			$urlCount = 0;

			while($row != FALSE){
				$urlList .= $row['url'];
				$urlList .= $eol;
				$urlCount++;
				$row = $res->fetch();
			}

			$q2  = "DELETE FROM {$tblDns} WHERE {$colUrl} LIKE '{$cleanUrl}'";


			echo "<pre>The following {$urlCount} url entries has been removed from 'dnslog' table as a match for '{$cleanUrl}'.\n\n{$urlList}</pre>";

			$db->exec($q2);

			$row = null;
			$res = null;

		}

		$db->query('END TRANSACTION');
		if($_POST['a1']==3) $db->query('VACUUM');
		$db  = null;

		exit();

	}
	else{

		echo 'Invalid url param!';
		exit();

	}
}





?>
<!DOCTYPE html>
<html>
<head>
<meta name = "viewport" content = "width = 420">
<title>MOD LIST</title>
<link rel="stylesheet" type="text/css" media="all" href="./css/dnsblocker-webgui-style-01.css" />
</head>
<body>
<form  action="modlist.php" method="POST">
<table class="t1">
<tr><td>
	<label for="txid1"><?php f3(); ?></label>
</td></tr>
<tr><td>
	<textarea id="txid1" name="u1"><?php f2(); ?></textarea>
</td></tr>
<?php f4(); ?>
<tr><td>
	<input type="submit" name="submit" value="Apply Changes" />
</td></tr>
</table>
</form>
</body>
</html>
