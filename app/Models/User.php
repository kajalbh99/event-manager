<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
	use Authenticatable, Authorizable, CanResetPassword;
    protected $table = 'users';
	
	protected $attributes = [
        'mobile' => '',
        
    ];
	
	public static function check_user_exits($email){
	   $user_details		=	User::where('email', trim($email))->first();
	   return $user_details;
	}
	
	public static function check_user_exits_by_username($username){
	   $user_details		=	User::where('user_name', trim($username))->first();
	   return $user_details;
	}
	
	public static function check_user_exits_with_email_username($email,$username){
	   $user_details		=	User::where('email', trim($email))->orWhere('user_name',trim($username))->first();
	   return $user_details;
	}
	
	public static function login_check_user_exits($email){
	   $user_details		=	User::where('email', trim($email))->orWhere('user_name',trim($email))->first();
	   return $user_details;
	}
	
	public function state() {
        return $this->hasOne("App\Models\State", "id", "state_id");
    }
	
	
	public function country() {
        return $this->hasOne("App\Models\Country", "id", "country_id");
    }
	
	public static function user_friends($id)
	{
		
		return DB::select("SELECT * FROM users where id IN(
							 select  (
							 CASE 
							 WHEN friend_id='".$id."' and is_friend = 1 THEN user_id 
							 WHEN user_id='".$id."' and is_friend = 1 THEN friend_id 
							 
							 END ) as uid from user_friend
						)");
	}
	
	public function commitee_members_events() {
		return $this->belongsToMany('App\Models\Event','commitee_members','member_id','event_id');
    }
	public function gallery()
    {
        return $this->hasMany('App\Models\UserPhotoGallery');
    }
	public function user_events_tickets() {
		return $this->belongsToMany('App\Models\Event','commitee_members','member_id','event_id');
    }
	public static function ajax_user_friends($id)
	{
		$user_friend_array = [];
		$user_friend = DB::select("select  (
							 CASE 
							 WHEN friend_id='".$id."'THEN user_id 
							 WHEN user_id='".$id."'THEN friend_id 
							 
							 END ) as uid from user_friend where is_friend='1'");
		foreach($user_friend as $k=>$v)
		{
			array_push($user_friend_array,$v->uid);
		}
		return $users = DB::table("users")->whereIn('id',$user_friend_array)->get(); 
		
						
	}
	
	public function events() {
        return $this->hasMany("App\Models\Event");
    }
	
	public function guards() {
        return $this->hasMany("App\Models\Guard","promoter_id");
    }
	
	public function account() {
        return $this->hasOne("App\Models\UserAccount", "user_id", "id");
    }
	
}
