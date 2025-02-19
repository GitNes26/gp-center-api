<?php

namespace App\Http\Controllers;

use App\Models\ObjResponse;
use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class UserInfoController extends Controller
{

    /**
     * Crear o Actualizar información del usuario.
     *
     * @return \Illuminate\Http\Response $response
     */
    public function createOrUpdate($user_id, $request)
    {
        try {
            $userInfo = UserInfo::where('user_id', $user_id)->first();

            $id = null;
            if ($userInfo) $id = $userInfo->id;
            else $userInfo = new UserInfo();

            $duplicate = $this->validateAvailableData($request->phone, $request->license_number, $request->payroll_number, $id);
            if ($duplicate["result"] == true) {
                return $duplicate;
            }

            $userInfo->user_id = $user_id;
            $userInfo->name = $request->name;
            $userInfo->paternal_last_name = $request->paternal_last_name;
            $userInfo->maternal_last_name = $request->maternal_last_name;
            $userInfo->phone = $request->phone;
            $userInfo->license_number = $request->license_number;
            $userInfo->license_type = $request->license_type;
            $userInfo->license_due_date = $request->license_due_date;
            $userInfo->payroll_number = $request->payroll_number;
            $userInfo->department = $request->department;
            //  $userInfo->department_id = $request->department_id;
            if ($request->community_id) $userInfo->community_id = $request->community_id;
            if ($request->street) $userInfo->street = $request->street;
            if ($request->num_ext) $userInfo->num_ext = $request->num_ext;
            if ($request->num_int) $userInfo->num_int = $request->num_int;

            $userInfo->save();

            $user = User::find($user_id);
            $dirPath = "GPCenter";
            if ($user->rol_id == 4) $dirPath .= "/mechanics";
            elseif ($user->rol_id == 5) $dirPath .= "/directors";
            elseif ($user->rol_id == 6) $dirPath .= "/drivers";
            elseif ($user->rol_id == 8) $dirPath .= "/voucherRequesters";


            $avatar = $this->ImageUp($request, "avatar", $dirPath, $userInfo->id, "avatar", true, "noAvatar");
            $img_license = $this->ImageUp($request, "img_license", $dirPath, $userInfo->id, "licencia", true, "noLicense");
            $img_firm = $this->ImageUp($request, "img_firm", $dirPath, $userInfo->id, "firma", true, "noFirm");
            if ($request->hasFile('avatar')) $userInfo->avatar = $avatar;
            if ($request->hasFile('img_license')) $userInfo->img_license = $img_license;
            if ($request->hasFile('img_firm')) $userInfo->img_firm = $img_firm;

            $userInfo->save();

            return $userInfo;
        } catch (\Exception $ex) {
            error_log("Hubo un error al crear o actualizar el userInfo -> " . $ex->getMessage());
            echo "Hubo un error al crear o actualizar el userInfo -> " . $ex->getMessage();
        }
    }


    public function validateAvailableData($phone, $license_number, $payroll_number, $id)
    {
        // #VALIDACION DE DATOS REPETIDOS
        $duplicate = $this->checkAvailableData('user_info', 'phone', $phone, 'El número telefónico', 'phone', $id, "users", true);
        if ($duplicate["result"] == true) return $duplicate;
        $duplicate = $this->checkAvailableData('user_info', 'license_number', $license_number, 'El número de licencia', 'license_number', $id, "users", true);
        if ($duplicate["result"] == true) return $duplicate;
        $duplicate = $this->checkAvailableData('user_info', 'payroll_number', $payroll_number, 'El empleado (número de nómina) ya ha sido registrado', 'payroll_number', $id, "users", true);
        if ($duplicate["result"] == true) return $duplicate;
        return array("result" => false);
    }
}