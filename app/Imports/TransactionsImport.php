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

    return new Transaction([
      'organization_id'    => $organization->id,
      'transaction_id'     => $row['transaction_id'],
      'transaction_status' => $row['transaction_status'],
      'transaction_type'   => $row['transaction_type'],
      'amount'             => $row['amount_authorized'],
      'card_number'        => $row['last_four_of_credit_card'],
      'expiration_date'    => $row['expiration_date'],
      'billing_name'       => $row['billing_first_name'].' '.$row['billing_last_name'],
      'billing_address'    => $row['billing_street_address'],
      'billing_city'       => $row['billing_city_locality'],
      'billing_zipcode'    => $row['billing_postal_code'],
      'billing_state'      => $row['billing_stateprovince_region'],
      'billing_country'    => $row['billing_country'],
      'shipping_name'      => $row['shipping_first_name'].' '.$row['shipping_last_name'],
      'shipping_address'   => $row['shipping_street_address'],
      'shipping_city'      => $row['shipping_city_locality'],
      'shipping_zipcode'   => $row['shipping_postal_code'],
      'shipping_state'     => $row['shipping_stateprovince_region'],
      'shipping_country'   => $row['shipping_country'],
      'transaction_date'   => Carbon::parse($row['created_datetime'])->toDateTimeString(),
    ]);
  }
}
