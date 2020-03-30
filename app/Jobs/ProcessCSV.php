<?php

namespace App\Jobs;

use Carbon\Carbon;

use Auth;
use \App\Organization;

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

    protected $filepath;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct( $filepath )
    {
        $this->filepath = $filepath;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {   
        $path = storage_path('app/') . $this->filepath;
        $contents = file_get_contents($path);

        file_put_contents(base_path('data-ingestion') . '/data.csv', $contents);

        $output = shell_exec("cd " . base_path('data-ingestion') . " && python ingest.py 2>&1");

        if( strpos($output, 'success') !== false ) {
            // Python script worked
            $contents = file_get_contents(  base_path( 'data-ingestion/insert_file_statement.csv' ) );
            $queries = explode("\n", $contents);
            $filtered_queries = [];
            $filtered_queries[] = 'drop table if exists base_table_temp;
            create table base_table_temp (txn_id varchar(40),
                txn_type varchar(20),
                txn_status varchar(20),
                sttlmnt_dt DATE ,
                auth_amt Decimal (7,2),
                sttlmnt_amt Decimal(7,2),
                refund_txn_id varchar(20),
                payment_type varchar(20),
                card_type varchar(20),
                cc_number varchar(20),
                billing_postal_cd varchar(20),
                billing_country varchar(100),
                shipping_postal_cd varchar(20),
                shipping_country varchar(100),
                ip_addr varchar(20),
                processor_response_code varchar(20),
                sttlmnt_currency varchar(20)
            );';

            foreach( $queries as $query ) {
                if( strlen( $query ) < 5 ) {
                    continue;
                }

                $query = explode('"', $query)[1];

                if( strpos( $query, "'NA', 'NA', 'NA', 'NA'") ) {
                    continue;
                }

                $filtered_queries[] = $query;
            }

            foreach( $filtered_queries as $query ) {
                $query = preg_replace_callback( "/'([0-9].*\/[0-9].*\/[0-9].*?)'/", function ($m){
                    return date("'Y-m-d 00:00:00'", strtotime($m[1]));
                }, $query);

                if( strpos($query, "'', '', '', '', '', ''") !== false ) {
                    continue;
                }
            
                DB::connection('mysql2')->getPdo()->exec( $query );
            }

            $queries = $this::sql_split( base_path( 'data-ingestion/ingest.sql' ) );

            foreach( $queries as $query ) {
                DB::connection('mysql2')->getPdo()->exec( $query );
            }

            return redirect('/dashboard');
            // Grab all the transactions that have been scored and import them into laravel
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
