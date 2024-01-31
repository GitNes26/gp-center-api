<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\ObjResponse;
use App\Models\DriverView;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class DriverController extends Controller
{
   /**
    * Mostrar lista de conductores activos
    *
    * @return \Illuminate\Http\Response $response
    */
   public function index(Response $response)
   {
      $response->data = ObjResponse::DefaultResponse();
      try {
         $list = DriverView::all();

         $response->data = ObjResponse::CorrectResponse();
         $response->data["message"] = 'peticion satisfactoria | lista de conductores.';
         $response->data["alert_text"] = "conductores encontrados";
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
         $list = DriverView::select('id', 'username as label')
            ->orderBy('username', 'asc')->get();
         $response->data = ObjResponse::CorrectResponse();
         $response->data["message"] = 'peticion satisfactoria | lista de conductores.';
         $response->data["alert_text"] = "conductores encontrados";
         $response->data["result"] = $list;
         $response->data["toast"] = false;
      } catch (\Exception $ex) {
         $response->data = ObjResponse::CatchResponse($ex->getMessage());
      }
      return response()->json($response, $response->data["status_code"]);
   }

   /**
    * Crear o Actualizar conductor.
    *
    * @return \Illuminate\Http\Response $response
    */
   public function createOrUpdate($user_id, $request)
   {
      try {
         $driver = Driver::where('user_id', $request->user_id)->first();

         $id = null;
         if ($driver) $id = $driver->id;
         else $driver = new Driver();

         $duplicate = $this->validateAvailableData($request->phone, $request->license_number, $id);
         if ($duplicate["result"] == true) {
            return $duplicate;
         }

         $driver->user_id = $user_id;
         $driver->director_id = $request->director_id;
         $driver->name = $request->name;
         $driver->paternal_last_name = $request->paternal_last_name;
         $driver->maternal_last_name = $request->maternal_last_name;
         $driver->phone = $request->phone;
         $driver->license_number = $request->license_number;
         $driver->license_due_date = $request->license_due_date;
         $driver->payroll_number = $request->payroll_number;
         $driver->department_id = $request->department_id;
         $driver->community_id = $request->community_id;
         $driver->street = $request->street;
         $driver->num_ext = $request->num_ext;
         $driver->num_int = $request->num_int;

         $driver->save();

         $avatar = $this->ImageUp($request, "avatar", $driver->id, "avatar", true, "noAvatar");
         $img_license = $this->ImageUp($request, "img_license", $driver->id, "licencia", true, "noLicense");
         if ($request->hasFile('avatar')) $driver->avatar = $avatar;
         if ($request->hasFile('img_license')) $driver->img_license = $img_license;

         $driver->save();
         return $driver;
      } catch (\Exception $ex) {
         error_log("Hubo un error al crear o actualizar el conductor ->" . $ex->getMessage());
         echo "Hubo un error al crear o actualizar el conductor ->" . $ex->getMessage();
      }
   }


   /**
    * Mostrar conductor.
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
         // $user = DriverView::where('user_id', $request->user_id)
         $user = DriverView::find($id);


         $response->data = ObjResponse::CorrectResponse();
         $response->data["message"] = 'peticion satisfactoria | conductor encontrado.';
         $response->data["alert_text"] = "Conductor encontrado";
         $response->data["result"] = $user;
      } catch (\Exception $ex) {
         $response = ObjResponse::CatchResponse($ex->getMessage());
      }
      return response()->json($response, $response->data["status_code"]);
   }


   private function validateAvailableData($phone, $license_number, $id)
   {
      $checkAvailable = new UserController();
      // #VALIDACION DE DATOS REPETIDOS
      $duplicate = $checkAvailable->checkAvailableData('drivers', 'phone', $phone, 'El número telefónico', 'phone', $id, "users");
      if ($duplicate["result"] == true) return $duplicate;
      $duplicate = $checkAvailable->checkAvailableData('drivers', 'license_number', $license_number, 'El número de licencia', 'license_number', $id, "users");
      if ($duplicate["result"] == true) return $duplicate;
      return array("result" => false);
   }


   private function ImageUp($request, $requestFile, $id, $posFix, $create, $nameFake)
   {
      $dir_path = "GPCenter/drivers";
      $dir = public_path($dir_path);
      $img_name = "";
      if ($request->hasFile($requestFile)) {
         $img_file = $request->file($requestFile);
         $instance = new UserController();
         $img_name = $instance->ImgUpload($img_file, $dir, $dir_path, "$id-$posFix");
      } else {
         if ($create) $img_name = "$dir_path/$nameFake.png";
      }
      return $img_name;
   }
}