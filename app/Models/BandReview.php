<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BandReview extends Model
{
    protected $table = 'band_reviews';
	
	public function user() {
        return $this->hasOne("App\Models\User", "id", "user_id"); 
    }
	
	public function band() {
        return $this->hasOne("App\Models\Band", "id", "band_id");
    }
	
	public function gallery_image() {
        return $this->hasMany("App\Models\BandReviewMedias", "band_reviews_id", "id");
    }
}
