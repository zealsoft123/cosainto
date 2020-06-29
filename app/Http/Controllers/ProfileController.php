<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfile;
use App\Organization;
use Auth;
use Illuminate\Support\Facades\Hash;


class ProfileController extends Controller
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

    public function show()
    {
        $user = Auth::User();
        $organization = Organization::findOrFail($user->organization);
        return view('profile', ['user' => $user, 'organization' => $organization]);
    }

    public function update(UpdateProfile $request)
    {
        $user = Auth::User();
        $organization = Organization::findOrFail($user->organization);


        if ($request->input('name')) {
            $user->name = request('name');
        }
        if ($request->input('position')) {
            $user->position = request('position');
        }
        if ($request->input('phone_number')) {
            $user->phone_number = request('phone_number');
        }
        if ($request->input('email')) {
            $user->email = request('email');
        }
        if ($request->input('new-password')) {
            $user->password = Hash::make(request('new-password'));
        }

        if ($request->input('business-name')) {
            $organization->name = request('business-name');
        }
        if ($request->input('url')) {
            $organization->url = request('url');
        }
        if ($request->input('category') != $organization->category) {
            $organization->category = request('category');
        }
        if ($request->input('payment-provider') != $organization->payment_provider) {
            $organization->payment_provider = request('payment-provider');
        }
        if ($request->input('street_address')) {
            $organization->street_address = request('street_address');
        }
        if ($request->input('city')) {
            $organization->city = request('city');
        }
        if ($request->input('state') != $organization->state) {
            $organization->state = request('state');
        }
        if ($request->input('zipcode')) {
            $organization->zipcode = request('zipcode');
        }
        if ($request->input('summary')) {
            $organization->summary = request('summary');
        }

        $user->save();
        $organization->save();

        return view('profile', ['user' => $user, 'organization' => $organization]);

    }
}
