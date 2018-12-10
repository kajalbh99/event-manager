<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    public function review() {
        return $this->hasMany("App\Models\HotelReview", "hotel_id", "id")->where('is_approved','=',1);
    }
	
	public function gallery() {
        return $this->hasMany("App\Models\HotelGallery", "hotel_id", "id");
    }


	
}
