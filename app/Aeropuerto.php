<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Aeropuerto extends Model
{
    //
    protected $table = "aeropuerto";

    public function vuelo(){
		return $this->belongsToMany('App\Vuelo');
	}
}
