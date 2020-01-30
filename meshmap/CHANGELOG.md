commit: 0541861  
Author: Eric - kg6wxc  
Date: Mon Jul 1 19:38:12 2019 -0700  

    More updates to the README files.

commit: 2fd99be  
Author: Eric - kg6wxc  
Date: Mon Jul 1 18:48:39 2019 -0700  

    Update README.md, README.html files

commit: fd6dd52  
Author: Eric - kg6wxc  
Date: Wed May 29 18:32:51 2019 -0700  

    Merge branch 'removeDupes' into 'master'

commit: a3e8836  
Author: Eric  
Date: Wed May 29 18:25:44 2019 -0700  

    Added duplicate record finder for node_info table.

commit: 01a9493  
Author: Eric - kg6wxc  
Date: Thu May 16 17:02:44 2019 -0700  

    Merge branch 'fixTypo' into 'master'

commit: 32161c5  
Author: Eric  
Date: Thu May 16 17:00:29 2019 -0700  

    fixed typo in wxc_functions and map_functions

commit: 484b6ef  
Author: Eric - KG6WXC  
Date: Tue May 7 00:56:13 2019 -0700  

    Merge branch 'readmeUpdateCopyrightHTML' into 'master'

commit: df235f4  
Author: Eric  
Date: Mon Apr 15 16:45:48 2019 -0700  

    Updated README.md Fixed some Markdowns

commit: c3750d4  
Author: Eric  
Date: Sun Mar 31 00:29:34 2019 -0700  

    Update README.html

commit: 97b7fbd  
Author: Eric  
Date: Sun Mar 31 00:25:29 2019 -0700  

    changes to README.html

commit: ecf6223  
Author: Eric  
Date: Sun Mar 31 00:19:58 2019 -0700  

    Update README.md

commit: 3d6200d  
Author: Eric  
Date: Thu Mar 28 17:45:50 2019 -0700  

    Merge branch 'newPollingScripts' into 'master'

commit: 5bf8319  
Author: Eric  
Date: Thu Mar 28 17:33:46 2019 -0700  

    New Polling script! It will better catch all sysinfo.json values. It will also be easier to add more values later.

commit: df60438  
Author: Eric  
Date: Mon Mar 11 22:29:14 2019 -0700  

    Thaaat's what I knew I forgot to add!

commit: c87fb1d  
Author: Eric  
Date: Mon Mar 11 20:49:14 2019 -0700  

    Merge branch 'kilometers' into 'master'

commit: e300cde  
Author: Eric  
Date: Mon Mar 11 20:37:19 2019 -0700  

    more edits for the change to kilometers

commit: 2368597  
Author: Eric  
Date: Sun Mar 10 22:33:21 2019 -0700  

    remove unneeded round()

commit: 53b87c0  
Author: Eric  
Date: Sun Mar 10 22:22:44 2019 -0700  

    more additions for changing to kilometers by default

