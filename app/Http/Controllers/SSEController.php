<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class SSEController extends Controller
{
    public function listen($channel)
    {
        // // Configurar los encabezados para la transmisión SSE
        // header("Content-Type: text/event-stream");
        // header("Cache-Control: no-cache");
        // header("Connection: keep-alive");
        // // header("Access-Control-Allow-Origin: *");  // Permitir cualquier origen
        // header("Access-Control-Allow-Methods: GET");  // Solo permitimos el método GET para SSE
        // // header("Access-Control-Allow-Headers: *");  // Permitir todos los encabezados

        // // Ruta al archivo donde se guarda el mensaje
        // // $path = public_path("GPCenter/events/{$channel}_message.txt");
        // // $filePath = storage_path($path);


        // $notifications = Notification::all();


        // // Verificar si el archivo existe
        // // if (file_exists($filePath)) {
        // //     // Leer el contenido del archivo
        // //     $message = file_get_contents($filePath);

        // // Enviar el mensaje como un evento SSE
        // echo "event: message\n";
        // echo "data: " . json_encode(['message' => $notifications]) . "\n\n";
        // // } else {
        // // Si el archivo no existe, enviar un mensaje predeterminado
        // //     echo "\n\n";
        // // }

        // // Forzar que el contenido se envíe al cliente
        // ob_flush();
        // flush();

        // // Opcional: Si deseas mantener la conexión abierta y enviar más mensajes más tarde,
        // // puedes descomentar sleep() para simular un retraso en el servidor.
        // sleep(30);  // Simular un retraso para la recepción del cliente.
    }

    public function disparar(Request $request)
    {
        // Validar que el cliente envíe el canal y el mensaje
        $validated = $request->validate([
            'channel' => 'required|string',
            'message' => 'required|string',
        ]);

        // Escribir el mensaje en un archivo temporal
        $filePath = storage_path("app/{$validated['channel']}_message.txt");
        file_put_contents($filePath, $validated['message']);  // Guardar el mensaje en el archivo

        // Responder que el evento ha sido disparado correctamente
        return response()->json(['message' => 'Evento disparado correctamente']);
    }
}