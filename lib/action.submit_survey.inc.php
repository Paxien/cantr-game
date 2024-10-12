<?php

$surveySent = HTTPContext::getString("survey_sent", null);

if ($surveySent != null) {
  $survey = new Survey(Db::get());
  $accepted = $survey->submitSurvey($surveySent, $player, $l);
  
  if ($accepted) {
    redirect("player");
  } else {
    CError::throwRedirectTag("player", "error_survey_not_accepted");
  }
}
