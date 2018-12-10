<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Band extends Model
{
    protected $table = 'bands';
	
	public function band_gallery() {
        return $this->hasMany("App\Models\BandsGallery", "band_id", "id");
    }
}
