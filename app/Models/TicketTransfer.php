<?php

namespace App\Models;
use Carbon\Carbon;
use DB;

use Illuminate\Database\Eloquent\Model;

class TicketTransfer extends Model
{
   protected $table = 'ticket_transfers';
   protected $fillable = ['ticket_id', 'sender_id', 'receiver_id'];
   
   public function receiver() {
		return $this->hasOne("App\Models\User", "id", "receiver_id");
   }
   
	
}
