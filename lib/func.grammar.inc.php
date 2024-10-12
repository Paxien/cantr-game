<?php

function adjust_nounphrase($l, $art, $adj, $noun, $situation, $gender = null, $forreplace = null)
{

  // don't make assumptions
  $situation = strtoupper($situation);
  $gender = strtolower($gender);

  if (!$adj) {
    $nounphrase = $noun;
    $wordorder = "<ITEM>";
  } else {
    $nounphrase = $adj . " " . $noun;
    $wordorder = "<ADJ> <ITEM>";
  }

  switch ($l) {
    case LanguageConstants::ENGLISH:

      if (!$art) {
        $art = "a";
      }

      $firstLetter = $nounphrase[0];
      if (strstr("aeiou", $firstLetter) && $adj != "used") { // if the first letter is a vowel
        $art = "an";
      }

      $nounphrase = $art . " " . $nounphrase;
      $wordorder = "<ART> " . $wordorder;
      break;

    case LanguageConstants::DUTCH:

      if (!$art) {
        $art = "een";
      }
      $nounphrase = $art . " " . $nounphrase;
      $wordorder = "<ART> " . $wordorder;
      break;

    case LanguageConstants::FRENCH:

      $original_adj = $adj;

      if (!$art) {
        $art = "un";
      }
      if (($gender == "f") && ($art == "un")) {
        $art = "une";
      }

      if ($adj) {
        if (($gender == "f") && ($adj != "flambant neuf")) {
          $adj = $adj . "e";
        }
        if ($adj == "vieuxe") {
          $adj = "vieille";
        }
        if ($adj == "nouveaue") {
          $adj = "nouvelle";
        }
        if ($adj == "vieux") {
          $letter = substr($noun, 0, 1);
          if (($letter == "a") || ($letter == "e") || ($letter == "i") || ($letter == "o") || ($letter = "u")) {
            $adj = "vieil";
          }
        }
      }

      if (!$adj) {
        $nounphrase = $art . " " . $noun;
        $wordorder = "<ART> <ITEM>";
      } else {
        $nounphrase = $art . " " . $noun . " " . $adj;
        $wordorder = "<ART> <ITEM> <ADJ>";
        if (($original_adj == "vieux") || ($original_adj == "nouveau")) {
          $nounphrase = $art . " " . $adj . " " . $noun;
          $wordorder = "<ART> <ADJ> <ITEM>";
        }
      }
      break;

    case LanguageConstants::GERMAN:

      if (!$gender) {
        $gender = "n";
      }
      if (!$art) {
        $art = "ein";
      }
      if (($gender == "f") && ($art == "ein")) {
        $art = "eine";
      }
      if (($gender == "m") && ($situation == "ACC")) {
        $art = "einen";
      }
      if (($gender == "m" || $gender == "n") && ($situation == "DAT")) {
        $art = "einem";
      }
      if (($gender == "f") && ($situation == "DAT")) {
        $art = "einer";
      }

      # adjective endings
      if (($gender == "m") && ($situation == "NOM")) {
        $adj_end = "er";
      }
      if (($gender == "m") && ($situation == "ACC")) {
        $adj_end = "en";
      }
      if ($situation == "DAT") {
        $adj_end = "en";
      }
      if (($gender == "n") && ($situation != "DAT")) {
        $adj_end = "es";
      }
      if (($gender == "f") && ($situation != "DAT")) {
        $adj_end = "e";
      }

      // check if there's an additional adjective that needs adjusting in the noun itself
      $np_parts = explode(" ", $noun);
      if ($np_parts[1]) {  # more than one word, let's assume adj+noun, adj gets ending as above
        $np_adj = $np_parts[0];
        if (substr($np_adj, -2) == "er") {
          $np_adj = (substr($np_adj, 0, -2));
        }
        if ($adj_end) {
          $np_adj .= $adj_end;
        }
        $noun = str_replace($np_parts[0], $np_adj, $noun); # reconstruct noun
      }

      if ($adj) {  # modify deterioration adjective and compile phrase
        if ($adj_end) {
          $adj = $adj . $adj_end;
        }  # add ending
        $nounphrase = $art . " " . $adj . " " . $noun;
        $wordorder = "<ART> <ADJ> <ITEM>";
      } else {
        $nounphrase = $art . " " . $noun;
        $wordorder = "<ART> <ITEM>";
      }


      break;

    case LanguageConstants::SPANISH:

      if (!$art) {
        $art = "un";
      }
      if ($gender == "f") {
        if ($art == "un") {
          $art = "una";
        }
        // adjust adj ending -o to -a
        if (substr($adj, -1) == "o") {
          $adj = (substr($adj, 0, -1)) . "a";
        }
      }

      $nounphrase = $art . " " . $nounphrase;
      $wordorder = "<ART> " . $wordorder;
      break;

    case LanguageConstants::RUSSIAN:

      $art = "";

      if (($adj) && ($gender != "m")) {
        $adj = substr($adj, 0, strlen($adj) - 2);
        if ($gender == "f") {
          $adj = $adj . "ая";
        } elseif ($gender == "n") {
          $adj = $adj . "ое";
        }
      }

      $nounphrase = $adj . " " . $noun;
      $wordorder = "<ADJ> <ITEM>";
      break;


    case LanguageConstants::SWEDISH:

      if (!$art) {
        $art = "en";
      }
      if (($gender == "n") && ($art == "en")) {
        $art = "ett";
      }

      if ($adj) {
        if ($gender == "n") {
          if (($adj == "oanvändbar") || ($adj == "gammal")) {
            $adj = $adj . "t";
          } elseif (strpos(" " . $adj, "ny")) {
            $adj = $adj . "tt";
          } else {
            $adj = mb_substr($adj, 0, -1) . "t";
          }
        }
        $nounphrase = $art . " " . $adj . " " . $noun;
        $wordorder = "<ART> <ADJ> <ITEM>";
      } else {
        $nounphrase = $art . " " . $noun;
        $wordorder = "<ART> <ITEM>";
      }
      break;

    case LanguageConstants::ESPERANTO:

      $art = "";
      if ($situation == "ACC") {

        $words = explode(" ", $noun);
        foreach ($words as &$word) {
          $lastLetter = mb_substr($word, -1);
          // -n suffix needs to be added for all singular (-o) and plural (-j) nouns and adjectives (-a)
          if (in_array($lastLetter, ["o", "j", "a"])) {
            $word .= "n";
          }
        }
        $noun = implode(" ", $words);
        // check if there's an additional adjective that needs adjusting in the noun itself
        if ($adj) {
          $adj = $adj . "n";
          $nounphrase = $adj . " " . $noun;
          $wordorder = "<ADJ> <ITEM>n";
        } else {
          $nounphrase = $noun;
          $wordorder = "<ITEM>n";
        }
      }
      break;

    case LanguageConstants::POLISH:

      $art = "";

      if (($adj) && ($gender != "m")) {
        $adj = mb_substr($adj, 0, -1);
        if ($gender == "f") {
          $adj = $adj . "a";
        }
        if ($gender == "n") {
          $adj = $adj . "e";
        }
      }

      $nounphrase = $adj . " " . $noun;
      $wordorder = "<ADJ> <ITEM>";
      break;

    case LanguageConstants::TURKISH:

      if ($adj) {
        $nounphrase = $adj . " " . $noun;
        $wordorder = "<ADJ> <ITEM>";
      } else {
        $nounphrase = $noun;
        $wordorder = "<ITEM>";
      }
      break;

      $adjoin_letter = "n";
      if ($gender == "TRY") {
        $adjoin_letter = "y";
      }

      $n_syllables = 2; //needs code to count syllables... or not...

      $vowels = ["a", "e", "ı", "i", "o", "ö", "u", "ü"];
      $max_pos = -1;
      for ($i = 0; $i < count($vowels); $i++) {
        $last_pos = mb_strrpos($noun, $vowels[$i]);
        if ($last_pos > $max_pos) {
          $max_pos = $last_pos;
        }
      }

      //only enters if there are vowels in noun
      if ($max_pos != -1) {
        $lastVowel = $noun[$max_pos];

        $suffix_vowel_1 = "";
        $suffix_vowel_2 = "";
        if ($lastVowel == "a" || $lastVowel == "ı") {
          $suffix_vowel_1 = "a";
          $suffix_vowel_2 = "ı";
        } elseif ($lastVowel == "o" || $lastVowel == "u") {
          $suffix_vowel_1 = "a";
          $suffix_vowel_2 = "u";
        } elseif ($lastVowel == "e" || $lastVowel == "i") {
          $suffix_vowel_1 = "e";
          $suffix_vowel_2 = "i";
        } elseif ($lastVowel == "ö" || $lastVowel == "ü") {
          $suffix_vowel_1 = "e";
          $suffix_vowel_2 = "ü";
        }

        if ($situation == "TRI") {
          $suffix_vowel = $suffix_vowel_2;
        } else {
          $suffix_vowel = $suffix_vowel_1;
        }

        $lastLetter = mb_substr($noun, -1);
        if (StringUtil::contains("aeıioöuü", $lastLetter)) {
          $noun .= $adjoin_letter;
        } elseif (StringUtil::contains("pçtk", $lastLetter) && $n_syllables > 1) {
          $replacements = ["/p/" => "b", "/ç/" => "c", "/t/" => "d", "/k/" => "g"];
          $noun = preg_replace(array_keys($replacements), array_values($replacements), $noun);
        }
        $noun .= $suffix_vowel;
      }

      if ($adj) {
        $nounphrase = $adj . " " . $noun;
        $wordorder = "<ADJ> <ITEM>";
      } else {
        $nounphrase = $noun;
        $wordorder = "<ITEM>";
      }

      break;

    case LanguageConstants::PORTUGUESE:
      if (!$art) {
        if ($gender == "f") {
          $art = "uma";
        } else {
          $art = "um";
        }
      }

      if ($adj) {
        if (substr($adj, -1) == "o" && $gender == "f") {
          $adj = substr_replace($adj, "a", -1);
        }
        $nounphrase = $noun . " " . $adj;
        $wordorder = "<ITEM> <ADJ>";
      }

      $nounphrase = $art . " " . $nounphrase;
      $wordorder = "<ART> " . $wordorder;
      break;

    case LanguageConstants::LITHUANIAN:

      $art = "";
      if ($adj) {
        if ($gender == "f") {
          $adj = substr($adj, 0, strlen($adj) - 1);
        } elseif ($gender == "mm") {
          if ($adj != "yrantis") {
            $adj = substr($adj, 0, strlen($adj) - 2) . "i";
          } else {
            $adj = "yrantys";
          }
        } elseif ($gender == "ff") {
          if ($adj != "yrantis") {
            $adj = substr($adj, 0, strlen($adj) - 2) . "os";
          } else {
            $adj = "yran&#x10d;ios";
          }
        }
      }
      $nounphrase = $adj . " " . $noun;
      $wordorder = "<ADJ> <ITEM>";
      break;

    case (6 || 8 || 10 || 11 || 12):
      $art = "";
      break;
  }

  if ($forreplace == 1) {
    $wordorder = str_replace("<ART>", $art, $wordorder);
    $wordorder = str_replace("<ADJ>", $adj, $wordorder);
    return trim($wordorder);
  } else {
    return trim($nounphrase);
  }
}

