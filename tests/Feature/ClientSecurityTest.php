<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pet;
use App\Models\Order;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class ClientSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_other_users_pets()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $pet = Pet::factory()->create(['client_id' => $user1->id]);
        
        $this->actingAs($user2)
            ->get("/profile/pets/{$pet->id}/edit")
            ->assertStatus(403);
    }

    public function test_user_cannot_access_other_users_orders()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $order = Order::factory()->create(['client_id' => $user1->id]);
        
        $this->actingAs($user2)
            ->get("/profile/orders/{$order->id}")
            ->assertStatus(403);
    }

    public function test_user_cannot_access_other_users_visits()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $visit = Visit::factory()->create(['client_id' => $user1->id]);
        
        $this->actingAs($user2)
            ->get("/profile/visits/{$visit->id}")
            ->assertStatus(403);
    }

    public function test_password_validation()
    {
        $user = User::factory()->create();
        
        $this->actingAs($user)
            ->post('/profile/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password'
            ])
            ->assertSessionHasErrors(['current_password']);
    }

    public function test_file_upload_validation()
    {
        $user = User::factory()->create();
        
        // Тест с недопустимым типом файла
        $this->actingAs($user)
            ->post('/profile/pets', [
                'name' => 'Test Pet',
                'species_id' => 1,
                'breed_id' => 1,
                'birthdate' => '2020-01-01',
                'gender' => 'male',
                'photo' => 'not-a-file'
            ])
            ->assertSessionHasErrors(['photo']);
    }

    public function test_sql_injection_protection()
    {
        $user = User::factory()->create();
        
        $this->actingAs($user)
            ->get('/profile/orders?search=1%27%20OR%201=1--')
            ->assertStatus(200);
        
        // Проверяем, что поиск не вернул все записи
        $this->assertDatabaseMissing('orders', [
            'client_id' => $user->id,
            'id' => 999999 // Несуществующий ID
        ]);
    }

    public function test_xss_protection()
    {
        $user = User::factory()->create();
        
        $this->actingAs($user)
            ->post('/profile', [
                'name' => '<script>alert("xss")</script>',
                'email' => $user->email,
                'phone' => '1234567890',
                'address' => 'Test Address'
            ])
            ->assertSessionHasNoErrors();
        
        // Проверяем, что скрипт не выполнился
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => '<script>alert("xss")</script>'
        ]);
    }

    public function test_csrf_protection()
    {
        $user = User::factory()->create();
        
        $this->actingAs($user)
            ->post('/profile/pets', [
                'name' => 'Test Pet',
                'species_id' => 1,
                'breed_id' => 1,
                'birthdate' => '2020-01-01',
                'gender' => 'male'
            ])
            ->assertStatus(419); // CSRF token mismatch
    }

    public function test_authentication_required()
    {
        $this->get('/profile')
            ->assertRedirect('/login');
        
        $this->get('/profile/pets')
            ->assertRedirect('/login');
        
        $this->get('/profile/orders')
            ->assertRedirect('/login');
    }

    public function test_session_regeneration_on_login()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password')
        ]);
        
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password'
        ])
        ->assertRedirect('/');
        
        // Проверяем, что сессия была регенерирована
        $this->assertAuthenticated();
    }
}
