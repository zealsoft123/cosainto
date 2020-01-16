<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\User;
use App\Transaction;
use App\Organization;

class TransactionControllerTest extends TestCase {
  use RefreshDatabase;

  public function test_can_view_transaction() {
    factory( User::class, 1 )->create();
    $user = User::first();
    $organization = Organization::findOrFail($user->organization);
    factory( Transaction::class, 1 )->create( [ 'organization_id' => $user->organization ] );
    $transaction = Transaction::first();

    $response = $this->actingAs($user)->get("/transaction/{$transaction->id}");

    // $response->assertStatus(200);
    // $response->assertSeeText("the ID is: {$transaction->id}");
    $response->assertSeeText( e("{$organization->name}") );
  }
}