function adjust_generic_charname($l, $chardesc, $grammar)
{
  $grammar = strtoupper($grammar);
  switch ($l) {
    case LanguageConstants::GERMAN:
      if ($grammar == "ACC") {
        if (strpos($chardesc, "Mann")) {
          $chardesc = str_replace("ein", "einen", $chardesc);
        }
      } elseif ($grammar == "DAT") {
        if (strpos($chardesc, "Mann")) {
          $chardesc = str_replace("ein", "einem", $chardesc);
        } elseif (strpos($chardesc, "Frau")) {
          $chardesc = str_replace("eine", "einer", $chardesc);
        }
      }
      break;
    case LanguageConstants::ESPERANTO:
      if ($grammar = "ACC") {
        $words = explode(" ", $chardesc);
        $chardesc = $words[0] . " " . $words[1] . "n " . $words[2] . "n";
      }
      break;
    default:
      break;
  }
  return $chardesc;
}

function generate_animal_desc($l, $animalname, $amount = false, $witharticle = false, $gender = null, $situation = "NOM")
{
  $animaldesc = "";
  $art = "";
  $animal = $animalname;

  $situation = strtoupper($situation);
  $gender = strtolower($gender);

  // default word order:
  if ($witharticle) {
    $animaldesc .= "<ART> ";
  }
  if ($amount) {
    $animaldesc .= "<AMOUNT> ";
  }
  $animaldesc .= "<ANIMAL>";

  switch ($l) {
    case LanguageConstants::ENGLISH:

      if ($witharticle) {  // determine correct article
        $art = "a";
        $letter = substr($animalname, 0, 1);
        if (($letter == "a") || ($letter == "e") || ($letter == "i") || ($letter == "o") || ($letter == "u")) {
          $art = "an";
        }
      }

      if (($amount) && ($amount != 1)) {  // determine correct plural
        if ($animalname != "sheep") {
          $animal = $animalname . "s";
        }
      }
      break;

    case LanguageConstants::DUTCH:

      if ($witharticle) {
        $art = "een";
      }

      if (($amount) && ($amount != 1)) {
        $animal = $animalname . "s";
      }
      break;

    case LanguageConstants::FRENCH:

      if ($witharticle) {
        $art = "un";
        if ($gender == "f") {
          $art = "une";
        }
      }

      if (($amount) && ($amount != 1)) {
        if (substr($animalname, -2, 2) == "al") {
          $animal = substr($animalname, 0, -2) . "aux";
        } else {
          $animal = $animalname . "s";
        }
      }
      break;

    case LanguageConstants::GERMAN:

      if ($witharticle) {

        if (!$gender) {
          $gender = "n";
        }
        if (!$art) {
          $art = "ein";
        }
        if (($gender == "f") && ($art == "ein")) {
          $art = "eine";
        }
        if (($gender == "m") && ($situation == "ACC")) {
          $art = "einen";
        }
        if (($gender == "m" || $gender == "n") && ($situation == "DAT")) {
          $art = "einem";
        }
        if (($gender == "f") && ($situation == "DAT")) {
          $art = "einer";
        }

      }

      if (($amount) && ($amount != 1)) {
        if ((substr($animalname, -2) == "er") || (substr($animalname, -4) == "chen")) {
          $animal = $animalname;
        } else {
          if ($animalname == "Wolf") {
            $animal = "W�lfe";
          } else {
            if ($animalname == "Kuh") {
              $animal = "K�he";
            } else {
              $animal = $animalname . "e";
            }
          }
        }
      }
      break;

    case LanguageConstants::SPANISH:

      if ($witharticle) {
        $art = "un";
        if ($gender == "f") {
          $art = "una";
        }
      }

      if (($amount) && ($amount != 1)) {
        $animal = $animalname . "s";
      }
      break;

    case LanguageConstants::SWEDISH:

      if ($witharticle) {
        $art = "en";
        if ($gender == "n") {
          $art = "ett";
        }
      }

      if (($amount) && ($amount != 1)) {
        $animal = $animalname . "s";
      }
      break;

    case LanguageConstants::ESPERANTO:

      if ($witharticle) {
        $art = "";
      }

      if (($amount) && ($amount != 1)) {
        $animal = $animalname . "j";
        $pos = strpos($animal, "a ");
        if ($pos > 0) {
          $leftpart = mb_substr($animal, 0, $pos + 1);
          $rightpart = mb_substr($animal, $pos + 1);
          $animal = $leftpart . "j" . $rightpart;
        }
      }

      if ($situation == "ACC") {
        $animal .= "n";
        $pos = strpos($animal, "a ");
        if ($pos > 0) {
          $leftpart = mb_substr($animal, 0, $pos + 1);
          $rightpart = mb_substr($animal, $pos + 1);
          $animal = $leftpart . "n" . $rightpart;
        }
        $pos = strpos($animal, "aj ");
        if ($pos > 0) {
          $leftpart = mb_substr($animal, 0, $pos + 2);
          $rightpart = mb_substr($animal, $pos + 2);
          $animal = $leftpart . "n" . $rightpart;
        }
      }
      break;

    case LanguageConstants::POLISH:

      if ($witharticle) {
        $art = "";
      }

      if (($amount) && ($amount != 1)) {
        $animal = $animalname . "s";
      }
      break;

    case LanguageConstants::PORTUGUESE:

      if ($witharticle) {
        $art = "um";
        if ($gender == "f") {
          $art = "uma";
        }
      }

      if (($amount) && ($amount != 1)) {
        $animal = $animalname . "s";
      }
      break;

    case LanguageConstants::LITHUANIAN:

      if ($witharticle) {
        $art = "";
      }

      if (($amount) && ($amount != 1)) {
        $animal = $animalname . "s";
      }
      break;

    case LanguageConstants::CHINESE:

      // default word order:
      $animaldesc = "";
      if ($witharticle) {
        $animaldesc .= "<ART>";
      }
      if ($amount) {
        $animaldesc .= "<AMOUNT><CW>";
      }
      $animaldesc .= "<ANIMAL>";

      if ($witharticle) {
        $art = "";
      }

      $cw = $gender;
      if (!$gender) {
        $cw = "&#20010;";
      }

      $animaldesc = str_replace("<CW>", $cw, $animaldesc);

    case (6 || 8 || 10 || 11 || 12):
      $art = "";
      break;
  }

  $animaldesc = str_replace("<ART>", $art, $animaldesc);
  $animaldesc = str_replace("<AMOUNT>", $amount, $animaldesc);
  $animaldesc = str_replace("<ANIMAL>", $animal, $animaldesc);
  $animaldesc = trim($animaldesc);
  return $animaldesc;
}
