<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Invoices;
class PaymentDone extends Mailable
{
    use Queueable, SerializesModels;
	public $invoice;
	public $ticket;
	public $basic_amount;
	public $amount;
	public $currency;
	public $source;
	public $sale_tax;
	
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Invoices $invoice,$ticket,$basic_amount,$amount,$currency,$source,$sale_tax)
    {
        $this->invoice = $invoice;
        $this->ticket = $ticket;
        $this->basic_amount = $basic_amount;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->source = $source;
        $this->sale_tax = $sale_tax;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
		$destinationPath = public_path('uploads/invoices/'.$this->invoice->id);
        return $this->subject('Ticket request has been accepted')
				/* ->attach($destinationPath.'/invoice.pdf') */
				->view('emails.invoices.paymentdone')->with(['ticket'=>$this->ticket,'basic_amount'=>$this->basic_amount,'amount'=>$this->amount,'currency'=>$this->currency,'source'=>$this->source,'sale_tax'=>$this->sale_tax]);
    }
}
