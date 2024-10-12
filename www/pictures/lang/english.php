<?php
/*******************************************************

Imagething - Languagefile
Copyright (C) 2003/2004 Renko <renko@virtual-life.net>
Translation: Renko.

This file is part of the Imagething software.
Imagething is distributed under specific license,
see LICENSE.TXT for details.
lang/english.php build 2
Lines marked with '// !!' are changed, comparing the
last build.

*******************************************************/

 // index.php
$txt[0] = "Upload your image:";
$txt[1] = "Restrictions:";
$txt[2] = "Max %VAR%kB per image";
$txt[3] = "Only JPG, JPEG, PNG or GIF";
$txt[4] = "Max %VAR% uploads per day per user";
$txt[5] = "Max %VAR% traffic per week per user";
$txt[6] = "Only the following referer(s) are allowed:"; // !!
$txt[102] = "The following referer(s) are blocked:"; // !!
$txt[7] = "%VAR% days no view? > Trash";
$txt[8] = "Personal stats:";
$txt[9] = "Today %VAR% images uploaded";
$txt[10] = "%VAR1% traffic left for %VAR2% days";
$txt[12] = "Remember the following information:"; // !!


 // checks.php
$txt[13] = "You have an uploadban.";
$txt[14] = "You are banned!";
$txt[15] = "Sorry, you have reached the upload limit for this day. Come back tommorrow.";
$txt[16] = "Your bandwidth-limit is reached. Come back later.";
$txt[17] = "Hmm, there is not enough space on the server. Come back later when enough space is avaible.";
$txt[18] = "The lifetime of images is now limited to one day because the aviable hard disk space is almost full.";
$txt[19] = "The imageuploads are useing more space then allowed. Come back later when more space is free.";


 // upload.php
$txt[104] = "The image is larger as allowed."; // !! (changed ID)
$txt[91] = "Your picture has more then %VAR% pixels. Use thumbnails!<br>
    	   To prevent you don't post this image without a thumbnail, the bandwidth-counter will count twice fast as normal for this picture.<p>
    	   On most forums you can thumbnail on the following way:<br>
    	   <i>[url=http://virtual-life.net/imageBIG.jpg][img][url=http://virtual-life.net/imageSMALL.jpg][/url]</i><p>";
$txt[92] = "This filetype is not allowed.";
$txt[93] = "Your file has been uploaded as";



 // admin pages
$txt[20] = "Back";

 // admin/imgmin.php
$txt[21] = "Imagething Image admin";
$txt[22] = "Delete";
$txt[23] = "Filename";
$txt[24] = "Download count";
$txt[25] = "Size";
$txt[26] = "Uploaded by";
$txt[27] = "Upload date";
$txt[28] = "Last request";
$txt[29] = "The selected items are deleted.";
$txt[30] = "What do I have to delete? You didn't select anything.";
$txt[31] = "Total %VAR% images - Parsetime:";


 // admin/index.php
$txt[32] = "Welcome admin. Where do you want to go?";
$txt[33] = "Statics";
$txt[34] = "View/edit settings";
$txt[35] = "IP Control";
$txt[36] = "Image Admin";
$txt[37] = "Readme.txt";
$txt[38] = "Imagething Control Panel";

 // admin/ip.php
$txt[39] = "Imagething IP control";
$txt[40] = "IP ban:";
$txt[41] = "Search to banned IP:";
$txt[42] = "Ban this IP:";
$txt[43] = "Upload ban";
$txt[44] = "Complete ban";
$txt[45] = "Last five bans:";
$txt[46] = "All";
$txt[47] = "Upload";
$txt[48] = "Complete";
$txt[49] = "No IPs found";
$txt[50] = "No limits IPs:";
$txt[51] = "Search to IP:";
$txt[52] = "Add this IP:";
$txt[53] = "Last five adds:";
$txt[54] = "You didn't enter in any IP.";
$txt[55] = "This IP has already a status. See the details below.";
$txt[56] = "The IP %VAR% had been succesfully added.";
$txt[57] = "You didn't enter any IP.";
$txt[58] = "The IP '%VAR%' is not found in the ban- or nolimit table.";
$txt[59] = "Unlimited IP";
$txt[60] = "None";
$txt[61] = "IP";
$txt[62] = "Status";
$txt[63] = "Date";
$txt[64] = "Notes";
$txt[65] = "Action";
$txt[66] = "No limits";
$txt[67] = "Delete";
$txt[68] = "Succesfully deleted";
$txt[69] = "The query did fail.";
$txt[70] = "The query has run succesful.";
$txt[90] = "Search";


 // admin/settings.php
$txt[71] = "Some fiels has more units. If you change more then one, the most upper wil be used.";
$txt[72] = "Some settings which not found on this page can be changed by editing manually the PHP file. For more information, see the manual.";
$txt[73] = "Imagething Settings";
$txt[74] = "Maximum uploads per IP:";
$txt[75] = "Max traffic per IP per week";
$txt[76] = "Delete the image when the image hasn't been requested for X days.";
$txt[77] = "See question above. Now when the allowed diskspace is used for 80% or more.";
$txt[78] = "Maximum image size";
$txt[79] = "Minimal free space in the uploadfolder (Most: Free space on hard disk/partition)";
$txt[80] = "Maximum used space in the uploadfolder. 0 means no limit.";
$txt[81] = "Because thumbnailing can save very much traffic it's possible to give users a warning if the image is widther then X pixels. The bandwidth for the traffic/user/week will count twice for this file. Which width must the image have to show this warning?";
$txt[82] = "Do you want a custom message on the upload page?";
$txt[83] = "Your custom message (in HTML):";
$txt[84] = "The changes has been saved succesfully!";
$txt[85] = "Yes";
$txt[86] = "No";
$txt[87] = "Which language must be used?";
$txt[88] = "Save";
$txt[89] = "Reset";
$txt[94] = "Referers"; // !!
$txt[95] = "Domain"; // !!
$txt[96] = "Action"; // !!
$txt[97] = "Deny"; // !!
$txt[98] = "Allow"; // !!
$txt[99] = "Other"; // !!
$txt[100] = "Change action"; // !!
$txt[101] = "Delete record"; // !!
$txt[103] = "Use mod_rewrite? (Apache users only, see documentation)"; // !!


 // admin/stats.php
// That little words.. nah, no multilangsupport now.
?>
