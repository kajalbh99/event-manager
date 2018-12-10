<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BandCarnival extends Model
{
    protected $table ='band_carnivals';
	
	public function band() {
        return $this->hasOne("App\Models\Band", "id", "band_id");
    }
}
