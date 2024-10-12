<?php

/**
 * This is a rather temporary solution. This functionality should be built in
 * Some generic class that handles textarea-input checking.
 */
function check_event_strings($str) {
  // Javascript events that could be used in cross site scripting
  // http://www.w3.org/TR/html4/interact/scripts.html#h-18.2.3

  $events = array('onload', 'onunload', 'onclick', 'ondblclick', 'onmousedown', 'onmouseup', 'onmouseover',
    'onmousemove', 'onmouseout', 'onfocus', 'onblur', 'onkeypress', 'onkeydown', 'onkeyup', 'onsubmit',
    'onreset', 'onselect', 'onchange', 'onerror', 'oncontextmenu');

  foreach ($events as $event) {
    if (stristr($str, $event)) {
      return false;
    }
  }
  return true;
}
