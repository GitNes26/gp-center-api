<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BecaView;
use App\Models\ObjResponse;
use App\Models\Level;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherView;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CounterController extends Controller
{
    /**
     * Contar elementos de menus.
     *
     * @return \Illuminate\Http\Int $folio
     */
    public function counterOfMenus(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $list = [];
            $vouchersList = VoucherView::select("voucher_status as counter", DB::raw("COUNT(voucher_status) as total"))->groupBy('voucher_status')->get();
            $usersList = User::select("roles.role as counter", DB::raw("COUNT(role_id) as total"))
                ->join('roles', 'users.role_id', '=', 'roles.id')
                ->groupBy('role_id')->get();
            array_push($list, ...$vouchersList);
            array_push($list, ...$usersList);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Peticion satisfactoria | contadores de los menus';
            $response->data["result"] = $list;
        } catch (\Exception $ex) {
            $msg =  "Error al consultar contador de los menus: " . $ex->getMessage();
            $response->data = ObjResponse::CatchResponse($msg);
        }
        return response()->json($response, $response->data["status_code"]);
    }
}