@extends('layouts.app')

{{-- $transaction and $organization are passed in --}}

@section('content')
  <div class="row justify-content-center mt-4 mb-5">
    <div class="col-12 text-center transaction-buttons">
      <a href="#" class="btn btn-primary" style="display: none">Download Invoice</a>
      <a href="#" class="btn btn-primary ml-4 mr-4">Request Manual Review</a>
      <a href="#" class="btn cosainto-secondary-btn">Upload Additional Documents</a>
    </div>
  </div>
  <div class="row justify-content-center">
    <div class="col-3 offset-1">
      <h3>Transaction Info</h3>

      <p><strong>Risk Score</strong>: {{$transaction->risk_score}}</p>
      <p><strong>Transaction ID</strong>: {{$transaction->transaction_id}}</p>
      <p><strong>Amount</strong>: ${{ number_format( $transaction->amount, 2, '.', ',' ) }}</p>
      <p><strong>Transaction Date</strong>: {{ Carbon\Carbon::parse( $transaction->transaction_date )->format('m/d/Y') }}</p>
      <p><strong>Card Number (Last 4)</strong>: {{$transaction->card_number}}</p>
      <p><strong>Expiration Date</strong>: {{$transaction->expiration_date}}</p>
    </div>
    <div class="col-3 address-column">
      <h3>Billing Address</h3>

      <p>{{$transaction->billing_name}}</p>
      <p>{{$transaction->billing_address}}</p>
      <p>{{$transaction->billing_city}}, {{$transaction->billing_state}}</p>
      <p>{{$transaction->billing_zipcode}}</p>
      <p>{{$transaction->billing_country}}</p>
    </div>
    <div class="col-3 address-column">
      <h3>Shipping Address</h3>

      <p>{{$transaction->shipping_name}}</p>
      <p>{{$transaction->shipping_address}}</p>
      <p>{{$transaction->shipping_city}}, {{$transaction->shipping_state}}</p>
      <p>{{$transaction->shipping_zipcode}}</p>
      <p>{{$transaction->shipping_country}}</p>
    </div>
  </div>
  <div class="row justify-content-center" style="display: none">
    <div class="col-md-4 justify-content-center">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title font-weight-bold">
            Investigation Summary
          </h3>
        </div>
        <div class="card-body">
          {{ $transaction->investigation_summary }}
        </div>
      </div>
    </div>
  </div>
@endsection
