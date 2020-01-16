<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

use App\User;
use App\Organization;
use App\Transaction;

class DashboardControllerTest extends TestCase {
  use RefreshDatabase;

  public function test_can_access_dashboard() {
    factory( User::class, 1 )->create();
    $user = User::first();
    $organization = Organization::findOrFail($user->organization);
    $transactions = factory( Transaction::class, 20 )->create( [ 'organization_id' => $user->organization ] );

    $response = $this->actingAs($user)->get("/dashboard");

    $response->assertStatus(200);
    // $response->assertSeeText("the ID is: {$transaction->id}");
    $response->assertSeeText( e("{$organization->name}") );
  }

  public function test_can_upload_file() {
    factory( User::class, 1 )->create();
    $user = User::first();
    $organization = Organization::findOrFail($user->organization);
    Storage::fake('local');
    $file = UploadedFile::fake()->create('transactions.csv', 60);

    $response = $this->actingAs($user)->post("/dashboard", ['transactions' => $file]);

    Storage::disk('local')->assertExists('temp/'.$file->hashName());
  }

  public function test_uploaded_file_must_be_csv() {
    factory( User::class, 1 )->create();
    $user = User::first();
    $organization = Organization::findOrFail($user->organization);
    Storage::fake('local');
    $file = UploadedFile::fake()->create('transactions.pdf', 60);

    $response = $this->actingAs($user)->post("/dashboard", ['transactions' => $file]);

    Storage::disk('local')->assertMissing('temp/'.$file->hashName());

  }
}
