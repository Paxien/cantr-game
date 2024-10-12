<?php

  $smarty = new CantrSmarty; 

  $tag = new tag;
  $tag->language = $l;
  $tag->content = "<CANTR REPLACE NAME=decimal_separator>";
  $decimalSep = $tag->interpret();
  
  function FA ($Val) {
    $result = sprintf ("%.2f", $Val);
    if ($GLOBALS ['decimalSep'] != '.')
      $result = str_replace (".", $GLOBALS ['decimalSep'], $result);
    return $result;
  }

  $db = Db::get();
  $OriginalBalance = 0;
  $stm = $db->query("SELECT * FROM finance ORDER BY id");
  foreach ($stm->fetchAll() as $Month) {
    // Must come before formatting
    $TotalIn += $Month->advertisements + $Month->periodicdonations;
    $TotalOut += $Month->transactionfees + $Month->serverrental + $Month->domainname + $Month->marketing;

    // string formatting
    $Month->advertisements = FA ($Month->advertisements);
    $Month->periodicdonations = FA ($Month->periodicdonations);
    $Month->transactionfees = FA ($Month->transactionfees);
    $Month->serverrental = FA ($Month->serverrental);
    $Month->domainname = FA ($Month->domainname);
    $Month->marketing = FA ($Month->marketing);
    
    $Months [] = clone $Month;
  }
  
  // $OriginalBalance = 1191.02;
  $OriginalBalance = 0;
  $CurrentBalance = $OriginalBalance + $TotalIn - $TotalOut;
 
  $smarty->assign ("Months", $Months);
  $smarty->assign ("OriginalBalance", FA ($OriginalBalance));
  $smarty->assign ("CurrentBalance", FA ($CurrentBalance));
  $smarty->assign ("TotalIn", FA ($TotalIn));
  $smarty->assign ("TotalOut", FA ($TotalOut));
  
  $smarty->displayLang ("statistics/info.financessummary.tpl", $lang_abr);






