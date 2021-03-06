<?php

namespace App\Imports;

use App\Organization;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TransactionsImport implements ToCollection, withHeadingRow
{

    /**
     * Store the data extracted from an Excel file
     * @var array
     */
    public $data = array();

    /**
     * @param Collection $rows
     *
     * @return void
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
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

            $expiration_date = false;
            if (array_key_exists('expiration_date', $row)) {
                $expiration_date = $row['expiration_date'];
            }
            if (!$expiration_date && array_key_exists('card_exp_year', $row)) {
                $expiration_date = substr($row['card_exp_year'], 2) . '-' . $month_strings[$row['card_exp_month']];
            }
            if (!$expiration_date) {
                $expiration_date = '';
            }

            $billing_address = false;
            if (array_key_exists('billing_street_address', $row)) {
                $billing_address = $row['billing_street_address'];
            }

            if (!$billing_address && array_key_exists('card_address_line1', $row)) {
                $billing_address = $row['card_address_line1'] . ' ' . $row['card_address_line2'];
            }
            if (!$billing_address) {
                $billing_address = '';
            }

            $billing_city = false;
            if (array_key_exists('billing_city_locality', $row)) {
                $billing_city = $row['billing_street_locality'];
            }

            if (!$billing_city && array_key_exists('card_address_city', $row)) {
                $billing_city = $row['card_address_city'];
            }
            if (!$billing_city) {
                $billing_city = '';
            }

            $billing_state = false;
            if (array_key_exists('billing_stateprovince_region', $row)) {
                $billing_state = $row['billing_stateprovince_region'];
            }

            if (!$billing_state && array_key_exists('card_address_state', $row)) {
                $billing_state = $row['card_address_state'];
            }
            if (!$billing_state) {
                $billing_state = '';
            }

            $transaction = [
                'organization_id' => $organization->id,
                'transaction_id' => $row->has('transaction_id') ? $row['transaction_id'] : $row['id'],
                'transaction_status' => $row->has('transaction_status') ? $row['transaction_status'] : $row['status'],
                'transaction_type' => $row->has('transaction_type') ? $row['transaction_type'] : $row['payment_source_type'],
                'amount' => $row->has('amount_authorized') ? $row['amount_authorized'] : $row['amount'],
                'card_number' => $row->has('last_four_of_credit_card') ? $row['last_four_of_credit_card'] : $row['card_last4'],
                'expiration_date' => $expiration_date,
                'billing_name' => $row->has('billing_first_name') ? $row['billing_first_name'] . ' ' . $row['billing_last_name'] : '',
                'billing_address' => $billing_address,
                'billing_city' => $billing_city,
                'billing_zipcode' => $row->has('billing_postal_code') ? $row['billing_postal_code'] : $row['card_address_zip'],
                'billing_state' => $billing_state,
                'billing_country' => $row->has('billing_country') ? $row['billing_country'] : $row['card_address_country'],
                'shipping_name' => $row->has('shipping_first_name') ? $row['shipping_first_name'] . ' ' . $row['shipping_last_name'] : '',
                'shipping_address' => $row['shipping_street_address'] ?? '',
                'shipping_city' => $row['shipping_city_locality'] ?? '',
                'shipping_zipcode' => $row['shipping_postal_code'] ?? '',
                'shipping_state' => $row['shipping_stateprovince_region'] ?? '',
                'shipping_country' => $row['shipping_country'] ?? '',
                'transaction_date' => $row->has('created_datetime') ? Carbon::createFromTimestamp($row['created_datetime'])->toDateTimeString() : Carbon::createFromTimestamp($row['created_utc'])->toDateTimeString(),
                'merchant_id' => $row['merchant_id'] ?? '',
                'card_type' => $row['card_type'] ?? $row['card_brand'] ?? ''
            ];

            array_push($this->data, $transaction);
        }
    }
}
