<?php

namespace App\Http\Controllers;

use App\Exports\TransactionsExport;
use App\Organization;
use App\Transaction;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Storage;

class TransactionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $transactions = Transaction::where('organization_id', Auth::User()->organization)->get();
        return $transactions;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $transaction transaction id
     * @return \Illuminate\Http\Response
     */
    public function show($transactionID)
    {
        $transaction = Transaction::findOrFail($transactionID);
        $organization = Organization::findOrFail($transaction->organization_id);


        return view('transaction', [
            'transaction' => $transaction,
            'organization' => $organization,

        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Transaction $transaction
     * @return \Illuminate\Http\Response
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Transaction $transaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transaction $transaction)
    {
        $transaction_id = intval($request->id);

        if ($transaction_id <= 0) {
            return redirect('/dashboard');
        }

        // If the reuqested transaction doesn't belong to this user, bail
        $transaction = \App\Transaction::find($transaction_id);
        $organization = Auth::User()->organization;

        if ($organization != $transaction->organization_id) {
            return redirect('/dashboard');
        }

        $notes = strip_tags($request->transaction_notes);

        $transaction->notes = $notes;

        $transaction->save();

        return redirect("/transaction/$transaction_id");
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Transaction $transaction
     * @return \Illuminate\Http\Response
     */
    public function openInvestigation(Transaction $transaction)
    {
        // charge customer through stripe
        // send transaction to salesforce
        // change investigation status on success
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Transaction $transaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transaction $transaction)
    {
        //
    }

    /**
     * Exports an entirety of a user's data to a CSV file.
     *
     * @return CSV download
     */
    public function export()
    {
        return Excel::download(new TransactionsExport, 'Cosainto Transactions.csv');
    }

    /**
     * Returns whether transactions have changed in the last 30 seconds
     *
     * @return bool
     */
    public static function last_updated()
    {
        $transactions = Transaction::where('organization_id', Auth::User()->organization)
            ->where('updated_at', '>=', Carbon::now()->subSeconds(30)->toDateTimeString())
            ->whereRaw('updated_at != created_at')
            ->get();

        return $transactions->count() > 0 ? 'true' : 'false';
    }

    /**
     * Splits the output SQL file into queries so that each one can run automatically
     *
     * @return
     * @var string delimiter THe delimiter that splits the SQL transactions
     *
     * @var string filepath
     */
    public static function sql_split($file, $delimiter = ';')
    {
        $queries = [];
        $filtered_queries = [];

        // Create the base table and base_table_temp
        $base_query = "drop table if exists base_table_temp;
        create table base_table_temp (
        txn_id varchar(100),
        txn_type varchar(20),
        txn_status varchar(20),
        sttlmnt_dt DATE ,
        auth_amt Decimal (7,2),
        sttlmnt_amt Decimal(7,2),
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

        DB::connection('mysql2')->getPdo()->exec($base_query);

        $new_txs = Transaction::whereNull('risk_score')->get();

        foreach ($new_txs as $tx) {
            $amount = number_format($tx->amount, 2, '.', '');
            $billing_zipcode = ($tx->billing_zipcode != '' && $tx->billing_zipcode != null) ? $tx->billing_zipcode : 'NA';
            $billing_country = $tx->billing_country != '' && $tx->billing_country != null ? $tx->billing_country : 'NA';
            $shipping_zipcode = $tx->shipping_zipcode != '' && $tx->shipping_zipcode != null ? $tx->shipping_zipcode : 'NA';
            $shipping_country = $tx->shipping_country != '' && $tx->shipping_country != null ? $tx->shipping_country : 'NA';

            $new_query = "INSERT INTO base_table_temp VALUES('$tx->transaction_id', '$tx->transaction_type', '$tx->transaction_status', '{$tx->transaction_date->toDateString()}', $amount, $amount, 'NA', 'Credit Card', '$tx->card_type', '$tx->card_number', '$billing_zipcode', '$billing_country', '$shipping_zipcode', '$shipping_country', 'NA', 1000.0, 'USD');";

            $filtered_queries[] = $new_query;
        }

        // $filtered_queries[] = "INSERT INTO base_table_temp VALUES('1xcbvrpy', 'sale', 'settled', '2018-12-11', 1084.85, 1084.85, 'NA', 'Credit Card', 'Visa', '479851******0045', 13790, 'United States of America', 'NA', 'NA', '192.237.212.164', 1000.0, 'USD');";
        // $filtered_queries[] = "INSERT INTO base_table_temp VALUES('2nk2hgjy', 'sale', 'settled', '2018-12-13', 995.42, 995.42, 'NA', 'Credit Card', 'MasterCard', '546616******2397', 28411, 'United States of America', 'NA', 'NA', '192.237.212.164', 1000.0, 'USD');";
        // $filtered_queries[] = "INSERT INTO base_table_temp VALUES('47wjpd20', 'sale', 'settled', '2018-12-11', 1115.65, 1115.65, 'NA', 'Credit Card', 'MasterCard', '546616******3446', 27519, 'United States of America', 'NA', 'NA', '192.237.212.164', 1000.0, 'USD');";
        // $filtered_queries[] = "INSERT INTO base_table_temp VALUES('5013smqh', 'sale', 'settled', '2018-12-13', 1505.15, 1505.15, 'NA', 'Credit Card', 'Visa', '479851******8310', 27966, 'United States of America', 'NA', 'NA', '192.237.212.164', 1000.0, 'USD');";
        // $filtered_queries[] = "INSERT INTO base_table_temp VALUES('5yxx6jy9', 'sale', 'settled', '2018-12-03', 1056.25, 1056.25, 'NA', 'Credit Card', 'Visa', '479851******7701', 43023, 'United States of America', 'NA', 'NA', '192.237.212.164', 1000.0, 'USD');";
        // $filtered_queries[] = "INSERT INTO base_table_temp VALUES('1xcbvrxy', 'sale', 'settled', Timestamp('2018-12-11 00:00:00'), 1084.85, 1084.85, 'NA', 'Credit Card', 'Visa', '479851******0045', 13790, 'United States of America', 'NA', 'NA', '192.237.212.164', 1000.0, 'USD');";

        if (is_file($file) === true) {
            $file = fopen($file, 'r');

            if (is_resource($file) === true) {
                $query = array();

                while (feof($file) === false) {
                    $query[] = fgets($file);

                    if (preg_match('~' . preg_quote($delimiter, '~') . '\s*$~iS', end($query)) === 1) {
                        $query = trim(implode('', $query));

                        $queries[] = $query;
                    }

                    if (is_string($query) === true) {
                        $query = array();
                    }
                }
            }
        }

        foreach ($queries as $key => $value) {
            if (strpos($queries[$key], '-- ') !== 0) {
                $filtered_queries[] = $value;
            } elseif (strpos($queries[$key], '---') !== 0) {
                $filtered_queries[] = $value;
            }
        }

        foreach ($filtered_queries as $query) {
            DB::connection('mysql2')->getPdo()->exec($query);
        }

        $results = DB::connection('mysql2')->select('SELECT * FROM cos_cons_txn_score');

        foreach ($results as $result) {
            $tx = Transaction::where('transaction_id', $result->txn_id)->first();

            if ($tx) {
                $tx->risk_score = number_format($result->risk_score, 2, '.', '');
                $tx->risk_reason = $result->risk_reason;
                $tx->save();
            }
        }

        return;

        // Tear down all the tables
        $colname = 'Tables_in_' . env('DB_DATABASE_SECOND');
        $tables = DB::connection('mysql2')->select('SHOW TABLES');

        foreach ($tables as $table) {

            $droplist[] = $table->$colname;

        }
        $droplist = implode(',', $droplist);

        DB::connection('mysql2')->beginTransaction();
        DB::connection('mysql2')->statement("DROP TABLE $droplist");
        DB::connection('mysql2')->commit();
    }

    public function upload(Request $request, $id)
    {
        // Make sure this transaction belongs to this user
        $tx = Transaction::find(intval($id));

        if (!$tx || !Auth::user()->organization == $tx->organization_id) {
            return redirect('/dashboard');
        }

        $request->validate(['document' => 'mimes:pdf']);

        $path = Storage::putFileAs(
            'public/docs/' . Auth::user()->id, $request->file('document'), $request->file('document')->getClientOriginalName()
        );

        $file_paths = explode(',', $tx->file_paths);
        $file_paths[] = $path;
        $file_paths = implode(',', array_filter($file_paths));

        $tx->file_paths = $file_paths;
        $tx->save();

        return redirect("/transaction/$id");
    }

    public function delete_file(Request $request, $tx_id, $hash)
    {
        $tx = Transaction::find(intval($tx_id));

        if (!$tx || !Auth::user()->organization == $tx->organization_id) {
            return redirect('/dashboard');
        }

        $files = explode(',', $tx->file_paths);

        foreach ($files as $key => $file) {
            if (md5($file) === $hash) {
                unset($files[$key]);
                Storage::delete($file);
            }
        }

        $tx->file_paths = implode(',', $files);
        $tx->save();

        return redirect("/transaction/$tx_id");
    }
}