commit: 6be0612  
Author: Eric  
Date: Sun Mar 10 17:15:12 2019 -0700  

    Fixes to checkDB.inc (which was also moved to it's own file) If the SQL server has binary logging enabled then this will not work. (by default it does not, you usually only need that for replication and other advanced features.)

commit: 02d4dc5  
Author: Eric  
Date: Fri Mar 8 19:58:24 2019 -0800  

    changing default distance to kilometers the conversion to miles will be done later in the code next step: checking for and changing the sql trigger.

commit: 0b6e74f  
Author: Eric  
Date: Fri Mar 8 18:14:58 2019 -0800  

    Merge branch 'master' of https://gitlab.kg6wxc.net/mesh/meshmap

commit: d47b65a  
Author: Eric  
Date: Sat Feb 2 15:34:09 2019 -0800  

    Merge branch 'NodePollingFix' into 'master'

commit: 7e01a58  
Author: Eric  
Date: Sat Feb 2 15:31:58 2019 -0800  

    bugfix: $meshRF was not always getting set correctly.

commit: cf16b9c  
Author: Eric  
Date: Wed Jan 30 00:06:09 2019 -0800  

    Update README.md

commit: 3ed6f60  
Author: Eric  
Date: Sat Jan 26 00:15:28 2019 -0800  

    Update CONTRIBUTING.md

commit: 26bfbe5  
Author: Eric  
Date: Fri Jan 25 19:53:14 2019 -0800  

    Merge branch 'AddFutureMesh' into 'master'

commit: d1dd2d4  
Author: Eric  
Date: Fri Jan 25 19:52:06 2019 -0800  

    bugfix: Add "Future Mesh" to the list of Non-Mesh Stations to add.

commit: d5894d0  
Author: Eric  
Date: Fri Jan 25 19:41:03 2019 -0800  

    Merge branch 'CustomJsonFiles' into 'master'

commit: 0efa818  
Author: Eric  
Date: Fri Jan 25 19:35:20 2019 -0800  

    Changes to catch user created json file infomation

commit: 504f227  
Author: Eric  
Date: Fri Dec 28 11:15:14 2018 -0800  

    Merge branch 'noRF' into 'master'

commit: 058a3e7  
Author: Eric  
Date: Fri Dec 28 11:12:00 2018 -0800  

    bugfix: restore links for node when RF gets turned off

commit: 61646a0  
Author: Eric  
Date: Thu Dec 13 20:55:35 2018 -0800  

    Merge branch 'moreTimezoneFixing' into 'master'

commit: 8c4a3f9  
Author: Eric  
Date: Thu Dec 13 20:38:01 2018 -0800  

    bugfix: yet even more fixes for timezone display.

commit: 1eed246  
Author: Eric  
Date: Thu Dec 13 10:14:11 2018 -0800  

    Update scripts/wxc_functions.inc

commit: 1547a5f  
Author: Eric  
Date: Thu Dec 13 10:11:00 2018 -0800  

    Update scripts/wxc_functions.inc

commit: bad29d3  
Author: Eric  
Date: Wed Dec 12 22:59:28 2018 -0800  

    Update scripts/wxc_functions.inc

commit: 40751b5  
Author: Eric  
Date: Wed Dec 12 22:52:50 2018 -0800  

    Merge branch 'moreTimeZone' into 'master'

commit: e101105  
Author: Eric  
Date: Wed Dec 12 22:47:49 2018 -0800  

    bugfix: more timezone fixes

commit: f46aaae  
Author: Eric  
Date: Wed Dec 12 22:43:51 2018 -0800  

    bugfix: more time timezone fixes.

commit: dc0d942  
Author: Eric  
Date: Fri Nov 30 22:36:45 2018 -0800  

    bugfix: set global timezone value in get-map-info

commit: 7de7be9  
Author: Eric  
Date: Wed Nov 28 23:15:02 2018 -0800  

    Merge branch 'popupLinkInNewTab' into 'master'

commit: 999abd4  
Author: Eric  
Date: Wed Nov 28 23:12:46 2018 -0800  

    enhancement: node pop-up link now opens in a new tab/window

commit: 7e136b8  
Author: Eric  
Date: Wed Nov 28 22:34:41 2018 -0800  

    Merge branch 'timezoneFixes' into 'master'

commit: af85935  
Author: Eric  
Date: Tue Nov 27 19:15:48 2018 -0800  

    fix typo

commit: 1b7690d  
Author: Eric  
Date: Tue Nov 27 19:13:09 2018 -0800  

    one thing at a time

commit: 3ac18ce  
Author: Eric  
Date: Tue Nov 27 18:54:30 2018 -0800  

    more timezone fixes

commit: 93f5e0d  
Author: Eric  
Date: Tue Nov 27 18:44:59 2018 -0800  

    even more timezone fixes

commit: cbbac3b  
Author: Eric  
Date: Tue Nov 27 18:20:38 2018 -0800  

    bugfix: more timezone fixes

commit: b9bc99e  
Author: Eric  
Date: Mon Nov 26 18:37:02 2018 -0800  

    bugfix: Display timezone with timestamps.

commit: 18dd89e  
Author: Eric  
Date: Fri Nov 23 20:49:58 2018 -0800  

    Add text explaining "expire_old_nodes" setting

commit: c0e3c68  
Author: Eric  
Date: Tue Oct 9 20:47:47 2018 -0700  

    Update CONTRIBUTING.md

commit: 859ad32  
Author: Eric  
Date: Tue Oct 9 20:46:24 2018 -0700  

    Update CONTRIBUTING.md

commit: f185226  
Author: Eric  
Date: Tue Oct 9 20:44:54 2018 -0700  

    Update CONTRIBUTING.md

commit: 89e4d02  
Author: Eric  
Date: Tue Oct 9 20:44:06 2018 -0700  

    Add contribution guide

commit: 8708562  
Author: Eric  
Date: Sat Sep 29 18:55:04 2018 -0700  

    Merge branch 'master' of https://gitlab.kg6wxc.net/mesh/meshmap

commit: 16122d3  
Author: Eric  
Date: Sat Sep 29 18:50:32 2018 -0700  

    bugfix: removed errant "echo \n" from get-map-info

commit: 0fb0aeb  
Author: Eric  
Date: Sat Sep 29 17:59:38 2018 -0700  

    Update CHANGELOG.md

commit: 7659245  
Author: Eric  
Date: Sat Sep 29 17:15:51 2018 -0700  

    Update CHANGELOG.md Deleted CHANGELOG

commit: edcff34  
Author: Eric  
Date: Sat Sep 29 16:22:59 2018 -0700  

    Update CHANGELOG

commit: 98e480f  
Author: Eric  
Date: Sat Sep 29 16:10:33 2018 -0700  

    Add CHANGELOG

commit: 3c5dd64  
Author: Eric  
Date: Sat Sep 29 15:04:05 2018 -0700  

    ignore this, just a test.

commit: 14d465c  
Author: Eric  
Date: Sat Sep 29 14:55:13 2018 -0700  

    testing hooks

commit: 708f9e4  
Author: Eric Satterlee - KG6WXC  
Date: Thu Sep 27 16:28:21 2018 -0700  

    bugfix: removed output from script when run in "silent" mode, fix sql statement typo

commit: 8affc06  
Author: Eric Satterlee - KG6WXC  
Date: Thu Sep 27 16:03:56 2018 -0700  

    update: changed current_stable_firmware_version to 3.18.9.0 in the default .ini file

commit: 26f01b3  
Author: Eric Satterlee - KG6WXC  
Date: Sun Sep 23 12:21:53 2018 -0700  

    enhancement: cleanup some of the output in "test-mode"

commit: 229a3e6  
Author: Eric Satterlee - KG6WXC  
Date: Sun Sep 23 10:31:45 2018 -0700  

    bugfix: typo and missing single quotes in wxc_functions.

commit: cf42ebd  
Author: Eric Satterlee - KG6WXC  
Date: Wed Sep 19 19:13:32 2018 -0700  

    updated scripts for newer sysinfo.json file.

commit: fa265ac  
Author: Eric Satterlee - KG6WXC  
Date: Tue Jul 24 01:49:34 2018 -0700  

    Enhancement map_display.php

commit: bd572f6  
Author: Eric Satterlee - KG6WXC  
Date: Mon Jul 23 01:06:50 2018 -0700  

    Bugfixes/Changes

commit: fb51e8e  
Author: Eric Satterlee - KG6WXC  
Date: Fri Jul 20 20:21:24 2018 -0700  

    Addition: It's a secret (unless you read the diff of course :) )

commit: 4bbb002  
Author: Eric Satterlee - KG6WXC  
Date: Fri Jul 20 19:08:53 2018 -0700  

    BugFix: map_functions.php

commit: 41275f1  
Author: Eric Satterlee - KG6WXC  
Date: Fri Jul 20 18:23:56 2018 -0700  

    Quick addition to the README file

commit: 6e0fb62  
Author: Eric Satterlee - KG6WXC  
Date: Fri Jul 20 17:44:16 2018 -0700  

    BugFixs/Enhancements/Additions

commit: 7723b47  
Author: Eric Satterlee - KG6WXC  
Date: Wed Jun 20 17:49:25 2018 -0700  

    Bugfix: removed margin when map is embedded in iFrame.

commit: 2e3a499  
Author: Eric Satterlee - KG6WXC  
Date: Wed Jun 20 17:38:43 2018 -0700  

    Enhancement: Allow map to be embedded in iFrames

commit: e8947ee  
Author: Eric Satterlee - KG6WXC  
Date: Sat Jun 16 21:35:56 2018 -0700  

    BugFix get-map-info wxc_functions

commit: a7d1053  
Author: Eric Satterlee - KG6WXC  
Date: Sun Jun 10 13:04:25 2018 -0700  

    BugFix: Mouse clicks no longer propagate through the layer control box

commit: a3dd4c4  
Author: Eric Satterlee - KG6WXC  
Date: Sun Jun 10 11:17:07 2018 -0700  

    BugFix: More 5GHz channels, 900MHz nodes now found by 'board_id'

commit: 51154ec  
Author: Eric Satterlee - KG6WXC  
Date: Fri Jun 8 21:30:54 2018 -0700  

    BugFix: wxc_functions.inc

commit: 929e0f9  
Author: Eric Satterlee - KG6WXC  
Date: Thu Jun 7 22:49:49 2018 -0700  

    BugFix: Added missing 173 channel to 5GHz band check

commit: 45d534f  
Author: Eric Satterlee - KG6WXC  
Date: Mon Jun 4 20:31:29 2018 -0700  

    Typo: fixed #4 in the README file.

commit: 1690d7a  
Author: Eric Satterlee - KG6WXC  
Date: Tue May 29 23:22:38 2018 -0700  

    BugFix/Enhancement export2csv admin-default.css

commit: 74201e3  
Author: Eric Satterlee - KG6WXC  
Date: Mon May 28 17:23:05 2018 -0700  

    Enhancement/BugFix - L.Control.SlideMenu.js README

commit: 09ec421  
Author: Eric Satterlee - KG6WXC  
Date: Mon May 28 12:44:44 2018 -0700  

    Enhacement - admin.php css files map_display.php leaflet-hash.js

commit: c8b27b4  
Author: Eric Satterlee - KG6WXC  
Date: Sun May 27 20:59:42 2018 -0700  

    Bugfix - README.md

commit: ad45ebf  
Author: Eric Satterlee - KG6WXC  
Date: Thu May 24 08:26:41 2018 -0700  

    Bugfix - otherAdmin.php

commit: 47b93ee  
Author: Eric Satterlee - KG6WXC  
Date: Wed May 23 23:52:59 2018 -0700  

    Bugfix get-map-info.php

commit: bfbf457  
Author: Eric Satterlee - KG6WXC  
Date: Wed May 23 22:20:30 2018 -0700  

    Bugfix/Update/Enhancement Admin pages

commit: 12b091b  
Author: Eric Satterlee - KG6WXC  
Date: Sun May 6 22:20:54 2018 -0700  

    Enhancement: map_display.php

commit: c32015e  
Author: Eric Satterlee - KG6WXC  
Date: Thu May 3 22:42:12 2018 -0700  

    Bug Fix: get-map-info.php, map-display.php

commit: 8837d7f  
Author: Eric Satterlee - KG6WXC  
Date: Thu May 3 18:08:48 2018 -0700  

    Bug fix: get-map-info.php

commit: 874f010  
Author: Eric Satterlee - KG6WXC  
Date: Tue May 1 20:22:17 2018 -0700  

    Fixed some issues in the README file

commit: f8d524b  
Author: Eric Satterlee - KG6WXC  
Date: Tue May 1 19:41:09 2018 -0700  

    fixed my stupid error in admin.php page.

commit: e24346c  
Author: Eric Satterlee - KG6WXC  
Date: Tue May 1 18:30:59 2018 -0700  

    changed default cronscript to use /bin/bash

commit: 58f8be7  
Author: Eric Satterlee - KG6WXC  
Date: Tue May 1 18:20:40 2018 -0700  

    whoops missed a quote in the right spot...

commit: 8fbe3df  
Author: Eric Satterlee - KG6WXC  
Date: Tue May 1 18:16:19 2018 -0700  

    Changed to use default ini and other user editable files.

commit: 011a7ef  
Author: Eric Satterlee - KG6WXC  
Date: Mon Apr 30 01:15:37 2018 -0700  

    Even more slight changes to the README stuff

commit: a8e3eb3  
Author: Eric Satterlee - KG6WXC  
Date: Mon Apr 30 00:43:19 2018 -0700  

    Broke it again... fixing...

commit: 42b666b  
Author: Eric Satterlee - KG6WXC  
Date: Mon Apr 30 00:35:47 2018 -0700  

    More slight changes to the README.md file

commit: 61e3979  
Author: Eric Satterlee - KG6WXC  
Date: Sun Apr 29 23:44:39 2018 -0700  

    fixing even more things... I am learning git better! :)

