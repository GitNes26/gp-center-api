<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\ObjResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    // /**
    //  * Mostrar lista de categorias.
    //  *
    //  * @return \Illuminate\Http\Response $response
    //  */
    // public function index(Response $response)
    // {
    //     $response->data = ObjResponse::DefaultResponse();
    //     try {
    //         $auth = Auth::user();
    //         $list = VW_Category::orderBy('id', 'desc');
    //         if ($auth->role_id > 1) $list = $list->where("active", true);
    //         $list = $list->get();

    //         $response->data = ObjResponse::SuccessResponse();
    //         $response->data["message"] = 'Peticion satisfactoria | Lista de categorias.';
    //         $response->data["result"] = $list;
    //     } catch (\Exception $ex) {
    //         $response->data = ObjResponse::CatchResponse($ex->getMessage());
    //     }
    //     return response()->json($response, $response->data["status_code"]);
    // }

    // /**
    //  * Mostrar listado para un selector.
    //  *
    //  * @return \Illuminate\Http\Response $response
    //  */
    // public function selectIndex(Response $response)
    // {
    //     $response->data = ObjResponse::DefaultResponse();
    //     try {
    //         $list = VW_Category::where('active', true)
    //             ->select('id as id', 'category as label')
    //             ->orderBy('category', 'asc')->get();

    //         $response->data = ObjResponse::SuccessResponse();
    //         $response->data["message"] = 'peticion satisfactoria | lista de categorias.';
    //         $response->data["alert_text"] = "categorias encontrados";
    //         $response->data["result"] = $list;
    //         $response->data["toast"] = false;
    //     } catch (\Exception $ex) {
    //         $response->data = ObjResponse::CatchResponse($ex->getMessage());
    //     }
    //     return response()->json($response, $response->data["status_code"]);
    // }

    /**
     * Crear o Actualizar categorias.
     *
     * @param \Illuminate\Http\Request $request
     * @param Int $id
     * 
     * @return \Illuminate\Http\Response $response
     */
    public function createOrUpdateNotification(array $arrayData)
    {
        // Request $request, Response $response, Int $id = null, $internal = false,
        // $response->data = ObjResponse::DefaultResponse();
        try {
            Log::info("SASASASA createOrUpdateNotification");
            $notification = Notification::find($id);
            if (!$notification) $notification = new Notification();

            // $notification->fill($internal ? $arrayData->all() : $request->all());
            $notification->user_id = 1; #$arrayData->user_id;
            Log::info("SASASASA $notification");
            $notification->title = "CAMBIO DE ESTATUS"; #$arrayData->title;
            $notification->message = "Se cambio el estatus a OTRO"; #$arrayData->message;
            var_dump($notification);
            $notification->save();

            // $response->data = ObjResponse::CorrectResponse();
            // $response->data["message"] = $id > 0 ? 'peticion satisfactoria | categoria editada.' : 'peticion satisfactoria | categoria registrada.';
            // $response->data["alert_text"] = $id > 0 ? "Categoria editada" : "Categoria registrada";
        } catch (\Exception $ex) {
            error_log("Hubo un error al crear o actualizar el categoria ->" . $ex->getMessage());
            // $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        // return response()->json($response, $response->data["status_code"]);
    }

    // /**
    //  * Mostrar categoria.
    //  *
    //  * @param   int $id
    //  * @param  \Illuminate\Http\Request $request
    //  * @return \Illuminate\Http\Response $response
    //  */
    // public function show(Request $request, Response $response, Int $id, bool $internal = false)
    // {
    //     $response->data = ObjResponse::DefaultResponse();
    //     try {
    //         $category = VW_Category::find($id);
    //         if ($internal) return $category;

    //         $response->data = ObjResponse::SuccessResponse();
    //         $response->data["message"] = 'peticion satisfactoria | categoria encontrado.';
    //         $response->data["result"] = $category;
    //     } catch (\Exception $ex) {
    //         $response->data = ObjResponse::CatchResponse($ex->getMessage());
    //     }
    //     return response()->json($response, $response->data["status_code"]);
    // }

    // /**
    //  * "Eliminar" (cambiar estado activo=0) categoria.
    //  *
    //  * @param  int $id
    //  * @param  int $active
    //  * @return \Illuminate\Http\Response $response
    //  */
    // public function delete(Response $response, Int $id)
    // {
    //     $response->data = ObjResponse::DefaultResponse();
    //     try {
    //         Category::where('id', $id)
    //             ->update([
    //                 'active' => false,
    //                 'deleted_at' => date('Y-m-d H:i:s')
    //             ]);

    //         $response->data = ObjResponse::SuccessResponse();
    //         $response->data["message"] = "peticion satisfactoria | categoria eliminado.";
    //         $response->data["alert_text"] = "Categoria eliminado";
    //     } catch (\Exception $ex) {
    //         $response->data = ObjResponse::CatchResponse($ex->getMessage());
    //     }
    //     return response()->json($response, $response->data["status_code"]);
    // }

    // /**
    //  * "Activar o Desactivar" (cambiar estado activo=1/0).
    //  *
    //  * @param  int $id
    //  * @param  int $active
    //  * @return \Illuminate\Http\Response $response
    //  */
    // public function disEnable(Response $response, Int $id, string $active)
    // {
    //     $response->data = ObjResponse::DefaultResponse();
    //     try {
    //         Category::where('id', $id)
    //             ->update([
    //                 'active' => $active === "reactivar" ? 1 : 0
    //             ]);

    //         $description = $active == "0" ? 'desactivado' : 'reactivado';
    //         $response->data = ObjResponse::SuccessResponse();
    //         $response->data["message"] = "peticion satisfactoria | categoria $description.";
    //         $response->data["alert_text"] = "Categoria $description";
    //     } catch (\Exception $ex) {
    //         $response->data = ObjResponse::CatchResponse($ex->getMessage());
    //     }
    //     return response()->json($response, $response->data["status_code"]);
    // }

    // /**
    //  * Eliminar uno o varios registros.
    //  *
    //  * @param  \Illuminate\Http\Request $request
    //  * @return \Illuminate\Http\Response $response
    //  */
    // public function deleteMultiple(Request $request, Response $response)
    // {
    //     $response->data = ObjResponse::DefaultResponse();
    //     try {
    //         // echo "$request->ids";
    //         // $deleteIds = explode(',', $ids);
    //         $countDeleted = sizeof($request->ids);
    //         Category::whereIn('id', $request->ids)->update([
    //             'active' => false,
    //             'deleted_at' => date('Y-m-d H:i:s'),
    //         ]);
    //         $response->data = ObjResponse::SuccessResponse();
    //         $response->data["message"] = $countDeleted == 1 ? 'peticion satisfactoria | registro eliminado.' : "peticion satisfactoria | registros eliminados ($countDeleted).";
    //         $response->data["alert_text"] = $countDeleted == 1 ? 'Registro eliminada' : "Registros eliminados  ($countDeleted)";
    //     } catch (\Exception $ex) {
    //         $response->data = ObjResponse::CatchResponse($ex->getMessage());
    //     }
    //     return response()->json($response, $response->data["status_code"]);
    // }


    // /**
    //  * Funcion para validar que campos no deben de duplicarse sus valores.
    //  * 
    //  * @return ObjRespnse|false
    //  */
    // private function validateAvailableData($category, $id)
    // {
    //     // #VALIDACION DE DATOS REPETIDOS
    //     $duplicate = $this->checkAvailableData('categories', 'category', $category, 'La categoria', 'category', $id, null);
    //     if ($duplicate["result"] == true) return $duplicate;
    //     return array("result" => false);
    // }
}
