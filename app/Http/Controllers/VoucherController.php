<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\ObjResponse;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    /**
     * Mostrar lista de vales activas
     *
     * @return \Illuminate\Http\Response $response
     */
    public function index(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $list = Voucher::all();

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | lista de vales.';
            $response->data["alert_text"] = "vales encontrados";
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
            $list = Voucher::where('vocuhers.active', true)
                ->select('vocuhers.id as id', 'vocuhers.activity as label')
                ->orderBy('vocuhers.id', 'asc')->get();
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Peticion satisfactoria | Lista de vales';
            $response->data["result"] = $list;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Mostrar vale.
     *
     * @param   int $id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function show(Request $request, Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $voucher = Voucher::find($request->id);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | vale encontrado.';
            $response->data["result"] = $voucher;
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

            $voucher = Voucher::find($id);
            if (!$voucher) $voucher = new Voucher();
            $voucher->user_id = $request->user_id;
            $voucher->foliated_vouchers = $request->foliated_vouchers;
            $voucher->stock_number = $request->stock_number;
            $voucher->vehicle_plates = $request->vehicle_plates;
            $voucher->payroll_number = $request->payroll_number;
            $voucher->department = $request->department;
            $voucher->name = $request->name;
            $voucher->paternal_last_name = $request->paternal_last_name;
            $voucher->maternal_last_name = $request->maternal_last_name;
            $voucher->phone = $request->phone;
            $voucher->activity = $request->activity;
            $voucher->voucher_status = $request->voucher_status;
            $voucher->quantity = $request->quantity;

            $voucher->save();

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = $id > 0 ? 'peticion satisfactoria | vale editado.' : 'peticion satisfactoria | vale registrado.';
            $response->data["alert_text"] = $id > 0 ? "Vale editado" : "Vale registrado";
        } catch (\Exception $ex) {
            error_log("Hubo un error al crear o actualizar el vale ->" . $ex->getMessage());
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Eliminar (cambiar estado activo=false) vale.
     *
     * @param  int $id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function destroy(Request $request, Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            Voucher::find($request->id)
                ->update([
                    'active' => false,
                    'deleted_at' => date('Y-m-d H:i:s'),
                ]);
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | vale eliminado.';
            $response->data["alert_text"] = 'Vale eliminado';
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }


    /**
     * Actualizar estatus del vale.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function updateStatus(Response $response, int $id, int $voucher_status, bool $internal=false)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $voucher = Voucher::find($id);
            $voucher->voucher_status = $voucher_status;
            $voucher->save();

            if ((bool)$internal) return 1;

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = "peticion satisfactoria | vale en estatus: $voucher_status.";
            $response->data["alert_text"] = "Vale en estatus: $voucher_status";
        } catch (\Exception $ex) {
            error_log($ex->getMessage());
            if ((bool)$internal) return 0;
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }
}