commit: c3571d4  
Author: Eric Satterlee - KG6WXC  
Date: Sun Apr 29 23:14:21 2018 -0700  

    still fixing README.md

commit: 59d6178  
Author: Eric Satterlee - KG6WXC  
Date: Sun Apr 29 23:09:55 2018 -0700  

    trying to fix the README.md file

commit: 8d940cf  
Author: Eric Satterlee - KG6WXC  
Date: Sun Apr 29 23:04:01 2018 -0700  

    fixing...

commit: 8689c67  
Author: Eric Satterlee - KG6WXC  
Date: Sun Apr 29 23:00:06 2018 -0700  

    fixed the README.md file. Yes yes, I broke it again. I know. :)

commit: 58c7cac  
Author: Eric Satterlee - KG6WXC  
Date: Sun Apr 29 22:57:06 2018 -0700  

    Updated to README.md file and made it much nicer. Logo updated in repo. favicon.ico added. Started the change to the -default files for the user editable files

commit: ba78d85  
Author: Eric Satterlee - KG6WXC  
Date: Sun Apr 22 01:41:16 2018 -0700  

    lots of things updated, way more than I want to type about right now.

commit: ce3fec8  
Author: Eric Satterlee - KG6WXC  
Date: Thu Apr 19 22:31:39 2018 -0700  

    Added GPL 3 license, also changed README (yet again)

