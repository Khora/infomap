# infomap
The infomap project aims to deliver information about social services in and around Athens to the people in need.
The database (a Google Spreadsheet) behind it can be edited by those having the edit URL.

# Deployment
Download the files from this repository according to the picture on any PHP webserver.
Do not include these files/folders:
 - INFOMAPS-MOCKS
 - .gitignore
 - README.MD

# cron job to periodically refresh the data cache
Place a shell script here: "/etc/cron.weekly"
Put this content in the shell script: "curl localhost/<<<path-to-infomap-system>>>/infomap.php?reload=true"
This requests the file with the reload cache flag enabled on a weekly basis.
In doing so, it refreshes the caches and speeds up the system for everyone else.

# Demo System
Access a demo system here: https://mydearfear.com/infomap/infomap.php
