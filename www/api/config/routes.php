<?php

$app->post('/auth/login', \Api\Controllers\AuthenticationController::class . ':login');
$app->post('/auth/validate', \Api\Controllers\AuthenticationController::class . ':validate');