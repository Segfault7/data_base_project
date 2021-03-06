<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Hotel;
use App\Habitacion;
use DB;
use DateTime;

class HotelController extends Controller
{
    public function alojamientos()
    {
        return view('alojamientos');
    }

    /**
     * Display a listing of the resource.
     * Mostar los hoteles
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('registro.add_data');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $hotel = new Hotel($request->all());
        $hotel->save();
    }

    /**
     * Display the specified resource.
     *
     * @param
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        //Validacion de los datos de entrada.
        $validateData = $request->validate([
            'destino' => 'required',
            'fecha_entrada' => 'required|date', //|after:tomorrow ??
            'fecha_salida' => 'required|date|after:fecha_entrada', //|after:tomorrow ??
            'adultosh1' => 'numeric|required|min:1'
        ]);

        //Se calcula la cantidad de personas que compartiran una habitacion.
        $cantidadh1 = $request->adultosh1 + $request->menoresh1;
        $cantidadh2 = $request->adultosh2 + $request->menoresh2;
        $cantidadh3 = $request->adultosh3 + $request->menoresh3;
        $cantidad = $cantidadh1 + $cantidadh2 + $cantidadh3;
        if($cantidadh3 > 0 || $cantidadh2 > 0){
            if($cantidadh3 > 0 && $cantidadh2 == 0){
                $cantidadh2 = $cantidadh3;
                $cantidadh3 = 0;
            }
            if($cantidadh3 == $cantidadh1 && $cantidadh2 > 0){
                $aux = $cantidadh2;
                $cantidadh2 = $cantidadh3;
                $cantidadh3 = $aux;
            }
        }
        //Se calculan las noches de alojamiento de acuerdo a las fechas de entrada y salida.
        $fecha_in = new DateTime($request->fecha_entrada);
        $fecha_out = new DateTime($request->fecha_salida);
        $noches_alojamiento = $fecha_in->diff($fecha_out);
        $noches = data_get($noches_alojamiento, 'days');

        //Se obtienen todos los hoteles
        $hoteles_1 = DB::table('hotel')
          ->join('habitacion', 'habitacion.rut_hotel', '=', 'hotel.rut_hotel')
          ->where('hotel.ciudad_hotel', $request->destino)
          ->where(function ($query) use ($cantidadh1,$cantidadh2,$cantidadh3){
              $query->where('habitacion.capacidad', $cantidadh1)->orWhere('habitacion.capacidad', $cantidadh2)->orWhere('habitacion.capacidad', $cantidadh3);
          })
          ->select('hotel.*', 'habitacion.nro_habitacion', 'habitacion.capacidad', 'habitacion.precio_noche', 'habitacion.tipo')
          ->get()
          ->unique();
        //Se obtienen los hoteles que no tienen habitaciones disponibles.
        $hoteles_2 = DB::table('hotel')
          ->join('habitacion', 'hotel.rut_hotel', '=', 'habitacion.rut_hotel')
          ->where('hotel.ciudad_hotel', $request->destino)
          ->where('fecha_entrada', '<=', $request->fecha_salida)
          ->where('fecha_salida', '>=', $request->fecha_entrada)
          ->where(function ($query) use ($cantidadh1,$cantidadh2,$cantidadh3){
              $query->where('capacidad', $cantidadh1)->orWhere('capacidad', $cantidadh2)->orWhere('capacidad', $cantidadh3);
          })
          ->select('hotel.*', 'habitacion.nro_habitacion', 'habitacion.capacidad', 'habitacion.precio_noche', 'habitacion.tipo')
          ->get();
        //Se quitan de $hoteles_1 los hoteles de $hoteles_2
        $hoteles_3 = collect();
        foreach ($hoteles_1 as $hotel) {
          if($hoteles_2->contains($hotel) == false){
            $hoteles_3->push($hotel);
          }
        }

        //Quitar hoteles que no tangan todas las habitaciones necesarias disponibles
        //--Se quitan los hoteles que no tengan las dos habitaciones solicitadas (con igual capacidad)
        $hoteles = collect();
        if($cantidadh1 == $cantidadh2 && $cantidadh1 != $cantidadh3){
          $grouped = $hoteles_3->groupBy('rut_hotel');
          foreach ($grouped as $group) {
            if($group->count() >= 2){
              $hoteles->push($group);
            }
          }
        }
        //--Se quitan los hoteles que no tengan las tres habitaciones soliitadas (con igual capacidad)
        else if($cantidadh1 == $cantidadh2 && $cantidadh1 == $cantidadh3){
          $grouped = $hoteles_3->groupBy('rut_hotel');
          foreach ($grouped as $group) {
            if($group->count() >= 3){
              $hoteles->push($group);
            }
          }
        }
        //--Se quitan los hoteles que no tengan las tres habitaciones solicitadas (con distinta capacidad)
        else{
          $grouped = $hoteles_3->groupBy('rut_hotel');
          foreach ($grouped as $group) {
            if($group->count() >= 3){
              $hoteles->push($group);
            }
          }
        }
        dd($hoteles);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

/*
//Se realizan las consulta a la base de datos de acuerdo a lo ingresado por el usuario. Obteniendo los hoteles que coincidan con el destino, fechas y habitaciones.
//Consulta para la habitacion 1
$consulta_hoteles_h1 = DB::table('hotel')->join('habitacion', 'habitacion.rut_hotel', '=', 'hotel.rut_hotel')
    ->where('hotel.ciudad_hotel', $request->destino)
    ->where('habitacion.capacidad', $cantidadh1)
    ->where(function ($query) use ($request){
        $query->where('habitacion.fecha_entrada', '>', $request->fecha_salida)->orWhere('habitacion.fecha_salida', '<', $request->fecha_entrada);
    })
    ->orderBy('hotel.precio_minimo', 'ASC')
    ->get();
    $hotelesh1 = $consulta_hoteles_h1->unique('nombre'); //Coleccion hoteles h1
//Estan disponibles la cantidad de habitaciones en un mismo hotel.
$hoteles_h1 = collect();
if($cantidadh1 == $cantidadh2){
    foreach ($hotelesh1 as $hotel) {
        # code...
        $cant = 0;
        foreach ($consulta_hoteles_h1 as $ht) {
            # code...
            $comp = strcmp($hotel->nombre, $ht->nombre);
            if($comp == 0){
                $cant++;
            }
        }
        if(($cantidadh3 != $cantidadh2 && $cant >= 2) || ($cantidadh3 == $cantidadh2 && $cant >= 3)){
            $hoteles_h1->push($hotel);
        }
    }
}
//Consulta para la habitacion 2
if($cantidadh2 > 0 && $cantidadh2 != $cantidadh1){
    $consulta_hoteles_h2 = DB::table('hotel')->join('habitacion', 'habitacion.rut_hotel', '=', 'hotel.rut_hotel')
    ->where('hotel.ciudad_hotel', $request->destino)
    ->where('habitacion.capacidad', $cantidadh2)
    ->where(function ($query) use ($request, $cantidadh2){
        $query->where('habitacion.fecha_entrada', '>', $request->fecha_salida)->orWhere('habitacion.fecha_salida', '<', $request->fecha_entrada);
    })
    ->orderBy('hotel.precio_minimo', 'ASC')
    ->get();
    $hoteles_h2 = $consulta_hoteles_h2->unique('nombre'); //Coleccion hoteles h2
    //Se crea una coleccion con los hoteles que tienen las habitaciones disponibles.
    $hoteles = collect();
    foreach ($hoteles_h1 as $hotel_h1) {
        foreach ($hoteles_h2 as $hotel_h2) {
            if($hotel_h2->nombre == $hotel_h1->nombre){
                $hoteles->push($hotel_h1);
            }
        }
    }
}
//Consulta para la habitacion 3
if($cantidadh3 > 0 && $cantidadh3 != $cantidadh2){
    $consulta_hoteles_h3 = DB::table('hotel')->join('habitacion', 'habitacion.rut_hotel', '=', 'hotel.rut_hotel')
    ->where('hotel.ciudad_hotel', $request->destino)
    ->where('habitacion.capacidad', $cantidadh3)
    ->where(function ($query) use ($request){
        $query->where('habitacion.fecha_entrada', '>', $request->fecha_salida)->orWhere('habitacion.fecha_salida', '<', $request->fecha_entrada);
    })
    ->orderBy('hotel.precio_minimo', 'ASC')
    ->get();
    $hoteles_h3 = $consulta_hoteles_h3->unique('nombre'); //Coleccion hoteles h3
    $hoteles_aux = collect();
    foreach ($hoteles as $hotel) {
        foreach ($hoteles_h3 as $hotel_h3) {
            if($hotel->nombre == $hotel_h3->nombre){
                $hoteles_aux->push($hotel_h3);
            }
        }
    }
    $hoteles = $hoteles_aux;
}

$info = array('noches' => $noches, 'capa1' => $cantidadh1, 'capa2' => $cantidadh2, 'capa3' => $cantidadh3);
//Se llama a la vista que mostrara los hoteles al usuario.
if(($cantidadh1 > 0 && $cantidadh2 == 0 && $cantidadh3 == 0) || ($cantidadh1 == $cantidadh2)){
    dd($hotelesh1);
    //return view('seleccion.hoteles')->with('hoteles', $hotelesh1)->with('info', $info);
}
else if($cantidadh1 == $cantidadh2){
  //return view('seleccion.hoteles')->with('hoteles', $hoteles_h1)->with('info', $info);
}
else{
  dd($hoteles);
    //return view('seleccion.hoteles')->with('hoteles', $hoteles)->with('info', $info);
}

*/
