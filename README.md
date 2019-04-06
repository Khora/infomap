# infomap
The infomap project aims to deliver information about social services in and around Athens to the people in need.
The database (a Google Spreadsheet) behind it can be edited by those having the edit URL.

# Deployment
Download the files from this repository according to the picture on any PHP webserver.
Do not include these files/folders:
 - INFOMAPS-MOCKS
 - .gitignore
 - README.MD
 
# Configuration
Create a folder called "config" on the top level where the website is deployed.
Create the following files in it:
 - arabicGid.txt
 - englishGid.txt
 - farsiGid.txt
 - frenchGid.txt
 - greekGid.txt
 - kurdishGid.txt
 - mapboxApiToken.txt
 - mapQuestApiKey.txt
 - pdfShiftIoApiKey.txt
 - spreadsheetId.txt
 - urduGid.txt
## arabicGid.txt
Put in the sheet ID for the "Arabic" tab of the Google Spreadsheet containing the data for the infomap.
e.g. "272972858"
## englishGid.txt
Put in the sheet ID for the "English" tab of the Google Spreadsheet containing the data for the infomap.
e.g. "0"
## farsiGid.txt
Put in the sheet ID for the "Farsi" tab of the Google Spreadsheet containing the data for the infomap.
e.g. "2092257293"
## frenchGid.txt
Put in the sheet ID for the "French" tab of the Google Spreadsheet containing the data for the infomap.
e.g. "2011609971"
## greekGid.txt
Put in the sheet ID for the "French" tab of the Google Spreadsheet containing the data for the infomap.
e.g. "1538519839"
## kurdishGid.txt
Put in the sheet ID for the "French" tab of the Google Spreadsheet containing the data for the infomap.
e.g. "1847047387"
## mapboxApiToken.txt
Put in a mapbox API token so the system can use the mapbox API.
e.g. "pk.r53IjoiY2FlemUiLCIfaTbXo8ptdXp6d3QyMGpweDN3bzhweTZ5IjqqXpG7.Si72Tah5nyXFljhdfiSY9yg"
## mapQuestApiKey.txt
Put in a MapQuest API token so the system can use the MapQuest API.
e.g. "3AAl4n9UOgMZB7dBO8hj1igYC4JZwwKh"
## pdfShiftIoApiKey.txt
e.g. "13e86817fee24b56b932a1585ca3e96a"
## spreadsheetId.txt
Put in the Google Spreadsheet ID containing the data for the infomap.
e.g. "1O1k_JJMitnFik1h3s5eiA6VjMvuejsqCndJ_ETL8FZd"
## urduGid.txt
Put in the sheet ID for the "French" tab of the Google Spreadsheet containing the data for the infomap.
e.g. "1531450539"

# cron job to periodically refresh the data cache
Place a shell script here: "/etc/cron.weekly"
Put this content in the shell script: "curl localhost/<<<path-to-infomap-system>>>/infomap.php?reload=true"
This requests the file with the reload cache flag enabled on a weekly basis.
In doing so, it refreshes the caches and speeds up the system for everyone else.

# Demo System
Access a demo system here: https://mydearfear.com/infomap/infomap.php
