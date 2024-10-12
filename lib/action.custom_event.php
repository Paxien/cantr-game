<?php

$objectId = HTTPContext::getInteger('object_id');
$eventName = $_REQUEST['custom_arg1'];


try {
  $object = CObject::loadById($objectId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

$customEventManager = new CustomEventManager($char, $object, $eventName);
if (($errorMessage = $customEventManager->validate()) === true) {
  $customEventManager->callEvent();
} else {
  CError::throwRedirect("char.inventory", $errorMessage);
}

redirect("char.events");
