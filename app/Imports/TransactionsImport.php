<?php

namespace App\Imports;

use Carbon\Carbon;

use App\Transaction;
use App\Organization;
use Auth;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TransactionsImport implements ToModel, withHeadingRow {
  /**
  * @param array $row
  *
  * @return \Illuminate\Database\Eloquent\Model|null
  */
  public function model(array $row) {
    $user = Auth::User();
    $organization = Organization::findOrFail($user->organization);

    $month_strings = [
      '',
      'Jan',
      'Feb',
      'Mar',
      'Apr',
      'May',
      'Jun',
      'Jul',
      'Aug',
      'Sep',
      'Oct',
      'Nov',
      'Dec'
    ];

    $expiration_date = $row['expiration_date'];
    if( !$expiration_date && array_key_exists( 'card_exp_year', $row ) ) {
      $expiration_date = substr( $row['card_exp_year'], 2 ) . '-' . $month_strings[ $row['card_exp_month'] ]; 
    }
    if( !$expiration_date ) {
      $expiration_date = '';
    }

    $billing_address = $row['billing_street_address'];

    if( !$billing_address && array_key_exists( 'card_address_line1', $row ) ) {
      $billing_address = $row['card_address_line1'] . ' ' . $row['card_address_line2'];
    }
    if( ! $billing_address ) {
      $billing_address = '';
    }

    $billing_city = $row['billing_city_locality'];

    if( !$billing_city && array_key_exists( 'card_address_city', $row ) ) {
      $billing_city = $row['card_address_city'];
    }
    if( ! $billing_city ) {
      $billing_city = '';
    }

    $billing_state = $row['billing_stateprovince_region'];

    if( !$billing_state && array_key_exists( 'card_address_state', $row ) ) {
      $billing_state = $row['card_address_state'];
    }
    if( ! $billing_state ) {
      $billing_state = '';
    }

    return new Transaction([
      'organization_id'    => $organization->id,
      'transaction_id'     => array_key_exists( 'transaction_id', $row ) ? $row['transaction_id'] : $row['id'],
      'transaction_status' => array_key_exists( 'transaction_status', $row ) ? $row['transaction_status'] : $row['status'],
      'transaction_type'   => array_key_exists( 'transaction_type', $row ) ? $row['transaction_type'] : $row['payment_source_type'],
      'amount'             => array_key_exists( 'amount_authorized', $row ) ? $row['amount_authorized'] : $row['amount'],
      'card_number'        => array_key_exists( 'last_four_of_credit_card', $row ) ? $row['last_four_of_credit_card'] : $row['card_last4'],
      'expiration_date'    => $expiration_date,
      'billing_name'       => array_key_exists( 'billing_first_name', $row ) ? $row['billing_first_name'] . ' ' . $row['billing_last_name'] : '',
      'billing_address'    => $billing_address,
      'billing_city'       => $billing_city,
      'billing_zipcode'    => array_key_exists( 'billing_postal_code', $row ) ? $row['billing_postal_code'] : $row['card_address_zip'],
      'billing_state'      => $billing_state,
      'billing_country'    => array_key_exists( 'billing_country', $row ) ? $row['billing_country'] : $row['card_address_country'],
      'shipping_name'      => array_key_exists( 'shipping_first_name', $row ) ? $row['shipping_first_name'] . ' ' . $row['shipping_last_name'] : '',
      'shipping_address'   => $row['shipping_street_address'] ?? '',
      'shipping_city'      => $row['shipping_city_locality'] ?? '',
      'shipping_zipcode'   => $row['shipping_postal_code'] ?? '',
      'shipping_state'     => $row['shipping_stateprovince_region'] ?? '',
      'shipping_country'   => $row['shipping_country'] ?? '',
      'transaction_date'   => array_key_exists( 'created_datetime', $row ) ? Carbon::parse($row['created_datetime'])->toDateTimeString() : Carbon::parse($row['created_utc'])->toDateTimeString(),
      'merchant_id'        => $row['merchant_id'] ?? '',
      'card_type'          => $row['card_type']
    ]);
  }
}