commit: 92b2ae7  
Author: Eric Satterlee - KG6WXC  
Date: Thu Apr 19 21:07:23 2018 -0700  

    Of course that wasn't all... more changes to README

commit: dbb226b  
Author: Eric Satterlee - KG6WXC  
Date: Thu Apr 19 20:59:31 2018 -0700  

    even more changes to the README file, hopefully this is it for that.

commit: 5c1ac4f  
Author: Eric Satterlee - KG6WXC  
Date: Thu Apr 19 20:26:45 2018 -0700  

    messing with the REAME file now

commit: dd6638e  
Author: Eric Satterlee - KG6WXC  
Date: Thu Apr 19 20:16:08 2018 -0700  

    More updates to the README file

commit: 397e7eb  
Author: Eric Satterlee - KG6WXC  
Date: Thu Apr 19 18:34:23 2018 -0700  

    more README changes

commit: a35f425  
Author: Eric Satterlee - KG6WXC  
Date: Thu Apr 19 18:21:05 2018 -0700  

    changed README file to compensate for the auto-generated README.html.

commit: bfda006  
Author: Eric Satterlee - KG6WXC  
Date: Thu Apr 19 17:59:03 2018 -0700  

    This may fix README.html

commit: bb38993  
Author: Eric Satterlee - KG6WXC  
Date: Thu Apr 19 17:54:57 2018 -0700  

    manually adding README.html (hopefully this will fix the repo)

