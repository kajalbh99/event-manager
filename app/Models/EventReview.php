<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\EventReviewMedias;

class EventReview extends Model
{
    protected $table = 'event_reviews'; 
	
	public function user() {
        return $this->hasOne("App\Models\User", "id", "user_id");
    }
	
	public function event() {
        return $this->hasOne("App\Models\Event", "id", "event_id");
    }
	
	public function gallery_image() {
        return $this->hasMany("App\Models\EventReviewMedias", "event_reviews_id", "id");
    }
}
