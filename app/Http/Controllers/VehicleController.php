<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\ObjResponse;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class VehicleController extends Controller
{
    /**
     * Mostrar lista de vehículos activos.
     *
     * @return \Illuminate\Http\Response $response
     */
    public function index(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $list = Vehicle::where('vehicles.active', true)
                ->join('brands', 'vehicles.brand_id', '=', 'brands.id')
                ->join('models', 'vehicles.model_id', '=', 'models.id')
                ->join('vehicle_status', 'vehicles.vehicle_status_id', '=', 'vehicle_status.id')
                ->join('vehicle_plates', function ($join) {
                    $join->on('vehicle_plates.vehicle_id', '=', 'vehicles.id')
                        ->where('vehicle_plates.expired', '=', 0);
                })
                ->select('vehicles.*', 'brands.brand', 'models.model', 'vehicle_status.vehicle_status', 'vehicle_status.bg_color', 'vehicle_status.letter_black', 'plates', 'initial_date', 'due_date')
                ->orderBy('vehicles.id', 'asc')->get();
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Peticion satisfactoria | Lista de vehículos.';
            $response->data["result"] = $list;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Mostrar listado para un selector.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function selectIndex(Request $request, Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $list = Vehicle::where('vehicles.active', true)->where('vehicles.', $request->brand_id)
                ->select('vehicles.id as id', 'vehicles.model as label')
                ->orderBy('vehicles.model', 'asc')->get();
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Peticion satisfactoria | Lista de vehículos';
            $response->data["result"] = $list;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Crear vehículo.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function create(Request $request, Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $duplicate = $this->validateAvailableData($request->stock_number, null);
            if ($duplicate["result"] == true) {
                $response->data = $duplicate;
                return response()->json($response);
            }

            $new_vehicle = Vehicle::create([
                'stock_number' => $request->stock_number,
                'brand_id' => $request->brand_id,
                'model_id' => $request->model_id,
                'year' => $request->year,
                'registration_date' => $request->registration_date,
                'vehicle_status_id' => $request->vehicle_status_id,
                'description' => $request->description,
                'circulation_card' => $request->circulation_card,
                'insurance_policy' => $request->insurance_policy,
                // violated
            ]);


            $img_preview = $this->ImageUp($request, 'img_preview',$new_vehicle->id,'preview', true, "sinAuto.png");
            $img_right = $this->ImageUp($request, 'img_right',$new_vehicle->id,'right', false, "noRight.png");
            $img_back = $this->ImageUp($request, 'img_back',$new_vehicle->id,'back', false, "noBack.png");
            $img_left = $this->ImageUp($request, 'img_left',$new_vehicle->id,'left', false, "noLeft.png");
            $img_front = $this->ImageUp($request, 'img_front',$new_vehicle->id,'front', false, "noFront.png");
            $img_circulation_card = $this->ImageUp($request, 'img_circulation_card',$new_vehicle->id,'circulation_card', true, "sinTarjeta.png");
            $img_insurance_policy = $this->ImageUp($request, 'img_insurance_policy',$new_vehicle->id,'insurance_policy', true, "sinPoliza.png");

            $vehicle = Vehicle::find($new_vehicle->id);
            if ($img_preview != "") $vehicle->img_preview = $img_preview;
            if ($img_right != "") $vehicle->img_right = $img_right;
            if ($img_back != "") $vehicle->img_back = $img_back;
            if ($img_left != "") $vehicle->img_left = $img_left;
            if ($img_front != "") $vehicle->img_front = $img_front;
            if ($img_circulation_card != "") $vehicle->img_circulation_card = $img_circulation_card;
            if ($img_insurance_policy != "") $vehicle->img_insurance_policy = $img_insurance_policy;
            $vehicle->save();

            $vehiclesPlatesController = new VehiclePlatesController();
            $vehiclesPlatesController->createByVehicle($request, $new_vehicle->id, false);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | vehículo registrado.';
            $response->data["alert_text"] = 'Vehículo registrado';
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Mostrar vehiculo buscando por No. Unidad o Placas.
     *
     * @param   int $id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function showBy(Request $request, String $searchBy, String $value,  Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $vehicle = Vehicle::where("vehicles.active", 1)->where($searchBy, $value)
                ->join('brands', 'vehicles.brand_id', '=', 'brands.id')
                ->join('models', 'vehicles.model_id', '=', 'models.id')
                ->join('vehicle_status', 'vehicles.vehicle_status_id', '=', 'vehicle_status.id')
                ->join('vehicle_plates', function ($join) {
                    $join->on('vehicle_plates.vehicle_id', '=', 'vehicles.id')
                        ->where('vehicle_plates.expired', '=', 0);
                })
                ->select('vehicles.*', 'brands.brand', 'brands.img_path as img_brand', 'models.model', 'vehicle_status.vehicle_status', 'vehicle_status.bg_color', 'vehicle_status.letter_black', 'plates', 'initial_date', 'due_date')
                ->first();

            $response->data = ObjResponse::CorrectResponse();
            if ($vehicle) {
                $response->data["message"] = 'peticion satisfactoria | vehículo encontrado.';
                $response->data["alert_title"] = "Vehículo encontrado";
                $response->data["result"] = $vehicle;
            } else {
                $response->data["message"] = 'peticion satisfactoria | vehículo NO encontrado.';
                $response->data["result"] = null;
                $response->data["alert_icon"] = "information";
                $response->data["alert_title"] = "No se encontro vehículo con esa información";
            }
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }


    /**
     * Mostrar vehículo.
     *
     * @param   int $id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function show(Request $request, Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $vehicle = Vehicle::where('vehicles.id', $request->id)
                ->join('brands', 'vehicles.brand_id', '=', 'brands.id')
                ->join('models', 'vehicles.model_id', '=', 'models.id')
                ->join('vehicle_status', 'vehicles.vehicle_status_id', '=', 'vehicle_status.id')
                ->join('vehicle_plates', function ($join) {
                    $join->on('vehicle_plates.vehicle_id', '=', 'vehicles.id')
                        ->where('vehicle_plates.expired', '=', 0);
                })
                ->select('vehicles.*', 'brands.brand', 'models.model', 'vehicle_status.vehicle_status', 'vehicle_status.bg_color', 'vehicle_status.letter_black', 'plates', 'initial_date', 'due_date')
                ->first();

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | vehículo encontrado.';
            $response->data["result"] = $vehicle;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Actualizar vehículo.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function update(Request $request, Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $duplicate = $this->validateAvailableData($request->stock_number, $request->id);
            if ($duplicate["result"] == true) {
                $response->data = $duplicate;
                return response()->json($response);
            }

            $img_preview = $this->ImageUp($request, 'img_preview',$request->id,'preview', false, "sinAuto.png");
            $img_right = $this->ImageUp($request, 'img_right',$request->id,'right', false, "noRight.png");
            $img_back = $this->ImageUp($request, 'img_back',$request->id,'back', false, "noBack.png");
            $img_left = $this->ImageUp($request, 'img_left',$request->id,'left', false, "noLeft.png");
            $img_front = $this->ImageUp($request, 'img_front',$request->id,'front', false, "noFront.png");
            $img_circulation_card = $this->ImageUp($request, 'img_circulation_card',$request->id,'circulation_card', false, "sinTarjeta.png");
            $img_insurance_policy = $this->ImageUp($request, 'img_insurance_policy',$request->id,'insurance_policy', false, "sinPoliza.png");

            $vehicle = Vehicle::find($request->id);

            $vehicle->stock_number = $request->stock_number;
            $vehicle->brand_id = $request->brand_id;
            $vehicle->model_id = $request->model_id;
            $vehicle->year = $request->year;
            $vehicle->registration_date = $request->registration_date;
            $vehicle->vehicle_status_id = $request->vehicle_status_id;
            $vehicle->description = $request->description;
            $vehicle->circulation_card = $request->circulation_card;
            $vehicle->insurance_policy = $request->insurance_policy;
                // violated
            if ($img_preview != "") $vehicle->img_preview = $img_preview;
            if ($img_right != "") $vehicle->img_right = $img_right;
            if ($img_back != "") $vehicle->img_back = $img_back;
            if ($img_left != "") $vehicle->img_left = $img_left;
            if ($img_front != "") $vehicle->img_front = $img_front;
            if ($img_circulation_card != "") $vehicle->img_circulation_card = $img_circulation_card;
            if ($img_insurance_policy != "") $vehicle->img_insurance_policy = $img_insurance_policy;

            $vehicle->save();


            $vehiclesPlatesController = new VehiclePlatesController();
            $vehiclesPlatesController->createByVehicle($request, 0, $request->changePlates == 1 ? true : false);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | vehículo actualizado.';
            $response->data["alert_text"] = 'Vehículo actualizado';
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Eliminar (cambiar estado activo=false) vehículo.
     *
     * @param  int $id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function destroy(Request $request, Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            Vehicle::find($request->id)
                ->update([
                    'active' => false,
                    'deleted_at' => date('Y-m-d H:i:s'),
                ]);
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | vehículo eliminado.';
            $response->data["alert_text"] = 'Vehículo eliminado';
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }


    /**
     * Actualizar estatus del vehículo.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function updateStatus(int $id, int $vehicle_status_id)
    {
        try {
            $vehicle = Vehicle::find($id)
                ->update([
                    'vehicle_status_id' => $vehicle_status_id,
                ]);
            return 1;
        } catch (\Exception $ex) {
            error_log($ex->getMessage());
            return 0;
        }
    }


    private function validateAvailableData($stock_number, $id)
    {
        #este codigo se pone en las funciones de registro y edicion
        // $duplicate = $this->validateAvailableData($request->username, $request->email, $request->id);
        // if ($duplicate["result"] == true) {
        //     $response->data = $duplicate;
        //     return response()->json($response);
        // }

        $checkAvailable = new UserController();
        // #VALIDACION DE DATOS REPETIDOS
        $duplicate = $checkAvailable->checkAvailableData('vehicles', 'stock_number', $stock_number, 'El número de inventario', 'stock_number', $id, null);
        if ($duplicate["result"] == true) return $duplicate;
        return array("result" => false);
    }


    private function ImageUp($request, $requestFile, $id, $posFix, $create, $nameFake) {
        $dir_path = "GPCenter/vehicles";
        $dir = public_path($dir_path);
        $img_name = "";
        if ($request->hasFile($requestFile)) {
            $img_file = $request->file($requestFile);
            $instance = new UserController();
            $dir_path = "$dir_path/$id";
            $dir = "$dir/$id";
            $img_name = $instance->ImgUpload($img_file, $dir, $dir_path, "$id-$posFix");
        } else {
            if ($create) $img_name = "$dir_path/$nameFake";
        }
        return $img_name;
    }
}
