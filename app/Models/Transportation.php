<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transportation extends Model
{
	 protected $table = 'transportation'; 
    public function review() {
        return $this->hasMany("App\Models\TransportationReview", "transportation_id", "id")->where('is_approved','=',1);
    }
	
	public function gallery() {
        return $this->hasMany("App\Models\TransportationGallery", "transportation_id", "id");
    }


	
}
