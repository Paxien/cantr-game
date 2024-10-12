<?php

namespace Api\Controllers;

use Api\Repositories\AuthenticationRepositoryInterface;
use Exception;
use Firebase\JWT\JWT;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class AuthenticationController
{
    /** @var \Psr\Container\ContainerInterface */
    private $container;

    /** @var \Api\Repositories\AuthenticationRepositoryInterface */
    private $repository;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->repository = $container[AuthenticationRepositoryInterface::class];
    }

    /**
     * @TODO: Review current auth for other missing features
     *
     * @param \Slim\Http\Request  $request
     * @param \Slim\Http\Response $response
     *
     * @return \Slim\Http\Response
     */
    public function login(Request $request, Response $response)
    {
        $data = json_decode($request->getBody()->getContents(), true);

        if (array_key_exists('username', $data) === false || array_key_exists('password', $data) === false) {
            return $response->withStatus(400)->withJson(['message' => 'Missing or invalid username/password.']);
        }

        $user = $this->repository->findUser($data['username']);

        if ($user === null || password_verify($data['password'], $user->password) === false) {
            return $response->withStatus(401)->withJson(['message' => 'Incorrect username or password.']);
        }

        $payload = [
            'iss' => 'https://cantr.net',
            'aud' => 'https://cantr.net',
            'iat' => time(),
            'nbf' => time(),
            'exp' => $this->container['settings']['jwt']['expiry'],
            'sub' => $user->id,
        ];

        $jwt = JWT::encode($payload, $this->container['settings']['jwt']['secret']);

        $this->repository->updateLastLogin($request, $user);

        return $response->withStatus(200)->withJson(['message' => $jwt]);
    }

    /**
     * Validates a JWT in the request payload and returns the user ID, otherwise, 401.
     *
     * @param \Slim\Http\Request  $request
     * @param \Slim\Http\Response $response
     *
     * @return \Slim\Http\Response
     */
    public function validate(Request $request, Response $response)
    {
        $data = json_decode($request->getBody()->getContents(), true);

        if (array_key_exists('jwt', $data) === false) {
            return $response->withStatus(401);
        }

        try {
            $user = JWT::decode($data['jwt'], $this->container['settings']['jwt']['secret'], ['HS256']);
        } catch (Exception $e) {
            return $response->withStatus(401)->withJson(['error' => $e->getMessage()]);
        }

        return $response->withStatus(200)->withJson($user);
    }
}