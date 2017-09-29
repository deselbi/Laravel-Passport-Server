<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Passport\ClientRepository;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Psr\Http\Message\ServerRequestInterface;


class OAuthController extends Controller
{

    /**
     * The client repository instance.
     *
     * @var \Laravel\Passport\ClientRepository
     */
    protected $clients;

    /**
     * The validation factory implementation.
     *
     * @var \Illuminate\Contracts\Validation\Factory
     */
    protected $validation;

    /**
     * AuthController constructor.
     * @param AccessTokenController $accessTokenController
     */
    public function __construct(AccessTokenController $accessTokenController)
    {
        $this->accessTokenController = $accessTokenController;
    }


    public function createClient(Request $request)
    {

        $this->clients = new ClientRepository();

        $this->validate($request, [
            'name' => 'required|max:255',
            'user_id' => 'required|integer',
            'redirect' => 'required|url',

        ]);

        return $this->clients->create(
            $request->user_id, $request->name, $request->redirect
        )->makeVisible('secret');

    }


    public function issueToken(ServerRequestInterface $request)
    {

        return $this->accessTokenController->issueToken($request);
    }
}
