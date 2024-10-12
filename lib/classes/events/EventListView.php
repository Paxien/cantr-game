<?php

class EventListView
{

  /** @var Character */
  private $observer;
  /** @var int[] */
  private $eventGroups;
  private $newestEventId;
  /** @var bool */
  private $html;
  /** @var Db */
  private $db;

  public function __construct(Character $observer, $html)
  {
    $this->observer = $observer;
    $this->html = $html;
    $this->db = Db::get();
  }

  /**
   * Get list of translated event lines starting from $newestEvent (inclusive) to $latestEvent (exclusive).
   * @param int $latestEvent the oldest of events which won't be presented in a result. Can be 0.
   * @param int $newestEvent the newest of events which will be presented in a result (-1 when all the events need to be shown)
   * @param bool $showMinutes true if date should comtain a minute of event
   * @return string[] list of fully translated event strings
   */
  public function interpret($latestEvent, $newestEvent = -1, $showMinutes = true)
  {

    $stm = $this->db->prepare("SELECT e.id AS id, e.type AS type, e.parameters AS parameters,
      e.day AS day, e.hour AS hour, e.minute AS minute, et.group AS `group`
      FROM events e
        INNER JOIN events_obs eo ON eo.event = e.id AND eo.observer = :observer
        LEFT JOIN events_types et ON e.type = et.type
      WHERE e.id > :latestEvent AND e.id <= :newestEvent");
    $stm->bindInt("observer", $this->observer->getId());
    $stm->bindInt("latestEvent", $latestEvent);
    $stm->bindInt("newestEvent", $newestEvent == -1 ? PHP_INT_MAX : $newestEvent);
    $stm->execute();
    $eventx = $stm->fetchAll();

    rsort($eventx);

    $eventExistingGroups = [];
    $tagsQueue = [];
    foreach ($eventx as $event_info) {
      $tagsQueue[] = "<CANTR REPLACE NAME=event_$event_info->type $event_info->parameters>";
    }

    $replaceTag = new ReplaceTag();
    $replaceTag->character = $this->observer->getId();
    $replaceTag->language = $this->observer->getLanguage();
    $tagsQueue = $replaceTag->interpretQueue($tagsQueue);

    $events = [];
    foreach ($eventx as $event_info) {
      $content = "<CANTR REPLACE NAME=event_$event_info->type $event_info->parameters>";

      $groupClass = "eventsgroup_nogruped";
      if ($event_info->group) {
        $eventExistingGroups[] = $event_info->group;
        $groupClass = "eventsgroup_" . $event_info->group;
      }

      $day = $event_info->day;
      $hour = $event_info->hour;
      $minute = sprintf('%02d', $event_info->minute);

      if ($this->html) {
        $minute = '<small>' . $minute . '</small>';
      }

      $line = $tagsQueue[$content];

      if ($showMinutes) {
        $eventText = $day . '-' . $hour . '.' . $minute . ': ' . $line;
      } else {
        $eventText = $day . '-' . $hour . ': ' . $line;
      }
      if ($this->html) {
        $eventText = '<div class="' . $groupClass . '" id="event-id-' . $event_info->id . '">' . $eventText . '</div>';
      }

      $this->newestEventId = max($this->newestEventId, $event_info->id);

      $events[] = $eventText;
    }

    $this->eventGroups = array_unique($eventExistingGroups);

    if (count($events) == 0) {
      return [];
    }

    return $this->translateEventsArray($events);
  }

  public function getGroups()
  {
    return $this->eventGroups;
  }

  public function getNewestEventId()
  {
    return $this->newestEventId;
  }

  private function translateEventsArray($events)
  {
    // interpretQueue interprets the line just once,, so inner tags of event (e.g. character) were not interpreted
    $SEPARATOR = "SEPARATOR-" . mt_rand();
    $events = implode($SEPARATOR, $events);

    $tag = new Tag();
    $tag->html = $this->html;
    $tag->language = $this->observer->getLanguage();
    $tag->character = $this->observer->getId();
    $tag->content = $events;
    $events = $tag->interpret();
    $tag->content = $events;
    $events = $tag->interpret();

    // split again interpreted text
    return explode($SEPARATOR, $events);
  }
}