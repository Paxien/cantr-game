<?php

class ObjectAction
{

  private $action;
  private $img;
  private $caption;

  public function __construct($action, $img, $caption)
  {
    $this->action = $action;
    $this->img = $img;
    $this->caption = $caption;
  }

  /**
   * @return string page that is invoked on index.php by executing this action
   */
  public function getAction()
  {
    return $this->action;
  }

  /**
   * @return string name of file in pictures directory that is linked with this action
   */
  public function getImg()
  {
    return $this->img;
  }

  /**
   * @return string text that is shown as tooltip for actions' image
   */
  public function getCaption()
  {
    return $this->caption;
  }

  public function asArray(CObject $object, $params = [])
  {
    return [
      "page" => $this->getAction(),
      "inputs" => $params + [
        "object_id" => $object->getId(),
      ],
      "img" => $this->getImg(),
      "img_title" => $this->getCaption(),
    ];
  }


}
