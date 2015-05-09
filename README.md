# dnsblocker-webgui
This is an attempt to create a WebGui for blocking and unblocking network traffic via intercepting DNS request using the linux daemon dnsmasq.

<ul>TODO List:
	<li>Issue 1 and 2</li>
	<li>
		<ul>Write a how to guide for the follwoing:
			<li>Installing dnsmasq, lighttpd, php-cgi and (sqlite3 optional)</li>
			<li>Setting up the webgui.</li>
			<li>[DONE] Createing a small (5/10MB) ramdrive for Raspberry PI users for dnsmasq continuous log.</li>
			<li>[DONE] Config dnsmasq to log query.</li>
			<li>[DONE] Setting up cron task to run dnsgui/inc/update-db-dnslog.php to update the dnslog.db every 30 min.</li>
			<li>Add a sudoer entry for www-data user for "(usr/sbin?)/dnsblocker-phpsudotask.sh" in </li>
			<li></li>
		</ul>
	</li>
	<li></li>
	<li></li>
</ul>

<h1>HOW TO GUIDE:</h1>

<h2>Ramdisk and dnsmasq log file</h2>
<p>dnsmasq daemon need to be configured so it logs all queries. This can be done by editing the /etc/dnsmasq.conf file. the follwing two line needed to be added.</p>
<pre>
log-queries
log-facility=/mnt/ramdisk/dnsmasq-log.txt
</pre>

<p>It is possible to create a small ramdrive (about 5 or 10MB) and configure the dnsmasq daemon write its logfile into the ramdrive using the "log-facility=" option in /etc/dnsmasq.conf.
A ramdrive can be created using command</p><pre> "mount -t tmpfs -o size=10M tmpfs /mnt/ramdisk"</pre>


<h2>Cron task and Updating the dnsgui/inc/dnslog.db database from dnsmasq log file</h2>
I have wrote an php script (/dnsgui/inc/update-db-dnslog.php) that reads and does some analysis and enter data into the dnslog table of the database. This task can be automated by creating a corn task.
crontab can be edited by typing "crontab -e" in terminal window.
<pre>*/30 * * * * /usr/sbin/update-dns-db.sh</pre>
Adding the above line at the bottom of the crontab file will run the script (update-dns-db.sh) every 30 minutes.
Writting the follwoing lines in the /usr/sbin/update-dns-db.sh file will cause the dnslog.db to be updated every 30 minues. update-dns-db.sh will also empty the dnsmasq logfile so that it does not keep on growing. This script will also recode the output message from the update-db-dnslog.php into the logfile which can be used for future troubleshooting.
<pre>
#!/bin/bash
# nowtime=$(date +%d-%m-%y-%H%M)
# /bin/cp /mnt/ramdisk/dnsmasq-log.txt /mnt/ramdisk/dnsmasq-log-$nowtime.bak
/usr/bin/php-cgi /var/www/dnsgui/inc/update-db-dnslog.php > /mnt/ramdisk/tmp.txt
/bin/date > /mnt/ramdisk/dnsmasq-log.txt
/bin/cat /mnt/ramdisk/tmp.txt >> /mnt/ramdisk/dnsmasq-log.txt
rm /mnt/ramdisk/tmp.txt
</pre>
<p>Once the data is in the dnslog.db, viewlog.php and index.php act as an interactive GUI to view and manipulate the dnslog.db.</p>
<h2>About dnsgui/inc/dnslog.db</h2>
<p>dnslog.db has the following 2 tables:</p>

<table border=1>
<tr><td colspan=6>dnslog</td></tr>
<tr>
<td>hit</td>
<td>url</td>
<td>op</td>
<td>t1</td>
<td>t2</td>
<td>ip</td>
</tr>
</table>

<table border=1>
<tr><td colspan=6>blocklist</td></tr>
<tr>
<td>url</td>
<td>op</td>
</tr>
</table>

<p>
dnslog table stores all the processed loged from dnsmasq logfile.
blocklist table stores list of all the urls that dnsmasq blocks using the .conf files in "/etc/dnsmasq.d/". the .conf file in "/etc/dnsmasq.d/" are generated based on this table.

hit = accumulated hit count
url = dns query url in dnslog table. address to be blocked in blocklist table.
op = blocked option (0=unblocked, 1=blocked by auto-list, 2=blocked by custom list)
t1 = first time and date the dns url query was requested.
t2 = last time and date the dns url query was requested.
ip = ip address of the last holt requested dns query about the given url
</p>
