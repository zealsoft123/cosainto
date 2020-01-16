<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\User;
use App\Organization;
use App\Transaction;

class ModelsTest extends TestCase {
  use RefreshDatabase;
  /**
   * A basic feature test example.
   *
   * @return void
   */
  public function test_user_belongs_to_organization() {
    $users = factory(User::class, 3)->create();

    $user = User::first();
    $organization = Organization::first();

    $this->assertEquals($user->organization, $organization->id);
  }

  public function test_organization_has_one_user() {
    $users = factory(User::class, 3)->create();

    $user = User::first();
    $organization = Organization::first();

    $this->assertEquals($organization->user, $user);
  }

  public function test_transactions_belong_to_one_organization() {
    $organization = factory(Organization::class, 1)->create();
    $organization = Organization::first();

    $transactions = factory(Transaction::class, 3)->create( [ 'organization_id' => $organization->id ] );

    foreach( $transactions as $transaction ) {
      $this->assertEquals($transaction->organization, $organization);
    }
  }

  public function test_organizations_have_many_transactions() {
    $organization = factory(Organization::class, 1)->create();
    $organization = Organization::first();
    $transactions = factory(Transaction::class, 3)->create( [ 'organization_id' => $organization->id ] );

    $transactions = Transaction::all();

    $this->assertEquals($organization->transactions, $transactions);
  }


}
