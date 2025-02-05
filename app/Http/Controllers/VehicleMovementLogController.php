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

    public function validationsToLoaned(Response $response, Int $vehicle_id, Int $user_id)
    {
        try {
            #VERIFICAR QUE EL VEHICULO ESTE ASIGNADO
            Log::info("validationsToLoaned ~ vehicle_id: $vehicle_id, user_id: $user_id");
            $lastMovement = $this->getLastAssignmentByVehicle($vehicle_id);
            Log::info("validationsToLoaned ~ lastMovement: " . json_encode($lastMovement));

            if (!$lastMovement) {
                $response->data["message"] = 'peticion satisfactoria | prestamo no concluido.';
                $response->data["alert_icon"] = "warning";
                $response->data["alert_text"] = "Prestamo no completado - El vehículo NO está asignado a un departamento/director";
                return $response;
            }
            $vehicle = Vehicle::find($vehicle_id);
            $director = DirectorView::where("user_id", $user_id)->first();

            $userAuth = Auth::user();
            #VERIFICAR QUE SOLO SuperAdmin, Adminis Patrimonio y el Director asignado PUEDAN PRESTAR
            if ((int)$userAuth->role_id > 2) {
                if ($userAuth->id == $lastMovement->active_user_id) {
                    $response->data["message"] = 'peticion satisfactoria | prestamo no concluido.';
                    $response->data["alert_icon"] = "warning";
                    $response->data["alert_text"] = "Prestamo no completado - Solo el director asignado a la unidad puede prestarlo.";
                    return $response;
                }
            }

            #VERIFICAR QUE SE ENCUENTRE EN EL STATUS CORRECTO
            if (!$this->validateCorrectStatus($response, $vehicle, 4)) return;
            #VERIFICAR LICENCIA
            if (!$this->validateLicense($response, $vehicle, $director)) return;
        } catch (Exception $ex) {
            $msg = "validationsToLoaned ~ Hubo un error al validar el movimiento -> " . $ex->getMessage();
            error_log($msg);
            Log::error($msg);
            return  $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return true;
    }
    public function validationsToAssign(Response $response, Int $vehicle_id, Int $user_id)
    {
        // $response->data = ObjResponse::DefaultResponse();
        try {
            $userAuth = Auth::user();
            #VERIFICAR QUE SOLO SuperAdmin y Adminis Patrimonio PUEDAN ASIGNAR
            Log::info("el rol del userAuth: " . $userAuth->role_id);
            if ((int)$userAuth->role_id > 2) {
                $response->data["message"] = 'peticion satisfactoria | prestamo no concluida.';
                $response->data["alert_icon"] = "warning";
                $response->data["alert_text"] = "Asignación no completada - Solo Patrimonio puede asignar unidades.";
                return $response;
            }

            // #VERIFICAR QUE EL VEHICULO NO ESTE ASIGNADO
            // // Log::info("validationsToAssign ~ vehicle_id: $vehicle_id, user_id: $user_id");
            // $lastMovement = $this->getLastMovementByVehicle($vehicle_id);
            // // Log::info("validationsToAssign ~ lastMovement: " . json_encode($lastMovement));

            // if ($lastMovement) {
            //     if (!in_array($lastMovement->vehicle_status_id, [1, 2])) {
            //         $response->data["message"] = 'peticion satisfactoria | asignacion no concluida.';
            //         $response->data["alert_icon"] = "warning";
            //         $response->data["alert_text"] = "Asignación no completada - El vehículo ya está asignado";
            //         return $response;
            //         // return "no hay asignaciones a este vehiculo";
            //     }
            // }
            $vehicle = Vehicle::find($vehicle_id);
            $director = DirectorView::where("user_id", $user_id)->first();

            #VERIFICAR QUE SE ENCUENTRE EN EL STATUS CORRECTO
            $responseValidate = $this->validateCorrectStatus($response, $vehicle, 3);
            if (!is_bool($response)) return $responseValidate;
            #VERIFICAR LICENCIA
            $responseValidate = $this->validateLicense($response, $vehicle, $director);
            if (!is_bool($response)) return $responseresponseValidate;
        } catch (Exception $ex) {
            $msg = "validationsToAssign ~ Hubo un error al validar el movimiento -> " . $ex->getMessage();
            error_log($msg);
            Log::error($msg);
            return $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return true;
    }

    private function validateCorrectStatus(Response $response, $vehicle, int $vehicle_status_movement_id)
    {
        try {
            Log::info("validateCorrectStatus ~\nel vehicle->vehicle_status_id: " . $vehicle->vehicle_status_id . " \nel vehicle_status_movement_id: " . $vehicle_status_movement_id);
            #SI ESTOY REALIZANDO UNA ASIGNACIÓN, VALIDAR...
            if ($vehicle_status_movement_id === 3) {
                if (in_array($vehicle->vehicle_status_id, [1, 2])) {
                    return true;
                } elseif ($vehicle->vehicle_status_id === 3) {
                    $response->data["message"] = 'peticion satisfactoria | vehiculo ya asignado.';
                    $response->data["alert_icon"] = "warning";
                    $response->data["alert_text"] = "Asignación no completada - El vehículo ya esta asignado";
                } elseif ($vehicle->vehicle_status_id === 4) {
                    $response->data["message"] = 'peticion satisfactoria | vehiculo ya asignado.';
                    $response->data["alert_icon"] = "warning";
                    $response->data["alert_text"] = "Asignación no completada - El vehículo ya esta asignado y se encuentra prestado";
                } elseif (in_array($vehicle->vehicle_status_id, [5, 7, 8])) {
                    $response->data["message"] = 'peticion satisfactoria | vehiculo en taller.';
                    $response->data["alert_icon"] = "warning";
                    $response->data["alert_text"] = "Asignación no completada - El vehículo se encuentra en el taller";
                }
                // Log::info("validateCorrectStatus ~ response: " . json_encode($response));
                return $response;
            }
            #SI ESTOY REALIZANDO UN PRESTAMO, VALIDAR...
            elseif ($vehicle_status_movement_id === 4) {
                if (in_array($vehicle->vehicle_status_id, [3])) {
                    return true;
                } elseif (in_array($vehicle->vehicle_status_id, [1, 2])) {
                    $response->data["message"] = 'peticion satisfactoria | vehiculo NO asignado.';
                    $response->data["alert_icon"] = "warning";
                    $response->data["alert_text"] = "Prestamo no completado - El vehículo NO esta asignado a un departamento/director";
                } elseif ($vehicle->vehicle_status_id === 4) {
                    $response->data["message"] = 'peticion satisfactoria | vehiculo NO asignado.';
                    $response->data["alert_icon"] = "warning";
                    $response->data["alert_text"] = "Prestamo no completado - El vehículo tiene un prestamo activo";
                } elseif (in_array($vehicle->vehicle_status_id, [5, 7, 8])) {
                    $response->data["message"] = 'peticion satisfactoria | vehiculo en taller.';
                    $response->data["alert_icon"] = "warning";
                    $response->data["alert_text"] = "Prestamo no completado - El vehículo se encuentra en el taller";
                }
                return $response;
            }
        } catch (Exception $ex) {
            $msg = "validateCorrectStatus ~ Hubo un error al validar el movimiento -> " . $ex->getMessage();
            error_log($msg);
            Log::error($msg);
            return $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return true;
    }
    private function validateLicense(Response $response, $vehicle, $director)
    {
        try {
            #VERIFICAR QUE SU LICENCIA NO ESTE VENCIDA
            if ($director->license_due_date != "") {

                $today = new DateTime();
                $license_due_date = new DateTime($director->license_due_date);

                if ($today > $license_due_date) {
                    $response->data["message"] = 'peticion satisfactoria | licencia vencida.';
                    $response->data["alert_icon"] = "warning";
                    $response->data["alert_text"] = "Asignación no completada - El conductor tiene la licencia vencida.";
                    return $response;
                }
            } else {
                $response->data["message"] = 'peticion satisfactoria | licencia vencida.';
                $response->data["alert_icon"] = "warning";
                $response->data["alert_text"] = "Asignación no completada - El conductor no tiene registrada la fecha de vencimiento de su licencia.";
                return $response;
            }

            #VERIFICAR QUE CONCIDAN EL TIPO DE LICENCIAS
            if ($vehicle->acceptable_license_type != "") {
                $acceptable_license_type = explode(",", $vehicle->acceptable_license_type);
                // return $director;
                if (!in_array($director->license_type, $acceptable_license_type)) {
                    $response->data["message"] = 'peticion satisfactoria | tipo de licencia no valida.';
                    $response->data["alert_icon"] = "warning";
                    $response->data["alert_text"] = "Asignación no completada - Tipo de licencia no valida para esta unidad.";
                    return $response;
                }
            } else {
                $response->data["message"] = 'peticion satisfactoria | tipo de licencia no valida.';
                $response->data["alert_icon"] = "warning";
                $response->data["alert_text"] = "Asignación no completada - El vehículo no tiene tipos de licencias asignados.";
                return $response;
            }
        } catch (Exception $ex) {
            $msg = "validateLicense ~ Hubo un error al validar el movimiento -> " . $ex->getMessage();
            error_log($msg);
            Log::error($msg);
            return $response->data = ObjResponse::CatchResponse($ex->getMessage());
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
        $response->data = ObjResponse::DefaultResponse();
        try {
            $userAuth = Auth::user();
            DB::beginTransaction();

            #VALIDACIONES
            if ($vehicle_status_id == 3) {
                $validate = $this->validationsToAssign($response, $vehicle_id, $request->active_user_id);
            } elseif ($vehicle_status_id == 4) {
                $validate = $this->validationsToLoaned($response, $vehicle_id, $request->active_user_id);
            }
            // $this->validationsToAssign($vehicle_id, $userAuth->id);

            if (!is_bool($validate)) {
                if ($validate->data["alert_icon"] == "warning") {
                    Log::info("VALIDATE: " . json_encode($validate));
                    return response()->json($validate, $validate->data["status_code"]);
                }
            }

            #ACTUALIZAR STATUS DEL VEHICULO
            $vehicleInstance = new VehicleController();
            $vehicleInstance->updateStatus($vehicle_id, $vehicle_status_id);



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
            if ($vehicle_status_id == 3) $vehicle_movement->valid = true;
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

    /**
     * obtener la ultima asignación del vehiculo.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Illuminate\Http\int $vehicle_id - indica el id del vehiculo a rastrear
     * @return \Illuminate\Http\Response $response
     */
    public function getLastAssignmentByVehicle(int $vehicle_id)
    {
        try {
            // Query base para obtener el movimiento de ASIGNACIÓN vigente y activa del vehículo
            $vehicle_movements = VehicleMovementLog::where('vehicle_id', $vehicle_id)
                ->where('valid', 1)
                ->where('active', 1)
                ->orderBy('id', 'desc');

            return $vehicle_movements->first();
        } catch (\Exception $ex) {
            error_log($ex->getMessage());
            return 0;
        }
    }
}
