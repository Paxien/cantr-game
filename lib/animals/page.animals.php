<?php
// SANITIZE INPUT

if ($char->isTravelling()) {
  CError::throwRedirectTag("char.events", "error_animals_impossible_istravelling");
}

// subpages handling
$subpages = ["domestication" => 0, "hunt" => 0];
if (isset($_GET['subpage'])) {
  setcookie('animals_subpage_' . $character, $_GET['subpage'], time() + 60 * 60 * 24 * 30); // to auto select subpage in the future
  $subpage = $_GET['subpage'];
} elseif (isset($_COOKIE['animals_subpage_' . $character])) {
  $subpage = $_COOKIE['animals_subpage_' . $character];
}

if (!array_key_exists($subpage, $subpages)) {
  $subpage = "hunt";
}
$sublink = "index.php?page=animals&subpage=";

if ($subpage == "domestication") {
  include _LIB_LOC . "/animals/page.animals.domestication.php";
} elseif ($subpage == "hunt") {
  include _LIB_LOC . "/animals/page.animals.hunt.php";
}
