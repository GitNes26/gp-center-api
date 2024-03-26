<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\ObjResponse;
use App\Models\VoucherView;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
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
            $auth = Auth::user();
            if (in_array($auth->role_id, [1, 7]))
                $list = VoucherView::orderBy('id', 'desc')->get();
            else
                $list = VoucherView::where("requested_by", $auth->id)->orderBy('id', 'desc')->get();

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
            $voucher = VoucherView::find($request->id);

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
            $voucher->requested_by = $request->requested_by;
            $voucher->internal_folio = $request->internal_folio;
            $voucher->letter_folio = $request->letter_folio;
            $voucher->foliated_vouchers = $request->foliated_vouchers;
            // $voucher->vehicle = $request->vehicle;
            // $voucher->vehicle_plates = $request->vehicle_plates;
            // $voucher->requested_amount = $request->requested_amount;
            // $voucher->payroll_number = $request->payroll_number;
            // $voucher->department = $request->department;
            // $voucher->name = $request->name;
            // $voucher->paternal_last_name = $request->paternal_last_name;
            // $voucher->maternal_last_name = $request->maternal_last_name;
            // $voucher->phone = $request->phone;
            $voucher->activity = $request->activity;
            $voucher->voucher_status = $request->voucher_status;

            if ($id > 0) {
                $voucher->approved_by = $request->approved_by; #user_id
                $voucher->approved_amount = $request->approved_amount;
                $voucher->approved_at = $request->approved_at;
                $voucher->canceled_by = $request->canceled_by; #user_id
                $voucher->canceled_comments = $request->canceled_comments;
                $voucher->canceled_at = $request->canceled_at;
            }

            $voucher->save();

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = $id > 0 ? 'peticion satisfactoria | vale editado.' : 'peticion satisfactoria | vale registrado.';
            $response->data["alert_text"] = $id > 0 ? "Vale editado" : "Vale registrado";
            $response->data["result"] = $voucher;
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
    public function updateStatus(Request $request, Response $response, int $id, string $voucher_status, bool $internal = false)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $voucher = Voucher::find($id);
            $voucher->voucher_status = $voucher_status;

            if ($voucher_status === "APROBADA") {
                $voucher->letter_folio = $request->letter_folio;
                $voucher->foliated_vouchers = $request->foliated_vouchers;
                $voucher->approved_by = $request->approved_by; #user_id
                $voucher->approved_amount = $request->approved_amount;
                $voucher->approved_at = $request->approved_at;
            } elseif ($voucher_status === "CANCELADA") {
                $voucher->canceled_at = $request->canceled_at; #user_id
                $voucher->canceled_comments = $request->canceled_comments;
                $voucher->canceled_by = $request->canceled_by;
            }

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

    /**
     * Al ver el voucher (voucher visto).
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function seenVoucher(Request $request, Response $response, int $id, bool $internal = false)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $voucher = Voucher::find($id);

            if ($voucher->viewed_by < 1) {
                $voucher->viewed_by = $request->viewed_by; #user_id
                $voucher->viewed_at = $request->viewed_at;

                $voucher->save();
            }

            if ((bool)$internal) return 1;

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = "peticion satisfactoria | vale visto.";
            $response->data["alert_text"] = "Vale visto";
        } catch (\Exception $ex) {
            error_log($ex->getMessage());
            if ((bool)$internal) return 0;
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Contar vales que...
     *
     * @param   int $id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function counter(Request $request, Response $response, String $key, String $value)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $values = explode(',', $value);
            $count = VoucherView::whereIn($key, $values)->count();

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | vales contados.';
            $response->data["result"] = $count;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }
}