commit: 7ebfcdd  
Author: Eric Satterlee - KG6WXC  
Date: Thu Apr 19 17:49:15 2018 -0700  

    Made it so get-map-info and parallel_node_polling cannot be run inside a browser.

commit: a0401c8  
Author: Eric Satterlee - KG6WXC  
Date: Wed Apr 18 23:12:17 2018 -0700  

    forgot to add a file

commit: f55f3db  
Author: Eric Satterlee - KG6WXC  
Date: Wed Apr 18 23:09:39 2018 -0700  

    Trying to validate against HTML5 Validator and more clean up.

commit: 8fdef47  
Author: Eric Satterlee - KG6WXC  
Date: Wed Apr 18 22:38:22 2018 -0700  

    Yet more cleaning...

commit: 66af1b9  
Author: Eric Satterlee - KG6WXC  
Date: Wed Apr 18 22:13:11 2018 -0700  

    more cleaning (there might be a lot of this, sorry)

commit: cee4998  
Author: Eric Satterlee - KG6WXC  
Date: Wed Apr 18 22:04:57 2018 -0700  

    leaflet-hash.js now always loaded from local resources.

commit: c70ffa8  
Author: Eric Satterlee - KG6WXC  
Date: Wed Apr 18 22:01:51 2018 -0700  

    fixed leaflet-hash.js URL

commit: 494fbb3  
Author: Eric Satterlee - KG6WXC  
Date: Wed Apr 18 21:58:38 2018 -0700  

    Cleaning some comments, more preparing for the "internet test" and other general cleanup.

commit: 0e4149f  
Author: Eric Satterlee - KG6WXC  
Date: Wed Apr 18 21:49:04 2018 -0700  

    changed where the "internet access checking" is at in the code.

commit: 61781bd  
Author: Eric Satterlee - KG6WXC  
Date: Wed Apr 18 21:42:11 2018 -0700  

    Setting up for the abilty to tell if the client has internet access or not.

