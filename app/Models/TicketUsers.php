<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketUsers extends Model
{
	protected $fillable = ['name', 'age', 'gender', 'ticket_id', 'created_at', 'updated_at'];
    protected $table = 'ticket_users';
}
