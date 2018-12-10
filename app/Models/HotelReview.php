<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelReview extends Model
{
    protected $table = 'hotel_reviews'; 
	
	public function user() {
        return $this->hasOne("App\Models\User", "id", "user_id");
    }
	
	public function hotel() {
        return $this->hasOne("App\Models\Hotel", "id", "hotel_id");
    }
	
	public function gallery_image() {
        return $this->hasMany("App\Models\HotelReviewMedias", "hotel_reviews_id", "id");
    }
}
