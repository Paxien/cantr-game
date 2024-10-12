<?php

$emailAddressManager = new InvalidEmailAddressManager($player);
$emailAddressManager->sendConfirmationEmail();

redirect("player");
