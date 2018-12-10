<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketTypePdfs extends Model
{
    protected $table = "ticket_type_pdfs";
	
	public function ticket_type(){
		return $this->hasOne('App\Models\EventTicketType','id','ticket_type_id');
	}
}
