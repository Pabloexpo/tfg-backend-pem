<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Mensaje;
use Illuminate\Support\Facades\DB;

class MensajeController extends Controller {

    //
    public function store(Request $request) {
        $validacion = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'mensaje' => 'required|string'
        ]);

        //Al validar. creamos el msj en la bbdd
        $mensaje = new Mensaje;
        $mensaje->nombre= $request->nombre; 
        $mensaje->email= $request->email; 
        $mensaje->mensaje= $request->mensaje; 
        $mensaje->respuesta= null; 
        $mensaje->save();

        //retornamos respuesta de msj exitoso
        return response()->json([
                    'success' => true,
                    'message' => 'Mensaje enviado correctamente',
                    'data' => $mensaje
                        ], 201);
    }

    public function getMensajes() {
        $mensajes = DB::select("SELECT * FROM mensajes WHERE respuesta = ''");

        return response()->json([
                    "mensaje" => "Mensajes de consumidores recuperados con éxito",
                    "mensajes" => $mensajes
                        ], 200);
    }

    public function respondeMensaje(Request $request, $mensaje_id) {
        $mensaje = Mensaje::find($mensaje_id);

        $mensaje->respuesta = $request->respuesta;
        $mensaje->save();

        return response()->json([
                    "mensaje" => "Respuesta realizada con éxito",
                    "mensaje" => $mensaje
        ]);
    }
}
