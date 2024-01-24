<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AssignedVehicle;
use App\Models\LoanedVehicle;
use App\Models\ObjResponse;
use App\Models\Vehicle;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

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

            #VERIFICAR QUE EL VEHICULO ESTE DISPONIBLE
            $response->data = ObjResponse::CorrectResponse();
            $vehicle = Vehicle::find($request->vehicle_id);
            if ($vehicle->vehicle_status_id !== 3) {
                $response->data["message"] = 'peticion satisfactoria | vehiculo no asignado.';
                $response->data["alert_icon"] = "warning";
                $response->data["alert_text"] = "Prestamo no completado - El vehículo no está asignado a ningun director";
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

            #ACTUALIZAR STATUS DEL VEHICULO
            $vehicleInstance = new VehicleController();
            $vehicleInstance->updateStatus($request->vehicle_id, 4); //Prestado



            //  $avatar = $this->ImageUp($request, "avatar", $loanedVehicle->id, true);
            //  $loanedVehicle->avatar = $avatar;
            $response->data["message"] = $id > 0 ? 'peticion satisfactoria | prestamo de vehiculo editada.' : 'peticion satisfactoria | prestamo de vehiculo registrada.';
            $response->data["alert_text"] = $id > 0 ? "Asignación de vehículo editada" : "Asignación de vehículo registrada";
        } catch (\Exception $ex) {
            error_log("Hubo un error al crear o actualizar el director ->" . $ex->getMessage());
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }
}
