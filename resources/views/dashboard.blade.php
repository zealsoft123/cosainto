@extends('layouts.app')

{{-- $transactions passed in: collection of all transactions for user's organization --}}

@section('content')

<div class="container py-4">
    <div class="row">
      <div class="col">
        <h1>{{ $organization->name }}</h1>
      </div>
      <div class="col-auto">
        <div class="upload-container">
          <form method="POST" action="/dashboard" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
              <label for="transactions" class="btn btn-primary upload-label">Upload Transactions (CSV)</label>
              <input type="file" class="form-control-file" name="transactions" id="transactions">
            </div>
            <button type="submit" class="btn btn-primary upload-csv">Upload</button>
          </form>
          <a href="/transaction/export"><button class="btn cosainto-secondary-btn mb-4">Download Transactions (CSV)</button></a>

          @error('transactions')
              <span class="transactions-error mb-3" role="alert">
                  <strong>{{ $message }}</strong>
              </span>
          @enderror
        </div>

        <span class="float-right my-1 font-italic">

          @if( null !== $organization->last_uploaded )
            Last Upload: {{ \Carbon\Carbon::parse( $organization->last_uploaded )->toFormattedDateString() }}
          @else
            Last Upload: Never
          @endif
        </span>

      </div>
    </div>
      <div class="card">
        <div class="row no-gutters">
          <div class="card-header col-3 pl-3">
            <div class="card-text">
              Total Transactions: {{ $transaction_count }}<br>
              Total Sales Count: {{ $sales_count }}<br>
              Total Sales Volume: ${{ $sales_volume }}<br>
              Chargeback Ratio: {{ $chargeback_ratio }}<br>
              Total Declines: {{ $total_declines }}
            </div>
          </div>
          <div class="card-body col-8">
            <div class="row no-gutters">
              <div class="col-auto px-3">
                <span class="clearfix font-weight-bold">Total Flagged Transactions</span>
                <span class="clearfix font-italic">N/A&#37; of Total</span>
                <span class="font-weight-bold dashboard-stats">N/A</span>
              </div>
              <div class="col-auto px-3">
                <span class="clearfix font-weight-bold">Cases in Review</span>
                <span class="clearfix font-italic">In Progress Cases</span>
                <span class="font-weight-bold dashboard-stats">{{ $inprogress_cases }}</span>
              </div>
              <div class="col-auto px-3">
                <span class="clearfix font-weight-bold">Completed Cases</span>
                <span class="clearfix font-italic">In our Queue</span>
                <span class="font-weight-bold dashboard-stats">{{ $completed_cases }}</span>

              </div>
            </div>
          </div>
        </div>
      </div>
    <div class="row justify-content-center mt-3">
      <div class="col-12 ">
        <div class="alert alert-primary additional-data" role="alert">
          <strong>Heads up!</strong> Some of the info in this table has changed since we loaded it for you. <a href="/dashboard">Refresh this page</a> to make sure you're seeing the latest data.
        </div>

        <div class="table-responsive">
          <table class="table table-striped table-hover transactions">
            <thead class="thead-light">
              <tr>
                <th scope="col">Transaction ID</th>
                <th scope="col">Status</th>
                <th scope="col">Type</th>
                <th scope="col">Amount</th>
                <th scope="col">Risk Score</th>
                <th scope="col">Risk Reason</th>
                <th scope="col">Case Status</th>
                <th scope="col">Date</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($transactions as $transaction)
                <tr>
                  <th scope="row">{{ $transaction->transaction_id }}</th>
                  <td>{{ ucwords( str_replace( '_', ' ', $transaction->transaction_status ) ) }}</td>
                  <td>{{ ucwords( $transaction->transaction_type ) }}</td>
                  <td>${{ number_format( $transaction->amount, 2, '.', ',' ) }}</td>
                  <td>{{ $transaction->risk_score ?? 'Pending' }}</td>
                  <td>{{ $transaction->risk_reason ?? 'Pending' }}</td>
                  @if( 'not_started' == $transaction->review_status )
                    <td><a href="/transaction/{{ $transaction->id }}">Request Review</a></td>
                  @elseif( 'pending' == $transaction->review_status )
                    <td><a href="/transaction/{{ $transaction->id }}">Review In Progress</a></td>
                    @elseif( 'completed' == $transaction->review_status )
                    <td><a href="/transaction/{{ $transaction->id }}">Review Completed</a></td>
                  @endif
                  <td>{{ $transaction->transaction_date->toFormattedDateString() }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
</div>
@endsection
