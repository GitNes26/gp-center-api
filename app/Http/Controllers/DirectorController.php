<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Director;
use App\Models\ObjResponse;
use App\Models\DirectorView;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class DirectorController extends Controller
{
   /**
    * Mostrar lista de directores activos del
    * uniendo con roles.
    *
    * @return \Illuminate\Http\Response $response
    */
   public function index(Response $response)
   {
      $response->data = ObjResponse::DefaultResponse();
      try {
         $list = DirectorView::all();

         $response->data = ObjResponse::CorrectResponse();
         $response->data["message"] = 'peticion satisfactoria | lista de directores.';
         $response->data["alert_text"] = "directores encontrados";
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
         $list = DirectorView::select('id', 'username as label')
            ->orderBy('username', 'asc')->get();
         $response->data = ObjResponse::CorrectResponse();
         $response->data["message"] = 'peticion satisfactoria | lista de directores.';
         $response->data["alert_text"] = "directores encontrados";
         $response->data["result"] = $list;
         $response->data["toast"] = false;
      } catch (\Exception $ex) {
         $response->data = ObjResponse::CatchResponse($ex->getMessage());
      }
      return response()->json($response, $response->data["status_code"]);
   }

   /**
    * Crear o Actualizar director.
    *
    * @return \Illuminate\Http\Response $response
    */
   public function createOrUpdate($user_id, $request)
   {
      try {
         $director = Director::where('user_id', $request->user_id)->first();

         $id = null;
         if ($director) $id = $director->id;
         else $director = new Director();

         $duplicate = $this->validateAvailableData($request->phone, $request->license_number, $request->payroll_number, $id);
         if ($duplicate["result"] == true) {
            return $duplicate;
         }

         $director->user_id = $user_id;
         $director->name = $request->name;
         $director->paternal_last_name = $request->paternal_last_name;
         $director->maternal_last_name = $request->maternal_last_name;
         $director->phone = $request->phone;
         $director->license_number = $request->license_number;
         $director->license_type = $request->license_type;
         $director->license_due_date = $request->license_due_date;
         $director->payroll_number = $request->payroll_number;
         $director->department = $request->department;
         //  $director->department_id = $request->department_id;
         if ($request->community_id) $director->community_id = $request->community_id;
         if ($request->street) $director->street = $request->street;
         if ($request->num_ext) $director->num_ext = $request->num_ext;
         if ($request->num_int) $director->num_int = $request->num_int;

         $director->save();

         $avatar = $this->ImageUp($request, "avatar", "GPCenter/directors", $director->id, "avatar", true, "noAvatar");
         $img_license = $this->ImageUp($request, "img_license", "GPCenter/directors", $director->id, "licencia", true, "noLicense");
         $img_firm = $this->ImageUp($request, "img_firm", "GPCenter/directors", $director->id, "firma", true, "noFirm");
         if ($request->hasFile('avatar')) $director->avatar = $avatar;
         if ($request->hasFile('img_license')) $director->img_license = $img_license;
         if ($request->hasFile('img_firm')) $director->img_firm = $img_firm;

         $director->save();

         return $director;
      } catch (\Exception $ex) {
         error_log("Hubo un error al crear o actualizar el director -> " . $ex->getMessage());
         echo "Hubo un error al crear o actualizar el director -> " . $ex->getMessage();
      }
   }


   /**
    * Mostrar director.
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
         // $user = DirectorView::where('user_id', $request->user_id)
         $user = DirectorView::find($id);

         $response->data = ObjResponse::CorrectResponse();
         $response->data["message"] = 'peticion satisfactoria | director encontrado.';
         $response->data["alert_text"] = "Director encontrado";
         $response->data["result"] = $user;
      } catch (\Exception $ex) {
         $response = ObjResponse::CatchResponse($ex->getMessage());
      }
      return response()->json($response, $response->data["status_code"]);
   }


   public function validateAvailableData($phone, $license_number, $payroll_number, $id)
   {
      $checkAvailable = new UserController();
      // #VALIDACION DE DATOS REPETIDOS
      $duplicate = $checkAvailable->checkAvailableData('directors', 'phone', $phone, 'El número telefónico', 'phone', $id, "users");
      if ($duplicate["result"] == true) return $duplicate;
      $duplicate = $checkAvailable->checkAvailableData('directors', 'license_number', $license_number, 'El número de licencia', 'license_number', $id, "users");
      if ($duplicate["result"] == true) return $duplicate;
      $duplicate = $checkAvailable->checkAvailableData('directors', 'payroll_number', $payroll_number, 'El empleado (número de nómina) ya ha sido registrado', 'payroll_number', $id, "users");
      if ($duplicate["result"] == true) return $duplicate;
      return array("result" => false);
   }


   // private function ImageUp($request, $requestFile, $id, $poxFix, $create, $nameFake)
   // {
   //    $dir_path = "GPCenter/directors";
   //    $dir = public_path($dir_path);
   //    $img_name = "";
   //    if ($request->hasFile($requestFile)) {
   //       $img_file = $request->file($requestFile);
   //       $instance = new UserController();
   //       $img_name = $instance->ImgUpload($img_file, $dir, $dir_path, "$id-$poxFix");
   //    } else {
   //       if ($create) $img_name = "$dir_path/$nameFake.png";
   //    }
   //    return $img_name;
   // }
}
