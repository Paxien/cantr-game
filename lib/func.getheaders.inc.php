<?php

// This to avoid conflicts when PHP is not run as an Apache module (i.e. our test environment)

function emu_getallheaders() {
   foreach($_SERVER as $name => $value)
       if(substr($name, 0, 5) == 'HTTP_')
           $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
   return $headers;
}

?>
