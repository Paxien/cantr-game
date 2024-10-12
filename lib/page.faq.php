<?php

$action = $_REQUEST['action'];

  show_title ("<CANTR REPLACE NAME=title_faq>");

  echo "<div class='page'><TABLE><TR><TD COLSPAN=2>";

  echo "<CANTR REPLACE NAME=page_faq_$action LANG=$l>";
  
  if ($action == 2) {
    echo "<CANTR REPLACE NAME=page_faq_2b LANG=$l>";//This segment has become too big to fit in one tag
  }

  echo "</TD></TR>";
  echo '<tr><td colspan="2" align="center"><a href="index.php?page=player"><img src="'._IMAGES.'/button_back2.gif" title="<CANTR REPLACE NAME=back_to_player>"></a></td></tr>';
  echo "</TABLE></div>";
