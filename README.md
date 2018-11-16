# infomap
The infomap project aims to deliver information about social services in Athens to the people in need. The database (a Google Spreadsheet) behind it can be edited by those having the edit URL.

# Deployment
Download the zipped repository from here and place it on any PHP-enabled webserver. Do not upload the gitignore, README, download and  files and folders to the webserver.

# cron job to periodically refresh the data cache
Place a shell script here: "/etc/cron.weekly"
Put this content in the shell script: "curl localhost/<<<path-to-infomap-system>>>/infomap.php?reload=true"
This requests the file with the reload cache flag enabled on a weekly basis.

# Demo System
Access a demo system here: https://mydearfear.com/infomap/infomap.php