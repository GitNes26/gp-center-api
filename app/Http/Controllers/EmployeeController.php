<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{

    /**
     * Crear o Actualizar información del usuario.
     *
     * @return \Illuminate\Http\Response $response
     */
    public function createOrUpdate($user_id, $request)
    {
        try {
            $Employee = Employee::where('user_id', $user_id)->first();

            $id = null;
            if ($Employee) $id = $Employee->id;
            else $Employee = new Employee();

            $duplicate = $this->validateAvailableData($request->phone, $request->license_number, $request->payroll_number, $id);
            if ($duplicate["result"] == true) {
                return $duplicate;
            }

            $Employee->user_id = $user_id;
            $Employee->name = $request->name;
            $Employee->paternal_last_name = $request->paternal_last_name;
            $Employee->maternal_last_name = $request->maternal_last_name;
            $Employee->phone = $request->phone;
            $Employee->license_number = $request->license_number;
            $Employee->license_type = $request->license_type;
            $Employee->license_due_date = $request->license_due_date;
            $Employee->payroll_number = $request->payroll_number;
            $Employee->department = $request->department;
            //  $Employee->department_id = $request->department_id;
            if ($request->community_id) $Employee->community_id = $request->community_id;
            if ($request->street) $Employee->street = $request->street;
            if ($request->num_ext) $Employee->num_ext = $request->num_ext;
            if ($request->num_int) $Employee->num_int = $request->num_int;

            $Employee->save();

            $user = User::find($user_id);
            $dirPath = "GPCenter";
            if ($user->rol_id == 4) $dirPath .= "/mechanics";
            elseif ($user->rol_id == 5) $dirPath .= "/directors";
            elseif ($user->rol_id == 6) $dirPath .= "/drivers";
            elseif ($user->rol_id == 8) $dirPath .= "/voucherRequesters";


            $avatar = $this->ImageUp($request, "avatar", $dirPath, $Employee->id, "avatar", true, "noAvatar");
            $img_license = $this->ImageUp($request, "img_license", $dirPath, $Employee->id, "licencia", true, "noLicense");
            $img_firm = $this->ImageUp($request, "img_firm", $dirPath, $Employee->id, "firma", true, "noFirm");
            if ($request->hasFile('avatar')) $Employee->avatar = $avatar;
            if ($request->hasFile('img_license')) $Employee->img_license = $img_license;
            if ($request->hasFile('img_firm')) $Employee->img_firm = $img_firm;

            $Employee->save();

            return $Employee;
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
