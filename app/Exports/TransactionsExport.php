<?php

namespace App\Exports;

use Auth;

use App\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransactionsExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $transactions = Transaction::where('organization_id', Auth::User()->organization)->get();
    }

    public function headings(): array {
    	return [
			'Transaction ID',
			'Cosainto Created Date',
			'Cosainto Updated Date',
			'Organization ID',
			'Transaction ID',
			'Transaction Status',
			'Transaction Type',
			'Amount',
			'Last 4 digits of Card Number',
			'Expiration Date',
			'Billing Name',
			'Billing Address',
			'Billing City',
			'Billing State',
			'Billing Zipcode',
			'Billing Country',
			'Shipping Name',
			'Shipping Address',
			'Shipping City',
			'Shipping State',
			'Shipping Zipcode',
			'Shipping Country',
			'Risk Score',
			'Risk Reason',
			'Investigation Summary',
			'Notes',
			'Transaction Date'
    	];
    }
}
