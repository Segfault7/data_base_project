<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Habitacion extends Model
{
    protected $table = "habitacion";
    //campos permitidos.
    protected $fillable = ['nro_habitacion', 'rut_hotel', 'precio_noche', 'fecha_entrada', 'fecha_salida', 'capacidad'];
    //protected $fillable = ['nro_habitacion', 'rut_hotel', 'precio_noche', 'capacidad', 'tipo'];

    //Relacion muchos a uno con Hotel.
    public function hotel(){
      return $this->belongsTo('App\Hotel');
    }

 	public function reserva(){
		return $this->belongsToMany('App\Reserva');
	}

	public function paquete(){
		return $this->belongsToMany('App\Paquete');
	}
}
