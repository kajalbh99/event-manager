<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guard extends Model
{
	use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'guards';
	protected $softDelete = true;
	public static function check_user_exits($user_name){
	   $user_details		=	Guard::where('user_name', trim($user_name))->first();
	   return $user_details;
	}
	
	
}
