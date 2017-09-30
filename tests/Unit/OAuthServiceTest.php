<?php


namespace Tests\Feature;

use App\User;
use Facades\App\Services\OAuthService;
use League\OAuth2\Server\AuthorizationServer;
use Laravel\Passport\Bridge\ClientRepository as PassportClientRepository;
use Laravel\Passport\Bridge\AccessTokenRepository as PassportAccessTokenRepository;
use Laravel\Passport\Bridge\ScopeRepository as PassportScopeRepository;


use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Zend\Diactoros\Request;


class OAuthServiceTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        factory(User::class, 20)->create();

    }

    /** @test */
    public function it_can_create_new_client_for_any_user()
    {

        $client = $this->addClient(10);

        $secret = $client->secret;
        $this->assertNotNull($secret);
        $this->assertEquals(10, $client->user_id);


    }

    /**
     * @return array
     */
    protected function addClient($userId)
    {
        return OAuthService::createClient("Test Name", $userId, "");
    }

    /** @test */
    public function it_can_create_new_token_for_any_client()
    {

        $client = $this->addClient(8);

        // login as new user
        $this->actingAs(factory(User::class)->create());
        $data = [
            'grant_type' => 'client_credentials',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'scope' => '',
        ];

        $request = (new \Zend\Diactoros\Request())
            ->withUri(new \Zend\Diactoros\Uri('http://example.com'))
            ->withMethod('GET')
            ->withAddedHeader('Content-Type', 'application/json');
        $request->getBody()->write(json_encode($data));

        $serverRequest = \Zend\Diactoros\ServerRequestFactory::fromGlobals(
            $_SERVER,
            [],
            $data,
            [],
            []
        );

        $response = OAuthService::issueToken($serverRequest);

        $data = \GuzzleHttp\json_decode($response->getBody());

        $this->assertNotNull($data->access_token);

    }

}
