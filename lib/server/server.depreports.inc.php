<?php

$page = "server.depreports";
include "server.header.inc.php";

$db = Db::get();
$today = GameDate::NOW()->getDay();
if ($today % 21 == 0) {

  $stm = $db->query("SELECT * FROM councils WHERE id NOT IN (3,10)");
  foreach ($stm->fetchAll() as $council_info) {

    $stm = $db->prepare("SELECT * FROM assignments WHERE status=1 AND council = :councilId");
    $stm->bindInt("councilId", $council_info->id);
    $stm->execute();
    if ($stm->rowCount() > 0) {
      $chair_assign_info = $stm->fetchObject();
    } else {
      $stm = $db->prepare("SELECT * FROM assignments WHERE status=2 AND council = :councilId");
      $stm->bindInt("councilId", $council_info->id);
      $stm->execute();
      if ($stm->rowCount() > 0) {
        $chair_assign_info = $stm->fetchObject();
      } else {
       $chair_assign_info->status=0;  
      }    
    }        

    echo "Requesting report for $council_info->name\n";

    if ($chair_assign_info->status) {
      $stm = $db->prepare("SELECT * FROM players WHERE id = :playerId");
      $stm->bindInt("playerId", $chair_assign_info->player);
      $stm->execute();
      $chair_info = $stm->fetchObject();
    }
    $start = $today - 21;
    $end = $today;

    $message = "";
    $message .= "(This template is meant for the chair to fill in and return to the GAB.\n";
    $message .= "If the chair is absent, please have someone take over this task\n";
    $message .= "and make clear in the report who filled it in. Make sure you CC to your department list.)\n\n";
    $message .= "------------------------------------------------------------------------\n";
    $message .= "TRI-WEEKLY REPORT\n";
    $message .= "------------------------------------------------------------------------\n\n";
    $message .= "Council: $council_info->name\n";

    switch ($chair_assign_info->status) {

      case 0:
        $message .= "No chair or vice chair \n";
        break;
      case 1:
        $message .= "Chair: $chair_info->firstname $chair_info->lastname <$chair_info->email>\n";
        break;
      case 2:
        $message .= "Vice-chair: $chair_info->firstname $chair_info->lastname <$chair_info->email>\n";
        break;

    }  

    $message .= "Period:  $start to $end\n\n";
    $message .= "------------------------------------------------------------------------\n";
    $message .= "GENERAL SUMMARY\n";
    $message .= "------------------------------------------------------------------------\n\n";
    $message .= "(Please remove all text between brackets and fill in this report. In this section\n";
    $message .= "describe briefly the activity of your department over the past three weeks. Only a brief\n";
    $message .= "telegram style overview is needed, unless there are exceptional circumstances,\n";
    $message .= "this should not be much longer than a few lines. Answer things like: What are the\n";
    $message .= "key activities over this period? What projects have been started, worked on, finished?\n";
    $message .= "What problems did we experience that require staff or policy change?)\n\n";
    $message .= "------------------------------------------------------------------------\n";
    $message .= "STAFF SUMMARY\n";
    $message .= "------------------------------------------------------------------------\n\n";
    $message .= "(Describe any staff issues that have occurred and need to occur. Any new members? Any\n";
    $message .= "member gone? Need more staff? If yes, what particular area?)\n\n";
    $message .= "------------------------------------------------------------------------\n";
    $message .= "STAFF OVERVIEW\n";
    $message .= "------------------------------------------------------------------------\n\n";
    $message .= "(Please describe for each member their status and activity over this period. Was\n";
    $message .= "this member absent, active, inactive, unresponsive? Is this member functioning well,\n";
    $message .= "excellent, poorly, etc. Except for unusual circumstances, no more than five words\n";
    $message .= "per member needed.)\n\n";
    
    $stm = $db->prepare("SELECT * FROM assignments WHERE council = :councilId AND status > 0");
    $stm->bindInt("councilId", $council_info->id);
    $stm->execute();
    foreach ($stm->fetchAll() as $member_assign_info) {
	  
      if ($member_assign_info->player != 999999) {
	    
	      $stm = $db->prepare("SELECT * FROM players WHERE id = :playerId");
	      $stm->bindInt("playerId", $member_assign_info->player);
	      $stm->execute();
	      $member_info = $stm->fetchObject();
	    
	      $message .= "$member_info->firstname $member_info->lastname <$member_info->email> (";

	      switch ($member_assign_info->status) {

	        case 0 : $message .= "hidden member"; break;
	        case 1 : $message .= "chair"; break;
	        case 2 : $message .= "vice chair"; break;
	        case 3 : $message .= "member"; break;
	        case 4 : $message .= "special member"; break;
	        case 5 : $message .= "aspirant member"; break;
	        case 6 : $message .= "on leave"; break;

	      }

	      $message .= ")";

	      if ($member_assign_info->special) { $message .= " [$member_assign_info->special]"; }

	      $message .= ":\n";
      }
    }

    if ($council_info->id == 1) {

      echo "\n";

      $stm = $db->query("SELECT * FROM assignments WHERE council=10 AND status>0");
      foreach ($stm->fetchAll() as $member_assign_info) {

	      if ($member_assign_info->player != 999999) {
	    
	        $stm = $db->prepare("SELECT * FROM players WHERE id = :playerId");
	        $stm->bindInt("playerId", $member_assign_info->player);
	        $stm->execute();
	        $member_info = $stm->fetchObject();
	    
	        $message .= "$member_info->firstname $member_info->lastname <$member_info->email> [$member_assign_info->special]:\n";
	      }
      }
    }

    if ($council_info->id == 1) {

      $gab = true;
      $message .= "\n";
      $message .= "------------------------------------------------------------------------\n";
      $message .= "DEPARTMENTS OVERVIEW\n";
      $message .= "------------------------------------------------------------------------\n\n";
      $message .= "(Please write a short summary for each department. Wait with this section\n";
      $message .= "until all departmental reports have been received. Describe briefly whether there\n";
      $message .= "are any staff issues, and describe how the departments are in general functioning.\n";
      $message .= "Describe what the general projects are that is being working on. Be brief - few lines\n";
      $message .= "per department should be enough.)\n\n";

      $stm = $db->query("SELECT * FROM councils WHERE id > 3 AND id != 10");
      foreach ($stm->fetchAll() as $dep_info) {
	      $message .= "$dep_info->name: \n";
      }
    }

    $mailService = new MailService("Game Administration Board", $GLOBALS['emailGAB']);
    $mailService->sendPlaintext("$council_info->email@cantr.net", "Triweekly Report $council_info->name ($start - $end) -TEMPLATE-", $message);
  }
}

include "server/server.footer.inc.php";