commit: 058c1f1  
Author: Eric Satterlee - KG6WXC  
Date: Sun Apr 15 18:45:06 2018 -0700  

    get-map-info, map_display and map_functions updated.

commit: 2933882  
Author: Eric Satterlee - KG6WXC  
Date: Sat Apr 14 16:45:06 2018 -0700  

    Changes to README file

commit: 5174fbc  
Author: Eric Satterlee - KG6WXC  
Date: Sat Apr 14 16:37:31 2018 -0700  

    Changes to README file

commit: 44218c6  
Author: Eric Satterlee - KG6WXC  
Date: Sat Apr 14 16:21:23 2018 -0700  

    Changes to README file

commit: 5c8257b  
Author: Eric Satterlee - KG6WXC  
Date: Sat Apr 14 16:12:37 2018 -0700  

    Changes to README file

commit: c2986cc  
Author: Eric Satterlee - KG6WXC  
Date: Sat Apr 14 12:30:21 2018 -0700  

    Added .gitignore file

commit: 15b0f06  
Author: Eric Satterlee - KG6WXC  
Date: Sat Apr 14 11:45:57 2018 -0700  

    fixing issue with get-map-info --help (I  hope)

commit: f2b982f  
Author: Eric Satterlee - KG6WXC  
Date: Sat Apr 14 11:05:24 2018 -0700  

    Remove usage of the "wxc_custom.inc" file.

commit: 9ef9ddb  
Author: Eric Satterlee - KG6WXC  
Date: Sat Apr 14 03:14:15 2018 -0700  

    more edits to the --help section of get-map-info.php

commit: 0988d35  
Author: Eric Satterlee - KG6WXC  
Date: Sat Apr 14 03:10:01 2018 -0700  

    ha! fixed a stupid typo and added more info to get-map-info.php --help

commit: 8557007  
Author: Eric Satterlee - KG6WXC  
Date: Sat Apr 14 03:03:45 2018 -0700  

    more little changes

commit: 8cce55b  
Author: Eric Satterlee - KG6WXC  
Date: Sat Apr 14 03:00:53 2018 -0700  

    another test

commit: 86fade7  
Author: Eric Satterlee - KG6WXC  
Date: Sat Apr 14 02:59:12 2018 -0700  

    forcing update to get-map-info

commit: ebadc3d  
Author: Eric Satterlee - KG6WXC  
Date: Sat Apr 14 02:56:17 2018 -0700  

    added --help ability to get-map-info.php

commit: 72192d2  
Author: Eric Satterlee - KG6WXC  
Date: Sat Apr 14 01:42:50 2018 -0700  

    more changes to README file

commit: 769f457  
Author: Eric Satterlee - KG6WXC  
Date: Sat Apr 14 00:50:24 2018 -0700  

    Fixed some errors in the README file

commit: c9e116b  
Author: Eric Satterlee - KG6WXC  
Date: Fri Apr 13 23:47:59 2018 -0700  

    Last commit failed, re-commiting

commit: 8f0dd53  
Author: Eric Satterlee - KG6WXC  
Date: Fri Apr 13 23:45:56 2018 -0700  

    Many changes.

commit: 5ac5c49  
Author: Eric Satterlee - KG6WXC  
Date: Tue Apr 10 14:55:31 2018 -0700  

    finxed warning from map_functions about undefined index in array creation

commit: 6bee870  
Author: Eric Satterlee - KG6WXC  
Date: Sat Apr 7 22:51:11 2018 -0700  

    Fixed issues with the station Pop-Ups.

commit: 8abe7a8  
Author: Eric Satterlee - KG6WXC  
Date: Sat Apr 7 13:51:02 2018 -0700  

    removed what git added in get-map-info.php (still learning this git stuff :) )

commit: 2c2aa28  
Author: Eric Satterlee - KG6WXC  
Date: Sat Apr 7 13:48:25 2018 -0700  

    Merge remote-tracking branch 'origin/master'

commit: 29ec34c  
Author: Eric Satterlee - KG6WXC  
Date: Sat Apr 7 13:47:17 2018 -0700  

    added node_map.sql file for importing into mysql/mariaDB

commit: c4c706b  
Author: Eric Satterlee - KG6WXC  
Date: Sat Apr 7 13:34:41 2018 -0700  

    yet more testing of git system

