<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AssignedVehicle;
use App\Models\DriverView;
use App\Models\LoanedVehicle;
use App\Models\ObjResponse;
use App\Models\Vehicle;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoanedVehicleController extends Controller
{
    /**
     * Mostrar lista de prestamos de vehiculo activas
     *
     * @return \Illuminate\Http\Response $response
     */
    public function index(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $list = LoanedVehicle::all();

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | lista de prestamos de vehiculo.';
            $response->data["alert_text"] = "Prestamos de vehiculos encontrados";
            $response->data["result"] = $list;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Crear o Actualizar prestamo de vehiculo.
     *
     * @return \Illuminate\Http\Response $response
     */
    public function createOrUpdate(Request $request, Response $response, Int $id = null)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            //  $duplicate = $this->validateAvailableData($request->phone, $request->license_number, $id);
            //  if ($duplicate["result"] == true) {
            //      return $duplicate;
            //  }

            #VERIFICAR QUE EL VEHICULO ESTE ASIGNADO
            $assignedVehicleController = new AssignedVehicleController();
            $lastAssignedVehicle = $assignedVehicleController->getLastAssignmentBy($response, 'vehicle_id', $request->vehicle_id, true);
            if ($lastAssignedVehicle) {
                if ($lastAssignedVehicle->active_assignment == 0) {
                    $response->data["message"] = 'peticion satisfactoria | prestamo no concluido.';
                    $response->data["alert_icon"] = "warning";
                    $response->data["alert_text"] = "Prestamo no completado - El vehículo no está asignado a ningún director";
                    return response()->json($response, $response->data["status_code"]);
                    // return "no hay asignaciones a este vehiculo";
                }
            }

            #VERIFICAR QUE EL VEHICULO NO TENGA PRESTAMO ACTIVO
            $lastLoan = $this->getLastLoanBy($response, 'assigned_vehicle_id', $lastAssignedVehicle->id, true);
            if ($lastLoan) {
                if ($lastLoan->active_loan == 1) {
                    $response->data["message"] = 'peticion satisfactoria | prestamo no concluido.';
                    $response->data["alert_icon"] = "warning";
                    $response->data["alert_text"] = "Prestamo no completado - El vehículo tiene un prestamo activo";
                    return response()->json($response, $response->data["status_code"]);
                    // return "no hay asignaciones a este vehiculo";
                }
            }

            $vehicle = Vehicle::find($lastAssignedVehicle->vehicle_id);
            $driver = DriverView::where("user_id", $request->requesting_user_id)->first();

            #VERIFICAR QUE SU LICENCIA NO ESTE VENCIDA
            if ($driver->license_due_date != "") {

                $today = new DateTime();
                $license_due_date = new DateTime($driver->license_due_date);

                if ($today > $license_due_date) {
                    $response->data["message"] = 'peticion satisfactoria | licencia vencida.';
                    $response->data["alert_icon"] = "warning";
                    $response->data["alert_text"] = "Préstamo no completada - El conductor tiene la licencia vencida.";
                    return response()->json($response, $response->data["status_code"]);
                }
            } else {
                $response->data["message"] = 'peticion satisfactoria | licencia vencida.';
                $response->data["alert_icon"] = "warning";
                $response->data["alert_text"] = "Préstamo no completada - El conductor no tiene registrada la fecha de vencimiento de su licencia.";
                return response()->json($response, $response->data["status_code"]);
            }

            #VERIFICAR QUE CONCIDAN EL TIPO DE LICENCIAS
            if ($vehicle->acceptable_license_type != "") {
                $acceptable_license_type = explode(",", $vehicle->acceptable_license_type);
                // return print_r($acceptable_license_type);
                if (!in_array($driver->license_type, $acceptable_license_type)) {
                    $response->data["message"] = 'peticion satisfactoria | tipo de licencia no valida.';
                    $response->data["alert_icon"] = "warning";
                    $response->data["alert_text"] = "Préstamo no completada - Tipo de licencia no valida para esta unidad.";
                    return response()->json($response, $response->data["status_code"]);
                }
            } else {
                $response->data["message"] = 'peticion satisfactoria | tipo de licencia no valida.';
                $response->data["alert_icon"] = "warning";
                $response->data["alert_text"] = "Préstamo no completada - El vehículo no tiene tipos de licencias asignados.";
                return response()->json($response, $response->data["status_code"]);
            }


            #VERIFICAR QUE ESTE EN EL ESTATUS CORRECTO = 3-ASIGNADO
            if ($vehicle->vehicle_status_id !== 3) {
                $response->data["message"] = 'peticion satisfactoria | vehiculo no asignado.';
                $response->data["alert_icon"] = "warning";
                $response->data["alert_text"] = "Prestamo no completado - El vehículo está en un estatus donde no es posible realizar el prestamo.";
                return response()->json($response, $response->data["status_code"]);
            }

            $userAuth = Auth::user();
            // return "hasta aqui todo bien";
            if ($userAuth->role_id <= 2) {
            } # no hay problema por ser admins,,, creo
            else if ($userAuth->role_id == 5) # Verificar que sea el usuario responsable de la unidad
            {
                // return "userAuth->id:$userAuth->id -- lastAssignedVehicle->user_id:$lastAssignedVehicle->user_id";
                if ((int)$userAuth->id != (int)$lastAssignedVehicle->user_id) {
                    $response->data["message"] = 'peticion satisfactoria | prestamo no concluida.';
                    $response->data["alert_icon"] = "warning";
                    $response->data["alert_text"] = "Prestamo no completado - Solo el director asignado a la unidad puede prestarlo.";
                    return response()->json($response, $response->data["status_code"]);
                }
            } else {
                $response->data["message"] = 'peticion satisfactoria | prestamo no concluida.';
                $response->data["alert_icon"] = "error";
                $response->data["alert_text"] = "Prestamo no completado - Solo el director asignado a la unidad puede prestarlo.";
                return response()->json($response, $response->data["status_code"]);
            }


            $loanedVehicle = LoanedVehicle::find($id);
            if (!$loanedVehicle) $loanedVehicle = new LoanedVehicle();
            $loanedVehicle->assigned_vehicle_id = $request->assigned_vehicle_id;
            $loanedVehicle->requesting_user_id = $request->requesting_user_id;
            $loanedVehicle->reason = $request->reason;
            $loanedVehicle->initial_km = $request->initial_km;
            $loanedVehicle->loan_date = $request->loan_date;
            if ($request->active_loan) $loanedVehicle->active_loan = (bool)$request->active_loan;
            if ($request->delivery_km) $loanedVehicle->delivery_km = $request->delivery_km;
            if ($request->delivery_date) $loanedVehicle->delivery_date = $request->delivery_date;

            $loanedVehicle->save();

            #REGISTRAR MOVIMIENTO
            $vehicleMovementInstance = new VehicleMovementController();
            $r = $vehicleMovementInstance->registerMovement($request->vehicle_id, (bool)false, $loanedVehicle->getTable(), $loanedVehicle->id);
            // var_dump($r);

            #ACTUALIZAR STATUS DEL VEHICULO
            $vehicleInstance = new VehicleController();
            $vehicleInstance->updateStatus($request->vehicle_id, 4); //Prestado


            $response->data = ObjResponse::CorrectResponse();

            $response->data["message"] = $id > 0 ? 'peticion satisfactoria | prestamo de vehiculo editada.' : 'peticion satisfactoria | prestamo de vehiculo registrada.';
            $response->data["alert_text"] = $id > 0 ? "Prestamo de vehículo editado" : "Prestamo de vehículo registrado";
        } catch (\Exception $ex) {
            Log::info("Hubo un error al crear o actualizar el prestamo del vehículo ->" . $ex->getMessage());
            error_log("Hubo un error al crear o actualizar el prestamo del vehículo ->" . $ex->getMessage());
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }


    /**
     * Crear o Actualizar prestamo de vehiculo.
     *
     * @return \Illuminate\Http\Response $response
     */
    public function returnLoan(Request $request, Response $response, Int $id = null)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            //  $duplicate = $this->validateAvailableData($request->phone, $request->license_number, $id);
            //  if ($duplicate["result"] == true) {
            //      return $duplicate;
            //  }

            #VERIFICAR QUE EL VEHICULO ESTE ASIGNADO
            $assignedVehicleController = new AssignedVehicleController();
            $lastAssignedVehicle = $assignedVehicleController->getLastAssignmentBy($response, 'vehicle_id', $request->vehicle_id, true);
            if ($lastAssignedVehicle) {
                if ($lastAssignedVehicle->active_assignment == 0) {
                    $response->data["message"] = 'peticion satisfactoria | devolucion de prestamo no concluido.';
                    $response->data["alert_icon"] = "warning";
                    $response->data["alert_text"] = "Devolución de prestamo no aplicable - El vehículo no está asignado a ningún director";
                    return response()->json($response, $response->data["status_code"]);
                    // return "no hay asignaciones a este vehiculo";
                }
            }

            #VERIFICAR QUE EL VEHICULO TENGA PRESTAMO ACTIVO
            $lastLoan = $this->getLastLoanBy($response, 'assigned_vehicle_id', $lastAssignedVehicle->id, true);
            if ($lastLoan) {
                if ($lastLoan->active_loan == 0) {
                    $response->data["message"] = 'peticion satisfactoria | devolucion de prestamo no concluido.';
                    $response->data["alert_icon"] = "warning";
                    $response->data["alert_text"] = "Devolución de prestamo no completado - El vehículo no tiene un prestamo activo";
                    return response()->json($response, $response->data["status_code"]);
                }
            } else {
                $response->data["message"] = 'peticion satisfactoria | devolucion de prestamo no concluido.';
                $response->data["alert_icon"] = "warning";
                $response->data["alert_text"] = "Devolución de prestamo no completado - El vehículo no tiene un prestamo activo";
                return response()->json($response, $response->data["status_code"]);
            }

            #VERIFICAR ESTE EN EL ESTATUS CORRECTO = 4-PRESTADO
            $vehicle = Vehicle::find($lastAssignedVehicle->vehicle_id);
            if ($vehicle->vehicle_status_id !== 4) {
                $response->data["message"] = 'peticion satisfactoria | vehiculo no asignado.';
                $response->data["alert_icon"] = "warning";
                $response->data["alert_text"] = "Devolución de prestamo no completado - El vehículo está en un estatus donde no es posible devolver la unidad.";
                return response()->json($response, $response->data["status_code"]);
            }

            $userAuth = Auth::user();
            if ($userAuth->role_id <= 2) {
            } # no hay problema por ser admins,,, creo
            else if ($userAuth->role_id == 6) # Verificar que sea el usuario quien solicitó el prestamo de la unidad
            {
                if ((int)$userAuth->id != (int)$lastLoan->requesting_user_id) {
                    $response->data["message"] = 'peticion satisfactoria | devolucion de prestamo no concluida.';
                    $response->data["alert_icon"] = "warning";
                    $response->data["alert_text"] = "Devolución de prestamo no completado - Solo el conductor solicitante puede devolver el prestamo.";
                    return response()->json($response, $response->data["status_code"]);
                }
            } else {
                $response->data["message"] = 'peticion satisfactoria | devolucion de prestamo no concluida.';
                $response->data["alert_icon"] = "error";
                $response->data["alert_text"] = "Devolución de prestamo no completado - Solo el conductor solicitante puede devolver el prestamo.";
                return response()->json($response, $response->data["status_code"]);
            }

            $lastLoan->active_loan = false;
            $lastLoan->delivery_km = $request->delivery_km;
            $lastLoan->delivery_comments = $request->delivery_comments;
            $lastLoan->delivery_date = $request->delivery_date;

            $lastLoan->save();

            #REGISTRAR MOVIMIENTO
            $vehicleMovementInstance = new VehicleMovementController();
            $r = $vehicleMovementInstance->registerMovement($request->vehicle_id, (bool)false, $lastLoan->getTable(), $lastLoan->id);
            // var_dump($r);

            #ACTUALIZAR STATUS DEL VEHICULO
            $vehicleInstance = new VehicleController();
            $vehicleInstance->updateStatus($vehicle->id, 3); //ASIGNADO

            $response->data = ObjResponse::CorrectResponse();

            $response->data["message"] = $id > 0 ? 'peticion satisfactoria | devolucion de prestamo de vehiculo editada.' : 'peticion satisfactoria | devolucion de prestamo de vehiculo registrada.';
            $response->data["alert_text"] = $id > 0 ? "Devolución de prestamo editado" : "Devolución de prestamo registrado";
        } catch (\Exception $ex) {
            error_log("Hubo un error al crear o actualizar el devolucion de prestamo del vehículo ->" . $ex->getMessage());
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }


    /**
     * Obtener ultima asignación
     *
     * @return \Illuminate\Http\Response $response
     */
    public function getLastLoanBy(Response $response, String $searchBy, String $value, Bool $internal = false)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $lastLoan = LoanedVehicle::where($searchBy, $value)->where('active', 1)->orderBy('id', 'desc')->first();

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | utlimo prestamo de vehiculo.';
            $response->data["alert_text"] = "Último prestamo de vehiculo";
            $response->data["result"] = $lastLoan;
            if ($internal === true) return $lastLoan;
        } catch (\Exception $ex) {
            error_log("Hubo un error al obtener el ultimo prestamo ->" . $ex->getMessage());
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
            if ($internal === true) return null;
        }
        return response()->json($response, $response->data["status_code"]);
    }
}