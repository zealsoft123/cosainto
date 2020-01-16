@extends('layouts.app')

{{-- $transactions passed in: collection of all transactions for user's organization --}}

@section('content')
  <div class="container py-4">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8 text-center">
        <h1>{{$user->name}}'s profile</h1>
        <form class="" action="/profile" method="post">
          @csrf

          <div class="card"><!--Account Information-->
              <div class="card-header">{{ __('User Information') }}</div>

              <div class="card-body">

                      <div class="form-group row"><!--name-->
                          <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Name') }}</label>

                          <div class="col-md-6">
                              <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" placeholder="{{$user->name}}" value="{{ old('name') }}" autocomplete="name" autofocus>

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
                              <input id="position" type="text" class="form-control @error('position') is-invalid @enderror" name="position" placeholder="{{$user->position}}" value="{{ old('position') }}" autocomplete="organization-title" autofocus>

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
                              <input id="phone" type="tel" class="form-control @error('phone') is-invalid @enderror" name="phone" placeholder="{{$user->phone_number}}" value="{{ old('phone') }}" autocomplete="tel">

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
                              <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" placeholder="{{$user->email}}" value="{{ old('email') }}"  autocomplete="email">

                              @error('email')
                                  <span class="invalid-feedback" role="alert">
                                      <strong>{{ $message }}</strong>
                                  </span>
                              @enderror
                          </div>
                      </div>

                      <div class="form-group row"><!--password-->
                          <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Current Password') }}</label>

                          <div class="col-md-6">
                              <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="password">

                              @error('password')
                                  <span class="invalid-feedback" role="alert">
                                      <strong>{{ $message }}</strong>
                                  </span>
                              @enderror
                          </div>
                      </div>

                      <div class="form-group row"><!--new-password-->
                          <label for="new-password" class="col-md-4 col-form-label text-md-right">{{ __('New Password') }}</label>

                          <div class="col-md-6">
                              <input id="new-password" type="password" class="form-control @error('new-password') is-invalid @enderror" name="new-password" autocomplete="new-password">

                              @error('new-password')
                                  <span class="invalid-feedback" role="alert">
                                      <strong>{{ $message }}</strong>
                                  </span>
                              @enderror
                          </div>
                      </div>

                      <div class="form-group row"><!--new-password_confirmation-->
                          <label for="new-password_confirmation" class="col-md-4 col-form-label text-md-right">{{ __('Confirm New Password') }}</label>

                          <div class="col-md-6">
                              <input id="new-password_confirmation" type="password" class="form-control" name="new-password_confirmation" autocomplete="new-password">
                          </div>
                      </div>

              </div>
          </div>

        <h2 class="my-3">{{$organization->name}}</h2>
        <div class="card"><!--Organization information-->
          <div class="card-header">{{ __('Organization Information') }}</div>
          <div class="card-body">
            <div class="form-group row"><!--business-name-->
                <label for="business-name" class="col-md-4 col-form-label text-md-right">{{ __('Business Name') }}</label>

                <div class="col-md-6">
                    <input id="business-name" type="text" class="form-control @error('business-name') is-invalid @enderror" name="business-name" placeholder="{{$organization->name}}" value="{{ old('business-name') }}" autocomplete="organization" autofocus>

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
                    <input id="url" type="text" class="form-control @error('url') is-invalid @enderror" name="url" placeholder="{{$organization->url}}" value="{{ old('url') }}" autocomplete="url" autofocus>

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
                    <input class="form-check-input" type="radio" name="category" id="tangibleProduct" value="tangibleProduct" @if($organization->category == "tangibleProduct") checked @endif >
                    <label class="form-check-label" for="tangibleProduct">
                      Tangible Product is Provided
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="category" id="saas" value="saas" @if($organization->category == "saas") checked @endif >
                    <label class="form-check-label" for="saas">
                      Software As A Service (SaaS)
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="category" id="ctp" value="ctp" @if($organization->category == "ctp") checked @endif >
                    <label class="form-check-label" for="ctp">
                      Consulting/Travel/Planning
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="category" id="other" value="other" @if($organization->category != "tangibleProduct" && $organization->category != "saas" && $organization->category != "ctp") checked @endif >
                    <label class="form-check-label" for="other">
                      Other
                    </label>
                  </div>
                  <input id="category-other" type="text" class="form-control @error('category-other') is-invalid @enderror" name="category-other" @if($organization->category != "tangibleProduct" && $organization->category != "saas" && $organization->category != "ctp") place-holder="{{$organization->category}}" @endif value="{{ old('category-other') }}" autocomplete="organization" autofocus>

                </div>
            </div>

            <div class="form-group row"><!--payment-provider-->
                <label for="payment-provider" class="col-md-4 col-form-label text-md-right">{{ __('Payment Provider') }}</label>

                <div class="col-md-6">
                    <Select id="payment-provider" type="text" class="form-control @error('payment-provider') is-invalid @enderror" name="payment-provider" value="{{ old('payment-provider') }}" autofocus>
                      <option value="stripe" @if($organization->payment_provider == "stripe") selected @endif>Stripe</option>
                      <option value="braintree" @if($organization->payment_provider == "braintree") selected @endif>Braintree</option>
                      <option value="adyen" @if($organization->payment_provider == "adyen") selected @endif>Adyen</option>
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
                    <input id="street_address" type="text" class="form-control @error('street_address') is-invalid @enderror" name="street_address" placeholder="{{$organization->street_address}}" value="{{ old('street_address') }}" autocomplete="street-address" autofocus>

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
                    <input id="city" type="text" class="form-control @error('city') is-invalid @enderror" name="city" placeholder="{{$organization->city}}" value="{{ old('city') }}" autocomplete="address-level2" autofocus>

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
                    <select id="state" type="text" class="form-control @error('state') is-invalid @enderror" name="state" value="{{ old('state') }}" autocomplete="address-address-level1" autofocus>
                      <option value="AL" @if($organization->state == "AL") selected @endif>Alabama</option>
                      <option value="AK" @if($organization->state == "AK") selected @endif>Alaska</option>
                      <option value="AZ" @if($organization->state == "AZ") selected @endif>Arizona</option>
                      <option value="AR" @if($organization->state == "AR") selected @endif>Arkansas</option>
                      <option value="CA" @if($organization->state == "CA") selected @endif>California</option>
                      <option value="CO" @if($organization->state == "CO") selected @endif>Colorado</option>
                      <option value="CT" @if($organization->state == "CT") selected @endif>Connecticut</option>
                      <option value="DE" @if($organization->state == "DE") selected @endif>Delaware</option>
                      <option value="FL" @if($organization->state == "FL") selected @endif>Florida</option>
                      <option value="GA" @if($organization->state == "GA") selected @endif>Georgia</option>
                      <option value="HI" @if($organization->state == "HI") selected @endif>Hawaii</option>
                      <option value="ID" @if($organization->state == "ID") selected @endif>Idaho</option>
                      <option value="IL" @if($organization->state == "IL") selected @endif>Illinois</option>
                      <option value="IN" @if($organization->state == "IN") selected @endif>Indiana</option>
                      <option value="IA" @if($organization->state == "IA") selected @endif>Iowa</option>
                      <option value="KS" @if($organization->state == "KS") selected @endif>Kansas</option>
                      <option value="KY" @if($organization->state == "KY") selected @endif>Kentucky</option>
                      <option value="LA" @if($organization->state == "LA") selected @endif>Louisiana</option>
                      <option value="ME" @if($organization->state == "ME") selected @endif>Maine</option>
                      <option value="MD" @if($organization->state == "MD") selected @endif>Maryland</option>
                      <option value="MA" @if($organization->state == "MA") selected @endif>Massachusetts</option>
                      <option value="MI" @if($organization->state == "MI") selected @endif>Michigan</option>
                      <option value="MN" @if($organization->state == "MN") selected @endif>Minnesota</option>
                      <option value="MS" @if($organization->state == "MS") selected @endif>Mississippi</option>
                      <option value="MO" @if($organization->state == "MO") selected @endif>Missouri</option>
                      <option value="MT" @if($organization->state == "MT") selected @endif>Montana</option>
                      <option value="NE" @if($organization->state == "NE") selected @endif>Nebraska</option>
                      <option value="NV" @if($organization->state == "NV") selected @endif>Nevada</option>
                      <option value="NH" @if($organization->state == "NH") selected @endif>New Hampshire</option>
                      <option value="NJ" @if($organization->state == "NJ") selected @endif>New Jersey</option>
                      <option value="NM" @if($organization->state == "NM") selected @endif>New Mexico</option>
                      <option value="NY" @if($organization->state == "NY") selected @endif>New York</option>
                      <option value="NC" @if($organization->state == "NC") selected @endif>North Carolina</option>
                      <option value="ND" @if($organization->state == "ND") selected @endif>North Dakota</option>
                      <option value="OH" @if($organization->state == "OH") selected @endif>Ohio</option>
                      <option value="OK" @if($organization->state == "OK") selected @endif>Oklahoma</option>
                      <option value="OR" @if($organization->state == "OR") selected @endif>Oregon</option>
                      <option value="PA" @if($organization->state == "PA") selected @endif>Pennsylvania</option>
                      <option value="RI" @if($organization->state == "RI") selected @endif>Rhode Island</option>
                      <option value="SC" @if($organization->state == "SC") selected @endif>South Carolina</option>
                      <option value="SD" @if($organization->state == "SD") selected @endif>South Dakota</option>
                      <option value="TN" @if($organization->state == "TN") selected @endif>Tennessee</option>
                      <option value="TX" @if($organization->state == "TX") selected @endif>Texas</option>
                      <option value="UT" @if($organization->state == "UT") selected @endif>Utah</option>
                      <option value="VT" @if($organization->state == "VT") selected @endif>Vermont</option>
                      <option value="VA" @if($organization->state == "VA") selected @endif>Virginia</option>
                      <option value="WA" @if($organization->state == "WA") selected @endif>Washington</option>
                      <option value="WV" @if($organization->state == "WV") selected @endif>West Virginia</option>
                      <option value="WI" @if($organization->state == "WI") selected @endif>Wisconsin</option>
                      <option value="WY" @if($organization->state == "WY") selected @endif>Wyoming</option>
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
                    <input id="zipcode" type="text" class="form-control @error('zipcode') is-invalid @enderror" name="zipcode" placeholder="{{$organization->zipcode}}" value="{{ old('zipcode') }}" autocomplete="postal-code" autofocus>

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
                    <textarea id="summary" type="text" class="form-control @error('summary') is-invalid @enderror" name="summary" placeholder="{{$organization->summary}}" value="{{ old('summary') }}" autofocus rows="5" placeholder="Description of some things that makes your business special"></textarea>

                    @error('summary')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

          </div>
        </div>
        <div class="form-group row mb-0 mt-4"><!--registration submit-->
          <div class="col-md-12">
            <button type="submit" class="btn btn-primary">
              {{ __('Update') }}
            </button>
          </div>
        </div>


        </form>
      </div>
    </div>
  </div>
@endsection
