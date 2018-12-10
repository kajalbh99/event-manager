<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllocatePdfs extends Model
{
    protected $table = "alocate_pdf_to_users";
	
	public function pdfFile(){
		return $this->hasOne('App\Models\TicketTypePdfs','id','pdf_id');
	}
	
	public function user(){
		return $this->hasOne('App\Models\User','id','user_id');
	}
	
	public function ticket(){
		return $this->hasOne('App\Models\Ticket','id','ticket_id');
	}
	
	public function ticket_type(){
		return $this->hasOne('App\Models\EventTicketType','id','ticket_type_id');
	}
}
