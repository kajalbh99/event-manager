<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFriend extends Model
{
    protected $table = 'user_friend';
	
	public function user() {
		return $this->hasOne("App\Models\User", "id", "user_id");
    }
	
	public function friend(){
		return $this->hasOne("App\Models\User", "id", "friend_id");
	}
}
