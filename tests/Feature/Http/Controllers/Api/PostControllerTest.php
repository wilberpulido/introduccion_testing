<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Post;
// use Illuminate\Contracts\Auth\Authenticatable;
use App\Models\User;

use function PHPUnit\Framework\assertJson;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;
    //refresca la base de datos sobre los test
    public function test_store()
    {
        //El nombre debe comenzar por test_
        // $this->withExceptionHandling();
        //Nos dice exactamente que pasa cuando hay un error, luego podemos comentar.
        $user = User::factory()->create();
        //Con actingAs($user,'api') nos logueamos con el usuario creado

        $response = $this->actingAs($user,'api')->json('POST','/api/posts',[
            'title'=> 'El titulo del post de prueba',
        ]);

        $response->assertJsonStructure(['id','title','created_at','updated_at'])
            ->assertJson(['title'=> 'El titulo del post de prueba'])
            ->assertStatus(201);

        $this->assertDatabaseHas('posts',['title'=> 'El titulo del post de prueba']);
    }
    public function test_validate_title()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user,'api')->json('POST', '/api/posts', [
            'title'=> ''
        ]);
    
        $response->assertStatus(422)
            ->assertJsonValidationErrors('title');
    }
    public function test_show()
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();


        $response = $this->actingAs($user,'api')->json('GET',"/api/posts/$post->id"); //id=1

        $response->assertJsonStructure(['id','title','created_at','updated_at'])
        ->assertJson(['title'=> $post->title])
        ->assertStatus(200); //Ok
    }
    public function test_404_status()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user,'api')->json('GET',"/api/posts/1000");

        $response->assertStatus(404); //Ok
    }
    public function test_update()
    {
        $this->withExceptionHandling();

        $post = Post::factory()->create();
        $user = User::factory()->create();


        $response = $this->actingAs($user,'api')->json('PUT',"/api/posts/$post->id",[
            'title'=> 'nuevo'
        ]); //id=1

        $response->assertJsonStructure(['id','title','created_at','updated_at'])
        ->assertJson(['title'=> 'nuevo'])
        ->assertStatus(200); //Ok

        $this->assertDatabaseHas('posts',['title'=>'nuevo']);
    }
    public function test_delete()
    {
        // $this->withExceptionHandling();
        $post = Post::factory()->create();
        $user = User::factory()->create();


        $response = $this->actingAs($user,'api')->json('DELETE',"/api/posts/$post->id"); //id=1

        $response->assertSee(null)
            ->assertStatus(204); //Sin contenido

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }
    public function test_index()
    {
        // $this->withExceptionHandling();
        Post::factory()->count(5)->create();
        $user = User::factory()->create();


        $response = $this->actingAs($user,'api')->json('GET','/api/posts');

        $response->assertJsonStructure([
            'data' => [
                '*' => ['id','title','created_at','updated_at']
            ]
        ])->assertStatus(200); //OK

    }
    public function test_guest()
    {
        $this->json('GET','api/posts')->assertStatus(401);
        $this->json('POST','api/posts')->assertStatus(401);
        $this->json('GET','api/posts/1000')->assertStatus(401);
        $this->json('PUT','api/posts/1000')->assertStatus(401);
        $this->json('DELETE','api/posts/1000')->assertStatus(401);
    
    }

}
