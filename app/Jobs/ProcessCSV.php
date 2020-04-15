<?php

namespace App\Jobs;

use Carbon\Carbon;

use Auth;
use \App\Organization;
use \App\Transaction;

use Illuminate\Support\Facades\Storage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

use DB;

class ProcessCSV implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {   
        if( file_exists( base_path('data-ingestion') . '/data.csv') ) {
            unlink(base_path('data-ingestion') . '/data.csv');
        }

        if( file_exists( base_path('data-ingestion') . '/data.xlsx') ) {
            unlink(base_path('data-ingestion') . '/data.xlsx');
        }

        if( file_exists( base_path('data-ingestion') . '/insert_file_statement.csv') ) {
            unlink(base_path('data-ingestion') . '/insert_file_statement.csv');
        }

        $user = Auth::User();
        $organization = Organization::findOrFail($user->organization);

        $output = shell_exec("cd " . base_path('data-ingestion') . " && python ingest.py 2>&1");

        if( strpos($output, 'success') !== false ) {
            // Python script worked
            $contents = file_get_contents(  base_path( 'data-ingestion/insert_file_statement.csv' ) );
            $queries = explode("\n", $contents);
            $filtered_queries = [];

            $filtered_queries[] = "drop table if exists base_table_temp;
                create table base_table_temp (
                txn_id varchar(100),
                txn_type varchar(20),
                txn_status varchar(20),
                sttlmnt_dt DATE ,
                auth_amt Decimal (18,2),
                sttlmnt_amt Decimal(18,2),
                refund_txn_id varchar(20),
                payment_type varchar(20),
                card_type varchar(20),
                cc_number varchar(50),
                billing_postal_cd varchar(20),
                billing_country varchar(100),
                shipping_postal_cd varchar(20),
                shipping_country varchar(100),
                ip_addr varchar(20),
                processor_response_code varchar(20),
                sttlmnt_currency varchar(20),
                file_type varchar(20),
                insert_date DATE,
                merch_id varchar(20)
            );";

            foreach( $queries as $query ) {
                if( strlen( $query ) < 5 ) {
                    continue;
                }

                $query = explode('"', $query)[1];

                $filtered_queries[] = $query;
            }

            foreach( $filtered_queries as $query ) {        
                // Fix python unicode stuff
                $query = str_replace(", u'", ", '", $query);
                $query = str_replace("(u'", "('", $query);
                $query = str_replace("''", "NULL", $query);

                $query = preg_replace_callback('/datetime\.datetime\((.*?)\)/', function($match){
                    $arr = explode(',', $match[1]);

                    $year  = $arr[0];
                    $month = sprintf("%02d", $arr[1]);
                    $day   = sprintf("%02d", $arr[2]);

                    return "Timestamp('{$year}-{$month}-{$day} 00:00:00')";
                }, $query);

                DB::connection('mysql2')->getPdo()->exec( $query );
            }

            $queries = $this::sql_split( base_path( 'data-ingestion/ingest.sql' ) );

            foreach( $queries as $query ) {
                DB::connection('mysql2')->getPdo()->exec( $query );
            }

            // Grab the IDs and scores into an array
            $txs = DB::connection('mysql2')->table('cos_cons_txn_score')->select('merch_id', 'txn_id', 'risk_score', 'risk_reason')->get();

            $existing_txs = [];

            foreach( $txs as $tx ) {
                if( in_array($tx->txn_id, $existing_txs) ) {
                    continue;
                }

                $tx_data = DB::connection('mysql2')->table('base_table_temp')->where('txn_id', '=', $tx->txn_id)->get();

                // Supplment array with data from `base_table_temp`
                $new_tx = new Transaction([
                  'organization_id'    => $organization->id,
                  'transaction_id'     => $tx_data[0]->txn_id,
                  'transaction_status' => $tx_data[0]->txn_status,
                  'transaction_type'   => $tx_data[0]->txn_type,
                  'amount'             => $tx_data[0]->sttlmnt_amt,
                  'card_number'        => $tx_data[0]->cc_number,
                  'expiration_date'    => 'NA',
                  'billing_name'       => '',
                  'billing_address'    => '',
                  'billing_city'       => '',
                  'billing_zipcode'    => $tx_data[0]->billing_postal_cd,
                  'billing_state'      => '',
                  'billing_country'    => $tx_data[0]->billing_country,
                  'shipping_name'      => '',
                  'shipping_address'   => '',
                  'shipping_city'      => '',
                  'shipping_zipcode'   => $tx_data[0]->shipping_postal_cd,
                  'shipping_state'     => '',
                  'shipping_country'   => $tx_data[0]->shipping_country,
                  'transaction_date'   => $tx_data[0]->sttlmnt_dt,
                  'merchant_id'        => $tx->merch_id,
                  'card_type'          => $tx_data[0]->card_type,
                  'risk_score'         => $tx->risk_score,
                  'risk_reason'        => $tx->risk_reason,
                ]);

                // Import into Laravel
                $new_tx->save();                
                $existing_txs[] = $tx->txn_id;
            }

            if( file_exists( base_path('data-ingestion') . '/data.csv') ) {
                unlink(base_path('data-ingestion') . '/data.csv');
            }

            if( file_exists( base_path('data-ingestion') . '/data.xlsx') ) {
                unlink(base_path('data-ingestion') . '/data.xlsx');
            }

            if( file_exists( base_path('data-ingestion') . '/insert_file_statement.csv') ) {
                unlink(base_path('data-ingestion') . '/insert_file_statement.csv');
            }

            return redirect('/dashboard');
        }
        else {
            dd($output);
            // Python script didn't work and there's some sort of error
        }
    }

    public static function sql_split($file, $delimiter = ';') {
    $queries = [];
    $filtered_queries = [];

    if (is_file($file) === true)
    {
        $file = fopen($file, 'r');

        if (is_resource($file) === true)
        {
            $query = array();

            while (feof($file) === false)
            {
                $query[] = fgets($file);

                if (preg_match('~' . preg_quote($delimiter, '~') . '\s*$~iS', end($query)) === 1)
                {
                    $query = trim(implode('', $query));

                    $queries[] = $query;
                }

                if (is_string($query) === true)
                {
                    $query = array();
                }
            }
        }
    }

    foreach ($queries as $key => $value) {
        if (strpos($queries[$key], '-- ') !== 0) {
            $filtered_queries[] = $value;
        }
        elseif( strpos($queries[$key], '---' ) !== 0 ) {
          $filtered_queries[] = $value;
        }
    }

    return $filtered_queries;
  }
}
