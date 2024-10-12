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
$txt[0] = "Upload je image:";
$txt[1] = "Restricties:";
$txt[2] = "Max %VAR%kB per image";
$txt[3] = "Enkel JPG, JPEG, PNG of GIF";
$txt[4] = "Max %VAR% uploads per dag per user";
$txt[5] = "Max %VAR% traffic per week per user";
$txt[6] = "Enkel de volgende referer(s) zijn toegestaan:"; // !!
$txt[102] = "De volgende referer(s) zijn geblokkeerd:"; // !!
$txt[7] = "%VAR% Dagen geen view? > Trash";
$txt[8] = "Persoonlijke stats:";
$txt[9] = "Vandaag %VAR% images geupload";
$txt[10] = "%VAR1% traffic over voor nog %VAR2% dagen";
$txt[12] = "Houd het volgende in gedachten:"; // !!


 // checks.php
$txt[13] = "Je hebt een uploadban.";
$txt[14] = "Je bent gebanned!";
$txt[15] = "Helaasch, per dag zijn er maximaal %VAR% images te uploaden per gebruiker, deze zijn al berijkt.";
$txt[16] = "Helaasch, je bandbreedte-tank is voor deze week leeg. Je kunt even geen bestanden meer uploaden.";
$txt[17] = "Oei, er is te weinig harde schijf ruimte beschikbaar. Om problemen met de server te voorkomen is de Imageuploader tijdelijk automatisch uitgeschakeld. Het zal vanzelf weer aan gaan zodra er genoeg ruimte vrij is wat ook automatisch gebeurd.";
$txt[18] = "De leeftijd van images is tijdelijk verkort tot 1 dag aangezien de toegestane opslagruimte bijna vol is.";
$txt[19] = "De imageuploads nemen meer ruimte in dan is toegestaan, daarom is de imageuploader tijdelijk uitgeschakeld.";


 // upload.php
$txt[104] = "Bestand groter dan toegestaan."; // !! (Changed ID)
$txt[91] = "Je plaatje is groter dan %VAR% pixels. Gebruik thumbnails!<br>
	   Om te voorkomen dat je dit plaatje niet zonder thumbnail zult posten zal de bandbreedte counter dubbel zo hard gaan bij dit plaatje.<p>
	   Als geheugen steuntje, thumbnailen gaat bij de meeste forums op de volgende manier:<br>
    	   <i>[url=http://virtual-life.net/plaatjeGROOT.jpg][img][url=http://virtual-life.net/plaatjeKLEIN.jpg][/url]</i><p>";
$txt[92] = "Dit bestandstype is niet toegestaan.";
$txt[93] = "Je bestand is geupload als";



 // admin pages
$txt[20] = "Ga terug";

 // admin/imgmin.php
$txt[21] = "Imagething Image admin";
$txt[22] = "Delete";
$txt[23] = "Bestandsnaam";
$txt[24] = "Aantal downloads";
$txt[25] = "Grootte";
$txt[26] = "Uploaded door";
$txt[27] = "Upload datum";
$txt[28] = "Laatste request";
$txt[29] = "De geselecteerde items zijn deleted.";
$txt[30] = "Wat moet er worden deleted? Je selecteerde niks.";
$txt[31] = "Total %VAR% images - Parsetime:";


 // admin/index.php
$txt[32] = "Welkom admin, waar wil je naar toe?";
$txt[33] = "Statestieken";
$txt[34] = "Bekijk/edit instellingen";
$txt[35] = "IP Control";
$txt[36] = "Image Admin";
$txt[37] = "Readme.txt";
$txt[38] = "Imagething Control Panel";

 // admin/ip.php
$txt[39] = "Imagething IP control";
$txt[40] = "IP ban:";
$txt[41] = "Zoek naar gebanned IP:";
$txt[42] = "Ban dit IP:";
$txt[43] = "Upload ban";
$txt[44] = "Complete ban";
$txt[45] = "Laatste vijf bans:";
$txt[46] = "Alle";
$txt[47] = "Upload";
$txt[48] = "Complete";
$txt[49] = "Geen IPs gevonden";
$txt[50] = "No limits IPs:";
$txt[51] = "Zoek naar IP:";
$txt[52] = "Voeg dit IP toe:";
$txt[53] = "Laatste vijf toevoegingen:";
$txt[54] = "Je vergat een IP in te vullen, sufkop :D";
$txt[55] = "Dit IP heeft reeds een status, zie hieronder de details.";
$txt[56] = "Het IP %VAR% is succesvol toegevoegd.";
$txt[57] = "Je vulde geen IP in.";
$txt[58] = "Het IP '%VAR%' komt niet voor in de ban- of nolimit tabel.";
$txt[59] = "Unlimited IP";
$txt[60] = "Geen";
$txt[61] = "IP";
$txt[62] = "Status";
$txt[63] = "Datum";
$txt[64] = "Opmerkingen";
$txt[65] = "Actie";
$txt[66] = "No limits";
$txt[67] = "Delete";
$txt[68] = "Succesvol deleted";
$txt[69] = "De query is niet succesvol uitgevoerd.";
$txt[70] = "De query is succesvol uitgevoerd.";
$txt[90] = "Zoek";


 // admin/settings.php
$txt[71] = "Bij een aantal instellingen staan er meerdere velden in andere eenheden. Als meerdere velden worden veranderd zal de bovenste worden opgeslagen in de databse.";
$txt[72] = "Een aantal instellingen die niet op deze pagina staan, kun je enkel veranderen door de PHP-bestanden handmatig te editen. Raadpleeg hiervoor de documentatie.";
$txt[73] = "Imagething Settings";
$txt[74] = "Maximale bestanden per IP per dag:";
$txt[75] = "Max traffic per IP per week";
$txt[76] = "Delete de image na X aantal dagen niet te zijn opgevraagd.";
$txt[77] = "Zie bovenstaande vraag. Maar nu als de toegestane opslag ruimte voor 80% of meer is gevuld.";
$txt[78] = "Maximale grootte images";
$txt[79] = "Minimale vrije ruimte in de uploaddir (meestal: Minimaal op harde schijf)";
$txt[81] = "Maximale ruimte in de uploaddir. 0 is geen limiet.";
$txt[81] = "Aangezien thumbnailen enorm veel traffic kan besparen krijgen gebruikers een waarschuwing als hun plaatje breder is dan een X aantal pixels. Als deze waarschuwing is gegeven zal de counter voor het traffic/IP dubbel zo hard gaan bij dit bestand. Bij hoeveel pixels breed moet deze waarschuwing te zien zijn?";
$txt[82] = "Custom verhaaltje op de uploadpagina?";
$txt[83] = "Het custom verhaaltje (in HTML):";
$txt[84] = "De veranderingen zijn succesvol doorgevoerd!";
$txt[85] = "Ja";
$txt[86] = "Neen";
$txt[87] = "Welke taal moet er worden gebruikt?";
$txt[88] = "Opslaan";
$txt[89] = "Reset";
$txt[94] = "Referers"; // !!
$txt[95] = "Domein"; // !!
$txt[96] = "Aktie"; // !!
$txt[97] = "Blokkeer"; // !!
$txt[98] = "Toestaan"; // !!
$txt[99] = "De rest"; // !!
$txt[100] = "Verander aktie"; // !!
$txt[101] = "Verwijder record"; // !!
$txt[103] = "Gebruik mod_rewrite? (Enkel voor Apache gebruikers, zie de documentatie)"; // !!




 // admin/stats.php
// That little words.. nah, no multilangsupport now.
?>
