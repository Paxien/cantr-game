<?php

$cloth_id = HTTPContext::getInteger('object_id');
$page = HTTPContext::getRawString('page');

$db = Db::get();
$stm = $db->prepare("SELECT cloth.id, cloth.specifics, cloth.person,
  clothtype.objectcategory clothcategory, cloth.weight AS weight, clothtype.project_weight AS standard_weight
  FROM objects as cloth, objecttypes as clothtype, objectcategories as objCat
  WHERE cloth.id = :clothId  AND clothtype.id = cloth.type
    AND clothtype.objectcategory = objCat.id AND objCat.parent = :clothesCategory");
$stm->bindInt('clothId', $cloth_id);
$stm->bindInt('clothesCategory', ObjectConstants::OBJCAT_CLOTHES);
$stm->execute();
$cloth_info = $stm->fetchObject();

if (!$cloth_info) {
  CError::throwRedirectTag("char.events", "error_clothes_wear_illegal_type");
} elseif ($cloth_info->person != $character) {
  CError::throwRedirectTag("char.events", "error_clothes_wear_illegal_owner");
}

/**
 * @param $clothingToTakeOff
 * @param Db $db
 * @throws Exception
 */
function takeOffClothing($clothingToTakeOff, Db $db)
{
  $stm = $db->prepare("SELECT objecttypes.project_weight AS weight
        FROM objecttypes, objects WHERE objects.id = :clothingId
        AND objecttypes.id = objects.type LIMIT 1");
  $stm->bindInt('clothingId', $clothingToTakeOff->id);
  $ownWeight = $stm->executeScalar();

  $newSpecifics = str_replace("wearing:1", "wearing:0", $clothingToTakeOff->specifics);
  $stm = $db->prepare("UPDATE `objects` SET `specifics` = :newSpecifics,
                     `weight` = `weight` + :additionalWeight WHERE `id` = :clothingId LIMIT 1");
  $stm->bindStr('newSpecifics', $newSpecifics);
  $stm->bindInt('additionalWeight', $ownWeight);
  $stm->bindInt('clothingId', $clothingToTakeOff->id);
  $stm->execute();
}

if ($page == "wear") {
  // If character already wears something of the same type, take it off first
  $stm = $db->prepare("SELECT obj.id, obj.specifics FROM objects obj, objecttypes objtype
      WHERE person = :charId AND obj.type=objtype.id AND objtype.objectcategory = :category
      AND lower(obj.specifics) LIKE '%wearing:1%' LIMIT :maxRings");
  $stm->bindInt('charId', $char->getId());
  $stm->bindInt('maxRings', ObjectConstants::MAX_RINGS_WORN);
  $stm->bindInt('category', $cloth_info->clothcategory);
  $stm->execute();
  $clothesBeingWorn = $stm->fetchAll();
  if ($cloth_info->clothcategory == _CLOTH_CATEGORY_RINGS && count($clothesBeingWorn) >= _MAX_WEARING_RINGS) {
    CError::throwRedirectTag("char.events", "error_wear_toomuch_rings");
  }

  $needToTakeOff = $cloth_info->clothcategory != _CLOTH_CATEGORY_RINGS && count($clothesBeingWorn) > 0;
  if ($needToTakeOff) {
    takeOffClothing($clothesBeingWorn[0], $db);
  }

  if (strpos($cloth_info->specifics, "wearing:") === false) {
    $cloth_info->specifics .= ";wearing:0";
  }
  $newSpecifics = str_replace("wearing:0", "wearing:1", $cloth_info->specifics);
  $weightWithoutStandardWeight = $cloth_info->weight - $cloth_info->standard_weight; // it's > 0 for clothes being containers
  $stm = $db->prepare("UPDATE `objects` SET `specifics` = :newSpecifics,
                     `weight`= GREATEST(0, :newWeight) WHERE `id` = :clothingId LIMIT 1");

  $stm->bindStr('newSpecifics', $newSpecifics);
  $stm->bindInt('newWeight', $weightWithoutStandardWeight);
  $stm->bindInt('clothingId', $cloth_info->id);
  $stm->execute();
}
if ($page == "unwear") {
  takeOffClothing($cloth_info, $db);
}

redirect("characterdescription", ["ocharid" => $character]);
