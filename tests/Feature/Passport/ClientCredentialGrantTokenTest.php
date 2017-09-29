<?php


namespace Tests\Feature;

use App\User;
use League\OAuth2\Server\AuthorizationServer;
use Laravel\Passport\Bridge\ClientRepository as PassportClientRepository;
use Laravel\Passport\Bridge\AccessTokenRepository as PassportAccessTokenRepository;
use Laravel\Passport\Bridge\ScopeRepository as PassportScopeRepository;


use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;


class ClientCredentialGrantTokenTest extends TestCase
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
        $data = [
            "name" => 'Client Name',
            "redirect" => 'http://example.com/callback',
            "user_id" => $userId
        ];

        $response = $this->post('/auth/clients', $data);

        $response->assertStatus(200);
        $response->assertJsonStructure();

        $content = $response->getContent();

        return \GuzzleHttp\json_decode($content);
    }

    /** @test */
    public function it_can_create_new_token_for_any_client()
    {

        $client = $this->addClient(8);

        // login as new user
        $this->actingAs(factory(User::class)->create());

        $response = $this->post('/auth/issueToken', [
            'grant_type' => 'client_credentials',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'scope' => '',
        ]);

        $data = \GuzzleHttp\json_decode($response->getContent());

        $this->assertNotNull($data->access_token);

    }

}
