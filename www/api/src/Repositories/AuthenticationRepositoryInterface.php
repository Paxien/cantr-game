<?php

namespace Api\Repositories;

use Slim\Http\Request;

interface AuthenticationRepositoryInterface
{
    /**
     * Mimics old codebase behaviour in generating the last login.
     *
     * Rewrite of session.php:start() method and some code in action.login.inc.php
     *
     * @param \Slim\Http\Request $request
     * @param                    $user
     *
     * @return void
     */
    function updateLastLogin(Request $request, $user);

    function findUser($username);
}