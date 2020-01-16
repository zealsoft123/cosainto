@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
          <h1>Registration</h1>
          <form method="POST" action="{{ route('register') }}">
              @csrf

            <div class="card"><!--Account Information-->
                <div class="card-header">{{ __('Account Information') }}</div>

                <div class="card-body">

                        <div class="form-group row"><!--name-->
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Name') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row"><!--position-->
                            <label for="position" class="col-md-4 col-form-label text-md-right">{{ __('Position') }}</label>

                            <div class="col-md-6">
                                <input id="position" type="text" class="form-control @error('position') is-invalid @enderror" name="position" value="{{ old('position') }}" required autocomplete="organization-title" autofocus>

                                @error('position')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row"><!--phone-->
                            <label for="phone" class="col-md-4 col-form-label text-md-right">{{ __('Phone Number') }}</label>

                            <div class="col-md-6">
                                <input id="phone" type="tel" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}" required autocomplete="tel">

                                @error('phone')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row"><!--email-->
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row"><!--password-->
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row"><!--password-confirm-->
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                </div>
            </div>

            <div class="card"><!--Organization information-->
              <div class="card-header">{{ __('Organization Information') }}</div>
              <div class="card-body">
                <div class="form-group row"><!--business-name-->
                    <label for="business-name" class="col-md-4 col-form-label text-md-right">{{ __('Business Name') }}</label>

                    <div class="col-md-6">
                        <input id="business-name" type="text" class="form-control @error('business-name') is-invalid @enderror" name="business-name" value="{{ old('business-name') }}" required autocomplete="organization" autofocus>

                        @error('business-name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="form-group row"><!--url-->
                    <label for="url" class="col-md-4 col-form-label text-md-right">{{ __('Website') }}</label>

                    <div class="col-md-6">
                        <input id="url" type="text" class="form-control @error('url') is-invalid @enderror" name="url" value="{{ old('url') }}" required autocomplete="url" autofocus>

                        @error('url')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="form-group row"><!--category-->
                    <label for="category" class="col-md-4 col-form-label text-md-right">{{ __('Category') }}</label>

                    <div class="col-md-6">
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="category" id="tangibleProduct" value="tangibleProduct" >
                        <label class="form-check-label" for="tangibleProduct">
                          Tangible Product is Provided
                        </label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="category" id="saas" value="saas" >
                        <label class="form-check-label" for="saas">
                          Software As A Service (SaaS)
                        </label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="category" id="ctp" value="ctp" >
                        <label class="form-check-label" for="ctp">
                          Consulting/Travel/Planning
                        </label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="category" id="other" value="other" >
                        <label class="form-check-label" for="other">
                          Other
                        </label>
                      </div>
                      <input id="category-other" type="text" class="form-control @error('category-other') is-invalid @enderror" name="category-other" value="{{ old('category-other') }}" autocomplete="organization" autofocus>

                    </div>
                </div>


                <div class="form-group row"><!--payment-provider-->
                    <label for="payment-provider" class="col-md-4 col-form-label text-md-right">{{ __('Payment Provider') }}</label>

                    <div class="col-md-6">
                        <Select id="payment-provider" type="text" class="form-control @error('payment-provider') is-invalid @enderror" name="payment-provider" value="{{ old('payment-provider') }}" required autofocus>
                          <option value="stripe">Stripe</option>
                          <option value="braintree">Braintree</option>
                          <option value="adyen">Adyen</option>
                        </select>

                        @error('payment-provider')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="form-group row"><!--street_address-->
                    <label for="street_address" class="col-md-4 col-form-label text-md-right">{{ __('Street Address') }}</label>

                    <div class="col-md-6">
                        <input id="street_address" type="text" class="form-control @error('street_address') is-invalid @enderror" name="street_address" value="{{ old('street_address') }}" required autocomplete="street-address" autofocus>

                        @error('street_address')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="form-group row"><!--city-->
                    <label for="city" class="col-md-4 col-form-label text-md-right">{{ __('City') }}</label>

                    <div class="col-md-6">
                        <input id="city" type="text" class="form-control @error('city') is-invalid @enderror" name="city" value="{{ old('city') }}" required autocomplete="address-level2" autofocus>

                        @error('state')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="form-group row"><!--state-->
                    <label for="state" class="col-md-4 col-form-label text-md-right">{{ __('State') }}</label>

                    <div class="col-md-6">
                        <select id="state" type="text" class="form-control @error('state') is-invalid @enderror" name="state" value="{{ old('state') }}" required autocomplete="address-address-level1" autofocus>
                          <option value="AL">Alabama</option>
                          <option value="AK">Alaska</option>
                          <option value="AZ">Arizona</option>
                          <option value="AR">Arkansas</option>
                          <option value="CA">California</option>
                          <option value="CO">Colorado</option>
                          <option value="CT">Connecticut</option>
                          <option value="DE">Delaware</option>
                          <option value="FL">Florida</option>
                          <option value="GA">Georgia</option>
                          <option value="HI">Hawaii</option>
                          <option value="ID">Idaho</option>
                          <option value="IL">Illinois</option>
                          <option value="IN">Indiana</option>
                          <option value="IA">Iowa</option>
                          <option value="KS">Kansas</option>
                          <option value="KY">Kentucky</option>
                          <option value="LA">Louisiana</option>
                          <option value="ME">Maine</option>
                          <option value="MD">Maryland</option>
                          <option value="MA">Massachusetts</option>
                          <option value="MI">Michigan</option>
                          <option value="MN">Minnesota</option>
                          <option value="MS">Mississippi</option>
                          <option value="MO">Missouri</option>
                          <option value="MT">Montana</option>
                          <option value="NE">Nebraska</option>
                          <option value="NV">Nevada</option>
                          <option value="NH">New Hampshire</option>
                          <option value="NJ">New Jersey</option>
                          <option value="NM">New Mexico</option>
                          <option value="NY">New York</option>
                          <option value="NC">North Carolina</option>
                          <option value="ND">North Dakota</option>
                          <option value="OH">Ohio</option>
                          <option value="OK">Oklahoma</option>
                          <option value="OR">Oregon</option>
                          <option value="PA">Pennsylvania</option>
                          <option value="RI">Rhode Island</option>
                          <option value="SC">South Carolina</option>
                          <option value="SD">South Dakota</option>
                          <option value="TN">Tennessee</option>
                          <option value="TX">Texas</option>
                          <option value="UT">Utah</option>
                          <option value="VT">Vermont</option>
                          <option value="VA">Virginia</option>
                          <option value="WA">Washington</option>
                          <option value="WV">West Virginia</option>
                          <option value="WI">Wisconsin</option>
                          <option value="WY">Wyoming</option>
                        </select>
                        @error('state')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="form-group row"><!--zipcode-->
                    <label for="zipcode" class="col-md-4 col-form-label text-md-right">{{ __('Zipcode') }}</label>

                    <div class="col-md-6">
                        <input id="zipcode" type="text" class="form-control @error('zipcode') is-invalid @enderror" name="zipcode" value="{{ old('zipcode') }}" required autocomplete="postal-code" autofocus>

                        @error('zipcode')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="form-group row"><!--summary-->
                    <label for="summary" class="col-md-4 col-form-label text-md-right">{{ __('Short Summary of What is Unique About Your Business?') }}</label>

                    <div class="col-md-6">
                        <textarea id="summary" type="text" class="form-control @error('summary') is-invalid @enderror" name="summary" value="{{ old('summary') }}" required autofocus rows="5" placeholder="Description of some things that makes your business special"></textarea>

                        @error('summary')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="form-group row mb-0"><!--registration submit-->
                  <div class="col-md-6 offset-md-4">
                    <button type="submit" class="btn btn-primary">
                      {{ __('Register') }}
                    </button>
                  </div>
                </div>
              </div>
            </div>

          </form>
        </div>
    </div>
</div>
@endsection
