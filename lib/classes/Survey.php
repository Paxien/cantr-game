<?php

class Survey
{
  const ANSWER_RADIO = 0;
  const ANSWER_RADIO_TEXT = 1;
  const ANSWER_TEXT = 2;
  const ANSWER_CHECKBOX = 3;
  const ANSWER_CHECKBOX_TEXT = 4;

  /** @var Db */
  private $db;
  private $name; // name of the survey
  private $s_id;
  public $form_action = "index.php?page=submitsurvey";
  private $questions = []; // array of all questions and answers
  private $from_time = null;
  private $to_time = null;

  public function __construct(Db $db)
  {
    $this->db = $db;
  }

  /**
   * List surveys from the db
   * @param $limit - how many surveys should be visible
   * @return array of survey objects
   */
  public function showSurveysList($limit = 100)
  {
    $stm = $this->db->prepare("SELECT surveys.*, 
    (SELECT COUNT(*) FROM survey_player_surveys plr_surveys
      WHERE plr_surveys.s_id = surveys.s_id
        AND plr_surveys.submitted = 1) AS answer_count,
    (SELECT COUNT(*) FROM survey_questions s_questions
      WHERE s_questions.of_survey = surveys.s_id) AS question_count 
    FROM surveys LIMIT :limit");
    $stm->bindInt("limit", $limit);
    $stm->execute();
    // huge query: surveys.* = all surveys, answer_count = how many people answered, question count = how many questions in the survey

    $surveyList = [];
    foreach ($stm->fetchAll() as $i => $survey) {
      $surveyList[$i]['answer_count'] = $survey->answer_count;
      $surveyList[$i]['question_count'] = $survey->question_count;
      $surveyList[$i]['s_id'] = $survey->s_id;
      $surveyList[$i]['name'] = '<CANTR REPLACE NAME=survey_s_' . $survey->s_id . '>';
      $surveyList[$i]['specific_players_list'] = ($survey->player_ids != ';');
      $surveyList[$i]['language'] = $survey->s_language;
      $surveyList[$i]['date'] = $survey->date;
      $surveyList[$i]['enabled'] = $survey->enabled;
    }
    return $surveyList;
  }

  public function resultExists($survNum)
  {
    $stm = $this->db->prepare("SELECT count(*) AS count FROM surveys WHERE s_id = :surveyId");
    $stm->bindInt("surveyId", $survNum);
    return $stm->executeScalar();
  }

  public function isPlayerSurveyRecorded($survNum, $player_id)
  {
    $stm = $this->db->prepare("SELECT count(*) AS count FROM survey_respondents
      WHERE survey_id = :surveyId AND player_id = :playerId");
    $stm->bindInt("surveyId", $survNum);
    $stm->bindInt("playerId", $player_id);
    $stm->executeScalar();
  }

  public function getSurveyData($sid)
  {
    $stm = $this->db->prepare("SELECT * FROM surveys WHERE s_id = :surveyId LIMIT 1");
    $stm->bindInt("surveyId", $sid);
    $stm->execute();
    return $stm->fetch(PDO::FETCH_ASSOC);
  }

  public function setSurveyData($surveyId, $enabled, $surveyLanguage, $playerIds)
  {
    $enabled = $enabled ? 1 : 0;

    if ($this->isPlayerIdListValid($playerIds)
      && ($surveyLanguage == 0 || array_key_exists($surveyLanguage, LanguageConstants::$LANGUAGE))) {
      $stm = $this->db->prepare("UPDATE surveys SET s_language = :language,
                   player_ids = :playerIds, enabled = :enabled WHERE s_id = :surveyId");
      $stm->bindInt("language", $surveyLanguage);
      $stm->bindStr("playerIds", $playerIds);
      $stm->bindInt("enabled", $enabled);
      $stm->bindInt("surveyId", $surveyId);
      $stm->execute();
    }
  }

  /**
   * List results of a certain survey
   * @param $survNum - survey ID
   * @param $backLink - where back link should direct
   * @return array of results for a survey
   */
  public function showResult($survNum, $backLink)
  {
    $between = $this->getDateRangeQueryPart();

    $stm = $this->db->prepare("SELECT * FROM surveys WHERE s_id = :surveyId");
    $stm->bindInt("surveyId", $survNum);
    $stm->execute();
    $surveyData = $stm->fetchObject();

    $questions = [];
    $textAnswers = [];

    $stm = $this->db->prepare("SELECT COUNT(*) AS count FROM survey_player_surveys
      WHERE s_id = :surveyId AND submitted = 1 AND $between");
    $stm->bindInt("surveyId", $survNum);
    $surveyed = $stm->executeScalar();
    $totalsurv = $surveyed;
    if ($totalsurv == 0) {
      $totalsurv = 1;
    } // to avoid division by 0

    $existingLanguages = [];

    $stm = $this->db->prepare("SELECT * FROM survey_questions WHERE of_survey = :surveyId");
    $stm->bindInt("surveyId", $survNum);
    $stm->execute();
    foreach ($stm->fetchAll() as $i => $questionData) {
      $questions[$i]['q_text'] = '<CANTR REPLACE NAME=survey_q_' . $questionData->q_id . '>';
      $questions[$i]['q_id'] = $questionData->q_id;
      $stm = $this->db->prepare("SELECT survey_answers.*,
        (SELECT count(*) FROM survey_player_answers, survey_player_surveys
          WHERE survey_player_answers.answer_option = survey_answers.a_id
            AND survey_player_surveys.ps_id = survey_player_answers.of_ps
            AND survey_player_surveys.submitted = 1 AND $between) AS count
        FROM survey_answers WHERE of_question = :questionId");
      $stm->bindInt("questionId", $questionData->q_id);
      $stm->execute();
      foreach ($stm->fetchAll() as $a => $answerData) {
        $questions[$i][$a]['a_text'] = '<CANTR REPLACE NAME=survey_a_' . $answerData->a_id . '>';
        $questions[$i][$a]['count'] = $answerData->count;
        $questions[$i][$a]['percent'] = number_format(($answerData->count / $totalsurv * 100), 2);
        $questions[$i]['n'] = $a + 1; // real size
        if (in_array($answerData->a_type, [self::ANSWER_RADIO_TEXT, self::ANSWER_CHECKBOX_TEXT, self::ANSWER_TEXT])) {
          $stm = $this->db->prepare("SELECT survey_player_answers.*,
            survey_player_surveys.s_lang
            FROM survey_player_answers, survey_player_surveys
            WHERE survey_player_answers.answer_option = :answerId
              AND survey_player_surveys.ps_id = survey_player_answers.of_ps
              AND survey_player_surveys.submitted = 1 AND $between");
          $stm->bindInt("answerId", $answerData->a_id);
          $stm->execute();
          foreach ($stm->fetchAll() as $textAnswer) {
            $existingLanguages[$textAnswer->s_lang] = LanguageConstants::$LANGUAGE[$textAnswer->s_lang]["lang_abr"];
            $textAnswers[$i][] = [
              "content" => $textAnswer->answer_text,
              "language" => $textAnswer->s_lang
            ];
          }
        }
      }
    }

    $playerIdsArray = $this->convertPlayerIdStringToArray($surveyData->player_ids);

    /*
      BY PLAYER
    */

    $stm = $this->db->prepare("SELECT ps_id, player_id, s_lang, date
      FROM survey_player_surveys
      WHERE survey_player_surveys.submitted = 1
        AND s_id = :surveyId AND $between ORDER BY date DESC");
    $stm->bindInt("surveyId", $survNum);
    $stm->execute();

    $plr_arr = [];
    foreach ($stm->fetchAll() as $plrdata) {

      $stm = $this->db->prepare("SELECT answer_option, answer_text, of_question
        FROM survey_player_answers WHERE of_ps = :playerSurveyId ORDER BY of_question");
      $stm->bindInt("playerSurveyId", $plrdata->ps_id);
      $stm->execute();
      $plr_ans_info = [];
      foreach ($stm->fetchAll() as $plr_ans) {
        $plr_ans_info[$plr_ans->of_question][] = (in_array($plr_ans->answer_text, ['NULL', null]) ? "<CANTR REPLACE NAME=survey_a_$plr_ans->answer_option>" : htmlspecialchars($plr_ans->answer_text));
      }
      $array = [];
      $array['q_a'] = $plr_ans_info;
      $array['s_lang'] = $plrdata->s_lang;
      $array['date'] = $plrdata->date;

      $plr_arr[] = $array;
    }

    /*
      BY PLAYER END
    */

    $result = [];
    $result['name'] = '<CANTR REPLACE NAME=survey_s_' . $surveyData->s_id . '>';
    $result['count'] = $surveyed;
    $result['creationDate'] = $surveyData->date;
    $result['enabled'] = $surveyData->enabled;
    $result['id_array'] = $playerIdsArray;
    $result['backLink'] = $backLink;
    $result['questions'] = $questions;
    $result['answers'] = $textAnswers;

    $result['players'] = $plr_arr;

    $result['languages'] = $existingLanguages;

    return $result;
  }

  public function isSurveyAvailable($survNum, $player_id, $player_language, $allowMultipleAnswers = false)
  {
    if (!$allowMultipleAnswers) {
      $stm = $this->db->prepare("SELECT survey_id FROM survey_respondents
        WHERE player_id = :playerId AND survey_id = :surveyId");
      $stm->bindInt("playerId", $player_id);
      $stm->bindInt("surveyId", $survNum);
      $alreadyAnswered = $stm->executeScalar();
      if ($alreadyAnswered) { // check if the survey was already answered by the player
        return false;
      }
    }

    $stm = $this->db->prepare("SELECT s_id FROM surveys
      WHERE s_id = :surveyId AND enabled = 1 AND s_language IN (0, :language)
      AND (player_ids = ';' OR player_ids LIKE :playerId) LIMIT 1");
    $stm->bindInt("surveyId", $survNum);
    $stm->bindInt("language", $player_language);
    $stm->bindStr("playerId", "%$player_id%");
    $surveyExists = $stm->executeScalar();
    return $surveyExists != null;
  }

  /**
   * @return int[] array with ids of available surveys
   */
  public function listOfAvailableSurveys($playerId, $playerLanguage)
  {
    $stm = $this->db->prepare("SELECT surveys.s_id as id,
        sr.survey_id IS NOT NULL AS is_answered FROM surveys
      LEFT JOIN survey_respondents sr ON
        sr.player_id = :playerId
        AND sr.survey_id = surveys.s_id
    WHERE surveys.enabled = 1 AND
        ((surveys.player_ids =';' AND (surveys.s_language IN (0, :language)))
        OR
        (surveys.player_ids LIKE :playerIds))");
    $stm->bindInt("playerId", $playerId);
    $stm->bindInt("language", $playerLanguage);
    $stm->bindStr("playerIds", "%;$playerId;%");
    $stm->execute();

    return Pipe::from($stm->fetchAll())->filter(function($survey) {
      return !$survey->is_answered && $survey->id != _EXIT_SURVEY_S_ID;
    })->map(function($survey) {
      return $survey->id;
    })->toArray();
  }


  /**
   * load Survey selected by ID from the DB
   * before using that function you should check if the survey is available for that player
   * @param $surveyId - survey ID
   * @throws Exception
   */
  public function loadSurvey($surveyId)
  {
    $stm = $this->db->prepare("SELECT s_id FROM surveys WHERE s_id = :surveyId");
    $stm->bindInt("surveyId", $surveyId);
    $this->s_id = $stm->executeScalar();
    $this->name = "<CANTR REPLACE NAME=survey_s_$surveyId>";

    $stm = $this->db->prepare("SELECT * FROM survey_questions WHERE of_survey = :surveyId"); // to get all questions from that survey
    $stm->bindInt("surveyId", $surveyId);
    $stm->execute();
    foreach ($stm->fetchAll() as $i => $questionsData) {
      $this->questions[$i]['text'] = '<CANTR REPLACE NAME=survey_q_' . $questionsData->q_id . '>';
      $this->questions[$i]['q_id'] = $questionsData->q_id;

      $stm = $this->db->prepare("SELECT * FROM survey_answers WHERE survey_answers.of_question= :questionId"); // all possible answers
      $stm->bindInt("questionId", $questionsData->q_id);
      $stm->execute();
      foreach ($stm->fetchAll() as $a => $answersData) {
        $this->questions[$i][$a]['type'] = $answersData->a_type;
        $this->questions[$i][$a]['text'] = '<CANTR REPLACE NAME=survey_a_' . $answersData->a_id . '>';
        $this->questions[$i][$a]['a_id'] = $answersData->a_id;
        $this->questions[$i]['n'] = $a + 1;
      }
    }
  }

  /**
   * When player submits whole survey data set
   * @param $surveyId
   * @param $playerId
   * @param $langId
   * @param bool $instantSubmit
   * @return bool if the survey was successfully submitted
   */
  public function submitSurvey($surveyId, $playerId, $langId, $instantSubmit = true)
  {
    $valid = $this->isSurveyAvailable($surveyId, $playerId, $langId, !$instantSubmit); // check if that action is allowed
    if (!$valid) {
      return false;
    }

    $stm = $this->db->prepare("SELECT * FROM survey_questions WHERE of_survey = :surveyId");
    $stm->bindInt("surveyId", $surveyId);
    $stm->execute();
    $queue = []; // query list built during checking of data, then executed ONLY IF all data is valid

    foreach ($stm->fetchAll() as $survQuestion) {
      $answers = HTTPContext::getArray('q_' . $survQuestion->q_id);
      if (empty($answers)) {
        return false;
      }

      $radioButtonsSelected = 0;
      foreach ($answers as $answer) {
        $stm = $this->db->prepare("SELECT survey_answers.a_type FROM survey_answers WHERE a_id = :answer");
        $stm->bindInt("answer", $answer);
        $answerType = $stm->executeScalar();
        $answerText = 'NULL';
        if (in_array($answerType, [self::ANSWER_RADIO_TEXT, self::ANSWER_CHECKBOX_TEXT, self::ANSWER_TEXT])) { // if the answer contains textfield
          $answerText = HTTPContext::getRawString('text_' . $answer, null);
          if ($answerText === null) {
            return false;
          }
          $answerText = $this->db->quote($answerText);
        }
        if (in_array($answerType, [self::ANSWER_RADIO_TEXT, self::ANSWER_RADIO])) {
          $radioButtonsSelected++;
        }


        $queue[] = [$survQuestion->q_id, $answer, $answerText];
      }
      if ($radioButtonsSelected > 1) {
        return false;
      }
    }

    // needed for exit survey (not instantly counted, but after some additional actions)
    $submit = $instantSubmit ? 1 : 0;
    $playerIdToRecord = $instantSubmit ? null : $playerId;
    if ($instantSubmit) {
      $stm = $this->db->prepare("INSERT INTO survey_respondents (survey_id, player_id) VALUES (:surveyId, :playerId)");
      $stm->bindInt("surveyId", $surveyId);
      $stm->bindInt("playerId", $playerId);
      $stm->execute();
    }

    $stm = $this->db->prepare("INSERT INTO survey_player_surveys (player_id, s_id, date, s_lang, submitted)
      VALUES (:playerId, :surveyId, NOW(), :language, :submit)");
    $stm->bindInt("playerId", $playerIdToRecord, true);
    $stm->bindInt("surveyId", $surveyId);
    $stm->bindInt("language", $langId);
    $stm->bindInt("submit", $submit);
    $stm->execute();
    $ps_id = $this->db->lastInsertId();

    // BRUTAL CONCAT OF ALL VALUES QUERY
    $queryString = "INSERT INTO survey_player_answers (of_ps, of_question, answer_option, answer_text) VALUES ";
    $answers = [];

    foreach ($queue as $ksx => $queryData) {
      $answers[] = "($ps_id, $queryData[0], $queryData[1], $queryData[2])";
    }
    $queryString .= implode(", ", $answers);
    $this->db->query($queryString); // unsafe query
    return true;
  }

  public function createSurvey()
  {
    $surv_enabled = HTTPContext::getString('surv_enabled', 0);
    $surv_player_ids = HTTPContext::getString('surv_player_ids', null);
    $surv_name = HTTPContext::getString('surv_name', null);
    $surv_lang = HTTPContext::getString('surv_lang', null);
    $n_of_q = HTTPContext::getString('n_of_q', 0);

    if ($surv_name == null || empty($surv_name) || $n_of_q <= 0 || !is_numeric($n_of_q)
      || !$this->isPlayerIdListValid($surv_player_ids) || !is_numeric($surv_lang)) {
      return "survey data incorrect (lack of questions or survey name, bad player ids or language format)";
    }

    $surv_enabled = $surv_enabled ? 1 : 0;
    $selectedLanguageExists = !empty(LanguageConstants::$LANGUAGE[$surv_lang]);
    if ($surv_lang != 0 && !$selectedLanguageExists) { // if it's not for all langs and the language doesn't exist
      return "selected language doesn't exist";
    }

    // checking data
    $q_names = [];
    for ($q = 0; $q < $n_of_q; $q++) {
      $ame = HTTPContext::getString('q_name_' . $q, null);
      if (!empty($ame)) {
        $q_names[$q] = $ame;
      } else {
        return "lack of question name $q";
      }
    }

    $ans = [];
    for ($q = 0; $q < $n_of_q; $q++) {
      $ans_len = HTTPContext::getString('q_' . $q, 0);
      if (!is_numeric($ans_len) || $ans_len <= 0) {
        return "bad answer format (or no answer for a question #$q)";
      } else {
        $ans[$q] = [];
        for ($a = 0; $a < $ans_len; $a++) {
          $a_type = HTTPContext::getString('q_' . $q . '_a_' . $a . '_type', null);
          $a_in = HTTPContext::getString('q_' . $q . '_a_' . $a . '_in', null);

          // Not allowing empty answers doesn't make sense when the player is going to fill it in themselves. (checkbox text)
          if ($a_type == null) {
            return "bad answer text format";
          }

          $ans[$q][$a] = [];
          $ans[$q][$a]['type'] = $a_type;
          $ans[$q][$a]['in'] = $a_in;
        }
      }
    }

    // START - DATA TO DB

    $languageSpecificSurvey = $surv_lang != 0;

    $stm = $this->db->prepare("INSERT INTO surveys (date, enabled, s_language, player_ids) values (NOW(), :enabled, :language, :playerIds)");
    $stm->bindInt("enabled", $surv_enabled);
    $stm->bindInt("language", $surv_lang);
    $stm->bindStr("playerIds", $surv_player_ids);
    $stm->execute();
    $surveyId = $this->db->lastInsertId();
    $this->createTranslation("survey_s_" . $surveyId, $surv_name, $languageSpecificSurvey);

    for ($q = 0; $q < $n_of_q; $q++) {
      $stm = $this->db->prepare("INSERT INTO survey_questions (of_survey) values (:surveyId)");
      $stm->bindInt("surveyId", $surveyId);
      $stm->execute();
      $questionId = $this->db->lastInsertId();
      $this->createTranslation("survey_q_" . $questionId, $q_names[$q], $languageSpecificSurvey);

      foreach ($ans[$q] as $a => $v) {
        $stm = $this->db->prepare("INSERT INTO survey_answers (of_question, a_type) values (:questionId, :type)");
        $stm->bindInt("questionId", $questionId);
        $stm->bindInt("type", $v['type']);
        $stm->execute();
        $a_id = $this->db->lastInsertId();

        if ($v['in'] != null) {
          $this->createTranslation("survey_a_" . $a_id, $v['in'], $languageSpecificSurvey);
        }
      }
    }
    return true;
  }

  private function createTranslation($tagName, $tagText, $languageSpecificSurvey)
  {
    $translationNote = $languageSpecificSurvey ? "DO NOT TRANSLATE!" : null;
    $stm = $this->db->prepare("INSERT INTO texts (type, language, name, content, grammar, translator, updated)
      VALUES (1, 1, :name, :text, :note, 'Survey manager', NOW())");
    $stm->bindStr("name", $tagName);
    $stm->bindStr("text", $tagText);
    $stm->bindStr("note", $translationNote, true);
    $stm->execute();
  }

  /**
   * showSurvey, must be loaded first i.e. using loadSurvey() function
   */

  public function showSurvey()
  {
    $survey = [];
    $survey['s_id'] = $this->s_id;
    $survey['name'] = $this->name;
    $survey['form_action'] = $this->form_action;
    $survey['ANSWER_RADIO'] = self::ANSWER_RADIO;
    $survey['ANSWER_RADIO_TEXT'] = self::ANSWER_RADIO_TEXT;
    $survey['ANSWER_TEXT'] = self::ANSWER_TEXT;
    $survey['ANSWER_CHECKBOX'] = self::ANSWER_CHECKBOX;
    $survey['ANSWER_CHECKBOX_TEXT'] = self::ANSWER_CHECKBOX_TEXT;
    $survey['questions'] = $this->questions;
    return $survey;
  }

  /**
   * Used to show results in specific time
   */
  public function setDateBounds($date_from, $date_to)
  {
    $from_time = strtotime($date_from);
    $to_time = strtotime($date_to);

    if ($from_time > 0 && $to_time > 0 && $to_time >= $from_time) {
      $this->from_time = $from_time;
      $this->to_time = $to_time;
    }
  }


  public function isPlayerIdListValid($plr)
  {
    if ($plr[0] != ';' || $plr[strlen($plr) - 1] != ';') {
      return false;
    }

    if ($plr == ';') {
      return true;
    }

    $playersList = explode(';', substr($plr, 1, -1));
    return Validation::isPositiveIntArray($playersList);
  }

  /**
   * Convert text player_ids to array of integers";111;234;" -> array(111, 234)
   * @param $playerIdsString
   * @return int[]
   */
  public function convertPlayerIdStringToArray($playerIdsString)
  {
    // if there are specific player ids selected as ppl who are being surveyed
    if (strlen(($playerIdsString)) > 1) {
      // deleting first and last character (";") and explode the rest
      return explode(';', substr($playerIdsString, 1, -1));
    }

    return [];
  }

  private function getDateRangeQueryPart()
  {
    $between = "1=1";
    if (isset($this->from_time)) {
      $between .= " AND survey_player_surveys.date > FROM_UNIXTIME($this->from_time)";
    }
    if (isset($this->to_time)) {
      $between .= " AND survey_player_surveys.date < FROM_UNIXTIME($this->to_time)";
    }
    return $between;
  }
}
