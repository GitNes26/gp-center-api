<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{

    /**
     * Crear o Actualizar información del usuario.
     *
     * @return \Illuminate\Http\Response $response
     */
    public function createOrUpdate($user_id, Request $request)
    {
        try {
            $employee = Employee::where('user_id', $user_id)->first();
            Log::info("EmployeeController ~ createOrUpdate ~ employee:" . $employee);
            $id = null;
            if ($employee) $id = $employee->id;
            else $employee = new Employee();

            $duplicate = $this->validateAvailableData($request->phone, $request->license_number, $request->payroll_number, $id);
            if ($duplicate["result"] == true) {
                return $duplicate;
            }

            // $employee->fill($request->all());

            $employee->user_id = $user_id;
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
            Log::info("EmployeeController ~ employee: " . $employee);

            $user = User::find($user_id);
            Log::info("EmployeeController ~ user: " . $user);
            $dirPath = "GPCenter";
            if (!is_null($request->dir)) $dirPath .= $request->dir;
            Log::info("EmployeeController ~ dirPath: " . $dirPath);

            $this->ImageUp($request, "avatar", $dirPath, $employee, "avatar", true, "noAvatar");
            // Log::info("EmployeeController ~ avatar: ".$avatar);
            $this->ImageUp($request, "img_license", $dirPath, $employee, "licencia", true, "noLicense");
            $this->ImageUp($request, "img_firm", $dirPath, $employee, "firma", true, "noFirm");

            return $employee;
        } catch (\Exception $ex) {
            error_log("Hubo un error al crear o actualizar el Employee -> " . $ex->getMessage());
            echo "Hubo un error al crear o actualizar el Employee -> " . $ex->getMessage();
        }
    }


    public function validateAvailableData($phone, $license_number, $payroll_number, $id)
    {
        // #VALIDACION DE DATOS REPETIDOS
        $duplicate = $this->checkAvailableData('employees', 'phone', $phone, 'El número telefónico', 'phone', $id, "users", true);
        if ($duplicate["result"] == true) return $duplicate;
        $duplicate = $this->checkAvailableData('employees', 'license_number', $license_number, 'El número de licencia', 'license_number', $id, "users", true);
        if ($duplicate["result"] == true) return $duplicate;
        $duplicate = $this->checkAvailableData('employees', 'payroll_number', $payroll_number, 'El empleado (número de nómina) ya ha sido registrado', 'payroll_number', $id, "users", true);
        if ($duplicate["result"] == true) return $duplicate;
        return array("result" => false);
    }
}