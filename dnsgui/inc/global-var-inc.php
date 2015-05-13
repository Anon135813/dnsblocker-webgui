<?php

// list of all globally used variables.

// ip address of the host that is running the dnsmasq and webgui
// this will be use to generate the block list ".conf" files
$GLOBALS['hostaddress'] = '192.168.1.8';

// database filename and location path.
$GLOBALS['dbpath'] 		= '/var/www/dnsgui/inc/db/';
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


// filename and location of the auto-list
$GLOBALS['adlistpath']		= '/etc/dnsmasq.d/';
$GLOBALS['adlistfilename']	= 'adlist.conf';
$GLOBALS['adlistfile']		= $GLOBALS['adlistpath'] . $GLOBALS['adlistfilename'];

// filename and the location of the custom-list
$GLOBALS['adlistCustompath']		= '/etc/dnsmasq.d/';
$GLOBALS['adlistCustomfilename']	= 'adlist-custom.conf';
$GLOBALS['adlistCustomfile']		= $GLOBALS['adlistCustompath'] . $GLOBALS['adlistCustomfilename'];


// sometimes helps when php project are cross platform.
$GLOBALS['eol'] = "\n";

?>
