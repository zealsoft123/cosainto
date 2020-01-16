<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Organization;
use Faker\Generator as Faker;

$factory->define(Organization::class, function (Faker $faker) {
    return [
      'name'              => $faker->company,
      'url'               => $faker->url,
      'category'          => $faker->randomElement( [ 'product', 'saas', 'consult', 'other' ] ),
      'payment_provider'  => $faker->randomElement( [ 'stripe', 'braintree', 'adyen' ] ),
      'street_address'    => $faker->streetAddress,
      'city'              => $faker->city,
      'state'             => $faker->stateAbbr,
      'zipcode'           => $faker->postcode,
      'summary'           => $faker->paragraph($nbSentences = 3, $variableNbSentences = true),
    ];
});
