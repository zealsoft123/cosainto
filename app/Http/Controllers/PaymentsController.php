<?php

namespace App\Http\Controllers;

use App\Transaction;
use Illuminate\Http\Request;

class PaymentsController extends Controller
{
    public function show(Request $request)
    {
        return view('payments.show', ['transaction' => Transaction::find($request->id)]);
    }

    public function charge(Request $request)
    {
        $inputs = $request->all();

        $tx = Transaction::find($inputs['transactionID']);

        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

        $charge = \Stripe\Charge::create([
            'amount' => 100,
            'currency' => 'usd',
            'source' => $inputs['stripeToken'],
            'description' => 'Manual review of Cosainto transaction ' . $tx->transaction_id
        ]);

        if ($charge->status == 'succeeded') {
            $transaction = Transaction::find($inputs['transactionID']);

            $transaction->review_status = 'pending';

            $transaction->save();

            SalesforceController::add_case($transaction);

            return redirect('/dashboard');
        }
    }
}
