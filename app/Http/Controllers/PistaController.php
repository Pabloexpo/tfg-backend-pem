<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pista;
use App\Models\Reserva;
use Illuminate\Support\Facades\DB;

class PistaController extends Controller {

    //Vamos a realizar la lógica para crear apis, para ello tendremos que valernos de los crud tradicionales
    //, en vez de devolver vistas, devolveremos json
    //obtenemos todas las pistas
    public function index() {
        $pistas = Pista::all();
        return response()->json($pistas);
    }

    //haremos una función para obtener una pista por id
    public function show($id) {
        $pista = Pista::find($id);
        if ($pista) {
            return response()->json($pista);  //Mandamos la pista por json
        } else {
            //si no encontramos nada, ,mandamos un msj con el resultado no encontrado y el status
            return response()->json(['message' => 'Pista no encontrada'], 400);
        }
    }

    // Crear una nueva pista
    public function store(Request $request) {
        $request->validate([
            'pista_nombre' => 'required|string|max:255',
            'pista_localidad' => 'nullable|string|max:255',
            'pista_cp' => 'nullable|string|max:20',
            'pista_foto' => 'nullable|url',
            'pista_descripcion' => 'nullable|string',
            'pista_telefono' => 'nullable|string',
        ]);

        // Creamos la pista con los datos 
        $pista = Pista::create($request->all());

        return response()->json($pista, 201); // Devolvemos con 201 que nos indica que está creada la pista
    }

    public function compruebaDisponibilidad(Request $request) {
        $validacion = $request->validate([
            'fecha' => 'required|date',
            'hora' => 'required|date_format:H:i',
            'pista' => 'required|integer'
        ]);
        //Combinamos la fecha y la hora para poder realizar la consulta 
        $fechaHora = $request->input('fecha') . ' ' . $request->input('hora');

        $disponiblidad = DB::select("SELECT * FROM reservas WHERE reserva_fecha=? AND reserva_pista = ?;", [$fechaHora, $request->pista]);
        if (!empty($disponiblidad)) {
            return response()->json([
                        'disponiblidad' => false,
                        'mensaje' => 'La pista no está disponible a esa hora'
            ]);
        } else {
            return response()->json([
                        'disponiblidad' => true,
                        'mensaje' => 'La pista está disponible a esa hora'
            ]);
        }
    }

    //Realizamos un endpoint para reservar la pista 
    public function reservarPista(Request $request) {
        $validacion = $request->validate([
            'fecha' => 'required|date',
            'hora' => 'required|date_format:H:i',
            'pista' => 'required|integer',
            'equipo_1' => 'required|integer',
            'equipo_2' => 'required|integer'
        ]);
        $fechaHora = $request->input('fecha') . ' ' . $request->input('hora');

        $reserva = new Reserva;

        $reserva->reserva_pista = $request->pista;
        $reserva->reserva_equipo1 = $request->equipo_1;
        $reserva->reserva_equipo2 = $request->equipo_2;
        $reserva->reserva_fecha = $fechaHora;
        //en la tabla saldrá 0 si es false o 1 si es true
        $reserva->reserva_finalizada = false;
        $reserva->save();

        return response()->json([
                    "mensaje" => "Reserva realizada con éxito",
                    "reserva" => $reserva
        ]);
    }

    function getPistaMasUtilizada() {
        //función para recuperar la pista mas utilizada 
        $pista = DB::select("SELECT partido_pista AS pista, COUNT(*) as veces_usada, pi.pista_nombre
    FROM partidos p
    JOIN pistas pi on pi.pista_id=p.partido_pista
    GROUP BY partido_pista, pi.pista_nombre
    ORDER BY veces_usada DESC
    LIMIT 1;");

        return response()->json($pista);
    }

    //MÉTODO RECOMENDADO POR RAUL SERRANO DE MOSTRAR ANTES DE CLICAR EN MOSTRAR DISPONIBLIDAD
    //DE PISTA, SI LA PISTA ESTÁ LIBRE O NO
    public function reservasPorPista($pista_id) {
        $reservas = Reserva::where('reserva_pista', $pista_id)
                ->whereDate('reserva_fecha', '>=', now())
                ->where('reserva_finalizada', 0)
                ->get(['reserva_fecha']);

        return response()->json([
                    'reservas' => $reservas
        ]);
    }
}
