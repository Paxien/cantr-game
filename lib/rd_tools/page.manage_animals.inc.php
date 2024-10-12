<?php

$action = $_REQUEST['action'];

$db = Db::get();
$playerInfo = Request::getInstance()->getPlayer();
if ($playerInfo->hasAccessTo(AccessConstants::ALTER_ANIMAL_TYPES)) {
  show_title ("<CANTR REPLACE NAME=title_manage_animals>");

  echo "<CENTER><TABLE WIDTH=700><TR><TD>";

  switch ($action) {

  case "new" :
  case "edit" :
    // SANITIZE INPUT
    $id = HTTPContext::getInteger("id");

    $animal_types = array();

    $animal_types[0] = "vegetation";
    $stm = $db->query("SELECT id,name FROM animal_types ORDER BY name");
    foreach ($stm->fetchAll() as $animal_type) {
      $animal_types[$animal_type->id] = $animal_type->name;
    }
    if ($action == "edit") {
      $stm = $db->prepare("SELECT adt.*, adt.id AS iid, at.* FROM animal_types at
        LEFT JOIN animal_domesticated_types adt ON adt.of_animal_type = at.id WHERE at.id = :id");
      $stm->bindInt("id", $id);
      $stm->execute();
      $animal = $stm->fetchObject();
    }

    echo "<FORM METHOD=post ACTION=\"index.php?page=manage_animals&action=store\">";
    echo "<TABLE>";
    echo "<TR><TD COLSPAN=2>If creating new type of animal then leave empty.</TD></TR>";
    echo "<TR><TD>id:</TD>";
    echo "<TD><INPUT TYPE=text NAME=id SIZE=30 VALUE=\"$animal->id\">";

    echo "<TR><TD COLSPAN=2><CANTR REPLACE NAME=page_manage_animals_1></TD></TR>";
    echo "<TR><TD><CANTR REPLACE NAME=form_name>:</TD>";
    echo "<TD><INPUT TYPE=text NAME=name SIZE=60 VALUE=\"$animal->name\">";
    echo "</TD></TR>";
    echo "<TR><TD COLSPAN=2><CANTR REPLACE NAME=page_manage_animals_3> (if domesticated - type 0)</TD></TR>";
    echo "<TR><TD><CANTR REPLACE NAME=form_travel_chance>:</TD>";
    echo "<TD><INPUT TYPE=text SIZE=20 NAME=travel_chance VALUE=$animal->travel_chance> %</TD></TR>";
    echo "<TR><TD COLSPAN=2><CANTR REPLACE NAME=page_manage_animals_4></TD></TR>";
    echo "<TR><TD><CANTR REPLACE NAME=form_reproduction_chance>:</TD>";
    echo "<TD><INPUT TYPE=text SIZE=20 NAME=reproduction_chance VALUE=$animal->reproduction_chance> %</TD></TR>";
    echo "<TR><TD COLSPAN=2><CANTR REPLACE NAME=page_manage_animals_5></TD></TR>";
    echo "<TR><TD><CANTR REPLACE NAME=form_area_types>:</TD>";
    echo "<TD><SELECT MULTIPLE NAME=area_types[] SIZE=3>";

    $stm = $db->prepare("SELECT unique_name FROM objecttypes WHERE objectcategory = :category");
    $stm->bindInt("category", ObjectConstants::OBJCAT_TERRAIN_AREAS);
    $stm->execute();
    foreach ($stm->fetchScalars() as $areaName) {
      echo "<OPTION VALUE=\"$areaName\" "; if (strstr($animal->area_types, $areaName)) { echo " SELECTED"; } echo ">$areaName";
    }

    echo "</SELECT></TD></TR>";
    echo "<TR><TD COLSPAN=2><CANTR REPLACE NAME=page_manage_animals_6> (if domesticated type 0)</TD></TR>";
    echo "<TR><TD><CANTR REPLACE NAME=form_attack_chance>:</TD>";
    echo "<TD><INPUT TYPE=text SIZE=20 NAME=attack_chance VALUE=$animal->attack_chance> %</TD></TR>";
    echo "<TR><TD COLSPAN=2><CANTR REPLACE NAME=page_manage_animals_7></TD></TR>";
    echo "<TR><TD><CANTR REPLACE NAME=form_attack_force>:</TD>";
    echo "<TD><INPUT TYPE=text SIZE=20 NAME=attack_force VALUE=$animal->attack_force></TD></TR>";
    echo "<TR><TD COLSPAN=2><CANTR REPLACE NAME=page_manage_animals_10></TD></TR>";
    echo "<TR><TD><CANTR REPLACE NAME=form_strength>:</TD>";
    echo "<TD><INPUT TYPE=text SIZE=20 NAME=strength VALUE=$animal->strength></TD></TR>";
    echo "<TR><TD COLSPAN=2><CANTR REPLACE NAME=page_manage_animals_11></TD></TR>";
    echo "<TR><TD><CANTR REPLACE NAME=form_armour>:</TD>";
    echo "<TD><INPUT TYPE=text SIZE=20 NAME=armour VALUE=$animal->armour></TD></TR>";
    echo "<TR><TD COLSPAN=2><CANTR REPLACE NAME=page_manage_animals_12></TD></TR>";
    echo "<TR><TD><CANTR REPLACE NAME=form_resources>:</TD>";
    echo "<TD><INPUT TYPE=text SIZE=60 NAME=resources VALUE='$animal->resources'></TD></TR>";
    echo "<TR><TD COLSPAN=2><CANTR REPLACE NAME=page_manage_animals_15> (if domesticated it is number which takes 1 digging slot)</TD></TR>";
    echo "<TR><TD><CANTR REPLACE NAME=form_max_in_location>:</TD>";
    echo "<TD><INPUT TYPE=text SIZE=20 NAME=max_in_location VALUE='$animal->max_in_location'></TD></TR>";

    echo "<TR><TD COLSPAN=2>If you want to have that animal domesticable into some else then type its id below.</TD></TR>";
    echo "<tr><td>Domesticable into:</td>";
    echo "<td><input type=text size=20 name=domesticable_into value='$animal->domesticable_into'></td></tr>";

    echo "<tr><td>Is domesticated?</td>";
    echo "<td><input type=checkbox name=is_domesticated value=yes "; if ($animal->iid) echo "checked"; echo "></td></tr>";

    echo "<TR><TD COLSPAN=2>Domesticated part. If animal is wild - leave empty. INFO <a href=\"http://forum.cantr.org/viewtopic.php?f=9&t=24297\">HERE</a></TD></TR>";

    echo "<tr><td>Type details:</td>";
    echo "<TD><textarea rows=5 cols=50 NAME=type_details>$animal->type_details</textarea></TD></TR>";
    echo "<TR><TD COLSPAN=2>Food is a bitmask; 1: hay, 2: vegetables, 4: meat</TD></TR>";
    echo "<TR><TD>Food types:</TD>";
    echo "<TD><INPUT TYPE=text SIZE=6 NAME=food_type VALUE='$animal->food_type'></TD></TR>";
    echo "<TR><TD>Food amount:</TD>";
    echo "<TD><INPUT TYPE=text SIZE=15 NAME=food_amount VALUE='$animal->food_amount'></TD></TR>";
    echo "<TR><TD>Tame rules:</TD>";
    echo "<TD><textarea rows=5 cols=50 NAME=tame_rules>$animal->tame_rules</textarea></TD></TR>";
    echo "<TR><TD>Weight:</TD>";
    echo "<TD><INPUT TYPE=text SIZE=10 NAME=weight VALUE='$animal->weight'></TD></TR>";
    echo "<TR><TD>Can be loyal:</TD>";
    echo "<td><input type=checkbox name=can_be_loyal value=yes "; if ($animal->can_be_loyal) echo "checked"; echo "></td></tr>";

    echo "</TABLE>";

    echo "<CENTER><BR><INPUT TYPE=submit VALUE=\"<CANTR REPLACE NAME=button_store>\"></CENTER>";
    echo "</FORM><BR>";

    break;

  case "store" :
    //SANITIZE INPUT
    $id = HTTPContext::getInteger("id");
    $name = HTTPContext::getRawString("name");
    $travel_chance = HTTPContext::getInteger("travel_chance");
    $reproduction_chance  = HTTPContext::getInteger("reproduction_chance");

    $area_types = HTTPContext::getArray("area_types");
    $area_types_str = implode(",", $area_types);

    $attack_chance  = HTTPContext::getInteger("attack_chance");
    $attack_force  = HTTPContext::getInteger("attack_force");
    $strength  = HTTPContext::getInteger("strength");
    $armour  = HTTPContext::getInteger("armour");
    $max_in_location  = HTTPContext::getInteger("max_in_location");
    $domesticable_into  = HTTPContext::getInteger("domesticable_into");
    $food_type  = HTTPContext::getInteger("food_type");
    $food_amount  = HTTPContext::getInteger("food_amount");
    $weight  = HTTPContext::getInteger("weight");
    $can_be_loyal  = HTTPContext::getInteger("can_be_loyal");

    $is_domesticated  = HTTPContext::getRawString("is_domesticated");
    $resources  = HTTPContext::getRawString("resources");
    $type_details  = HTTPContext::getRawString("type_details");
    $tame_rules  = HTTPContext::getRawString("tame_rules");
    $domesticable_into = $domesticable_into ?: null;

    $stm = $db->prepare("SELECT COUNT(*) FROM animal_types WHERE id = :id");
    $stm->bindInt("id", $id);
    $count = $stm->executeScalar();

    if ($count) {
      $stm = $db->prepare("UPDATE animal_types SET name = :name, travel_chance = :travelChance,
                        reproduction_chance = :reproductionChance, area_types = :areaTypes,
                        attack_chance = :attackChance, attack_force = :attackForce, strength = :strength,
                        armour = :armour, resources = :resources, max_in_location = :maxInLocation,
                        domesticable_into = :domesticableInto WHERE id = :id");
      $stm->execute([
        "name" => $name, "travelChance" => $travel_chance, "reproductionChance" => $reproduction_chance,
        "areaTypes" => $area_types_str, "attackChance" => $attack_chance, "attackForce" => $attack_force,
        "strength" => $strength, "armour" => $armour, "resources" => $resources, "maxInLocation" => $max_in_location,
        "domesticableInto" => $domesticable_into, "id" => $id,
      ]);
    } else {
      $stm = $db->prepare("INSERT INTO animal_types (name, travel_chance, reproduction_chance, area_types,
        attack_chance, attack_force, strength, armour, resources, max_in_location, domesticable_into)
        VALUES (:name, :travelChance, :reproductionChance, :areaTypes, :attackChance, :attackForce,
          :strength, :armour, :resources, :maxInLocation, :domesticableInto)");
      $stm->execute([
        "name" => $name, "travelChance" => $travel_chance, "reproductionChance" => $reproduction_chance,
        "areaTypes" => $area_types_str, "attackChance" => $attack_chance, "attackForce" => $attack_force,
        "strength" => $strength, "armour" => $armour, "resources" => $resources, "maxInLocation" => $max_in_location,
        "domesticableInto" => $domesticable_into,
      ]);
      $id = $db->lastInsertId();
    }

    if ($is_domesticated) {
      $stm = $db->prepare("SELECT COUNT(*) FROM animal_domesticated_types WHERE of_animal_type = :id");
      $stm->bindInt("id", $id);
      $ref_adt = $stm->executeScalar();
      $type_details = HTTPContext::getRawString("type_details");
      $food_type = HTTPContext::getInteger("food_type");
      $food_amount = HTTPContext::getInteger("food_amount");
      $tame_rules = HTTPContext::getRawString("tame_rules");
      $weight = HTTPContext::getInteger("weight");
      $can_be_loyal = $_REQUEST['can_be_loyal'] ? 1 : 0;

      if ($ref_adt) {
        $stm = $db->prepare("UPDATE animal_domesticated_types SET type_details = :typeDetails, food_type = :foodType,
          food_amount = :foodAmount, tame_rules = :tameRules, weight = :weight, can_be_loyal = :canBeLoyal
          WHERE of_animal_type = :id");
        $stm->execute([
          "typeDetails"=> $type_details, "foodType" => $food_type, "foodAmount" => $food_amount,
          "tameRules" => $tame_rules, "weight" => $weight, "canBeLoyal" => $can_be_loyal, "id" => $id,
        ]);
        $stm = $db->prepare("SELECT of_object_type FROM animal_domesticated_types WHERE of_animal_type = :id");
        $stm->bindInt("id", $id);
        $objectType = $stm->executeScalar();
        $stm = $db->prepare("UPDATE objecttypes SET project_weight = :weight WHERE id = :id");
        $stm->bindInt("weight", $weight);
        $stm->bindInt("id", $objectType);
        $stm->execute();
      } else {
        $stm = $db->prepare("SELECT id FROM objecttypes WHERE name = :name LIMIT 1");
        $stm->bindStr("name", $name);
        $objectTypeWithSameName = $stm->executeScalar();
        if ($objectTypeWithSameName == null) {
          $instrOut = "buttons:pack_join>pack_join>alt_pack_join,".
            "pet_animal>custom_event/petanimal_owner/petanimal_observer>alt_pet_animal";
          $instrInv = "buttons:pet_animal>custom_event/petheldanimal_owner/petheldanimal_observer>alt_pet_animal";

          $stm = $db->prepare("INSERT INTO objecttypes (name, unique_name,
            show_instructions_outside, show_instructions_inventory, subtable, skill, category, rules,
            visible, report, deter_rate_turn, deter_rate_use, repair_rate, deter_visible, project_weight, objectcategory)
          VALUES (:name, :uniqueName, :showInstructionsOutside, :showInstructionsInventory,  'animal_domesticated', 0,
          'domesticated animals', 0, 1, 2, 0, 0, 0, 1, :weight, :category)");
          $stm->execute([
            "name" => $name, "uniqueName" => str_replace(" ", "_", $name),
            "showInstructionsOutside" => $instrOut, "showInstructionsInventory" => $instrInv, "weight" => $weight,
            "category" => ObjectConstants::OBJCAT_DOMESTICATED_ANIMALS]);
          $objectType = $db->lastInsertId();
        } else {
          $objectType = $objectTypeWithSameName;
        }
        $stm = $db->prepare("INSERT INTO animal_domesticated_types (of_animal_type, of_object_type, type_details,
          food_type, food_amount, tame_rules, weight, can_be_loyal)
          VALUES (:id, :objectType, :typeDetails, :foodType, :foodAmount, :tameRules, :weight, :canBeLoyal)");
        $stm->execute([
          "id" => $id, "objectType" => $objectType, "typeDetails"=> $type_details, "foodType" => $food_type,
          "foodAmount" => $food_amount, "tameRules" => $tame_rules, "weight" => $weight, "canBeLoyal" => $can_be_loyal,
        ]);
      }
    }
    else {
      $stm = $db->prepare("SELECT of_object_type FROM animal_domesticated_types WHERE of_animal_type = :id");
      $stm->bindInt("id", $id);
      $objectType = $stm->executeScalar();
      if ($objectType != null) {
        $stm = $db->prepare("DELETE FROM objecttypes WHERE id = :objectType LIMIT 1");
        $stm->bindInt("objectType", $objectType);
        $stm->execute();

        $stm = $db->prepare("DELETE FROM animal_domesticated_types WHERE of_animal_type = :id LIMIT 1");
        $stm->bindInt("id", $id);
        $stm->execute();
      }
    }



  default :

    show_title ("Wild animals");

    echo "<TABLE class=\"altern\">";

    echo "<TR><TD><CANTR REPLACE NAME=form_name></TD><TD><CANTR REPLACE NAME=form_travel_chance></TD><TD><CANTR REPLACE NAME=form_reproduction_chance></TD><TD><CANTR REPLACE NAME=form_area_types></TD><TD><CANTR REPLACE NAME=form_attack_chance></TD><TD><CANTR REPLACE NAME=form_attack_force></TD><TD><CANTR REPLACE NAME=form_strength></TD><TD><CANTR REPLACE NAME=form_armour></TD><TD><CANTR REPLACE NAME=form_resources></TD><TD><CANTR REPLACE NAME=form_max_in_location></TD><td>Domesticable into</td><TD></TD></TR>";
    $stm = $db->query("SELECT at.*, ado.name AS dom_in_name FROM animal_types at
      LEFT JOIN animal_domesticated_types adt ON adt.of_animal_type = at.id
      LEFT JOIN animal_types ado ON ado.id = at.domesticable_into
      WHERE adt.id IS NULL ORDER BY at.name");
    foreach ($stm->fetchAll() as $animal) {
      echo "<TR>";
      echo "<TD>$animal->name ($animal->id)</TD>";
      echo "<TD>$animal->travel_chance %</TD>";
      echo "<TD>$animal->reproduction_chance %</TD>";
      echo "<TD style=\"word-wrap:break-word;max-width:300px;\">$animal->area_types</TD>";
      echo "<TD>$animal->attack_chance %</TD>";
      echo "<TD>$animal->attack_force</TD>";
      echo "<TD>$animal->strength</TD>";
      echo "<TD>$animal->armour</TD>";
      echo "<TD style=\"word-wrap:break-word;max-width:300px;\">$animal->resources</TD>";
      echo "<TD>$animal->max_in_location</TD>";
      echo "<td style=\"word-wrap:break-word;max-width:100px;\">$animal->domesticable_into $animal->dom_in_name</td>";
      echo "<TD><A HREF=\"index.php?page=manage_animals&action=edit&id=$animal->id\">[<CANTR REPLACE NAME=button_view_edit>]</A></TD>";
      echo "</TR>";
    }

    echo "</TABLE>";


    // domesticated

    show_title ("Domesticated animals");

    echo "<TABLE class=\"altern\">";

    echo "<TR><TD><CANTR REPLACE NAME=form_name></TD>
    <TD><CANTR REPLACE NAME=form_travel_chance></TD>
    <TD><CANTR REPLACE NAME=form_reproduction_chance></TD>
    <TD><CANTR REPLACE NAME=form_area_types></TD>
    <TD><CANTR REPLACE NAME=form_attack_chance></TD>
    <TD><CANTR REPLACE NAME=form_attack_force></TD>
    <TD><CANTR REPLACE NAME=form_strength></TD>
    <TD><CANTR REPLACE NAME=form_armour></TD>
    <TD><CANTR REPLACE NAME=form_resources></TD>
    <TD><CANTR REPLACE NAME=form_max_in_location>
    </TD><td>Domesticable into</td>
    <td>Type details</td>
    <td>Food type</td>
    <td>Food amount</td>
    <td>Tame rules</td>
    <td>Weight</td>
    <td>Can be loyal</b>
    <TD></TD></TR>";
    $stm = $db->query("SELECT adt.*, adt.id AS iid, at.* FROM animal_types at
      LEFT JOIN animal_domesticated_types adt ON adt.of_animal_type = at.id
      WHERE adt.id IS NOT NULL ORDER BY at.name");
    foreach ($stm->fetchAll() as $animal) {
      echo "<TR>";
      echo "<TD>$animal->name ($animal->id)</TD>";
      echo "<TD>$animal->travel_chance %</TD>";
      echo "<TD>$animal->reproduction_chance %</TD>";
      echo "<TD style=\"word-wrap:break-word;max-width:300px;\">$animal->area_types</TD>";
      echo "<TD>$animal->attack_chance %</TD>";
      echo "<TD>$animal->attack_force</TD>";
      echo "<TD>$animal->strength</TD>";
      echo "<TD>$animal->armour</TD>";
      echo "<TD style=\"word-wrap:break-word;max-width:300px;\">$animal->resources</TD>";
      echo "<TD>$animal->max_in_location</TD>";
      echo "<td style=\"word-wrap:break-word;max-width:100px;\">$animal->domesticable_into</td>";
      echo "<td style=\"word-wrap:break-word;max-width:400px;\">$animal->type_details</td>";
      echo "<td>$animal->food_type</td>";
      echo "<td>$animal->food_amount</td>";
      echo "<td style=\"word-wrap:break-word;max-width:300px;\">$animal->tame_rules</td>";
      echo "<td>$animal->weight</td>";
      echo "<td>$animal->can_be_loyal</td>";
      echo "<TD><A HREF=\"index.php?page=manage_animals&action=edit&id=$animal->id\">[<CANTR REPLACE NAME=button_view_edit>]</A></TD>";
      echo "</TR>";
    }

    echo "</TABLE>";

    echo "<CENTER><BR><A HREF=\"index.php?page=manage_animals&action=new\"><CANTR REPLACE NAME=page_manage_animals_new></A></CENTER>";
  }

  echo "<CENTER><A HREF=\"index.php?page=player\"><CANTR REPLACE NAME=back_to_player></A></CENTER>";
  echo "</TD></TR></TABLE></CENTER>";
} else {
  CError::throwRedirectTag("player", "error_not_authorized");
}
