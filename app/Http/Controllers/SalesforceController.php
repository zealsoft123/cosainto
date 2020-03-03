<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Cache;

use Auth;
use App\Organization;
use Storage;
use URL;

class SalesforceController extends Controller
{
    public static function add_case( $transaction ) {
    	$connection_info = self::connect();

    	$token = $connection_info['token'];
    	$instance_url = $connection_info['url'];

		$user = Auth::user();
		$org  = Organization::find( $user->organization );

		$url = "$instance_url/services/data/v39.0/sobjects/Case/";

		$data = [
			'Origin'          => 'Web',
			'Status'          => 'New',
			'SuppliedEmail'   => $user->email,
			'SuppliedName'    => $user->name,
			'SuppliedPhone'   => $user->phone_number,
			'SuppliedCompany' => $org->name,
			'Subject'         => "Cosainto Transaction ID: $transaction->transaction_id",
			'Description'     => self::assemble_description( $transaction )
		];

		$data_string = json_encode($data);

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER,
		          array("Authorization: OAuth $token",
		              "Content-type: application/json",
		          	  "Content-Length: " . strlen($data_string)
		          	));

		$json_response = curl_exec($curl);

		if( ! json_decode( $json_response )->success ) {
			dd('Something went wrong. Please contact feedback@cosainto.com and reference transaction ID ' . $transaction->transaction_id );
		}
		else {
			return redirect('/dashboard');
		}
    }

    public static function connect() {
		if ( ! Cache::has('salesforce_token') ) {
			$loginurl = "https://na174.salesforce.com/services/oauth2/token";

			$params = "grant_type=password"
			. "&client_id=" . env('SF_CLIENT_ID')
			. "&client_secret=" . env('SF_CLIENT_SECRET')
			. "&username=" . env('SF_USER_NAME')
			. "&password=" . env('SF_PASSWORD') . env('SF_SECURITY_TOKEN');
			
			$curl = curl_init($loginurl);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

			$json_response = curl_exec($curl);
			$response = json_decode( $json_response );

			$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

			if ( $status != 200 ) {
			    die("Error: call to URL failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
			}

			curl_close($curl);

			$token = $response->access_token;
			$instance_url = $response->instance_url;

			Cache::put('salesforce_token', $token, 5);
			Cache::put('salesforce_url', $instance_url, 5);
    	}
    	else {
    		$token = Cache::get('salesforce_token');
    		$instance_url = Cache::get('salesforce_url');
    	}

    	return [
			'token' => $token,
			'url'   => $instance_url
    	];
    }

    public static function assemble_description( $tx ) {
    	$description = "Risk Score: " . $tx->risk_score . PHP_EOL;
    	$description .= "Risk Reason: " . $tx->risk_reason . PHP_EOL;
    	$description .= "Amount: $" . number_format( $tx->amount, 2 ) . PHP_EOL;
    	$description .= "Merchant ID: " . $tx->merchant_id . PHP_EOL;
    	
    	$description .= "Last 4 of Card Num: ";
    	$description .= strlen( $tx->card_number ) > 4 ? substr( $tx->card_number, -4 ) : $tx->card_number;
    	$description .= PHP_EOL;

		$description .= "Exp. Date: " . $tx->expiration_date . PHP_EOL;

		$description .= "Notes: " . $tx->notes . PHP_EOL;

		$description .= PHP_EOL;

		$files = explode(',', $tx->file_paths );

		foreach( $files as $file ) {
			$url = URL::to('/') . Storage::url($file);
			$description .= 'Attached File: ' . $url . PHP_EOL;
		}

		$description .= PHP_EOL;

		$description .= "Billing Address: " . PHP_EOL;
		$description .= "Billing Name: " . $tx->billing_name . PHP_EOL;
      	$description .= "Billing Address: " . $tx->billing_address . PHP_EOL;
      	$description .= "Billing City: " . $tx->billing_city . " " . $tx->billing_state . PHP_EOL;
      	$description .= "Billing Zipcode: " . $tx->billing_zipcode . PHP_EOL;
      	$description .= "Billing Country: " . $tx->billing_country . PHP_EOL;

      	$description .= PHP_EOL;

      	$description .= "Shipping Address: " . PHP_EOL;
		$description .= "Shipping Name: " . $tx->shipping_name . PHP_EOL;
      	$description .= "Shipping Address: " . $tx->shipping_address . PHP_EOL;
      	$description .= "Shipping City: " . $tx->shipping_city . " " . $tx->shipping_state . PHP_EOL;
      	$description .= "Shipping Zipcode: " . $tx->shipping_zipcode . PHP_EOL;
      	$description .= "Shipping Country: " . $tx->shipping_country . PHP_EOL;

    	return $description;
    }
}
