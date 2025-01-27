<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use App\Models\Vehicle;
use App\Models\VehicleMovementLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VehicleMovementLogController extends Controller
{
    /**
     * Mostrar lista de movimientos del vehiculo.
     *
     * @return \Illuminate\Http\Response $response
     */
    public function index(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $auth = Auth::user();
            $list = VehicleMovementLog::orderBy('id', 'desc');
            if ($auth->role_id > 1) $list = $list->where("active", true);
            $list = $list->get();

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Peticion satisfactoria | Lista de movimientos del vehiculo.';
            $response->data["result"] = $list;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }


    /**
     * Mostrar historial de movimientos del vehículo.
     *
     * @param   int $id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function history(Response $response, Int $vehicle_id, bool $internal = false)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $result = DB::select('CALL sp_vehicle_history(?)', [$vehicle_id]);
            if ($internal) return $result;

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | historial del vehículo encontrado.';
            $response->data["result"] = $result;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }


    /**
     * crear movimiento.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function registerMovement(int $vehicle_id, bool $need_approved, string $table_assoc, int $table_assoc_register_id)
    {
        try {
            $userAuth = Auth::user();
            $vehicle = Vehicle::find($vehicle_id);

            $vehicle_movement = new VehicleMovementLog();
            $vehicle_movement->user_id = $userAuth->id;
            $vehicle_movement->vehicle_id = $vehicle->id;
            $vehicle_movement->old_vehicle_status_id = $vehicle->vehicle_status_id;
            // $vehicle_movement->need_approved = $need_approved;Entregar
            $vehicle_movement->table_assoc = $table_assoc;
            $vehicle_movement->table_assoc_register_id = $table_assoc_register_id;
            // var_dump($vehicle_movement);
            $vehicle_movement->save();
            return 1;
        } catch (\Exception $ex) {
            // Registra el error en los logs
            error_log($ex->getMessage());

            return [
                'success' => false,
                'message' => $ex->getMessage(),
            ];
        }
    }
    /**
     * obtener ultimo movimiento.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Illuminate\Http\int $vehicle_id - indica el id del vehiculo a rastrear
     * @param  \Illuminate\Http\int $position - indica la posicion que se desea obtener (1, el ultimo, 2 el penultimo...)
     * @return \Illuminate\Http\Response $response
     */
    public function getLastMovementByVehicle(int $vehicle_id, int $position = 1)
    {
        try {
            // Query base para obtener los movimientos activos del vehículo
            $vehicle_movements = VehicleMovementLog::where('vehicle_id', $vehicle_id)
                ->where('active', 1)
                ->orderBy('id', 'desc');

            if ($position == 1) {
                // Si se pide el último, usa `first` para optimización
                return $vehicle_movements->first();
            } else {
                // Obtén una lista de movimientos hasta la posición deseada
                $list = $vehicle_movements->limit($position)->get();

                // Verifica si la posición solicitada existe en la lista
                if ($list->count() >= $position) {
                    return $list[$position - 1]; // El índice es posición - 1 (arreglo basado en 0)
                } else {
                    // Si no hay suficientes movimientos
                    return null;
                }
            }
        } catch (\Exception $ex) {
            error_log($ex->getMessage());
            return 0;
        }
    }
}