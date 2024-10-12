<?php


class MapView
{
  private $isDegree;
  private $isCompass;
  private $language;
  /** @var Db */
  private $db;

  public function __construct($isDegree, $isCompass, $language)
  {
    $this->isDegree = $isDegree;
    $this->isCompass = $isCompass;
    $this->language = $language;
    $this->db = Db::get();
  }
  
  public function show($x, $y, $fov)
  {
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Tue, 24 Nov 1970 05:00:00 GMT");
    
    $zoom = 4.05;
    
    $isDegree = $this->isDegree;
    $isCompass = $this->isCompass;
    $language = $this->language;
    
    $distance = round(21*$fov/100); // for round view 

    if (isset($x) && isset($y)) {
      
      $compass = (($distance == 21) && ($isCompass)) ? 9 : 0;
      $output = imagecreatetruecolor ($distance * 2 * $zoom + 2 * $compass, $distance * 2 * $zoom + 2 * $compass);
      
      $color_sea = imagecolorallocate ($output, 116, 116, 242);
      $color_background = imagecolorallocate ($output, 0x00, 0x44, 0x00);
      $color_centrepoint = imagecolorallocate ($output, 255, 165, 0);
      $color_ship = imagecolorallocate ($output, 0,40,0);
      $color_ship_border = imagecolorallocate ($output, 180,180,210);
      $color_town = imagecolorallocate ($output, 0,0,0);
      $color_town_border = imagecolorallocate ($output, 100,100,100);
      $color_white = imagecolorallocate ($output, 255,255,255);
      $color_transparent = imagecolortransparent ($output, $color_background);
      
      // It seems as if the first allocated color is no more automatically used as the background.
      // Thus fill the image manually with the background color.
      imagefill($output, 1, 1, $color_sea);


      // Do we need info from an existing map?
      $stm = $this->db->query("SELECT x1, y1, width, height, file FROM maps");
      foreach ($stm->fetchAll() as $map) {

        if (($overlap = $this->overlap( $map->x1, $map->y1, $map->x1 + $map->width, $map->y1 + $map->height,
          $x - $distance, $y - $distance, $x + $distance, $y + $distance ))) {

          $x_offset_viewport = $x - $distance;
          $y_offset_viewport = $y - $distance;
          
          $dest_x = $overlap[0] - $x_offset_viewport;
          $dest_y = $overlap[1] - $y_offset_viewport;
          
          $x_offset_map = $map->x1;
          $y_offset_map = $map->y1;
          
          $source_x = $overlap[0] - $x_offset_map;
          $source_y = $overlap[1] - $y_offset_map;
          
          $source_width = $overlap[2] - $overlap[0];
          $source_height = $overlap[3] - $overlap[1];
          
          $stm = $this->db->prepare("SELECT data FROM maps WHERE x1 = :x && y1 = :y");
          $stm->bindFloat("x", $map->x1);
          $stm->bindFloat("y", $map->y1);
          $imagedata = $stm->executeScalar();
          
          $mapimage = imagecreatefromstring ($imagedata);
          
          imagecopyresampled ($output, $mapimage, $dest_x * $zoom + $compass, $dest_y * $zoom + $compass, $source_x, $source_y, $source_width * $zoom, $source_height * $zoom, $source_width, $source_height);

          imagedestroy ($mapimage);
        }
      }

      // Set a dot for other vessels

      $x1 = $x - $distance;
      $x2 = $x + $distance;
      $y1 = $y - $distance;
      $y2 = $y + $distance;

      $stm = $this->db->prepare("SELECT id FROM sailing WHERE
        x > (:x1 - 21) AND x < (:x2 + 21) AND y > (:y1 - 21) AND y < (:y2 + 21)");
      $stm->bindFloat("x1", $x1);
      $stm->bindFloat("x2", $x2);
      $stm->bindFloat("y1", $y1);
      $stm->bindFloat("y2", $y2);
      $stm->execute();
      foreach ($stm->fetchScalars() as $sailingId) {

        try {
          $sailing = Sailing::loadById($sailingId);
          $vesx = floor (($sailing->getX() - $x1) * $zoom);
          $vesy = floor (($sailing->getY() - $y1) * $zoom);

          $speedPerHour = $sailing->getSpeed() * (SailingConstants::TURNS_PER_DAY / GameDateConstants::HOURS_PER_DAY);

          if ($speedPerHour > 0) {
            $speedx = - (2 + 0.4 * $speedPerHour * GameDateConstants::HOURS_PER_DAY) * cos (deg2rad ($sailing->getDirection()));
            $speedy = - (2 + 0.4 * $speedPerHour * GameDateConstants::HOURS_PER_DAY) * sin (deg2rad ($sailing->getDirection()));
            
            $this->imageSmoothAlphaLine($output, $compass + $vesx, $compass + $vesy,
              floor ($compass + $vesx + $speedx), floor ($compass + $vesy + $speedy), 255, 255, 255);
          }
          
          imagefilledellipse ($output, $compass + $vesx, $compass + $vesy, 6, 6, $color_ship_border);
          imagefilledellipse ($output, $compass + $vesx, $compass + $vesy, 4, 4, $color_ship);
        } catch (InvalidArgumentException $e) {
          Logger::getLogger(__CLASS__)->warn("Impossible to show data for sailing ". $sailingId);
        }
      }
      // Set a dot for all towns

      $stm = $this->db->prepare("SELECT x, y FROM locations WHERE type=1 AND x > :x1 AND x < :x2 AND y > :y1 AND y < :y2");
      $stm->bindFloat("x1", $x1);
      $stm->bindFloat("x2", $x2);
      $stm->bindFloat("y1", $y1);
      $stm->bindFloat("y2", $y2);
      $stm->execute();
      foreach ($stm->fetchAll() as $town_info) {
        imagefilledellipse ($output, $compass + ($town_info->x - $x1) * $zoom,
          $compass + ($town_info->y - $y1) * $zoom, 8, 8, $color_town_border);
        imagefilledellipse ($output, $compass + ($town_info->x - $x1) * $zoom,
          $compass + ($town_info->y - $y1) * $zoom, 6, 6, $color_town);
      }
        //imagefilledellipse ($output, ($town_info->x - $x1) * $zoom, ($town_info->y - $y1) * $zoom, 4, 4, $color_town);
        //imagesetpixel ($output, 1, 1, $color_town);

      // Set a dot at the centre (= location of observer)
      imagefilledellipse ($output, $compass + $distance * $zoom, $compass + $distance * $zoom, 6, 6, $color_centrepoint);

      // No I want something like a circle cutout from the image, so that you really see everything from a certain
      // radius from the centre and not the rest.
      if (!$compass) {
        imageellipse ($output, $distance * $zoom, $distance * $zoom, 
          2 * $distance * $zoom - 4, 2 * $distance * $zoom - 4, $color_transparent);
    
        imagefilltoborder ($output, 0, 0, $color_transparent, $color_transparent);
      } else {
        $compassimage = imagecreatefromgif ($isDegree == 5 ? "graphics/cantr/pictures/compassdigit.gif" : "graphics/cantr/pictures/compassletter". ($language == 9 ? "pl" : "") .".gif");
        imagecolortransparent ($compassimage, imagecolorat ($compassimage, 100, 100));
        imagecopymerge ($output, $compassimage, 0, 0, 0, 0, 189, 189, 100);
        imagedestroy ($compassimage);
      }
      // Ok, dump the whole thing, then ;) ...
      header ('Content-Type: image/png');
      imagepng ($output);
      
    } else {
      Logger::getLogger("map")->error("not all params [". $x .",". $y ."] dist: ". $distance);
    }
  }

  // Returns an array of coordinates representing the overlap rectangle.
  // If no overlap is found, then it returns 0.

  private function overlap ($x11, $y11, $x12, $y12, $x21, $y21, $x22, $y22) {

    if ($x21 >= $x12) return 0; // Second rectangle to the right of first rectangle.
    if ($x22 <= $x11) return 0; // Second rectangle to the left of first rectangle.
      
    if ($y21 >= $y12) return 0; // Second rectangle below first rectangle.
    if ($y22 <= $y11) return 0; // Second rectangle above first rectangle.
      
    // If we are here, then we know that there's an overlapping.
    $result[] = max($x11, $x21);
    $result[] = max($y11, $y21);
    $result[] = min($x12, $x22);
    $result[] = min($y12, $y22);
    
    return $result;
  }

  // function imageSmoothAlphaLine() - version 1.0
  // taken from http://pl.php.net/manual/pl/function.imageline.php

  private function imageSmoothAlphaLine ($image, $x1, $y1, $x2, $y2, $r, $g, $b, $alpha=0) {
    $icr = $r;
    $icg = $g;
    $icb = $b;
    $dcol = imagecolorallocatealpha ($image, $icr, $icg, $icb, $alpha);
   
    if ($y1 == $y2 || $x1 == $x2) {
     imageline($image, $x1, $y1, $x2, $y2, $dcol);
    } else {
     $m = ($y2 - $y1) / ($x2 - $x1);
     $b = $y1 - $m * $x1;

     if (abs ($m) <2) {
       $x = min($x1, $x2);
       $endx = max($x1, $x2) + 1;

       while ($x < $endx) {
         $y = $m * $x + $b;
         $ya = ($y == floor($y) ? 1: $y - floor($y));
         $yb = ceil($y) - $y;
    
         $trgb = ImageColorAt($image, $x, floor($y));
         $tcr = ($trgb >> 16) & 0xFF;
         $tcg = ($trgb >> 8) & 0xFF;
         $tcb = $trgb & 0xFF;
         imagesetpixel($image, $x, floor($y), imagecolorallocatealpha($image, ($tcr * $ya + $icr * $yb), ($tcg * $ya + $icg * $yb), ($tcb * $ya + $icb * $yb), $alpha));
   
         $trgb = ImageColorAt($image, $x, ceil($y));
         $tcr = ($trgb >> 16) & 0xFF;
         $tcg = ($trgb >> 8) & 0xFF;
         $tcb = $trgb & 0xFF;
         imagesetpixel($image, $x, ceil($y), imagecolorallocatealpha($image, ($tcr * $yb + $icr * $ya), ($tcg * $yb + $icg * $ya), ($tcb * $yb + $icb * $ya), $alpha));
   
         $x++;
       }
     } else {
       $y = min($y1, $y2);
       $endy = max($y1, $y2) + 1;

       while ($y < $endy) {
         $x = ($y - $b) / $m;
         $xa = ($x == floor($x) ? 1: $x - floor($x));
         $xb = ceil($x) - $x;
   
         $trgb = ImageColorAt($image, floor($x), $y);
         $tcr = ($trgb >> 16) & 0xFF;
         $tcg = ($trgb >> 8) & 0xFF;
         $tcb = $trgb & 0xFF;
         imagesetpixel($image, floor($x), $y, imagecolorallocatealpha($image, ($tcr * $xa + $icr * $xb), ($tcg * $xa + $icg * $xb), ($tcb * $xa + $icb * $xb), $alpha));
   
         $trgb = ImageColorAt($image, ceil($x), $y);
         $tcr = ($trgb >> 16) & 0xFF;
         $tcg = ($trgb >> 8) & 0xFF;
         $tcb = $trgb & 0xFF;
         imagesetpixel ($image, ceil($x), $y, imagecolorallocatealpha($image, ($tcr * $xb + $icr * $xa), ($tcg * $xb + $icg * $xa), ($tcb * $xb + $icb * $xa), $alpha));
   
         $y ++;
       }
     }
    }
  }
}
