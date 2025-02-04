<?php

namespace App\Http\Controllers;

use App\Models\DirectorView;
use App\Models\ObjResponse;
use App\Models\Vehicle;
use App\Models\VehicleMovementLog;
use App\Models\VehicleStatus;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            $auth = Auth::user();
            $list = VehicleMovementLog::where('vehicle_id', $vehicle_id)->orderBy('id', 'desc');
            if ($auth->role_id > 1) $list = $list->where("active", true);
            $list = $list->get();
            // $result = DB::select('CALL sp_vehicle_history(?)', [$vehicle_id]);
            // if ($internal) return $result;

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | historial del vehículo encontrado.';
            $response->data["result"] = $result;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    public function validationsToAssign(Response $response, Int $vehicle_id, Int $user_id)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            #VERIFICAR QUE EL VEHICULO NO ESTE ASIGNADO
            // Log::info("validationsToAssign ~ vehicle_id: $vehicle_id, user_id: $user_id");
            $lastMovement = $this->getLastMovementByVehicle($vehicle_id);
            // Log::info("validationsToAssign ~ lastMovement: " . json_encode($lastMovement));

            if ($lastMovement) {
                if (in_array($lastMovement->vehicle_status_id, [1, 2])) {
                    $response->data["message"] = 'peticion satisfactoria | asignacion no concluida.';
                    $response->data["alert_icon"] = "warning";
                    $response->data["alert_text"] = "Asignación no completada - El vehículo ya está asignado";
                    return response()->json($response, $response->data["status_code"]);
                    // return "no hay asignaciones a este vehiculo";
                }
            }
            $vehicle = Vehicle::find($vehicle_id);
            $director = DirectorView::where("user_id", $user_id)->first();
            #VERIFICAR QUE SU LICENCIA NO ESTE VENCIDA
            if (!$this->validateLicenseActive($response, $director)) return;
            #VERIFICAR QUE CONCIDAN EL TIPO DE LICENCIAS
            if (!$this->validateLicenseType($response, $vehicle, $director)) return;
            #VERIFICAR QUE SE ENCUENTRE EN EL STATUS CORRECTO
            if (!$this->validateCorrectStatus($response, $vehicle)) return;
        } catch (Exception $ex) {
            $msg = "validationsToAssign ~ Hubo un error al validar el movimiento -> " . $ex->getMessage();
            error_log($msg);
            Log::error($msg);
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return $response;
    }
    private function validateLicenseActive(Response $response, $director)
    {
        try {
            if ($director->license_due_date != "") {

                $today = new DateTime();
                $license_due_date = new DateTime($director->license_due_date);

                if ($today > $license_due_date) {
                    $response->data["message"] = 'peticion satisfactoria | licencia vencida.';
                    $response->data["alert_icon"] = "warning";
                    $response->data["alert_text"] = "Asignación no completada - El director tiene la licencia vencida.";
                    return response()->json($response, $response->data["status_code"]);
                }
            } else {
                $response->data["message"] = 'peticion satisfactoria | licencia vencida.';
                $response->data["alert_icon"] = "warning";
                $response->data["alert_text"] = "Asignación no completada - El director no tiene registrada la fecha de vencimiento de su licencia.";
                return response()->json($response, $response->data["status_code"]);
            }
        } catch (Exception $ex) {
            $msg = "validateLicenseActive ~ Hubo un error al validar el movimiento -> " . $ex->getMessage();
            error_log($msg);
            Log::error($msg);
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return true;
    }
    private function validateLicenseType(Response $response, $vehicle, $director)
    {
        try {
            if ($vehicle->acceptable_license_type != "") {
                $acceptable_license_type = explode(",", $vehicle->acceptable_license_type);
                // return $director;
                if (!in_array($director->license_type, $acceptable_license_type)) {
                    $response->data["message"] = 'peticion satisfactoria | tipo de licencia no valida.';
                    $response->data["alert_icon"] = "warning";
                    $response->data["alert_text"] = "Asignación no completada - Tipo de licencia no valida para esta unidad.";
                    return response()->json($response, $response->data["status_code"]);
                }
            } else {
                $response->data["message"] = 'peticion satisfactoria | tipo de licencia no valida.';
                $response->data["alert_icon"] = "warning";
                $response->data["alert_text"] = "Asignación no completada - El vehículo no tiene tipos de licencias asignados.";
                return response()->json($response, $response->data["status_code"]);
            }
        } catch (Exception $ex) {
            $msg = "validateLicenseType ~ Hubo un error al validar el movimiento -> " . $ex->getMessage();
            error_log($msg);
            Log::error($msg);
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return true;
    }
    private function validateCorrectStatus(Response $response, $vehicle)
    {
        try {
            // $response->data = ObjResponse::CorrectResponse();
            if ($vehicle->vehicle_status_id === 3) {
                $response->data["message"] = 'peticion satisfactoria | vehiculo ya asignado.';
                $response->data["alert_icon"] = "warning";
                $response->data["alert_text"] = "Asignación no completada - El vehículo ya esta asignado";
                return response()->json($response, $response->data["status_code"]);
            }
            if ($vehicle->vehicle_status_id === 5) {
                $response->data["message"] = 'peticion satisfactoria | vehiculo en taller.';
                $response->data["alert_icon"] = "warning";
                $response->data["alert_text"] = "Asignación no completada - El vehículo se encuentra en el taller";
                return response()->json($response, $response->data["status_code"]);
            }
        } catch (Exception $ex) {
            $msg = "validateCorrectStatus ~ Hubo un error al validar el movimiento -> " . $ex->getMessage();
            error_log($msg);
            Log::error($msg);
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return true;
    }


    /**
     * crear movimiento.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function registerMovement(Request $request, Response $response, int $vehicle_status_id, int $vehicle_id)
    {
        try {
            $userAuth = Auth::user();

            #VALIDACIONES
            $validate = $this->validationsToAssign($response, $vehicle_id, $request->active_user_id);
            // $this->validationsToAssign($vehicle_id, $userAuth->id);

            if ($validate->data["alert_icon"] == "warning") {
                Log::info("VALIDATE: " . json_encode($validate));
                return response()->json($validate, $validate->data["status_code"]);
            }

            DB::beginTransaction();
            #ACTUALIZAR STATUS DEL VEHICULO
            $vehicleInstance = new VehicleController();
            $vehicleInstance->updateStatus($vehicle_id, $vehicle_status_id); //Asignado



            // $vehicle = Vehicle::find($vehicle_id);
            $status = VehicleStatus::find($vehicle_status_id);

            $vehicle_movement = new VehicleMovementLog();
            $vehicle_movement->user_id = $userAuth->id;
            $vehicle_movement->vehicle_id = $vehicle_id;
            $vehicle_movement->vehicle_status_id = $vehicle_status_id;
            // $vehicle_movement->need_approved = $need_approved;Entregar
            $vehicle_movement->active_user_id = $request->active_user_id;
            $vehicle_movement->km = $request->km;
            $vehicle_movement->comments = $request->comments;
            $vehicle_movement->table_assoc = $request->table_assoc;
            $vehicle_movement->table_assoc_register_id = $request->table_assoc_register_id;
            // var_dump($vehicle_movement);
            $vehicle_movement->save();
            DB::commit();

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = "peticion satisfactoria | $status->vehicle_status de vehiculo registrada.";
            $response->data["alert_text"] = "Estatus en vehículo: $status->vehicle_status";
            // $response->data["message"] = $id > 0 ? "peticion satisfactoria | $status->vehicle_status de vehiculo editada." : "peticion satisfactoria | $status->vehicle_status de vehiculo registrada.";
            // $response->data["alert_text"] = $id > 0 ? "Estatus en vehículo: $status->vehicle_status editada" : "Estatus en vehículo: $status->vehicle_status registrada";
        } catch (\Exception $ex) {
            DB::rollBack();
            $msg = "registerMovement ~ Hubo un error al validar el movimiento -> " . $ex->getMessage();
            error_log($msg);
            Log::error($msg);
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
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