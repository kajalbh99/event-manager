<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoices;
class InvoiceController extends Controller
{
    public function show(Request $req,$id)
	{
		$invoice = Invoices::findOrFail($id);
		if($invoice)
		{
			$payment_response = json_decode($invoice->ticket->payment_response);
			$charged_amount = number_format(($payment_response->amount /100), 2, '.', ' ');
			return view('invoice.detail')->with(['invoice'=>$invoice,'charged_amount'=>$charged_amount]);
		}
	}
}
