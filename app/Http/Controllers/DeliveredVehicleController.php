<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DeliveredVehicle;
use App\Models\LoanedVehicle;
use App\Models\ObjResponse;
use App\Models\Vehicle;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class DeliveredVehicleController extends Controller
{
    /**
     * Mostrar lista de devoluciones de vehiculo
     *
     * @return \Illuminate\Http\Response $response
     */
    public function index(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $list = DeliveredVehicle::all();

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | lista de devoluciones de vehiculo.';
            $response->data["alert_text"] = "Devoluciones de vehiculos encontrados";
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
                    $response->data["message"] = 'peticion satisfactoria | devolucion de unidad no concluido.';
                    $response->data["alert_icon"] = "warning";
                    $response->data["alert_text"] = "Devolución de unidad no aplicable - El vehículo no está asignado a ningún director";
                    return response()->json($response, $response->data["status_code"]);
                    // return "no hay asignaciones a este vehiculo";
                }
            } else {
                $response->data["message"] = 'peticion satisfactoria | devolucion de unidad no concluido.';
                $response->data["alert_icon"] = "warning";
                $response->data["alert_text"] = "Devolución de unidad no aplicable - El vehículo no está asignado a ningún director";
                return response()->json($response, $response->data["status_code"]);
            }

            #VERIFICAR QUE EL VEHICULO NO TENGA PRESTAMO ACTIVO
            $loanedVehicleController = new LoanedVehicleController();
            $lastLoan = $loanedVehicleController->getLastLoanBy($response, 'assigned_vehicle_id', $lastAssignedVehicle->id, true);
            if ($lastLoan) {
                if ($lastLoan->active_loan === 1) {
                    $response->data["message"] = 'peticion satisfactoria | devolucion de unidad no concluido.';
                    $response->data["alert_icon"] = "warning";
                    $response->data["alert_text"] = "Devolución de unidad no completado - El vehículo tiene un prestamo activo";
                    return response()->json($response, $response->data["status_code"]);
                    // return "no hay asignaciones a este vehiculo";
                }
            }


            #VERIFICAR ESTE EN EL ESTATUS CORRECTO = 3-ASIGNADO
            $vehicle = Vehicle::find($lastAssignedVehicle->vehicle_id);
            if ($vehicle->vehicle_status_id !== 3) {
                $response->data["message"] = 'peticion satisfactoria | vehiculo no asignado.';
                $response->data["alert_icon"] = "warning";
                $response->data["alert_text"] = "Prestamo no completado - El vehículo está en un estatus donde no es posible devolver la unidad.";
                return response()->json($response, $response->data["status_code"]);
            }

            $userAuth = Auth::user();
            if ($userAuth->role_id <= 2) {
            } # no hay problema por ser admins,,, creo
            else if ($userAuth->role_id == 5) # Verificar que sea el usuario responsable de la unidad
            {
                if ((int)$userAuth->id != (int)$lastAssignedVehicle->user_id) {
                    $response->data["message"] = 'peticion satisfactoria | devolucion de unidad no concluida.';
                    $response->data["alert_icon"] = "warning";
                    $response->data["alert_text"] = "Devolución de unidad no completado - Solo el director asignado a la unidad puede devolverla.";
                    return response()->json($response, $response->data["status_code"]);
                }
            } else {
                $response->data["message"] = 'peticion satisfactoria | devolucion de unidad no concluida.';
                $response->data["alert_icon"] = "error";
                $response->data["alert_text"] = "Devolución de unidad no completado - Solo el director asignado a la unidad puede devolverla.";
                return response()->json($response, $response->data["status_code"]);
            }

            $accident_folio = $this->_getLastFolio();

            $deliveredVehicle = DeliveredVehicle::find($id);
            if (!$deliveredVehicle) $deliveredVehicle = new DeliveredVehicle();
            $deliveredVehicle->accident_folio = (int)$accident_folio + 1;
            $deliveredVehicle->assigned_vehicle_id = $request->assigned_vehicle_id;
            $deliveredVehicle->reason = $request->reason;
            $deliveredVehicle->km_deliver = $request->km_deliver;
            $deliveredVehicle->date = $request->date;

            $deliveredVehicle->save();

            #REGISTRAR MOVIMIENTO
            $vehicleMovementInstance = new VehicleMovementController();
            $r = $vehicleMovementInstance->registerMovement($request->vehicle_id, (bool)false, $deliveredVehicle->getTable(), $deliveredVehicle->id);
            // var_dump($r);

            #ACTUALIZAR STATUS DEL VEHICULO
            $vehicleInstance = new VehicleController();
            $vehicleInstance->updateStatus($vehicle->id, 2); //DISPONIBLE

            #ACTUALIZAR PRESTAMO ACTIVO DE LA ASIGNACION
            $lastAssignedVehicle->active_assignment = 0;
            $lastAssignedVehicle->save();


            $response->data = ObjResponse::CorrectResponse();

            $response->data["message"] = $id > 0 ? 'peticion satisfactoria | devolucion de unidad de vehiculo editada.' : 'peticion satisfactoria | devolucion de unidad de vehiculo registrada.';
            $response->data["alert_text"] = $id > 0 ? "Devolución de unidad editado" : "Devolución de unidad registrado";
        } catch (\Exception $ex) {
            error_log("Hubo un error al crear o actualizar el devolucion de unidad del vehículo -> " . $ex->getMessage());
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Obtener el ultimo folio.
     *
     * @return \Illuminate\Http\Int $folio
     */
    private function _getLastFolio()
    {
        try {
            $folio = DeliveredVehicle::max('accident_folio');
            if ($folio == null) return 0;
            return $folio;
        } catch (\Exception $ex) {
            $msg =  "Error obtener el ultimo folio: " . $ex->getMessage();
            echo "$msg";
            return $msg;
        }
    }
}
