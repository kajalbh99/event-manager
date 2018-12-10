<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportationReview extends Model
{
    protected $table = 'transportation_reviews'; 
	
	public function user() {
        return $this->hasOne("App\Models\User", "id", "user_id");
    }
	
	public function transportation() {
        return $this->hasOne("App\Models\Transportation", "id", "transportation_id");
    }
	
	public function gallery_image() {
        return $this->hasMany("App\Models\TransportationReviewMedias", "transportation_reviews_id", "id");
    }
}
