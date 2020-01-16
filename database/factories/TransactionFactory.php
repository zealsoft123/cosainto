<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Transaction;
use Faker\Generator as Faker;

$factory->define(Transaction::class, function (Faker $faker) {
  return [
    'organization_id'       => factory(App\Organization::class),
    'transaction_id'        => $faker->regexify('[A-Za-z0-9]{20}'),
    'transaction_status'    => $faker->randomElement(['settling', 'settled', 'processor_declined']),
    'transaction_type'      => $faker->randomElement(['sale', 'credit', 'dispute']),
    'amount'                => $faker->randomFloat(2, 0, 500),
    'card_number'           => $faker->creditCardNumber,
    'expiration_date'       => $faker->creditCardExpirationDateString,
    'billing_name'          => $faker->name,
    'billing_address'       => $faker->streetAddress,
    'billing_city'          => $faker->city,
    'billing_state'         => $faker->stateAbbr,
    'billing_zipcode'       => $faker->postcode,
    'billing_country'       => $faker->country,
    'shipping_name'         => $faker->name,
    'shipping_address'      => $faker->streetAddress,
    'shipping_city'         => $faker->city,
    'shipping_state'        => $faker->stateAbbr,
    'shipping_zipcode'      => $faker->postcode,
    'shipping_country'      => $faker->country,
    'risk_score'            => $faker->numberBetween(0, 100),
    'risk_reason'           =>$faker->randomElement(['high settlement amount',
                                                      'geo mismatch',
                                                      'high outlier',
                                                      'concentrated card',
                                                      'high asp',
                                                      'high outlier amount',
                                                    ]),
    'investigation_summary' => $faker->paragraphs(3, true),
    'notes'                 => $faker->paragraph,
    'transaction_date'      =>$faker->dateTimeThisYear(),
  ];
});
