<?php

namespace App\Http\Controllers;

use Auth;

use App\Transaction;
use Illuminate\Http\Request;

use App\Organization;
use App\Exports\TransactionsExport;
use Maatwebsite\Excel\Facades\Excel;

class TransactionController extends Controller {
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct() {
      $this->middleware('auth');
  }


  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index() {
    $transactions = Transaction::where('organization_id', Auth::User()->organization)->get();
    return $transactions;
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create() {
    //
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request) {
    //
  }

  /**
   * Display the specified resource.
   *
   * @param  int $transaction transaction id
   * @return \Illuminate\Http\Response
   */
  public function show(int $transactionID) {
    $transaction = Transaction::findOrFail($transactionID);
    $organization = Organization::findOrFail($transaction->organization_id);


    return view('transaction', [
      'transaction'   => $transaction,
      'organization'  => $organization,

    ]);
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  \App\Transaction  $transaction
   * @return \Illuminate\Http\Response
   */
  public function edit(Transaction $transaction) {
    //
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \App\Transaction  $transaction
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, Transaction $transaction) {

  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \App\Transaction  $transaction
   * @return \Illuminate\Http\Response
   */
   public function openInvestigation(Transaction $transaction) {
     // charge customer through stripe
     // send transaction to salesforce
     // change investigation status on success
   }

  /**
   * Remove the specified resource from storage.
   *
   * @param  \App\Transaction  $transaction
   * @return \Illuminate\Http\Response
   */
  public function destroy(Transaction $transaction) {
    //
  }

  /**
   * Exports an entirety of a user's data to a CSV file.
   *
   * @return CSV download
   */
  public function export() {
    return Excel::download(new TransactionsExport, 'Cosainto Transactions.csv');
  }

  /**
   * Creates a hash of the current transactions so we 
   * can know when the set of transactions has changed.
   *
   * @return string hash of $transactions
   */
  public static function hash() {
    $transactions = Transaction::where('organization_id', Auth::User()->organization)->get();

    return md5( $transactions );
  }
}
