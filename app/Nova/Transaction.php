<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Http\Requests\NovaRequest;

class Transaction extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Transaction';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('Transaction ID', 'transaction_id')->rules('required'),
            Text::make('Transaction Status', 'transaction_status')->readonly(),
            Text::make('Transaction ID', 'transaction_type')->readonly()->hideFromIndex(),
            Currency::make('Amount', 'amount')->format('%.2n')->readonly()->hideFromIndex(),
            Select::make('Review Status', 'review_status')
              ->rules('required')
              ->options([
                  'not_started' => 'Not Started',
                  'pending' => 'Pending',
                  'completed' => 'Completed'
              ])
              ->withMeta(['value' => $this->review_status ?? 'not_started']),
            Text::make('Risk Score', 'risk_score'),
            Text::make('Risk Reason', 'risk_reason'),
            Textarea::make('Transaction Notes', 'notes')->hideFromIndex()->readonly(),
            Textarea::make('Investigation Summary', 'investigation_summary')->hideFromIndex()
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
