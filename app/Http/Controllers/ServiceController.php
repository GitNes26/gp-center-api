<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ObjResponse;
use App\Models\ServiceApprovedView;
use App\Models\ServiceClosedView;
use App\Models\ServiceInReviewedView;
use App\Models\ServiceOpenedView;
use App\Models\ServiceRejectedView;
use App\Models\ServiceView;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    /**
     * Mostrar lista de servicios activas.
     *
     * @return \Illuminate\Http\Response $response
     */
    public function index(Response $response, string $status = null)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $userAuth = Auth::user();

            $ViewService = new ServiceView();
            if ($status == "ABIERTA") $ViewService = new ServiceOpenedView();
            elseif ($status == "APROBADA") $ViewService = new ServiceApprovedView();
            elseif ($status == "RECHAZADA") $ViewService = new ServiceRejectedView();
            elseif ($status == "EN REVISIÓN") $ViewService = new ServiceInReviewedView();
            elseif ($status == "CERRADA") $ViewService = new ServiceClosedView();

            $list = $userAuth->role_id == 3 ? $ViewService::where('user_id', $userAuth->id)->get() : $ViewService::all();
            // $list = Service::where('services.active', true)
            //     ->join('vehicles', 'services.vehicle_id', '=', 'vehicles.id')
            //     ->join('brands', 'vehicles.brand_id', '=', 'brands.id')
            //     ->join('models', 'vehicles.model_id', '=', 'models.id')
            //     ->join('vehicle_status', 'vehicles.vehicle_status_id', '=', 'vehicle_status.id')
            //     ->join('vehicle_plates', function ($join) {
            //         $join->on('vehicle_plates.vehicle_id', '=', 'vehicles.id')
            //             ->where('vehicle_plates.expired', '=', 0);
            //     })
            //     ->join('users', 'services.mechanic_id', '=', 'users.id')
            //     ->select('services.*', 'vehicles.stock_number', 'vehicles.year', 'vehicles.registration_date', 'vehicles.description', 'brands.brand', 'models.model', 'vehicle_status.vehicle_status', 'vehicle_status.bg_color', 'vehicle_status.letter_black', 'plates', 'initial_date', 'due_date', 'users.username')
            //     ->orderBy('services.id', 'desc')->get();
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Peticion satisfactoria | Lista de servicios.';
            $response->data["result"] = $list;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Mostrar listado para un selector.
     *
     * @return \Illuminate\Http\Response $response
     */
    public function selectIndex(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $list = Service::where('active', true)
                ->select('services.id as id', 'services.service as label')
                ->orderBy('services.service', 'asc')->get();
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Peticion satisfactoria | Lista de servicios';
            $response->data["result"] = $list;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Crear un nuevo servicio.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function create(Request $request, Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $folio = $this->getLastFolio();

            $new_service = Service::create([
                'folio' => (int)$folio + 1,
                'vehicle_id' => $request->vehicle_id,
                'contact_name' => $request->contact_name,
                'contact_phone' => $request->contact_phone,
                'pre_diagnosis' => $request->pre_diagnosis,
                'requested_by' => $request->requested_by,
                'requested_at' => $request->requested_at,
                // 'mechanic_id' => $request->mechanic_id,
                // 'final_diagnosis' => $request->final_diagnosis,
                // 'evidence_img_path' => $request->evidence_img_path,
            ]);

            $vehicleInstance = new VehicleController();
            $vehicleInstance->updateStatus($request->vehicle_id, 5); //En Taller/Servicio

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | servicio registrado.';
            $response->data["alert_text"] = "Servicio registrado <br> tu folio es el <b>#$new_service->folio</b>";
            $response->data["result"] = $new_service;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Mostrar servicio.
     *
     * @param   int $id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function show(Request $request, Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $service = Service::where('services.id', $request->id)
                ->join('vehicles', 'services.vehicle_id', '=', 'vehicles.id')
                ->join('brands', 'vehicles.brand_id', '=', 'brands.id')
                ->join('models', 'vehicles.model_id', '=', 'models.id')
                ->join('vehicle_status', 'vehicles.vehicle_status_id', '=', 'vehicle_status.id')
                ->join('vehicle_plates', function ($join) {
                    $join->on('vehicle_plates.vehicle_id', '=', 'vehicles.id')
                        ->where('vehicle_plates.expired', '=', 0);
                })
                ->join('users', 'services.mechanic_id', '=', 'users.id')
                ->select('services.*', 'vehicles.stock_number', 'vehicles.year', 'vehicles.registration_date', 'vehicles.description', 'brands.brand', 'models.model', 'vehicle_status.vehicle_status', 'vehicle_status.bg_color', 'vehicle_status.letter_black', 'plates', 'initial_date', 'due_date', 'users.username')
                ->orderBy('services.id', 'asc')->first();

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | servicio encontrado.';
            $response->data["result"] = $service;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Actualizar servicio.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function update(Request $request, Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $service = Service::find($request->id)
                ->update([
                    'folio' => $request->folio,
                    'vehicle_id' => $request->vehicle_id,
                    'contact_name' => $request->contact_name,
                    'contact_phone' => $request->contact_phone,
                    'pre_diagnosis' => $request->pre_diagnosis,
                    'final_diagnosis' => $request->final_diagnosis,
                    'mechanic_id' => $request->mechanic_id,
                    'status' => $request->status,
                    // 'evidence_img_path' => $request->evidence_img_path,
                ]);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | servicio actualizado.';
            $response->data["alert_text"] = 'Servicio actualizado';
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Eliminar (cambiar estado activo=false) servicio.
     *
     * @param  int $id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function destroy(Request $request, Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            Service::find($request->id)
                ->update([
                    'active' => false,
                    'deleted_at' => date('Y-m-d H:i:s'),
                ]);
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | servicio eliminado.';
            $response->data["alert_text"] = 'Servicio eliminado';
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Obtener el ultimo folio.
     *
     * @return \Illuminate\Http\Int $folio
     */
    private function getLastFolio()
    {
        try {
            $folio = Service::max('folio');
            if ($folio == null) return 0;
            return $folio;
        } catch (\Exception $ex) {
            $msg =  "Error al obtener el ultimo folio: " . $ex->getMessage();
            echo "$msg";
            return $msg;
        }
    }
}
