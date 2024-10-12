<?php

class CSSTidyOptimiseValidationExtension extends csstidy_optimise
{

  public function __construct($css)
  {
    parent::__construct($css);
  }

  function discard_invalid_selectors(&$array)
  {
    foreach ($array as $selector => $decls) {
      $ok = true;
      $selectors = array_map('trim', explode(',', $selector));
      foreach ($selectors as $s) {
        $simple_selectors = preg_split('/\s*[+>~\s]\s*/', $s);
        foreach ($simple_selectors as $ss) {
          if ($ss === '') {
            $ok = false;
          } elseif (!preg_match("/^[a-zA-Z#\.][a-zA-Z0-9\-_\.#:]*$/", $ss)) {
            $ok = false;
          }
          // could also check $ss for internal structure,
          // but that probably would be too slow
        }
      }
      if (!$ok) {
        unset($array[$selector]);
      }
    }
  }
}