commit: c5afd25  
Author: Eric Satterlee - KG6WXC  
Date: Sat Apr 7 13:17:48 2018 -0700  

    testing git

commit: 825d43f  
Author: Eric Satterlee - KG6WXC  
Date: Sat Apr 7 13:13:38 2018 -0700  

    trying to remove some files from the git repo

commit: 7ad4061  
Author: Eric Satterlee - KG6WXC  
Date: Wed Apr 4 23:47:08 2018 -0700  

    just changed some comments in get-map-info (and testing git from laptop)

commit: 7fa8aca  
Author: ride  
Date: Thu Mar 29 00:08:09 2018 -0700  

    testing from windows...

commit: 50375ac  
Author: Eric Satterlee - KG6WXC  
Date: Wed Mar 28 22:58:10 2018 -0700  

    really testing new domain name, added some new comments in map_display

commit: 94f81ac  
Author: Eric Satterlee - KG6WXC  
Date: Wed Mar 28 17:18:07 2018 -0700  

    updated the apache conf file to block access to the ini and inc files also block access to the scripts directory.

commit: b3b917d  
Author: Eric Satterlee - KG6WXC  
Date: Wed Mar 28 15:41:29 2018 -0700  

    just a little cleaning up of my commented lines in map_functions

commit: b03aea0  
Author: Eric Satterlee - KG6WXC  
Date: Wed Mar 28 15:35:43 2018 -0700  

    fixed log warning about empty needle on line 181 of map_display

commit: 91575e1  
Author: Eric Satterlee - KG6WXC  
Date: Wed Mar 28 14:36:43 2018 -0700  

    could be fixed

commit: 25875f6  
Author: Eric Satterlee - KG6WXC  
Date: Wed Mar 28 11:02:08 2018 -0700  

    still not right...

commit: 5b4451a  
Author: Eric Satterlee - KG6WXC  
Date: Mon Mar 26 00:11:37 2018 -0700  

    more testing of the git system

commit: 1808533  
Author: Eric Satterlee - KG6WXC  
Date: Mon Mar 26 00:06:30 2018 -0700  

    even more comment for testing git with https

commit: aaffb86  
Author: Eric Satterlee - KG6WXC  
Date: Mon Mar 26 00:04:51 2018 -0700  

    yet again

commit: 2b77b49  
Author: Eric Satterlee - KG6WXC  
Date: Mon Mar 26 00:03:25 2018 -0700  

    even more

commit: 6e99863  
Author: Eric Satterlee - KG6WXC  
Date: Sun Mar 25 23:58:16 2018 -0700  

    ...

commit: 9ea011c  
Author: Eric Satterlee - KG6WXC  
Date: Sun Mar 25 23:55:54 2018 -0700  

    more tests

commit: 6c28d20  
Author: Eric Satterlee - KG6WXC  
Date: Sun Mar 25 23:21:46 2018 -0700  

    .....2

commit: 4312fc6  
Author: Eric Satterlee - KG6WXC  
Date: Sun Mar 25 23:19:57 2018 -0700  

    ....

commit: f0338b8  
Author: Eric Satterlee - KG6WXC  
Date: Sun Mar 25 23:14:25 2018 -0700  

    test https commit1

commit: 770add7  
Author: Eric Satterlee - KG6WXC  
Date: Sun Mar 25 22:56:29 2018 -0700  

    testing more resetting git dir permissions

commit: 49835e6  
Author: Eric Satterlee - KG6WXC  
Date: Sun Mar 25 22:43:56 2018 -0700  

    yet more testing

commit: 1f6138d  
Author: Eric Satterlee - KG6WXC  
Date: Sun Mar 25 22:42:21 2018 -0700  

    more testing of git

commit: 5ab50a6  
Author: Eric Satterlee - KG6WXC  
Date: Sun Mar 25 22:36:20 2018 -0700  

    test

commit: 9f03aa1  
Author: Eric Satterlee - KG6WXC  
Date: Sun Mar 25 22:34:02 2018 -0700  

    test of git system....

commit: 725ab30  
Author: Eric Satterlee - KG6WXC  
Date: Sun Mar 25 22:30:16 2018 -0700  

    working on load_LinkedTO function.

commit: 2fac9a6  
Author: Eric Satterlee - KG6WXC  
Date: Sun Mar 25 12:02:47 2018 -0700  

    Initial commit!

