<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'organization_id',
    'transaction_id',
    'transaction_status',
    'transaction_type',
    'amount',
    'card_number',
    'expiration_date',
    'billing_name',
    'billing_address',
    'billing_city',
    'billing_zipcode',
    'billing_state',
    'billing_country',
    'shipping_name',
    'shipping_address',
    'shipping_city',
    'shipping_zipcode',
    'shipping_state',
    'shipping_country',
    'risk_score',
    'risk_reason',
    'investigation_summary',
    'notes',
    'transaction_date',
  ];

  /**
   * The attributes that are dates
   *
   * @var array
   */
  protected $dates = ['transaction_date'];
  
  public function organization() {
    return $this->belongsTo('App\Organization');
  }
}
