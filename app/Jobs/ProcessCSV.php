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
        $path = storage_path('app/') . $this->filepath;
        $contents = file_get_contents($path);

        file_put_contents(base_path('data-ingestion') . '/data.csv', $contents);
    }
}
