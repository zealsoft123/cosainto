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
            $queries = $this::sql_split( base_path( 'data-ingestion/out.sql' ) );

            foreach( $queries as $query ) {
              DB::connection('mysql2')->getPdo()->exec( $query );
            }

            dd('done');
            // Grab all the transactions fromm
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
