@extends('layouts.app')

{{-- $transaction and $organization are passed in --}}

@section('content')
  <div class="row justify-content-center mt-4 mb-5">
    <div class="col-12 text-center transaction-buttons">
      <a href="#" class="btn btn-primary" style="display: none">Download Invoice</a>
      @if( 'not_started' == $transaction->review_status )
        <a href="#" class="btn btn-primary ml-4 mr-4" data-toggle="modal" data-target="#paymentConfirmationModal">Request Manual Review</a>
      @endif
      <form method="POST" class="transaction-docs" action="/transaction/{{ $transaction->id }}/upload" enctype="multipart/form-data">
        @csrf
        <div class="form-group mb-0">
          <label for="document" class="btn btn-primary upload-label doc-upload mb-0">Upload Additional Documents (PDF)</label>
          <input type="file" class="form-control-file d-none" name="document" id="document">
        </div>
        <button type="submit" class="btn btn-primary upload-document d-none">Upload Additional Documents</button>
      </form>
    </div>
  </div>
  <div class="row justify-content-center">
    <div class="col-xs-12 col-sm-3 offset-1">
      <h3>Transaction Info</h3>

      <p><strong>Risk Score</strong>: {{$transaction->risk_score}}</p>
      <p><strong>Transaction ID</strong>: {{$transaction->transaction_id}}</p>
      <p><strong>Amount</strong>: ${{ number_format( $transaction->amount, 2, '.', ',' ) }}</p>
      <p><strong>Transaction Date</strong>: {{ Carbon\Carbon::parse( $transaction->transaction_date )->format('m/d/Y') }}</p>
      <p><strong>Card Number (Last 4)</strong>: {{ strlen( $transaction->card_number ) > 4 ? substr( $transaction->card_number, -4 ) : $transaction->card_number }}</p>
      <p><strong>Expiration Date</strong>: {{$transaction->expiration_date}}</p>
    </div>
    <div class="col-xs-12 col-sm-3 offset-1 address-column">
      <h3>Billing Address</h3>

      <p>{{$transaction->billing_name}}</p>
      <p>{{$transaction->billing_address}}</p>
      <p>{{$transaction->billing_city}}, {{$transaction->billing_state}}</p>
      <p>{{$transaction->billing_zipcode}}</p>
      <p>{{$transaction->billing_country}}</p>
    </div>
    <div class="col-xs-12 col-sm-3 offset-1 address-column">
      <h3>Shipping Address</h3>

      <p>{{$transaction->shipping_name}}</p>
      <p>{{$transaction->shipping_address}}</p>
      <p>{{$transaction->shipping_city}}, {{$transaction->shipping_state}}</p>
      <p>{{$transaction->shipping_zipcode}}</p>
      <p>{{$transaction->shipping_country}}</p>
    </div>
  </div>
  @if( $transaction->file_paths)
  <div class="row justify-content-center supporting-docs mb-4">
      <div class="col-md-4 justify-content-center">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title font-weight-bold">
              Supporting Documents
            </h3>
          </div>
          <div class="card-body">
            <?php $file_paths = explode(',', $transaction->file_paths); ?>
            <ul>
              
            </ul>
            @foreach( $file_paths as $path )
              <div class="file-group">
                <a class="m-2" href="{{ Storage::url($path) }}"><?php $arr = explode( '/', $path ); echo array_pop( $arr ); ?></a>
                <a class="delete-file" href="/transaction/{{$transaction->id}}/file/delete/{{md5($path)}}"><i class="mdi mdi-close"></i></a>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  @endif
  <div class="row justify-content-center transaction-notes mb-4">
      <div class="col-md-4 justify-content-center">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title font-weight-bold">
              Transaction Notes
            </h3>
          </div>
          <div class="card-body">
            <form class="" action="/transaction/{{ $transaction->id }}/update" method="post">
              @csrf
              <div class="form-group row"><!--position-->
                  <div class="col-12">
                      <textarea name="transaction_notes" id="transaction_notes" cols="30" rows="10">{{ $transaction->notes }}</textarea>
                      @error('transaction_notes')
                          <span class="invalid-feedback" role="alert">
                              <strong>{{ $message }}</strong>
                          </span>
                      @enderror
                  </div>
              </div>
              <button class="btn btn-primary">Save Notes</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  @if( $transaction->investigation_summary )
    <div class="row justify-content-center mb-4">
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
  @endif

<div class="modal" tabindex="-1" role="dialog" id="paymentConfirmationModal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Heads up!</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p><strong>Manual review is a paid service and you will have to enter your credit card details on the next page before proceeding.</strong><br><br>Are you sure you want to request a manual review?</p>
      </div>
      <div class="modal-footer">
        <a href="/transaction/{{ $transaction->id }}/payment"><button type="button" class="btn btn-primary">Yes!</button></a>
        <button type="button" class="btn btn-cosainto-secondary-button" data-dismiss="modal">Not right now.</button>
      </div>
    </div>
  </div>
</div>
@endsection
