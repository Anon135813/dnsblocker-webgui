# dnsblocker-webgui
<p>This is an attempt to create a WebGui for blocking and unblocking network traffic via intercepting DNS request using the linux daemon dnsmasq.</p>
<p>TODO List:</p>
<ul>
	<li>Issue 1 and 2</li>
	<li>Write a how to guide for the follwoing:
		<ul>
			<li>Installing dnsmasq, lighttpd, php-cgi and (sqlite3 optional)</li>
			<li>Setting up the webgui.</li>
			<li>[DONE] Createing a small (5/10MB) ramdrive for Raspberry PI users for dnsmasq continuous log.</li>
			<li>[DONE] Config dnsmasq to log query.</li>
			<li>[DONE] Setting up cron job to run dnsgui/inc/update-db-dnslog.php to update the dnslog.db every 30 min.</li>
			<li>Add a sudoer entry for www-data user for "(usr/sbin?)/dnsblocker-phpsudotask.sh" in </li>
		</ul>
	</li>
</ul>
<h1>HOW TO GUIDE:</h1>
<h2>Ramdisk and dnsmasq log file</h2>
<p>dnsmasq daemon need to be configured so that it logs all queries. This can be done by editing the /etc/dnsmasq.conf file. the follwing two line needed to be added.</p>
<pre>
log-queries
log-facility=/mnt/ramdisk/dnsmasq-log.txt
</pre>
<p>It is possible to create a small ramdrive (about 5 or 10MB) and configure the dnsmasq daemon write its logfile into the ramdrive using the "log-facility=" option in /etc/dnsmasq.conf.
A ramdrive can be created by using the command:</p><pre> "mount -t tmpfs -o size=10M tmpfs /mnt/ramdisk"</pre>
<h2>Cron job and Updating the dnsgui/inc/dnslog.db database from dnsmasq log file</h2>
<p>I have wrote a php script (/dnsgui/inc/update-db-dnslog.php) that reads and does some analysis and enter data into the dnslog table of the database. This task can be automated by creating a cron job.
crontab can be edited by typing "crontab -e" in terminal window.</p>
<pre>*/30 * * * * /usr/sbin/update-dns-db.sh</pre>
<p>Adding the above line at the bottom of the crontab file will run the script (update-dns-db.sh) every 30 minutes.
Writting the follwoing lines in the /usr/sbin/update-dns-db.sh file will cause the dnslog.db to be updated every 30 minues. update-dns-db.sh will also empty the dnsmasq logfile so that it does not keep on growing. This script will also recode the output message from the update-db-dnslog.php into the logfile which can be used for future troubleshooting.</p>
<pre>
#!/bin/bash
/usr/bin/php-cgi /var/www/dnsgui/inc/update-db-dnslog.php
</pre>
<p>Once the data is in the dnslog.db, viewlog.php and index.php act as an interactive GUI to view and manipulate the dnslog.db.</p>
<h2>About dnsgui/inc/dnslog.db</h2>
<p>dnslog.db has the following 2 tables:</p>
<pre>
+-------------------------------+
|    dnslog table               |
+-------------------------------+
| hit | url | op | t1 | t2 | ip |
+-------------------------------+

+-----------------+
| blocklist table |
+-----------------+
|   url  |   op   |
+-----------------+
</pre>
<p>"dnslog" table stores all the processed loged from dnsmasq logfile. "blocklist" table stores list of all the urls that dnsmasq blocks using the ".conf" files in "/etc/dnsmasq.d/". The ".conf" file in "/etc/dnsmasq.d/" are generated based on this table.</p>
<pre>
hit = accumulated hit count
url = dns query url in dnslog table. address to be blocked in blocklist table.
op = blocked option (0=unblocked, 1=blocked by auto-list, 2=blocked by custom list)
t1 = first time and date the dns url query was requested.
t2 = last time and date the dns url query was requested.
ip = ip address of the last holt requested dns query about the given url
</pre>

<h2>About the "dnsgui\" directory and setting up webgui</h2>
<pre>
dnsgui/
├── css
│   └── dnsblocker-webgui-style-01.css
├── img
│   ├── block-icon-color1.png
│   ├── checkbox-color1.gif
│   ├── circle-green1.png
│   ├── circle-red1.png
│   ├── search-icon-color2.png
│   └── unblock-icon-color1.png
├── inc
│   ├── dnslog.db
│   ├── global-var-inc.php
│   ├── update-blocklist-conf-files.php
│   ├── update-db-blocklist.php
│   └── update-db-dnslog.php
├── index.php
├── modlist.php
├── test.php
└── viewlog.php
</pre>
<p>The directory "dnsgui/" needs to be put inside your webservers "document-root" directory. In my case, on a default lighttpd install it was '/var/www'. You can find your document-root directory true location in /etc/lighttpd.d/lighttpd.conf file.</p>

<h2>About auto-list and custom-list:</h2>
<p>According to the /etc/dnsmasq.d/README file, dnsmasq by default will read all files in '/etc/dnsmasq.d/' directory. So it is possible to create just one single file with all the list of blocked domain addresses. But this webgui follows the original pi-hole convention and separates all the blocked domain addresses into two distinct list (auto-list and custom-list). According to the pi-hole convention, all the blocked domain addresses that are acquired as a part of automated script from web sources are stored in the adblock.conf (aka auto-list, Op=1 in webgui). All the blocked domain addresses added by the user manually goes into the adblock-custom.conf (aka custom-list. Op=2 in webgui). When webgui regenerates the '.conf' files, it appropriatly puts all the automically acquired blocked addresses into auto-list (can be any filename discribe in dnsgui/inc/global-var-inc.php) and puts all the manually blocked addresses into custom-list (can also be any filename discribe in dnsgui/inc/global-var-inc.php).</p>
<h2>About sudoer and fire permissions requirement for dnsgui:</h2>
<p>on a default lighttpt and php-cgi install dnsblocker-webgui runs as 'www-data' user under linux. In order for the webgui to carry out certain operation 'www-data' user will require read and write permissions to certain files. Without sufficient permissions those operations will fail.
<ul>
	<li>For almost all operation 'www-data' require both read and write permissions to “dnsgui/inc/dnslog.db” file.</li>
	<li>In order to regenerating '.conf' files in the '/etc/dnsmasq.d' directory, 'www-data' user needs read and write permissions to auto-list and custom-list '.conf' files.</li>
	<li>In order to empty the dnsmasq log file, 'www-data' user need read and write permissions to that file.</li>
</ul>
'www-data' user also required a sudoer entry with 'NOPASSWD' option to “dnsblocker-phpsudotask.sh” script file. Please read appropriate documents and guide to learn how to add sudoer entry into your linux. Generally it is does via the “visudo” command and by adding the following line. This sudoer entry will allow dnsgui to restart dnsmasq daemon as sudo from php scripts.</p>
<pre>www-data ALL=(ALL) NOPASSWD: /home/pi/dnsblocker-phpsudotask.sh</pre>





