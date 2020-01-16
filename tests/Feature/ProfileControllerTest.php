<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\User;
use App\Organization;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileControllerTest extends TestCase {
  use RefreshDatabase;

  /**
   * A basic feature test example.
   *
   * @return void
   */
  public function test_can_view_profile() {
    factory( User::class, 1 )->create();
    $user = User::first();
    $organization = Organization::findOrFail($user->organization);

    $response = $this->actingAs($user)->get("/profile");

    $response->assertSee("{$user->name}'s profile");
  }

  public function test_organization_details_are_on_profile_page() {
    factory( User::class, 1 )->create();
    $user = User::first();
    $organization = Organization::findOrFail($user->organization);

    $response = $this->actingAs($user)->get("/profile");

    $response->assertSee( e($organization->name) );
  }

  public function test_user_can_partially_update_user_info() {
    factory( User::class, 1 )->create();
    $user = User::first();
    $name = $user->name;

    $response = $this->actingAs($user)->json(
      'POST',
      '/profile',
      [
        'name' => 'my new name',
        'password' => 'password',
      ]
    );

    $user->refresh();

    $this->assertEquals('my new name', $user->name );
  }

  public function test_user_must_use_correct_password() {
    factory( User::class, 1 )->create();
    $user = User::first();
    $name = $user->name;

    $response = $this->actingAs($user)->json(
      'POST',
      '/profile',
      [
        'name' => 'my new name',
        'password' => 'notmypassword',
      ]
    );

    $user->refresh();

    $this->assertNotEquals('my new name', $user->name );
  }

  public function test_user_must_update_with_valid_email() {
    factory( User::class, 1 )->create();
    $user = User::first();

    $response = $this->actingAs($user)->json(
      'POST',
      '/profile',
      [
        'email' => 'notarealemail'
      ],
    );
    $user->refresh();
    $this->assertNotEquals( 'notarealemail', $user->email );
  }

  public function test_user_can_update_password() {
    factory( User::class, 1 )->create();
    $user = User::first();

    $response = $this->actingAs($user)->json(
      'POST',
      '/profile',
      [
        'password' => 'password',
        'new-password' => 'newpassword',
        'new-password_confirmation' => 'newpassword'
      ],
    );
    $user->refresh();
    $this->assertTrue( Hash::check( 'newpassword', $user->password ) );

  }

  public function test_user_can_update_organization_info() {
    factory( User::class, 1 )->create();
    $user = User::first();
    $organization = Organization::findOrFail($user->organization);
    $business_name = 'neworg';
    $url = 'www.newurl.org';
    $category = 'newcat';
    if ($organization->payment_provider !== 'adyen'){
      $payment_provider = 'adyen';
    }
    else {
      $payment_provider = 'stripe';
    }

    $street_address = 'newStreet';
    $city = 'newcity';
    if ($organization->state !== 'IL'){
      $state = 'IL';
    }
    else {
      $state = 'MA';
    }
    $zipcode = '12345';
    $summary = 'newsummary';

    $response = $this->actingAs($user)->json(
      'POST',
      '/profile',
      [
        'password' => 'password',
        'business-name' => $business_name,
        'url' => 'www.newurl.org',
        'category' => $category,
        'payment-provider' => $payment_provider,
        'street_address' => $street_address,
        'city' => $city,
        'state' => $state,
        'zipcode' => $zipcode,
        'summary' => $summary,
      ]
    );
    $organization->refresh();

    $this->assertEquals($url, $organization->url);
    $this->assertEquals($business_name, $organization->name);
    $this->assertEquals($category, $organization->category);
    $this->assertEquals($payment_provider, $organization->payment_provider);
    $this->assertEquals($street_address, $organization->street_address);
    $this->assertEquals($city, $organization->city);
    $this->assertEquals($state, $organization->state);
    $this->assertEquals($zipcode, $organization->zipcode);
    $this->assertEquals($summary, $organization->summary);
  }

  public function test_user_must_use_strong_password() {
    factory( User::class, 1 )->create();
    $user = User::first();

    // This password should be too short andn shouldn't be changed
    $response = $this->actingAs($user)->json('POST', '/profile', ['password' => 'password']);

    $this->assertNotEquals( Hash::make( 'password' ), $user->password);
  }
}
