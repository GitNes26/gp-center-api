<?php

namespace App\Http\Controllers;

use App\Models\Mechanic;
use App\Models\MechanicView;
use App\Models\ObjResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MechanicController extends Controller
{
    /**
     * Mostrar lista de mecanicos.
     *
     * @return \Illuminate\Http\Response $response
     */
    public function index(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $auth = Auth::user();
            $list = MechanicView::orderBy('full_name', 'desc');
            if ($auth->role_id > 1) $list = $list->where("active", true);
            $list = $list->get();

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Peticion satisfactoria | Lista de mecanicos.';
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
            $list = MechanicView::where('active', true)
                ->select('id as id', DB::raw("CONCAT(payroll_number,' - ',full_name) as label"))
                ->orderBy('full_name', 'asc')->get();

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | lista de mecanicos.';
            $response->data["alert_text"] = "mecanicos encontrados";
            $response->data["result"] = $list;
            $response->data["toast"] = false;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Crear o Actualizar mecanicos.
     *
     * @param \Illuminate\Http\Request $request
     * @param Int $id
     * 
     * @return \Illuminate\Http\Response $response
     */
    public function createOrUpdate(Request $request, Response $response, Int $id = null)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $duplicate = $this->validateAvailableData($request->payroll_number, $request->email, $request->phone, $id);
            if ($duplicate["result"] == true) {
                $response->data = $duplicate;
                return response()->json($response);
            }

            $mechanic = Mechanic::find($id);
            if (!$mechanic) $mechanic = new Mechanic();

            $mechanic->fill($request->all());
            $mechanic->active = true;
            $mechanic->save();

            $avatar = $this->ImageUp($request, "avatar", "GPCenter/mechanics", $mechanic->id, "avatar", $id > 0 ? false : true, "noAvatar");
            if ($request->hasFile('avatar')) $mechanic->avatar = $avatar;
            $mechanic->save();


            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = $id > 0 ? 'peticion satisfactoria | mecánico editada.' : 'peticion satisfactoria | mecánico registrada.';
            $response->data["alert_text"] = $id > 0 ? "Mecánico editado" : "Mecánico registrado";
        } catch (\Exception $ex) {
            error_log("Hubo un error al crear o actualizar el mecánico -> " . $ex->getMessage());
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Mostrar mecanico.
     *
     * @param   int $id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function show(Request $request, Response $response, Int $id, bool $internal = false)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $mechanic = MechanicView::find($id);
            if ($internal) return $mechanic;

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | mecánico encontrado.';
            $response->data["result"] = $mechanic;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * "Eliminar" (cambiar estado activo=0) mecanico.
     *
     * @param  int $id
     * @param  int $active
     * @return \Illuminate\Http\Response $response
     */
    public function delete(Response $response, Int $id)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            Mechanic::where('id', $id)
                ->update([
                    'active' => false,
                    'deleted_at' => date('Y-m-d H:i:s')
                ]);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = "peticion satisfactoria | mecanico eliminado.";
            $response->data["alert_text"] = "Mecánico eliminado";
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * "Activar o Desactivar" (cambiar estado activo=1/0).
     *
     * @param  int $id
     * @param  int $active
     * @return \Illuminate\Http\Response $response
     */
    public function disEnable(Response $response, Int $id, string $active)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $description = $active === "reactivar" ? 'reactivado' : 'desactivado';
            Mechanic::where('id', $id)
                ->update([
                    'active' => $active === "reactivar" ? 1 : 0
                ]);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = "peticion satisfactoria | mecanico $description.";
            $response->data["alert_text"] = "Mecánico $description";
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Eliminar uno o varios registros.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function deleteMultiple(Request $request, Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            // echo "$request->ids";
            // $deleteIds = explode(',', $ids);
            $countDeleted = sizeof($request->ids);
            Mechanic::whereIn('id', $request->ids)->update([
                'active' => false,
                'deleted_at' => date('Y-m-d H:i:s'),
            ]);
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = $countDeleted == 1 ? 'peticion satisfactoria | registro eliminado.' : "peticion satisfactoria | registros eliminados ($countDeleted).";
            $response->data["alert_text"] = $countDeleted == 1 ? 'Registro eliminado' : "Registros eliminados  ($countDeleted)";
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }


    /**
     * Funcion para validar que campos no deben de duplicarse sus valores.
     * 
     * @return ObjRespnse|false
     */
    private function validateAvailableData($payroll_number, $email, $phone, $id)
    {
        // #VALIDACION DE DATOS REPETIDOS
        $duplicate = $this->checkAvailableData('mechanics', 'payroll_number', $payroll_number, 'El nombre del mecánico', 'payroll_number', $id, null);
        if ($duplicate["result"] == true) return $duplicate;
        $duplicate = $this->checkAvailableData('mechanics', 'email', $email, 'El nombre del mecánico', 'email', $id, null);
        if ($duplicate["result"] == true) return $duplicate;
        $duplicate = $this->checkAvailableData('mechanics', 'phone', $phone, 'El nombre del mecánico', 'phone', $id, null);
        if ($duplicate["result"] == true) return $duplicate;
        return array("result" => false);
    }
}