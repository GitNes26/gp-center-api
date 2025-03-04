<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\ObjResponse;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    /**
     * Mostrar lista de menus por rol activos.
     *
     * @return \Illuminate\Http\Response $response
     */
    public function MenusByRole(String $pages_read, Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $list = Menu::where('menus.active', true)
                ->orderBy('menus.order', 'asc')->get();
            if ($pages_read != "todas") {
                $menus_ids = rtrim($pages_read, ",");
                $menus_ids = explode(",", $menus_ids);
                // print_r($menus_ids) ;
                $list = Menu::where('menus.active', true)
                    ->whereIn("menus.id", $menus_ids)
                    ->orderBy('menus.order', 'asc')->get();
            }
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Peticion satisfactoria | Lista de menus por rol.';
            $response->data["result"] = $list;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Obtener id de la pagina por su url.
     *
     * @return \Illuminate\Http\Response $response
     */
    public function getIdByUrl(Request $request, Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $menu = Menu::where('url', $request->url)->where('active', 1)->select("id")->first();
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Peticion satisfactoria | Lista de menus.';
            $response->data["result"] = $menu;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Mostrar listado menus principales para un selector.
     *
     * @return \Illuminate\Http\Response $response
     */
    public function headersSelectIndex(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $list = Menu::where('active', true)->where('belongs_to', 0)
                ->select('menus.id as id', 'menus.menu as label')
                ->orderBy('menus.menu', 'asc')->get();
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Peticion satisfactoria | Lista de menus';
            $response->data["result"] = $list;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * "Activar o Desactivar" (cambiar estado activo) menu.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response $response
     */
    public function DisEnableMenu(Int $id, Int $active, Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            Menu::where('id', $id)
                ->update([
                    'active' => (bool)$active
                ]);

            $description = $active == "0" ? 'desactivado' : 'reactivado';
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = "peticion satisfactoria | menu $description.";
            $response->data["alert_text"] = "Menú $description";
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }
    //#region CRUD

    /**
     * Mostrar lista de menus activos.
     *
     * @return \Illuminate\Http\Response $response
     */
    public function index(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $list = Menu::leftJoin('menus as patern', 'menus.belongs_to', '=', 'patern.id')
                ->select('menus.*', 'patern.menu as patern')
                ->orderBy('menus.id', 'asc')->get();
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Peticion satisfactoria | Lista de menus.';
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
            $list = Menu::where('active', true)
                ->select('menus.id as id', 'menus.menu as label')
                ->orderBy('menus.menu', 'asc')->get();
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Peticion satisfactoria | Lista de menus';
            $response->data["result"] = $list;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }
    public function selectIndexToRoles(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $list = Menu::where('menus.active', true)->where('menus.belongs_to', '>', 0)
                ->leftJoin('menus as patern', 'menus.belongs_to', '=', 'patern.id')
                ->select(
                    "menus.id as id",
                    DB::raw("CONCAT(patern.menu,' : ', menus.menu) as label")
                )
                ->orderBy('menus.menu', 'asc')->get();
            // $sql = $list->toSql();
            // return $sql;
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Peticion satisfactoria | Lista de menus';
            $response->data["result"] = $list;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }


    /**
     * Mostrar listado para un selector (id=urls).
     *
     * @return \Illuminate\Http\Response $response
     */
    public function selectIndexUrl(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $list = Menu::where('active', true)
                ->select('menus.url as id', 'menus.menu as label')
                ->orderBy('menus.menu', 'asc')->get();
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Peticion satisfactoria | Lista de menus';
            $response->data["result"] = $list;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Crear o Actualizar prestamo de menu.
     *
     * @return \Illuminate\Http\Response $response
     */
    public function createOrUpdate(Request $request, Response $response, Int $id = null)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            // $duplicate = $this->validateAvailableData($request->menu, $id);
            // if ($duplicate["result"] == true) {
            //     return $duplicate;
            // }

            $menu = Menu::find($id);
            if (!$menu) $menu = new Menu();

            $menu->menu = $request->menu;
            $menu->caption = $request->caption;
            $menu->type = $request->type;
            $menu->belongs_to = $request->belongs_to;
            if ($request->url) $menu->url = $request->url;
            if ($request->icon) $menu->icon = $request->icon;
            $menu->order = $request->order;
            /* if ($request->others_permissions) */
            $menu->others_permissions = $request->others_permissions;
            $menu->show_counter = (bool)$request->show_counter;
            $menu->counter_name = $request->counter_name;
            if ($request->read_only) $menu->read_only = (bool)$request->read_only;
            if ($request->active) $menu->active = (bool)$request->active;

            $menu->save();

            // $new_others_permissions = "";
            // if (strlen($request->others_permissions) > 1) {
            //     $others_permissions = explode(",", $request->others_permissions);
            //     foreach ($others_permissions as $op) {
            //         $trim_op = trim($op);
            //         $new_others_permissions .= "$menu->id@$trim_op, ";
            //     }
            //     rtrim($new_others_permissions, ", ");
            //     return $new_others_permissions;
            // }
            // if ($request->others_permissions) $menu->others_permissions = $request->others_permissions;
            // $menu->save();


            $response->data = ObjResponse::CorrectResponse();

            $response->data["message"] = $id > 0 ? 'peticion satisfactoria | menu editado.' : 'peticion satisfactoria | menu registrado.';
            $response->data["alert_text"] = $id > 0 ? "Menú editado" : "Menú registrado";
        } catch (\Exception $ex) {
            error_log("Hubo un error al crear o actualizar el menu -> " . $ex->getMessage());
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }


    /**
     * Mostrar menu.
     *
     * @param   int $id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function show(Request $request, Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $menu = Menu::where('menus.id', $request->id)
                ->leftJoin('menus as patern', 'menus.belongs_to', '=', 'patern.id')
                ->select('menus.*', 'patern.menu as patern')
                ->first();

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | menu encontrado.';
            $response->data["result"] = $menu;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }


    /**
     * Eliminar (cambiar estado activo=false) menu.
     *
     * @param  int $id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function destroy(Request $request, Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            Menu::find($request->id)
                ->update([
                    'active' => false,
                    'deleted_at' => date('Y-m-d H:i:s'),
                ]);
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | menu eliminado.';
            $response->data["alert_text"] = 'Menú eliminado';
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }
    //#endregion CRUD

    private function validateAvailableData($menu, $id)
    {
        $checkAvailable = new UserController();
        // #VALIDACION DE DATOS REPETIDOS
        $duplicate = $checkAvailable->checkAvailableData('menus', 'menu', $menu, 'El nombre del menú', 'menu', $id, null);
        if ($duplicate["result"] == true) return $duplicate;
        return array("result" => false);
    }
}
