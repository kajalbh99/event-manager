<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommitteeMember extends Model
{
    protected $table = 'commitee_members';
	
	public function user() {
		return $this->hasOne("App\Models\User", "id", "member_id");
	}
}
