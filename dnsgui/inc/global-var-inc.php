<?php

// list of all globally used variables.

// ip address of the host that is running the dnsmasq and webgui
// this will be use to generate the block list ".conf" files
$GLOBALS['hostaddress'] = '192.168.1.8';

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

// database filename and location path.
$GLOBALS['dbfile'] 	= '/var/www/dnsgui/inc/db/dnslog.db';

// dnsmasq logfile name and location path.
$GLOBALS['dnslogfile']	= '/mnt/ramdisk/dnsmasq-log.txt';

// dnsblocker-phpsudotask.sh script location
$GLOBALS['phpsudotaskfile'] = '/home/pi/dnsblocker-phpsudotask.sh';


// filename and location of the auto-list
$GLOBALS['adlistfile'] = '/etc/dnsmasq.d/adlist.conf';

// filename and the location of the custom-list
$GLOBALS['adlistCustomfile'] = '/etc/dnsmasq.d/adlist-custom.conf';


// location for php session file/server cockie store
$GLOBALS['sessionPath'] = '/mnt/ramdisk/';

// sometimes helps when php project are cross platform.
$GLOBALS['eol'] = "\n";

// SQL SCHEMA FOR dnslog and blocklist table
// CREATE TABLE "blocklist"("url" varchar(256) primary key not null, "op" int not null);
// CREATE TABLE "dnslog"("url" varchar(256) primary key not null, "t1" varchar(16), "t2" varchar(16), "ip" varchar(16), "hit" int not null, "op" int not null);


?>
