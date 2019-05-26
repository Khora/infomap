# infomap
The infomap project aims to deliver information about social services in and around Athens to the people in need.
The database (a Google Spreadsheet) behind it can be edited by those having the edit URL.

# Deployment
Create a target folder on the PHP enabled webserver on which the system shall run.
Place that folder under where files are served by the webserver so it is accessible via http.
Name that folder "infomap".
Create a folder called "downloads" within that "infomap" folder and make the "downloads" folder writeable by the webserver (via chmod).

order deny,allow
deny from all

Download the files from this repository (Khora/infomap) and place them in the newly created "infomap" folder.
Do not include these files/folders:
 - INFOMAPS-MOCKS
 - .gitignore
 - README.MD
 
# Configuration
Create a folder called "config" in the "infomap" folder.
Create the following files in it:
 - arabicGid.php
 - englishGid.php
 - farsiGid.php
 - frenchGid.php
 - greekGid.php
 - kurdishGid.php
 - mapboxApiToken.php
 - mapQuestApiKey.php
 - pdfShiftIoApiKey.php
 - spreadsheetId.php
 - urduGid.php
 - emailAddressesIncorrectData.php
 - senderEmailAddress.php
## arabicGid.php
Put in the sheet ID for the "Arabic" tab of the Google Spreadsheet containing the data for the infomap.
e.g. &lt;?php $arabicGid = "272972858"; ?&gt;
## englishGid.php
Put in the sheet ID for the "English" tab of the Google Spreadsheet containing the data for the infomap.
e.g. &lt;?php $englishGid = "0"; ?&gt;
## farsiGid.php
Put in the sheet ID for the "Farsi" tab of the Google Spreadsheet containing the data for the infomap.
e.g. &lt;?php $farsiGid = "2092257293"; ?&gt;
## frenchGid.php
Put in the sheet ID for the "French" tab of the Google Spreadsheet containing the data for the infomap.
e.g. &lt;?php $frenchGid = "2011609971"; ?&gt;
## greekGid.php
Put in the sheet ID for the "Greek" tab of the Google Spreadsheet containing the data for the infomap.
e.g. &lt;?php $greekGid = "1538519839"; ?&gt;
## kurdishGid.php
Put in the sheet ID for the "Kurdish" tab of the Google Spreadsheet containing the data for the infomap.
e.g. &lt;?php $kurdishGid = "1847047387"; ?&gt;
## mapboxApiToken.php
Put in a mapbox API token so the system can use the mapbox API.
e.g. &lt;?php $mapboxApiToken = "pk.r53IjoiY2FlemUiLCIfaTbXo8ptdXp6d3QyMGpweDN3bzhweTZ5IjqqXpG7.Si72Tah5nyXFljhdfiSY9yg"; ?&gt;
## mapQuestApiKey.php
Put in a MapQuest API token so the system can use the MapQuest API.
e.g. &lt;?php $mapQuestApiKey = "3AAl4n9UOgMZB7dBO8hj1igYC4JZwwKh"; ?&gt;
## pdfShiftIoApiKey.php
e.g. &lt;?php $pdfShiftIoApiKey = "13e86817fee24b56b932a1585ca3e96a"; ?&gt;
## spreadsheetId.php
Put in the Google Spreadsheet ID containing the data for the infomap.
e.g. &lt;?php $spreadsheetId = "1O1k_JJMitnFik1h3s5eiA6VjMvuejsqCndJ_ETL8FZd"; ?&gt;
## urduGid.php
Put in the sheet ID for the "Urdu" tab of the Google Spreadsheet containing the data for the infomap.
e.g. &lt;?php $urduGid = "1531450539"; ?&gt;
## emailAddressesIncorrectData.php
Put in the e-mail addresses of all accounts that shall receive system e-mails. The addresses have to be a comma separated list.
If someone reports an incorrect data set, e-mails will get sent to all addresses mentioned in this file. Then, the responsible people can check the data and correct it if necessary
e.g. &lt;?php $emailAddressesIncorrectData = "example1@host1.com,example2@host2.com,example3@host3.com,example4@host4.com"; ?&gt;
## senderEmailAddress.php
Put in the e-mail address that shall be the "sender" of all e-mails sent by the infomap system.
Beware that some hosters have restrictions as to which addresses and especially hostnames may be chosen to be the "sender" addresses of anything sent via PHP.
e.g. &lt;?php $senderEmailAddress = "example1@host1.com"; ?&gt;

# cron job to periodically refresh the data cache
Edit the cron jobs on the system with the command "crontab -e".
Select your favorite editor by entering the corresponding number and pressing enter.
Add this line to the end of the file and save it: "@daily curl http://khora.social.coop/infomap/infomap.php?reload=true &gt; /dev/null"
This requests the website with the reload cache flag enabled on a regular basis.
In doing so, it refreshes the caches and speeds up the system for everyone else.

# Running System
Access the running system here: http://khora.social.coop/infomap
