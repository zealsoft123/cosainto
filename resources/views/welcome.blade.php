@extends('layouts.app')

@section('nav')
@endsection

@section('content')
  <div class="container-fluid home-hero">
    <nav class="navbar navbar-dark navbar-expand-md">
        <div class="container-fluid">
            <a class="navbar-brand text-white font-weight-bold" href="{{ url('/') }}">
                {{ config('app.name', 'Laravel') }}
                <small class="font-italic tagline text-white">Risk Operations Platform</small>
            </a>
            <br>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Left Side Of Navbar -->
                <ul class="navbar-nav mr-auto">

                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="navbar-nav ml-auto">
                    <!-- Authentication Links -->
                    @guest
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ route('login') }}">{{ __('Login') }}</a>
                        </li>
                        @if (Route::has('register'))
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('register') }}">{{ __('Register') }}</a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle text-white" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ Auth::user()->name }} <span class="caret"></span>
                            </a>

                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="/dashboard">
                                    {{ __('Dashboard') }}
                                </a>

                                <a class="dropdown-item" href="/profile">
                                    {{ __('Profile') }}
                                </a>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                                 document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>
    <div class="container">

      <section class="row mt-5">
      <div class="col-md-6">
        <h2>Your own scalable risk operations team</h2>
        <ul>
          <li>Identify credit fraud and compliance risk for free</li>
          <li>Initiate investageions management solution</li>
          <li>Improve operational efficiencies</li>
        </ul>
        <a href="/register" class="btn btn-dark">Try it for Free</a>
      </div>
      <div class="col-md-6">
        <img src="{{asset('images/screenshot.png')}}" alt="" class="img-fluid">
      </div>
    </section>
    </div>
  </div>
    <div class="container mt-5 pt-3">

    <section class="row mt-5 justify-content-center">
      <div class="col-8">
        <header>
          <h2>Improve operational efficiency</h2>
          <p>Our experienced risk mitigation staff will handle your investigations</p>
        </header>
        <ol>
          <li>Upload transactions to be scored by our risk mitigation engine</li>
          <li>Review tagged transactions and mark for follow up</li>
          <li>Request our experienced team to run investigations on your behalf</li>
        </ol>
        <a href="/register" class="btn btn-primary">Try it for Free</a>
      </div>
      <div class="col-4">
        <img src="{{ asset('images/risk_analysis.svg') }}" class="img-fluid" style="height:100%" alt="">
      </div>
    </section>
    <section class="row mt-5 py-5 mb-5 justify-content-center">
      <div class="col-3">
        <img src="{{ asset('images/bomb.svg') }}" class="img-fluid" alt="">
      </div>
      <div class="col-9">
        <header>
          <h2>Spot and rank risk before it explodes</h2>
          <p>Our scoring engine weights risk on a tried and true scale to call out and rank transactions</p>
          <a href="/register" class="btn btn-primary">Try it for Free</a>
        </header>
      </div>
    </section>
  </div>
@endsection
