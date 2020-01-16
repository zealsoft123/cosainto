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
        $user = Auth::User();
        $organization = Organization::findOrFail($user->organization);

        $organization->last_uploaded = Carbon::now();
        $organization->save();

        $tx_table = "tx_data_" . md5( time() . $user->id . $user->organization );

        Schema::create( $tx_table, function (Blueprint $table) {
            $table->bigIncrements('id');
        });

        // Create a custom database table for this upload
        
        // Import each transaction into the base custom table

        // Grab all the SQL that needs to be run against the transactions

        // Find/replace the base table name to the specifically generated table name

        // Append the hash to all the table names in the sql statement so that each import is separated

        // Destroy the custom database table for this upload
        Schema::drop( $tx_table );

        Storage::delete($this->filepath);
    }
}
