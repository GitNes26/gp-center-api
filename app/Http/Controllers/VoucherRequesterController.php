<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\VoucherRequester;
use App\Models\ObjResponse;
use App\Models\VoucherRequesterView;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class VoucherRequesterController extends Controller
{
   /**
    * Mostrar lista de solicitadores de vales activos del
    * uniendo con roles.
    *
    * @return \Illuminate\Http\Response $response
    */
   public function index(Response $response)
   {
      $response->data = ObjResponse::DefaultResponse();
      try {
         $list = VoucherRequesterView::all();

         $response->data = ObjResponse::CorrectResponse();
         $response->data["message"] = 'peticion satisfactoria | lista de solicitadores de vales.';
         $response->data["alert_text"] = "solicitadores de vales encontrados";
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
         $list = VoucherRequesterView::select('id', 'username as label')
            ->orderBy('username', 'asc')->get();
         $response->data = ObjResponse::CorrectResponse();
         $response->data["message"] = 'peticion satisfactoria | lista de solicitadores de vales.';
         $response->data["alert_text"] = "solicitadores de vales encontrados";
         $response->data["result"] = $list;
         $response->data["toast"] = false;
      } catch (\Exception $ex) {
         $response->data = ObjResponse::CatchResponse($ex->getMessage());
      }
      return response()->json($response, $response->data["status_code"]);
   }

   /**
    * Crear o Actualizar solicitador de vales.
    *
    * @return \Illuminate\Http\Response $response
    */
   public function createOrUpdate($user_id, $request)
   {
      try {
         $voucher_requester = VoucherRequester::where('user_id', $request->user_id)->first();

         $id = null;
         if ($voucher_requester) $id = $voucher_requester->id;
         else $voucher_requester = new VoucherRequester();

         $duplicate = $this->validateAvailableData($request->phone, $request->payroll_number, $id);
         if ($duplicate["result"] == true) {
            return $duplicate;
         }

         $voucher_requester->user_id = $user_id;
         $voucher_requester->name = $request->name;
         $voucher_requester->paternal_last_name = $request->paternal_last_name;
         $voucher_requester->maternal_last_name = $request->maternal_last_name;
         $voucher_requester->phone = $request->phone;
         $voucher_requester->payroll_number = $request->payroll_number;
         $voucher_requester->department = $request->department;

         $voucher_requester->save();

         $avatar = $this->ImageUp($request, "avatar", $voucher_requester->id, "avatar", true, "noAvatar");
         $img_firm = $this->ImageUp($request, "img_firm", $voucher_requester->id, "firm", true, "noFirm");
         $img_stamp = $this->ImageUp($request, "img_stamp", $voucher_requester->id, "stamp", true, "noStamp");
         if ($request->hasFile('avatar')) $voucher_requester->avatar = $avatar;
         if ($request->hasFile('img_firm')) $voucher_requester->img_firm = $img_firm;
         if ($request->hasFile('img_stamp')) $voucher_requester->img_stamp = $img_stamp;

         $voucher_requester->save();

         return $voucher_requester;
      } catch (\Exception $ex) {
         error_log("Hubo un error al crear o actualizar el solicitador de vales ->" . $ex->getMessage());
         echo "Hubo un error al crear o actualizar el solicitador de vales ->" . $ex->getMessage();
      }
   }


   /**
    * Mostrar solicitador de vales.
    *
    * @param   int $id
    * @param  \Illuminate\Http\Request $request
    * @return \Illuminate\Http\Response $response
    */
   public function show(Request $request, Int $id, Response $response)
   {
      $response->data = ObjResponse::DefaultResponse();
      try {
         // echo "el id: $request->id";
         // $user = VoucherRequesterView::where('user_id', $request->user_id)
         $user = VoucherRequesterView::find($id);

         $response->data = ObjResponse::CorrectResponse();
         $response->data["message"] = 'peticion satisfactoria | solicitador de vales encontrado.';
         $response->data["alert_text"] = "VoucherRequester encontrado";
         $response->data["result"] = $user;
      } catch (\Exception $ex) {
         $response = ObjResponse::CatchResponse($ex->getMessage());
      }
      return response()->json($response, $response->data["status_code"]);
   }


   public function validateAvailableData($phone, $payroll_number, $id)
   {
      $checkAvailable = new UserController();
      // #VALIDACION DE DATOS REPETIDOS
      $duplicate = $checkAvailable->checkAvailableData('voucher_requesters', 'phone', $phone, 'El número telefónico', 'phone', $id, "users");
      if ($duplicate["result"] == true) return $duplicate;

      $duplicate = $checkAvailable->checkAvailableData('voucher_requesters', 'payroll_number', $payroll_number, 'El empleado (número de nómina) ya ha sido registrado', 'payroll_number', $id, "users");
      if ($duplicate["result"] == true) return $duplicate;
      return array("result" => false);
   }


   private function ImageUp($request, $requestFile, $id, $poxFix, $create, $nameFake)
   {
      $dir_path = "GPCenter/voucherRequesters";
      $dir = public_path($dir_path);
      $img_name = "";
      if ($request->hasFile($requestFile)) {
         $img_file = $request->file($requestFile);
         $instance = new UserController();
         $img_name = $instance->ImgUpload($img_file, $dir, $dir_path, "$id-$poxFix");
      } else {
         if ($create) $img_name = "$dir_path/$nameFake.png";
      }
      return $img_name;
   }
}
