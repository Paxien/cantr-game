<?php

namespace Api\Repositories;

use Api\Repositories\AuthenticationRepositoryInterface;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;

class AuthenticationRepository implements AuthenticationRepositoryInterface
{
    /** @var \Illuminate\Database\Connection */
    private $database;

    /** @var \Psr\Container\ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->database = $container->get('db');
        $this->container = $container;
    }

    /**
     * Returns the user POPO or null, using ID or Username column based on type.
     *
     * @param string $username
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|null
     */
    public function findUser($username)
    {
        return $this->database->table('players')->where(\is_numeric($username) === true ? 'id' : 'username', '=', $username)->first();
    }

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
    public function updateLastLogin(Request $request, $user)
    {
        $clientIp = $request->getAttribute('ip_address');
        $remoteAddress = $request->getServerParam('REMOTE_ADDR');
        $ipRecord = null;
        $startTime = date("Y-m-d H:i:s", time());
        $endTime = date("Y-m-d H:i:s", $this->container['settings']['jwt']['expiry']); // Sets endtime to when the JWT expires.
        $info = $startTime . ' ' . $remoteAddress . ' (' . gethostbyaddr($remoteAddress) . ')';

        if (isset($clientIp) === true && preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}(\.\d{1,3}.\d{1,3})?$/", $clientIp) === 1) {
            $ipRecord = $this->database->table('ips')
                ->where('player', '=', $user->id)
                ->where('client_ip', '=', $clientIp)
                ->first();
        }

        if ($ipRecord === null) {
            $this->database->table('ips')->insert([
                'player' => $user->id,
                'ip' => $remoteAddress,
                'client_ip' => $clientIp,
                'times' => 1,
                'lasttime' => $startTime,
                'endtime' => $endTime,
            ]);
        } else {
            $this->database->table('ips')
                ->where('player', '=', $user->id)
                ->where('client_ip', '=', $clientIp)
                ->update([
                    'times' => $ipRecord->times + 1,
                    'lasttime' => $startTime,
                    'endtime' => $endTime,
                ]);
        }

        $turn = $this->database->table('turn')->max('day');

        $this->database->table('player_logins')->updateOrInsert([
            'player_id' => $user->id,
            'date' => date("Y-m-d H:i:s"),
            'onetime' => 0,
            'origin' => isset($_REQUEST['from']) === true ? $_REQUEST['from'] : '',
        ]);

        $this->database->table('players')->where('id', '=', $user->id)->update([
            'lastminute' => date("i") + 60 * date("H"),
            'lastdate' => $turn,
            'lastlogin' => $info,
            'recent_activity' => $user->recent_activity ?: 1,
        ]);
    }
}