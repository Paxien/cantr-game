<?php

// SANITIZE INPUT
$killid = HTTPContext::getInteger('killid');
$info = $_REQUEST['info'];

$db = Db::get();

$playerInfo = Request::getInstance()->getPlayer();
if ($playerInfo->hasAccessTo(AccessConstants::VIEW_RESEARCH_ON_PLAYERS)) {
	if ($killid) {
		Session::deleteSessionFromDatabase($killid);
	}

	if (!isset($info)) $info = 'proximity';

	if ($info == 'proximity') {

		$stm = $db->query("SELECT players.id, players.email, players.firstname, players.lastname, locations.id AS location_id, locations.name AS location, COUNT(*) AS number
      FROM chars, players, locations WHERE chars.player = players.id AND chars.location = locations.id AND chars.location != 0
      AND chars.status = 1 GROUP BY chars.location, chars.player HAVING COUNT(*) > 2 ORDER BY number DESC");

		echo "<CENTER><H2>Players with many characters in physical proximity</H2><BR><TABLE>"; 

		foreach ($stm->fetchAll() as $result) {
			echo "<TR><TD><A HREF=\"index.php?page=infoplayer&player_id=$result->id\">[i]</A></TD><TD><B>$result->firstname</B></TD><TD><B>$result->lastname</B></TD><TD><FONT COLOR=yellow>$result->email</FONT></TD><TD>$result->location ($result->location_id)</TD><TD><FONT COLOR=red><B>$result->number</B></FONT></TD></TR>";
		}

		echo "</TABLE></CENTER>";
	}

	if ($info == 'ips') {

		$stm = $db->query("SELECT ip, COUNT(*) AS number FROM ips WHERE (CURDATE() - 3) <= ips.lasttime GROUP BY ip HAVING COUNT(*) > 1 ORDER BY number DESC");
		echo "<CENTER><H2>IPs with multiple players</H2><BR><TABLE>";

		foreach ($stm->fetchAll() as $ipresult) {
		  $remhost = gethostbyaddr ($ipresult->ip);

			$stm = $db->prepare("SELECT ips.lasttime, players.id, players.firstname, players.lastname, players.email FROM ips, players WHERE ips.ip = :ip AND ips.player = players.id GROUP BY players.id");
			$stm->bindStr("ip", $ipresult->ip);
			$stm->execute();
			foreach ($stm->fetchAll() as $result) {
				echo "<TR><TD><A HREF=\"index.php?page=infoplayer&player_id=$result->id\">[i]</A></TD><TD><B>$result->firstname</B></TD><TD><B>$result->lastname</B></TD><TD><FONT COLOR=yellow>$result->email</FONT></TD><TD>$ipresult->ip ($remhost)</TD><TD>$result->lasttime</TD></TR>";
			}
		}

		echo "</TABLE></CENTER>";
	}

	if ($info == 'sessions') {

		echo '<CENTER><H2>Current sessions</H2><BR><TABLE>';

		$stm = $db->query('SELECT * FROM sessions ORDER BY info DESC');
		foreach ($stm->fetchAll() as $result) {
			$stm = $db->prepare("SELECT * FROM players WHERE id = :playerId");
			$stm->bindInt("playerId", $result->player);
			$stm->execute();
			$info = $stm->fetchObject();
			echo "<TR><TD><A HREF=\"index.php?page=infoplayer&player_id=$result->player\">[i]</A></TD><TD><B>$info->firstname</B></TD><TD><B>$info->lastname</B></TD><TD><FONT COLOR=yellow>$info->email</FONT></TD><TD><A HREF=\"index.php?page=researchplayers&killid=$result->player\">[kill]</A></TD><TD>$result->info</TD></TR>";
		}

		echo '</TABLE></CENTER>';
	}

	echo '<BR><CENTER>';
	echo "<A HREF=\"index.php?page=researchplayers&info=proximity\">Search for characters in close proximity</A><BR>";
	echo "<A HREF=\"index.php?page=researchplayers&info=ips\">Search for IPs with multiple players</A><BR>";
	echo "<A HREF=\"index.php?page=researchplayers&info=sessions\">Search for current sessions</A><BR><BR>";
	echo "<A HREF=\"index.php?page=player\">Back to player page</A>";
	echo '</CENTER>';
}
