<?php
$ocharid = HTTPContext::getInteger('ocharid');
show_title ("<CANTR REPLACE NAME=public_desc_guide_title>");
?>
<div class="page">
<CANTR REPLACE NAME=public_desc_guidelines>
</div>

<div class="centered">
  <a href="index.php?page=characterdescription&ocharid=<?php echo $ocharid; ?>">
    <CANTR REPLACE NAME=button_char_go_back>
  </a>
</div>


