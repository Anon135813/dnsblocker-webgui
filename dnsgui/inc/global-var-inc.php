<?php

// list of all globally used variables.

// database filename and location path.
$GLOBALS['dbpath'] 		= '/var/www/dnsgui/inc/';
$GLOBALS['dbfilename'] 	= 'dnslog.db';
$GLOBALS['dbfile'] 		= $GLOBALS['dbpath'] . $GLOBALS['dbfilename'];

// database table names
$GLOBALS['tblBlk'] 	= '"blocklist"';
$GLOBALS['tblDns']	= '"dnslog"';

// database table column names
$GLOBALS['colUrl']	= '"url"';
$GLOBALS['colT1']	= '"t1"';
$GLOBALS['colT2']	= '"t2"';
$GLOBALS['colHit']	= '"hit"';
$GLOBALS['colIp']	= '"ip"';
$GLOBALS['colOp']	= '"op"';


// dnsmasq logfile name and location path.
$GLOBALS['dnslogpath'] 		= '/mnt/ramdisk/';
$GLOBALS['dnslogfilename'] 	= 'dnsmasq-log.txt';
$GLOBALS['dnslogfile']	 	= $GLOBALS['dnslogpath'] . $GLOBALS['dnslogfilename'];

// dnsblocker-phpsudotask.sh script location
$GLOBALS['phpsudotaskpath'] 	= '/home/pi/';
$GLOBALS['phpsudotaskfilename'] = 'dnsblocker-phpsudotask.sh';
$GLOBALS['phpsudotaskfile'] 	= $GLOBALS['phpsudotaskpath'] . $GLOBALS['phpsudotaskfilename'];


$GLOBALS['adlistpath']		= '/etc/dnsmasq.d/';
$GLOBALS['adlistfilename']	= 'adlist.conf';
$GLOBALS['adlistfile']		= $GLOBALS['adlistpath'] . $GLOBALS['adlistfilename'];


$GLOBALS['adlistCustompath']		= '/etc/dnsmasq.d/';
$GLOBALS['adlistCustomfilename']	= 'adlist-custom.conf';
$GLOBALS['adlistCustomfile']		= $GLOBALS['adlistCustompath'] . $GLOBALS['adlistCustomfilename'];



// sometimes helps when php project are cross platform.
$GLOBALS['eol'] = "\n";

?>
