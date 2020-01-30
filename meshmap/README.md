<!-- KG6WXC MeshMap README.md file -->
<!-- May 2018 -->
<img src="https://mapping.kg6wxc.net/meshmap/images/MESHMAP_LOGO.svg" style="float:left; vertical-align: middle;"/>
<h1 style="float: left; vertical-align: middle;">MeshMap</h1><br/>  

[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)
[![HamRadio](https://img.shields.io/badge/HamRadio-Roger!-green.svg)](https://www.arednmesh.org)
[![MattermostChat](https://img.shields.io/badge/Chat-Mattermost-blueviolet.svg)](https://mattermost.kg6wxc.net)  
Automated mapping of [AREDN](https://arednmesh.org) Networks.  

2016-2019 - Eric Satterlee / KG6WXC  

Addtional Credit to: Mark/N2MH and Glen/K6GSE for their work on this project and to the rest of the [AREDN](https://arednmesh.org) team, without them this would not be a project.  

Licensed under GPL v3 and later.  
[Donations](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=6K5KQYYU34H4U&currency_code=USD&source=url) / Beer accepted! :) 

[Demo Map](https://mapping.kg6wxc.net/meshmap)

## Requirements
---------------
- **Apache webserver**  
(or equiv)  
- **PHP5+**  
- **mysqli PHP extension**   
- **mysqlnd PHP extension**  
- **openssl PHP extension**  
(you may only need mysqlnd, it should be safe to enable both for now)  
(One or more of these extensions may need to be enabled in php.ini)  
(if you do not already have mysqlnd, you might need to install it, `apt-get install php[5 or 7]-mysqlnd`)  
(<em>the requirement for the mysqlnd extension will be removed in the near future</em>)  
- **MySQL/MariaDB**  
(Other database systems are up to you)  
- **An AREDN Mesh node available over the local network**  
(Preferably connected to an AREDN network...)  
- **Map Tiles**  
(Either in a static directory or available via some tile server...)  
- **RPi3 or better**  
(The DB access can be pretty slow on an RPi1, you can move the DB to another system though...)  
(If your local mesh network grows to become very large, with tunnels opened to the entire planet,<br/>
you might find even an RPi3+ to become inadequate, it'll still work, it just might be slow)  
- **Patience**  
(Perhaps a lot!)
- **Familiarity with Linux/Apache/SQL**  
(You don't need to be a pro, but this should not be your first trip to a command line)  

<blockquote style="background: #d3d3d3; margin-right: 30%;">In theory, this <em>should</em> run on a Windows system as well.<br/>
It does not require anything specific to Linux (<em>Perhaps with the exception of the cron task</em>).<br/>
There is no reason that cronjob could not be adapted to run from a Windows scheduled task though.<br/>
PHP is PHP after all.</blockquote>  

### Map Tile Server info
**Without a map tile server or static tiles, Mesh users without internet access on their systems may not see any map tiles.**  
On the mesh, you *cannot* expect the client to have internet access in order to retrieve the tiles, you must provide them yourself, one way or another.  
The main map webpage will try to check for internet access and load the appropriate maps.  
Default internet map tile servers have been provided in the ini file, but the ini file will need tweaking if you want to use "local" tile servers or directories.  

It is *way* beyond the scope of this README file to help in setting up a map tile server.  
You are unfortunatly on your own there.  
It is a time consuming and computationaly expensive process, but can be done on "normal" hardware.  
It also takes *100's of GB of HD space* if you want to map the entire world, and that *does not* include the tiles themselves, that is only for the data required to **create** the map tiles.  
A good place to start for more info is: [https://switch2osm.org/serving-tiles/](https://switch2osm.org/serving-tiles/)  
If you attempt it, be patient, you *will* get it wrong more than a few times but in the end you might be surprised. :)  

<blockquote style="background: #66cc66; margin-right: 30%;">Tip: Another option is that some programs, Xastir in particular, will save the map tiles they use.<br/>
You *can* use those tiles as well, but they must be made to be accessible by your webserver.</blockquote>  

You *might* be able to convince KG6WXC to create local map tiles for you, if the area you want is in the USA, he does not have the available SSD space for the entire world... yet.  
If you do ask, be prepared, it literally takes KG6WXC's system about 3-4 days just to render the tiles for a smallish area and it's kind of a PITA!  
<blockquote style="background: #d3d3d3; margin-right: 30%">As an example, KG6WXC once made tiles for the Mesa Az. mesh group.<br/>
It was a smallish area around Phoenix Az, out to a zoom of about 8 or something.<br/>
It ended up at around 3GB of map tiles and took about 4 days of total run time to render on the server...<br/>
<em>and</em> it had to restart a few times too, due to running out of 8GB of RAM and having to tweak a few things along the way...<br/>
It actually took much longer than the 4 days of actual run time.<br/>
Building/Using a map tile server is not for the faint of heart!</blockquote>
 
## Initial setup for a freshly installed Raspbian 9 (Stretch) system
----------
(*Should* work for other Linuxes as well, change where needed)

- **1: Clone the projects directory from the git repository and enter it**  
`git clone https://mapping.kg6wxc.net/git/meshmap ; cd meshmap`

- **2: Import the SQL file to create the database**  
*Example*: `sudo mysql < node_map.sql`

- **3: Create a user for the database, you might have to login to the mysql server as root.**  
Here is an example of creating a mySQL user and granting access to the node_map database:  
Choose your own password!
> `sudo mysql`  
> `CREATE USER 'mesh-map'@'localhost' IDENTIFIED BY 'password';`  
> `GRANT ALL PRIVILEGES on node_map.* TO 'mesh-map'@'localhost';`  
> `FLUSH PRIVILEGES;`

- **4: Copy scripts/user-settings.ini-default to scripts/user-settings.ini and edit the user-settings.ini file**  
    * You **must** do this or the **<em>entire system</em>** will refuse to run!  
    * The file scripts/user-settings.ini is the most important to get right.  
    It is **very important** to make sure your SQL username and password are correct in scripts/user-settings.ini!!  
    * Also important is, if the system that this is running on cannot resolve "localnode.local.mesh" you can change that in the user-settings.ini file.  
    * Once you save to the user-settings.ini file any changes you make will not be overwritten by future updates.  
    The "-default" files *will probably* change though and you will need to update your personal files when this happens.
    * There are many other things you can change in the ini files.  
    The default center position of the map, node expiration intervals, the header messages, logo, etc.  
    * *Please read* the comments in the user-settings.ini file for more info about the different settings.  
    * There is also a "custom.inc-default" PHP file that can be used for more site specific overrides if needed.  
    Read that file for info on what it does, it can safely be ignored by most users.
<blockquote style="background: #B00000; margin-right: 45%;"><strong>The way the user editable files are distrubuted has changed!.</strong><br/></blockquote>  

- **4.5: To make sure it is all working at this point is probably a good idea.**  
You should now be able to run get-map-info.php from the scripts directory.  
I would suggest giving it a test run or two first.  
Node polling can take lots of time, especially on a large network. Be Patient! :)  
Enter the meshmap/scripts directory.  
Run the `get-map-info.php` script.  
    <blockquote style="background: #66CC66; margin-right: 35%;">Tip: if you get a "command not found" error, you may need to run it like this:<br/> `./get-map-info.php <option>` </blockquote>
These are options you can send to get-map-info.php:  
    > `--test-mode-no-sql`  
    Output to console only, *do not* read/write to the database.  
    <blockquote style="background: #FFFF99; margin-right: 30%">This will make sure the scripts can reach your localnode and the rest of the network.  
    This will **not** update the database, you won't see anything on the map page yet, it only outputs to the terminal for testing.</blockquote>
    > `--test-mode-with-sql`  
    Output to console *and* read/write to the database.  
    <blockquote style="background: #FFFF99; margin-right: 40%;">This will ensure everything is setup correctly and get some data into your database!</blockquote>
    <blockquote style="background: #66CC66; margin-right: 30%;">Tip: <em><strong>Do not</strong></em> ctrl-C out of the script while it is using the database!<br/>
	Let it finish, even if it seems hung up.<br/>
	You should recieve some error if something is <em>actually</em> wrong.<br/>
	Using ctrl-C to stop the script midway will cause problems in the database, <em>do not</em> do it!</blockquote>
<blockquote style="background: #d3d3d3; margin-right: 30%;">If the --test-mode-no-sql is successful, you can go ahead and run the script with --test-mode-with-sql or just without any options.<br/>
Run the script without options and there is no on screen output (this is for cron).</blockquote>  

- **5: Copy httpd-meshmap.conf-default to the apache2 "Conf Available" directory**, `/etc/apache2/conf-available`  
Rename the file as httpd-meshmap.conf (or whatever you want to call it really.)  
Once the file is copied, you need to edit it and make sure the `<Alias>` and `<Directory>` directives have the correct paths.  
After you have made sure the file is correct then run: `sudo a2enconf httpd-meshmap`  
This is will load the config into Apache and if successful, it will tell you to reload apache, do so.  
    <blockquote style="background: #d3d3d3; margin-right: 30%;"><em>Other linux distibutions may require you to copy this file into /etc/httpd/extra<br>and then edit /etc/httpd/httpd.conf and add the line:</em> Include extra/httpd-meshmap.conf <em>somewhere.</em></blockquote>  

- **6: Load up the page: http://myhostname/meshmap/index.php and you should hopefully see your data.**  
You may or may not see any map tiles, depending on if the system you are using to view the page has access to the internet or not.  
Even without map tiles, you should still see your data being mapped out.  

- **7: The cronscript.sh file is to automatically run the polling script and can be run from cron every minute.**  
(or at whatever interval you choose)  
Copy the cronscript.sh-default to where ever you like and rename it to just cronscript.sh (or whatever you want).  
Then, you **must** edit the cronscript.sh file and make sure the path it uses to get to the scripts directory is correct!  
After that, create a cron entry with `crontab -e`  
A cron entry is as easy as this: `* * * * * /home/pi/cronscript.sh`  
    <blockquote style="background: #d3d3d3; margin-right: 30%;">You <em>can</em> safely run the script every minute in cron like this.<br>It won't actually do anything unless the intervals specified in the ini file have expired.</blockquote>  
  
## Updating the scripts
----------
Simply run a "git pull" from the meshmap directory and the scripts will be updated from the git repo.  
The user-settings.inc, meshmap-settings.ini, cronscript.sh, and custom.inc files will *not* be affected by updating.  
The settings in the default ini files *may* still change and have things added or removed in future versions.  
For now tho, if the default ini files change, and you still have the old ones in use, things will probably break! Be Warned!  
Hopefully in the future this process can be more automated.  
  
If you make changes beyond the user editable files I encourage you to perhaps push the changes upstream, please contact kg6wxc@gmail.com if you wish to do so.  
    <blockquote style="background: #d3d3d3; margin-right: 30%;">I am making changes all the time, it would be a good idea to run "git pull" from time to time to find any updates.</blockquote>  

## Notes on usage of the map pages
----------
http://(hostname)/meshmap/node_report.php will show you all the info in the DB without trying to map anything.  
This can be useful to see if all the data is there or to find nodes that have no location set. (or other issues)  
    
There is an "admin" page, which is still in the works, what is there now does work tho.  
Load up: http://(hostname)/meshmap/admin/admin.php in your web browser.  
The first time the admin page is loaded it will ask you to create a username and password, do so.  
This initial user will be a "super-user" and can then add/remove other users.  
I've tried to provide instructions on the admin pages themselves.  
From the admin pages you can "fix" a nodes location, which can be helpful for those users that forget the "-" in front of their longitude. :)  
You can add the "Non Mesh" Markers, fire stations, police stations, EOC's , etc from the admin pages...  
The admin pages also allow for some maintenance of the database, more feedback is encouraged on this!  
  
You can change the way the page looks by copying webpage/css/meshmap-default.css to webpage/css/meshmap.css.  
The meshmap.css file will override the -default.css file.  
(This also applies to other *-default.css files, there are a few, please look at them if you wish to customize the layout of the map)  

## ToDo List
----------
(In no particular order)  
- [x] Finally finish my admin page idea (mostly).  
- [x] Add new MeshMap Logo.  
- [x] User css files will override the defaults.  
- [x] Polling script checks the DB before it runs and makes changes if needed.  
- [x] Catch more nodes information now, like 3.15.1.0b04 and hopefully some other pesky ones!  
    (this will probably cause some warnings during node polling, but it is getting the info it needs)  
    (also helps clean up the database and was a nice side effect of that)  
- [x] Make the numbers for stations and links in the attribution bar a bit more accurate I hope.  
- [x] Add a "Ruler" to allow for measuring of distance and bearings.  
    (elevation plot of the line drawn via this ruler will hopefully come next)  
- [ ] Change css file for the "?" slide-out menu.  
- [ ] Make "Parallel Threads" work again in get-map-info script, with limits on how many can be run at once.  
    (this will greatly speed up network polling)  
- [ ] Implement N2MH's "Link aging" idea(s).  
- [ ] The "Planning" Tab.  
- [ ] Make it so other networks can export their data for use on a "Mega Map" type page. :)
  
## Contributing
----------
**Contribution is encouraged!!**  
I can't think of *everything*!  
If you find an improvement, typo, or whatever, please,  sign up at https://gitlab.kg6wxc.net or send an email to kg6wxc@gmail.com or something!!  

This README file last updated: July 2019 (fixed some Markdowns)
