<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AssignedVehicle;
use App\Models\ObjResponse;
use App\Models\Vehicle;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AssignedVehicleController extends Controller
{
    /**
     * Mostrar lista de asignaciones de vehiculo activas
     *
     * @return \Illuminate\Http\Response $response
     */
    public function index(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $list = AssignedVehicle::all();

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | lista de asignaciones de vehiculo.';
            $response->data["alert_text"] = "asignaciones de vehiculo encontrados";
            $response->data["result"] = $list;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Crear o Actualizar asignacion de vehiculo.
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

            #VERIFICAR QUE EL VEHICULO NO ESTE ASIGNADO
            $lastAssignedVehicle = $this->getLastAssignmentBy($response, 'vehicle_id', $request->vehicle_id, true);
            if ($lastAssignedVehicle->active_assignment) {
                $response->data["message"] = 'peticion satisfactoria | asignacion no concluida.';
                $response->data["alert_icon"] = "warning";
                $response->data["alert_text"] = "Asignación no completada - El vehículo ya está asignado";
                return response()->json($response, $response->data["status_code"]);
                // return "no hay asignaciones a este vehiculo";
            }


            $response->data = ObjResponse::CorrectResponse();
            $vehicle = Vehicle::find($request->vehicle_id);
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

            $assignedVehicle = AssignedVehicle::find($id);
            if (!$assignedVehicle) $assignedVehicle = new AssignedVehicle();
            $assignedVehicle->user_id = $request->user_id;
            $assignedVehicle->vehicle_id = $request->vehicle_id;
            $assignedVehicle->date = $request->date;
            $assignedVehicle->km_assignment = $request->km_assignment;
            if ($request->active_assignment) $assignedVehicle->active_assignment = (bool)$request->active_assignment;

            $assignedVehicle->save();

            #ACTUALIZAR STATUS DEL VEHICULO
            $vehicleInstance = new VehicleController();
            $vehicleInstance->updateStatus($request->vehicle_id, 3); //Asignado



            //  $avatar = $this->ImageUp($request, "avatar", $assignedVehicle->id, true);
            //  $assignedVehicle->avatar = $avatar;
            $response->data["message"] = $id > 0 ? 'peticion satisfactoria | asignacion de vehiculo editada.' : 'peticion satisfactoria | asignacion de vehiculo registrada.';
            $response->data["alert_text"] = $id > 0 ? "Asignación de vehículo editada" : "Asignación de vehículo registrada";
        } catch (\Exception $ex) {
            error_log("Hubo un error al crear o actualizar el director ->" . $ex->getMessage());
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Obtener ultima asignación
     *
     * @return \Illuminate\Http\Response $response
     */
    public function getLastAssignmentBy(Response $response, String $searchBy, String $value, Bool $internal = false)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $lastAssignment = AssignedVehicle::where($searchBy, $value)->where('active', 1)->orderBy('id', 'desc')->first();

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | ultima asignacion de vehiculo.';
            $response->data["alert_text"] = "Última asignación de vehiculo";
            $response->data["result"] = $lastAssignment;
            if ($internal === true) return $lastAssignment;
        } catch (\Exception $ex) {
            error_log("Hubo un error al obtener la ultima asignación ->" . $ex->getMessage());
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
            if ($internal === true) return null;
        }
        return response()->json($response, $response->data["status_code"]);
    }
}
