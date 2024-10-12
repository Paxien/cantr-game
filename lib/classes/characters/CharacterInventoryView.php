<?php

class CharacterInventoryView
{
  /** @var Character */
  private $char;
  /** @var Character */
  private $observer;
  /** @var Db */
  private $db;

  public function __construct(Character $char, Character $observer)
  {
    $this->char = $char;
    $this->observer = $observer;
    $this->db = Db::get();
  }

  public function getVisibleItems()
  {
    $inventory_items = array();

    $contList = CObject::inInventoryOf($this->char)->hasProperty("Storage")->findIds();

    $stm = $this->db->prepare("SELECT o.id AS oid FROM objects o
      INNER JOIN objecttypes ot ON ot.id = o.type AND ot.visible = 1
      INNER JOIN objectcategories oc ON oc.id = ot.objectcategory
      WHERE (o.person = :charId) AND 
        ( (oc.parent IS NULL OR oc.parent != :clothesCategory)
        OR ( o.specifics IS NULL OR o.specifics NOT LIKE '%wearing:1%' ) )
      ORDER BY ot.objectcategory");
    $stm->bindInt("charId", $this->char->getId());
    $stm->bindInt("clothesCategory", ObjectConstants::OBJCAT_CLOTHES);
    $stm->execute();

    $objects = CObject::bulkLoadByIds($stm->fetchScalars());
    foreach ($objects as $carried) {
      $objectView = new ObjectView($carried, $this->observer);
      $desc = $objectView->show('transfer');

      $item = [];
      $item['name'] = $desc->transfer;
      $item['description'] = Descriptions::getDescription($carried->getId(), Descriptions::TYPE_OBJECT);
      if (in_array($carried->getId(), $contList)) {
        $contentNames = $carried->printContainerContent();
        $item['bottom'] = (!empty($contentNames)) ? "<p class=\"sign\">$contentNames</p>" : "";
      }
      $inventory_items[] = $item;
    }

    return $inventory_items;
  }

  public function getInterpretedClothes() {
    $clothes = $this->getClothes();
    $toTranslate = array();
    foreach ($clothes as $clothing) {
      $toTranslate[] = $clothing['name'];
      if (strstr($clothing['desc'], "<CANTR")) {
        $toTranslate[] = $clothing['desc'];
      }
    }

    $tag = new ReplaceTag(null, false, null, $this->observer->getId(), $this->observer->getLanguage());
    $translated = $tag->interpretQueue($toTranslate);
    foreach ($clothes as &$clothing) {
      $clothing['name'] = $translated[$clothing['name']];
      $clothing['desc'] = isset($translated[$clothing['desc']]) ? $translated[$clothing['desc']] : $clothing['desc'];
    }
    return $clothes;
  }

  public function getClothes()
  {
    $clothes = array();

    $stm = $this->db->prepare("SELECT cloth.id cid, ctype.name AS cname, ctype.unique_name,
      ccategory.hides, ctype.objectcategory AS ctype
      FROM objects AS cloth
      INNER JOIN objecttypes AS ctype ON ctype.id = cloth.type
      INNER JOIN objectcategories AS objCat ON objCat.id = ctype.objectcategory
        AND objCat.parent = :clothesCategory
      INNER JOIN clothes_categories AS ccategory ON ccategory.id = ctype.objectcategory
      WHERE cloth.person = :charId AND cloth.specifics LIKE '%wearing:1%'
      ORDER BY ccategory.sortn");
    $stm->bindInt("clothesCategory", ObjectConstants::OBJCAT_CLOTHES);
    $stm->bindInt("charId", $this->char->getId());
    $stm->execute();

    $hidden = array();
    foreach ($stm->fetchAll() as $cloth_info) {
      $hidden = array_merge($hidden, explode(',', $cloth_info->hides));
      $cName = "<CANTR REPLACE NAME=item_". $cloth_info->unique_name ."_o>";
      $cDesc = Descriptions::getDescription($cloth_info->cid, Descriptions::TYPE_OBJECT);
      if (empty($cDesc)) {
        $cDesc = "<CANTR REPLACE NAME=cloth_desc_". $cloth_info->unique_name .">";
      }

      $clothes[] = array(
        "id" => $cloth_info->cid,
        "type" => $cloth_info->ctype,
        "name" => $cName,
        "desc" => $cDesc,
      );
    }

    foreach ($clothes as &$clothing) { // check if piece of cloth is visible
      $clothing['hidden'] = in_array($clothing["type"], $hidden);
    }

    return $clothes;
  }

}
