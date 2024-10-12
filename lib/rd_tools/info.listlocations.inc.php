<?php


// SANITIZE INPUT
$step = $_REQUEST['step'];
$id = HTTPContext::getInteger('id');
$region = HTTPContext::getInteger('region');
$prevstep = $_REQUEST['prevstep'];

$db = Db::get();
$requestData = Request::getInstance();
$adminPlayer = $requestData->getPlayer();

if ($adminPlayer->hasAccessTo(AccessConstants::ALTER_LOCATIONS)) {

  $stm = $db->prepare("SELECT id, unique_name FROM objecttypes
    WHERE objectcategory = :category ORDER BY id");
  $stm->bindInt("category", ObjectConstants::OBJCAT_TERRAIN_AREAS);
  $stm->execute();
  foreach ($stm->fetchAll() as $area_info) {
    $areanames[$area_info->id] = $area_info->unique_name;
  }
	
  switch ($step) {
	
  case "edit":
  case "sub":
    $stm = $db->prepare("SELECT * FROM locations WHERE id = :id");
    $stm->bindInt("id", $id);
    $stm->execute();
    $location_info = $stm->fetchObject();
		
  case "new":		
    if ($step == "edit") {
				
      $name = $location_info->name;
      $type = $location_info->type;
      $regio = $location_info->region;
      $area = $location_info->area;
      $size = $location_info->size;
      $borders_lake = $location_info->borders_lake;
      $borders_sea = $location_info->borders_sea;
      $map = $location_info->map;
      $x = $location_info->x;
      $y = $location_info->y;
    }
		
    if ($step == "sub") {
				
      $name = '';
      $type = 4;
      $regio = $id;
      $area = $location_info->area;
      $size = "0";
      $borders_lake = "0";
      $borders_sea = "0";
      $map = "0";
      $x = "0";
      $y = "0";
    }

    if ($step == "new") {

      $name = '';
      $type = 1;
      $regio = $region;
      $area = 1;
      $size = "0";
      $borders_lake = "0";
      $borders_sea = "0";
      $map = "0";
      $x = "0";
      $y = "0";
    }

    show_title ("LOCATIONS FORM");

    echo "<CENTER><TABLE WIDTH=700>";
			
    echo "<FORM METHOD=post ACTION=\"index.php?page=listlocations\">";
    echo "<INPUT TYPE=hidden NAME=region VALUE=$region>";
    echo "<INPUT TYPE=hidden NAME=regio VALUE=$regio>";
    echo "<INPUT TYPE=hidden NAME=id VALUE=$id>";
    echo "<INPUT TYPE=hidden NAME=step VALUE=store>";
    echo "<INPUT TYPE=hidden NAME=prevstep VALUE=$step>";
			
    echo "<TR VALIGN=top><TD WIDTH=200><B>Name:</B></TD>";
    echo "<TD COLSPAN=2 WIDTH=500><INPUT TYPE=text NAME=name VALUE=\"$name\" SIZE=30></TD></TR>";
    echo "<TR VALIGN=top><TD WIDTH=200><B>Type:</B></TD>";
    echo "<TD WIDTH=100>";
    echo "<INPUT TYPE=radio NAME=type VALUE=1"; if ($type == 1) { echo " CHECKED"; } echo ">location<BR>";
    echo "<INPUT TYPE=radio NAME=type VALUE=4"; if ($type == 4) { echo " CHECKED"; } echo ">subfield";
    echo "</TD><TD>";
			
    while (list ($key, $val) = each ($areanames)) {
				
      echo "<INPUT TYPE=radio NAME=area VALUE=$key"; if ($area == $key) { echo " CHECKED"; } echo "> $val<BR>";
    }
			
    echo "</TD></TR><TR VALIGN=top><TD WIDTH=200></TD><TD WIDTH=100>";
    echo "<INPUT TYPE=radio NAME=type VALUE=2"; if ($type == 2) { echo " CHECKED"; } echo ">building<BR>";
    echo "<INPUT TYPE=radio NAME=type VALUE=3"; if ($type == 3) { echo " CHECKED"; } echo ">vehicle";
    echo "</TD><TD>";
			
    echo "<SELECT NAME=area2>";
    $stm = $db->query("SELECT * FROM objecttypes ORDER BY category,name");
    foreach ($stm->fetchAll() as $objecttype_info) {
      echo "<OPTION VALUE=$objecttype_info->id"; if ($area == $objecttype_info->id) { echo " SELECTED"; } echo "> $objecttype_info->name<BR>";
    }
			
    echo "</SELECT>";
    echo "</TD></TR>";

    echo "<TR VALIGN=top><TD WIDTH=200><B>Size:</B></TD>";
    echo "<TD><INPUT TYPE=text SIZE=20 NAME=size VALUE=\"$size\"></TD></TR>"; 

    echo "<TR VALIGN=top><TD WIDTH=200><B>Coordinates:</B></TD>";
    echo "<TD><INPUT TYPE=text SIZE=10 NAME=x VALUE=\"$x\"> , "; 
    echo "<INPUT TYPE=text SIZE=10 NAME=y VALUE=\"$y\"></TD></TR>"; 

    echo "<TR VALIGN=top><TD WIDTH=200><B>Borders a lake:</B></TD>";
    echo "<TD>";
    echo "<INPUT TYPE=radio NAME=borders_lake VALUE=1"; if ($borders_lake) { echo " CHECKED"; } echo "> Yes<BR>";
    echo "<INPUT TYPE=radio NAME=borders_lake VALUE=0"; if (!$borders_lake) { echo " CHECKED"; } echo "> No";
    echo "</TD></TR>";
    echo "<TR VALIGN=top><TD WIDTH=200><B>Borders a sea:</B></TD>";
    echo "<TD>";
    echo "<INPUT TYPE=radio NAME=borders_sea VALUE=1"; if ($borders_sea) { echo " CHECKED"; } echo "> Yes<BR>";
    echo "<INPUT TYPE=radio NAME=borders_sea VALUE=0"; if (!$borders_sea) { echo " CHECKED"; } echo "> No";
    echo "</TD></TR>";
    echo "<TR VALIGN=top><TD WIDTH=200><B>Has a map:</B></TD>";
    echo "<TD>";
    echo "<INPUT TYPE=radio NAME=map VALUE=1"; if ($map) { echo " CHECKED"; } echo "> Yes<BR>";
    echo "<INPUT TYPE=radio NAME=map VALUE=0"; if (!$map) { echo " CHECKED"; } echo "> No";
    echo "</TD></TR>";
    echo "<TR VALIGN=top><TD COLSPAN=3 ALIGN=center><BR><INPUT TYPE=submit VALUE=\"Store\"></TD></TR>";
    
    echo "</TABLE></CENTER>";
    break;	
		
  case "store":
    // SANITIZE INPUT

    $name = HTTPContext::getRawString('name');
    $type = HTTPContext::getInteger('type');
    $regio = HTTPContext::getInteger('regio');
    $area = HTTPContext::getInteger('area');
    $borders_lake = HTTPContext::getInteger('borders_lake');
    $borders_sea = HTTPContext::getInteger('borders_sea');
    $map = HTTPContext::getInteger('map');
    $size = HTTPContext::getInteger('size');
    $x = HTTPContext::getInteger('x');
    $y = HTTPContext::getInteger('y');

    if ($prevstep == "edit") {
				
      if (($type == 1) or ($type == 4)) {
					
        $area = $area;
      } else {
					
        $area = $area2;
      }
				
      $stm = $db->prepare("UPDATE locations SET name = :name, type = :type, area = :area, borders_lake = :bordersLake,
       borders_sea = :bordersSea, map = :map, size = :size, x = :x, y = :y WHERE id = :id");
      $stm->execute([
        "name" => $name, "type" => $type, "area" => $area, "bordersLake" => $borders_lake,
        "bordersSea" => $borders_sea, "map" => $map, "size" => $size, "x" => $x, "y" => $y, "id" => $id,
      ]);
				
      $playerInfo = Request::getInstance()->getPlayer();
				
      $message = "Location altered by {$playerInfo->getFullName()}:\n\n";
      $message .= "ID: $id\n";
      $message .= "Name: $name\n";
      $message .= "Type: $type\n";
      $message .= "Area: $area\n";
      $message .= "Size: $size\n";
      $message .= "X: $x\n";
      $message .= "Y: $y\n";
      $message .= "Borders lake: $borders_lake\n";
      $message .= "Borders sea: $borders_sea\n";
      $message .= "Has map: $map\n\n";
      $message .= "(This is an automatically created message)";
				
      mail ($GLOBALS['emailGMS'],"Location $name changed","$message","From: Game Administration Council <".$GLOBALS['emailGMS'].">");
    } else {
				
      if (($type == 1) or ($type == 4)) {
					
	$area = $area;
      } else {
	
	$area = $area2;
      }
				
      $stm = $db->prepare("INSERT INTO locations (name,type,region,area,size,x,y,borders_lake,borders_sea,map)
        VALUES (:name, :type, :region, :area, :size, :x, :y, :bordersLake, :bordersSea, :map)");
      $stm->execute([
        "name" => $name, "type" => $type, "region" => $region, "area" => $area, "bordersLake" => $borders_lake,
        "bordersSea" => $borders_sea, "map" => $map, "size" => $size, "x" => $x, "y" => $y,
      ]);
      $id = $db->lastInsertId();

      $playerInfo = Request::getInstance()->getPlayer();
      $message = "Location added by {$playerInfo->getFullName()}:\n\n";
      $message .= "ID: $id\n";
      $message .= "Name: $name\n";
      $message .= "Type: $type\n";
      $message .= "Area: $area\n";
      $message .= "Size: $size\n";
      $message .= "X: $x\n";
      $message .= "Y: $y\n";
      $message .= "Borders lake: $borders_lake\n";
      $message .= "Borders sea: $borders_sea\n";
      $message .= "Has map: $map\n\n";
      $message .= "(This is an automatically created message)";
				
      mail ($GLOBALS['emailGMS'],"Location $name added","$message","From: Game Administration Council <".$GLOBALS['emailGMS'].">");
    }		
    break;
			
  case "drop":
    $stm = $db->prepare("UPDATE locations SET type=1,region=4 WHERE id = :id");
    $stm->bindInt("id", $id);
    $stm->execute();
			
    $stm = $db->prepare("SELECT name FROM locations WHERE id = :id");
    $stm->bindInt("id", $id);
    $stm->execute();
    $location_info = $stm->fetchObject();

    $playerInfo = Request::getInstance()->getPlayer();

    $message = "Location dropped by {$playerInfo->getFullName()}:\n\n";
    $message .= "ID: $id\n";
    $message .= "Name: $location_info->name\n";
    $message .= "(This is an automatically created message)";
    
    mail ($GLOBALS['emailGMS'],"Location $location_info->name dropped","$message","From: Game Administration Council <".$GLOBALS['emailGMS'].">");			
	}
    
  // LIST OF LOCATIONS
		    
  show_title ("LOCATIONS LIST");
    
  echo "<CENTER><TABLE WIDTH=700>";
    
  $level = 1;
	
  unset($ref);
  $stm = $db->prepare("SELECT * FROM locations WHERE type = 1 AND region = :region ORDER BY name");
  $stm->bindInt("region", $region);
  $stm->execute();
  $ref[$level] = $stm;
	
  while ($level) {
	
    if ($location_info = $ref[$level]->fetchObject()) {
			
      echo "<TR VALIGN=top><TD WIDTH=450>";
			
      echo str_repeat ("&nbsp;", ($level - 1) * 8);	
			
      echo "$location_info->name ($location_info->id)";

      echo " <A HREF=\"index.php?page=listlocations&region=$region&step=edit&id=$location_info->id\">[edit]</A>";
      echo " <A HREF=\"index.php?page=listlocations&region=$region&step=sub&id=$location_info->id\">[sub]</A>";
      echo " <A HREF=\"index.php?page=listlocations&region=$region&step=drop&id=$location_info->id\">[drop]</A>";
      
      $splocStm = $db->prepare("SELECT language FROM spawninglocations WHERE id = :id");
      $splocStm->bindInt("id", $location_info->id);
      $splotLanguage = $splocStm->executeScalar();
      if ($splotLanguage) {
        $languageName = LanguageConstants::$LANGUAGE[$splotLanguage]["en_name"];
        echo "<BR><I><B>".ucfirst($languageName) . " starting location</B></I>";
      }
			
      echo "</TD><TD WIDTH=150>";
			
      switch ($location_info->type) {
				
        case 1:
        case 4:
          echo $areanames[$location_info->area];
          break;
        case 2:
        case 3:
          $stm = $db->prepare("SELECT name FROM objecttypes WHERE id = :id");
          $stm->bindInt("id", $location_info->area);
          $typename = $stm->executeScalar();
          echo $typename;
          break;
      }
	
      echo "</TD>";
      echo "<TD WIDTH=50 ALIGN=right>$location_info->size</TD>";
      echo "<TD WIDTH=50>($location_info->x,$location_info->y)</TD>";
      echo "</TR>";
			
      $level++;
			
      $stm = $db->prepare("SELECT * FROM locations WHERE type > 1 AND region = :region ORDER BY name");
      $stm->bindInt("region", $location_info->id);
      $stm->execute();
      $ref[$level] = $stm;
    } else {
      $level--;
    }
  }
	
  echo "</TABLE>";
  echo "<CENTER><BR><BR><A HREF=\"index.php?page=listlocations&region=$region&step=new\">Create a new location</A>";
  echo "<BR><A HREF=\"index.php?page=player\">Back to player page</A></CENTER>";
} else {
  CError::throwRedirect("player", "You are trying to enter a game administration page you are not allowed access to.");
}
