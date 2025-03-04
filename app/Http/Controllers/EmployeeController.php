<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use App\Models\User;
use App\Models\Employee;
use App\Models\EmployeeView;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
   /**
    * Mostrar lista de empleados activos del
    * uniendo con roles.
    *
    * @return \Illuminate\Http\Response $response
    */
   public function index(Response $response)
   {
      $response->data = ObjResponse::DefaultResponse();
      try {
         $userAuth = Auth::user();
         $list = $userAuth->role_id == 1 ? EmployeeView::all() : EmployeeView::where('active', true)->get();

         $response->data = ObjResponse::CorrectResponse();
         $response->data["message"] = 'peticion satisfactoria | lista de empleados.';
         $response->data["alert_text"] = "empleados encontrados";
         $response->data["result"] = $list;
      } catch (\Exception $ex) {
         $msg = "EmployeesController ~ index: " . $ex->getMessage();
         Log::error($msg);
         $response->data = ObjResponse::CatchResponse($msg);
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
         $userAuth = Auth::user();
         $list = EmployeeView::where('active', true)->select("id as id", DB::raw("CONCAT(payroll_number,' - ',full_name) as label"))->orderBy("full_name", "asc")->get();

         $response->data = ObjResponse::CorrectResponse();
         $response->data["message"] = 'peticion satisfactoria | lista de empleados.';
         $response->data["alert_text"] = "empleados encontrados";
         $response->data["result"] = $list;
         $response->data["toast"] = false;
      } catch (\Exception $ex) {
         $msg = "EmployeesController ~ selectIndex: " . $ex->getMessage();
         Log::error($msg);
         $response->data = ObjResponse::CatchResponse($msg);
      }
      return response()->json($response, $response->data["status_code"]);
   }

   /**
    * Mostrar lista de empleados activos del
    * uniendo con roles.
    *
    * @return \Illuminate\Http\Response $response
    */
   public function show(Response $response, Int $id)
   {
      $response->data = ObjResponse::DefaultResponse();
      try {
         $userAuth = Auth::user();
         $employee = EmployeeView::find($id);

         $response->data = ObjResponse::CorrectResponse();
         $response->data["message"] = 'peticion satisfactoria |empleado encontrado.';
         $response->data["alert_text"] = "empleado encontrado";
         $response->data["result"] = $employee;
      } catch (\Exception $ex) {
         $msg = "EmployeesController ~ index: " . $ex->getMessage();
         Log::error($msg);
         $response->data = ObjResponse::CatchResponse($msg);
      }
      return response()->json($response, $response->data["status_code"]);
   }

   /**
    * Crear o Actualizar información del empleado.
    * @param  \Illuminate\Http\Request $request
    * @param  int $id
    * @return \Illuminate\Http\Response $response
    */
   public function createOrUpdate(Request $request, Response $response, $id = null)
   {
      try {
         $response->data = ObjResponse::DefaultResponse();
         $employee = Employee::find($id);
         if (!$employee) $employee = new Employee();
         // Log::info("EmployeeController ~ createOrUpdate ~ id:" . $id);
         // Log::info("EmployeeController ~ createOrUpdate ~ request:" . $request);
         // Log::info("EmployeeController ~ createOrUpdate ~ request2:" . json_encode($request));

         $duplicate = $this->validateAvailableData($request->phone, $request->license_number, $request->payroll_number, $id);
         if ($duplicate["result"] == true) {
            $response->data = $duplicate;
            return response()->json($response);
         }

         // $employee->fill($request->all());

         // $employee->user_id = $user_id;
         $employee->name = $request->name;
         $employee->paternal_last_name = $request->paternal_last_name;
         $employee->maternal_last_name = $request->maternal_last_name;
         $employee->phone = $request->phone;
         $employee->license_number = $request->license_number;
         $employee->license_type = $request->license_type;
         $employee->license_due_date = $request->license_due_date;
         $employee->payroll_number = $request->payroll_number;
         $employee->department = $request->department;
         //  $employee->department_id = $request->department_id;
         if ($request->community_id) $employee->community_id = $request->community_id;
         if ($request->street) $employee->street = $request->street;
         if ($request->num_ext) $employee->num_ext = $request->num_ext;
         if ($request->num_int) $employee->num_int = $request->num_int;

         // $employee->timestamps = false;
         $employee->save();
         // Log::info("EmployeeController ~ employee: " . $employee);


         $dirPath = "GPCenter";
         if (!is_null($request->dir)) $dirPath .= $request->dir;

         $this->ImageUp($request, "avatar", $dirPath, $employee, "avatar", true, "noAvatar");
         $this->ImageUp($request, "img_license", $dirPath, $employee, "licencia", true, "noLicense");
         $this->ImageUp($request, "img_firm", $dirPath, $employee, "firma", true, "noFirm");

         $response->data = ObjResponse::CorrectResponse();
         $response->data["message"] = $id > 0 ? "peticion satisfactoria | $request->objName editado." : "peticion satisfactoria | $request->objName registrado.";
         $response->data["alert_text"] = $id > 0 ? "$request->objName editado" : "$request->objName registrado";
      } catch (\Exception $ex) {
         $msg = "EmployeeController ~ createOrUpdate ~ Hubo un error al crear o actualizar el Employee -> " . $ex->getMessage();
         Log::error($msg);
         $response->data = ObjResponse::CatchResponse($msg);
      }
      return response()->json($response, $response->data["status_code"]);
   }

   /**
    * "Eliminar" (cambiar estado activo=false) empleado.
    *
    * @param  int $id
    * @return \Illuminate\Http\Response $response
    */
   public function destroy(int $id, Response $response)
   {
      $response->data = ObjResponse::DefaultResponse();
      try {
         Employee::find($id)
            ->update([
               'active' => false,
               'deleted_at' => date('Y-m-d H:i:s'),
            ]);
         $response->data = ObjResponse::CorrectResponse();
         $response->data["message"] = 'peticion satisfactoria | empleado eliminado.';
         $response->data["alert_text"] = "Usuario eliminado";
      } catch (\Exception $ex) {
         $response->data = ObjResponse::CatchResponse($ex->getMessage());
      }
      return response()->json($response, $response->data["status_code"]);
   }

   /**
    * Eliminar empleado o empleados.
    *
    * @param  int $id
    * @param  \Illuminate\Http\Request $request
    * @return \Illuminate\Http\Response $response
    */
   public function destroyMultiple(Request $request, Response $response)
   {
      $response->data = ObjResponse::DefaultResponse();
      try {
         // echo "$request->ids";
         // $deleteIds = explode(',', $ids);
         $countDeleted = sizeof($request->ids);
         Employee::whereIn('id', $request->ids)->update([
            'active' => false,
            'deleted_at' => date('Y-m-d H:i:s'),
         ]);
         $response->data = ObjResponse::CorrectResponse();
         $response->data["message"] = $countDeleted == 1 ? 'peticion satisfactoria | empleado eliminado.' : "peticion satisfactoria | empleados eliminados ($countDeleted).";
         $response->data["alert_text"] = $countDeleted == 1 ? 'Usuario eliminado' : "Usuarios eliminados  ($countDeleted)";
      } catch (\Exception $ex) {
         $response->data = ObjResponse::CatchResponse($ex->getMessage());
      }
      return response()->json($response, $response->data["status_code"]);
   }

   /**
    * "Activar o Desactivar" (cambiar estado activo) empleado.
    *
    * @param  int $id
    * @return \Illuminate\Http\Response $response
    */
   public function disEnable(Int $id, Int $active, Response $response)
   {
      $response->data = ObjResponse::DefaultResponse();
      try {
         Employee::where('id', $id)
            ->update([
               'active' => (bool)$active
            ]);

         $description = $active == "0" ? 'desactivado' : 'reactivado';
         $response->data = ObjResponse::CorrectResponse();
         $response->data["message"] = "peticion satisfactoria | empleado $description.";
         $response->data["alert_text"] = "Empleado $description";
      } catch (\Exception $ex) {
         $response->data = ObjResponse::CatchResponse($ex->getMessage());
      }
      return response()->json($response, $response->data["status_code"]);
   }


   public function validateAvailableData($phone, $license_number, $payroll_number, $id)
   {
      // #VALIDACION DE DATOS REPETIDOS
      $duplicate = $this->checkAvailableData('employees', 'phone', $phone, 'El número telefónico', 'phone', $id, null, false);
      if ($duplicate["result"] == true) return $duplicate;
      $duplicate = $this->checkAvailableData('employees', 'license_number', $license_number, 'El número de licencia', 'license_number', $id, null, false);
      if ($duplicate["result"] == true) return $duplicate;
      $duplicate = $this->checkAvailableData('employees', 'payroll_number', $payroll_number, 'El empleado (número de nómina) ya ha sido registrado,', 'payroll_number', $id, null, false);
      if ($duplicate["result"] == true) return $duplicate;
      return array("result" => false);
   }
}
