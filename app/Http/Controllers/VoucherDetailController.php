<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\VoucherDetail;
use App\Models\ObjResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VoucherDetailController extends Controller
{
    /**
     * Mostrar lista de detalles del vale activas
     *
     * @return \Illuminate\Http\Response $response
     */
    public function indexByVoucher(Response $response, Int $id, bool $internal=false)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
                $list = VoucherDetail::where('active',1)->where("voucher_id", $id)->get();

                if ($internal === true) return $list;

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | lista de detalles del vale.';
            $response->data["alert_text"] = "detalles encontrados";
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
    public function createOrUpdate(Request $request, Response $response, Int $voucher_id = null)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            //  $duplicate = $this->validateAvailableData($request->phone, $request->license_number, $id);
            //  if ($duplicate["result"] == true) {
            //      return $duplicate;
            //  }

            $voucher_detail = VoucherDetail::where('voucher_id',$voucher_id)->first();
            if (!$voucher_detail) $voucher_detail = new VoucherDetail();
            $voucher_detail->voucher_id = $voucher_id;
            $voucher_detail->vehicle = $request->vehicle;
            $voucher_detail->vehicle_plates = $request->vehicle_plates;
            $voucher_detail->requested_amount = $request->requested_amount;
            $voucher_detail->payroll_number = $request->payroll_number;
            $voucher_detail->department = $request->department;
            $voucher_detail->name = $request->name;
            $voucher_detail->paternal_last_name = $request->paternal_last_name;
            $voucher_detail->maternal_last_name = $request->maternal_last_name;
            $voucher_detail->phone = $request->phone;

            $voucher_detail->save();

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = $voucher_detail->id > 0 ? 'peticion satisfactoria | detalle de vale editado.' : 'peticion satisfactoria | detalle de vale registrado.';
            $response->data["alert_text"] = $voucher_detail->id > 0 ? "Detalle de Vale editado" : "Detalle de Vale registrado";
        } catch (\Exception $ex) {
            error_log("Hubo un error al crear o actualizar el vale ->" . $ex->getMessage());
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

     /**
     * Eliminar detalle voucher o detalles voucher.
     *
     * @param  int $id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function destroy(Request $request, Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            // echo "$request->ids";
            // $deleteIds = explode(',', $ids);
            $countDeleted = sizeof($request->ids);
            VoucherDetail::whereIn('id', $request->ids)->delete();
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = $countDeleted == 1 ? 'peticion satisfactoria | detalle eliminado.' : "peticion satisfactoria | detalles eliminados ($countDeleted).";
            $response->data["alert_text"] = $countDeleted == 1 ? 'Detalle eliminado' : "Detalles eliminados ($countDeleted)";
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }


}
