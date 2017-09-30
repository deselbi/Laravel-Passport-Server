<?php
/**
 * Created by PhpStorm.
 * User: ivan
 * Date: 29.9.17.
 * Time: 18.31
 */

namespace App\Services;


use Illuminate\Support\Facades\App;
use Laravel\Passport\ClientRepository;
use League\OAuth2\Server\AuthorizationServer;
use Zend\Diactoros\Response as Psr7Response;

class OAuthService
{


    public function createClient($name, $userId, $redirectUrl)
    {
        $clients = new ClientRepository();

        return $clients->create(
            $userId, $name, $redirectUrl
        )->makeVisible('secret');

    }

    public function issueToken($request)
    {
        /** @var League\OAuth2\Server\AuthorizationServer $server */
        $server = App::make('League\OAuth2\Server\AuthorizationServer');
        return $server->respondToAccessTokenRequest($request, new Psr7Response);
    }
}