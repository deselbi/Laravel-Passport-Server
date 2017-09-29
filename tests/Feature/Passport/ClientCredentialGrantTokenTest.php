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

    /** @test */
    public function it_can_create_new_client_for_authenticated_user()
    {


        $user = factory(User::class)->create();
        $this->actingAs($user);

        $client = $this->addClient();

        $secret = $client->secret;
        $this->assertNotNull($secret);
        $this->assertEquals($user->id, $client->id);

        $response = $this->get('/oauth/clients');
        $response->assertStatus(200);
        $response->assertJsonStructure();

        $content = $response->getContent();
        $clients = \GuzzleHttp\json_decode($content);


    }

    /**
     * @return array
     */
    protected function addClient()
    {
        $data = [
            "name" => 'Client Name',
            "redirect" => 'http://example.com/callback'
        ];

        $response = $this->post('/oauth/clients', $data);

        $response->assertStatus(200);
        $response->assertJsonStructure();

        $content = $response->getContent();

        return \GuzzleHttp\json_decode($content);
    }

    /** @test */
    public function it_can_create_new_token_for_any_client()
    {

        $this->actingAs(factory(User::class)->create());
        $client = $this->addClient();


        // login as new user
        $this->actingAs(factory(User::class)->create());

        $response = $this->post('http://your-app.com/oauth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'scope' => '',
        ]);


        $data = \GuzzleHttp\json_decode($response->getContent());

        $this->assertNotNull($data->access_token);


    }

}
