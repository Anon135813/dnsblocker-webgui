<?php

require('update-db-dnslog-inc.php');
require('global-var-inc.php');

global $dnslogfile;

$msg = ImportDnsmasqLog();
$f = fopen($dnslogfile, 'w');
if($f!=FALSE){
	fwrite($f, $msg);
	fclose($f);
}

?>

