<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'url',
        'category', // product, saas, ctp, 'other' (specified but not one of those three)
        'payment_provider',
        'street_address',
        'city',
        'state', //state abbrev
        'zipcode',
        'summary',
    ];

    public function user()
    {
        return $this->hasOne('App\User', 'organization');
    }

    public function transactions()
    {
        return $this->hasMany('App\Transaction', 'organization_id');
    }

}
