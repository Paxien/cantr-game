<?php


class DraggingView
{
  private $subject;
  private $observer;

  public function __construct(Dragging $subject, Character $observer = null)
  {
    $this->subject = $subject;
    $this->observer = $observer;
  }

  public function getInterpretedName($useHtml = true)
  {
    $nameTag = TagBuilder::forText($this->getNameTag())->allowHtml($useHtml)
      ->observedBy($this->observer)->language($this->observer->getLanguage())->build();
    return $nameTag->interpret();
  }

  public function getNameTag()
  {
    if ($this->subject->getVictimType() == DraggingConstants::TYPE_HUMAN) {
      $victimTag = "<CANTR CHARNAME ID=". $this->subject->getVictim() .">";
    } else {
      $victimTag = "<CANTR OBJNAME ID=". $this->subject->getVictim() ." TYPE=1>";
    }

    if ($this->subject->getGoal() != DraggingConstants::GOAL_FROM_PROJECT) {
      $goalTag = " to <CANTR LOCNAME ID=". $this->subject->getGoal() .">";
    } else {
      $goalTag = " <CANTR REPLACE NAME=dragging_from_project>";
    }
    return $victimTag . $goalTag;
  }
}
