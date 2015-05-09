# dnsblocker-webgui
This is an attempt to create a WebGui for blocking and unblocking network traffic via intercepting DNS request using the linux daemon dnsmasq.

TODO List:
1. Create a link for emptying dnslog table in index.php.

2. Write a how to guide for the follwoing:
2.1 Installing dnsmasq, lighttpd, php-cgi and (sqlite3 optional)
2.2 Setting up the webgui.
2.3 Create a small (5/10MB) ramdrive for Raspberry PI users for dnsmasq continuous log.
2.2 Config dnsmasq to log query.
2.3 Setting up corn task to run dnsgui/inc/update-db-dnslog.php to update the dnslog.db every 30 min.
2.4 Add a sudoer entry for www-data user for "dnsblocker-phpsudotask.sh" in (usr/sbin?)
2.5 Extend index.php to show linux system info, uptime, free mem etc

3. Upload the bash script files (dnsblocker-phpsudotask.sh, update-dns-db.sh etc)
