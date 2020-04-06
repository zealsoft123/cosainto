<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;

use App\Http\Controllers\TransactionController;

use App\Imports\TransactionsImport;
use App\Transaction;
use App\Organization;
use App\Jobs\ProcessCSV;
use Auth;
use Redirect;

use App\Http\Controllers\SalesforceController;

class DashboardController extends Controller {
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct() {
      $this->middleware('auth');
  }

  /**
   * Show the application dashboard.
   *
   * @return \Illuminate\Contracts\Support\Renderable
   */
  public function index() {
    \App\Http\Controllers\TransactionController::sql_split( base_path( 'data-ingestion/ingest.sql' ) );
    return view( 'dashboard', $this->getDashboardData());
  }

  /**
   * Show upload csv of new transactions to run against the model.
   *
   * @return \Illuminate\Contracts\Support\Renderable
   */
  public function uploadTransactions(Request $request) {
    $request->validate(['transactions' => 'mimes:csv,txt,xlsx,xls']);

    $file = $request->file('transactions');
    $ext = $file->getClientOriginalExtension();
    $file->move(base_path('data-ingestion'), "data.{$ext}");

    // Save file to proper file path
    ProcessCSV::dispatch();

    $updated_data = $this->getDashboardData();

    return Redirect::route('dashboard', [ 'updated_data' => $updated_data ] );
  }

  protected function getDashboardData() {
    $transactions = Transaction::where('organization_id', Auth::User()->organization)->get();
    $user = Auth::User();
    $organization = Organization::find($user->organization);

    return [
      'transactions'      => $transactions,
      'user'              => $user,
      'organization'      => $organization,
      'transaction_count' => count( $transactions ),
      'sales_count'       => $this->getSalesCount( $organization ),
      'sales_volume'      => $this->getSalesVolume( $organization ),
      'chargeback_ratio'  => count( $transactions ) !== 0 ? $this->getChargebacks( $organization ) / count( $transactions ) : 'N/A' ,
      'total_declines'    => $this->getDeclines( $organization ),
      'inprogress_cases'  => $transactions->filter(function($tx){return 'pending' == $tx->review_status; })->count(),
      'completed_cases'   => $transactions->filter(function($tx){return 'completed' == $tx->review_status; })->count()
    ];
  }

  protected function getSalesCount( $organization ){
    $transactions = Transaction::where(
      [
        [ 'organization_id', '=', $organization->id ],
        [ 'transaction_type', '=', 'sale' ],
      ]
    )->get();

    return count( $transactions );
  }

  protected function getSalesVolume( $organization ){
    $transactions = Transaction::where(
      [
        [ 'organization_id', '=', $organization->id ],
        [ 'transaction_type', '=', 'sale' ],
      ]
    )->get();

    $sales_volume = 0;

    foreach( $transactions as $transaction ) {
      $sales_volume += $transaction->amount;
    }

    return number_format( $sales_volume, 2, '.', ',' );
  }

  protected function getChargebacks( $organization ){
    $transactions = Transaction::where(
      [
        [ 'organization_id', '=', $organization->id ],
        [ 'transaction_type', '=', 'dispute' ],
      ]
    )->get();
    
    return count( $transactions );
  }

  protected function getDeclines( $organization ){
    $transactions = Transaction::where(
      [
        [ 'organization_id', '=', $organization->id ],
        [ 'transaction_status', '=', 'processor_declined' ],
      ]
    )->get();
    
    return count( $transactions );
  }
}
