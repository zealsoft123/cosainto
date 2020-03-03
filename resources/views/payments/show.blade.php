@extends('layouts.app')

{{-- $transactions passed in: collection of all transactions for user's organization --}}

@section('content')
	<script src="/js/payments.js"></script>

	<div class="container py-4">
    	<div class="row justify-content-center">
			<div class="col-12 col-md-8 text-center cosainto">
				<h4 class="mb-4">Start a Manual Review for Transaction #{{$transaction->id}}</h4>
				<form action="/charge" method="post" id="payment-form">
					@csrf
					<input id="transactionID" type="hidden" value="{{$transaction->id}}" name="transactionID">

					<div class="error">
						<div class="message"></div>
					</div>
					<div class="row">
						<div class="field">
		                	<input id="cosainto-name" class="input empty" type="text" placeholder="John Doe" required="" autocomplete="name" name="cosaintoName">
		                	<label for="cosainto-name">Name</label>
		                	<div class="baseline"></div>
		          		</div>
					</div>
					<div class="row">
						<div class="field">
		                	<input id="cosainto-email" class="input empty" type="email" placeholder="johndoe@acme.com" required="" autocomplete="email" name="cosainto-email">
		                	<label for="cosainto-email">Email</label>
		                	<div class="baseline"></div>
		          		</div>
					</div>

					<div class="row">
						<div class="field">
		                	<input id="cosainto-address" class="input empty" type="text" placeholder="185 Berry St" required="" autocomplete="address-line1" name="cosainto-address">
		                	<label for="cosainto-address">Address</label>
		                	<div class="baseline"></div>
		          		</div>
					</div>

					<div class="row">
						<div class="field half-width">
				            <input id="cosainto-city" data-tid="elements_examples.form.city_placeholder" class="input empty" type="text" placeholder="San Francisco" required="" autocomplete="address-level2" name="cosainto-city">
				            <label for="cosainto-city" data-tid="elements_examples.form.city_label">City</label>
				            <div class="baseline"></div>
				    	</div>
				    	<div class="field quarter-width">
			                <input id="cosainto-state" data-tid="elements_examples.form.state_placeholder" class="input empty" type="text" placeholder="CA" required="" autocomplete="address-level1" name="cosainto-state">
			                <label for="cosainto-state" data-tid="elements_examples.form.state_label">State</label>
			                <div class="baseline"></div>
			        	</div>
			        	<div class="field quarter-width">
			                <input id="cosainto-zip" data-tid="elements_examples.form.postal_code_placeholder" class="input empty" type="text" placeholder="94107" required="" autocomplete="postal-code" name="cosainto-zip">
			                <label for="cosainto-zip" data-tid="elements_examples.form.postal_code_label">ZIP</label>
			                <div class="baseline"></div>
			            </div>
					</div>

					<div class="row">
						<div class="field">
							<div id="cosainto-card"></div>
						</div>
					</div>
				  <button class="btn btn-primary" type="submit" data-tid="elements_examples.form.pay_button">Begin Manual Review Process</button>

				  <p class="disclaimer mt-2 font-italic">Your card will be charged $1.</p>
				</form>
			</div>
		</div>
	</div>
@endsection