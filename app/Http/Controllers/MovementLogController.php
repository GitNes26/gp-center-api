<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MovementLog;
use App\Models\ObjResponse;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class MovementLogController extends Controller
{
    /**
     * Mostrar lista de registro de movimientos activas.
     *
     * @return \Illuminate\Http\Response $response
     */
    public function index(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $list = MovementLog::where('movement_logs.active', true)
                ->join('users', 'movement_logs.user_id','=','users.id')
                ->select('movement_logs.*','users.username','users.email')
                ->orderBy('movement_logs.id', 'asc')->get();
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Peticion satisfactoria | Lista de registro de movimientos.';
            $response->data["result"] = $list;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Mostrar listado para un selector.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function selectIndex(Request $request,Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $list = MovementLog::where('movement_logs.active', true)
            // ->where('movement_logs.action', $request->action) // quiza implementar despues, como filtro
                ->select('movement_logs.id as id', 'movement_logs.model as label')
                ->orderBy('movement_logs.model', 'asc')->get();
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Peticion satisfactoria | Lista de registro de movimientos';
            $response->data["result"] = $list;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Crear registro de movimiento.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function create(Request $request)
    {
        try {
            $new_movement_log = new MovementLog();
            $new_movement_log->user_id = $request->user_id;
            $new_movement_log->action = $request->action;
            $new_movement_log->table = $request->table;
            if ($request->column != "") $new_movement_log->column = $request->column;
            $new_movement_log->id_register = $request->id_register;
            if ($request->previous_value != "")  $new_movement_log->previous_value = $request->previous_value;
            if ($request->comments != "") $new_movement_log->comments = $request->comments;

            $new_movement_log->save();
        } catch (\Exception $ex) {
            error_log($ex->getMessage());
        }
    }

    /**
     * Mostrar registro de movimiento.
     *
     * @param   int $id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function show(Request $request, Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $movement_log = MovementLog::where('movement_logs.id',$request->id)
                ->join('users', 'movement_logs.user_id','=','users.id')
                ->select('movement_logs.*','users.username','users.email')
                ->first();

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | registro de movimiento encontrado.';
            $response->data["result"] = $movement_log;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Actualizar registro de movimiento.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function update(Request $request, Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $duplicate = $this->validateAvailableData($request->model, $request->id);
            if ($duplicate["result"] == true) {
                $response->data = $duplicate;
                return response()->json($response);
            }

            $movement_log = MovementLog::find($request->id)
                ->update([
                    'user_id' => $request->user_id,
                    'action' => $request->action,
                    'table' => $request->table,
                    'column' => $request->column,
                    'id_register' => $request->id_register,
                    'previous_value' => $request->previous_value,
                    'comments' => $request->comments,
                ]);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | registro de movimiento actualizado.';
            $response->data["alert_text"] = 'registro de movimiento actualizado';
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Eliminar (cambiar estado activo=false) registro de movimiento.
     *
     * @param  int $id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function destroy(Request $request, Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            MovementLog::find($request->id)
                ->update([
                    'active' => false,
                    'deleted_at' => date('Y-m-d H:i:s'),
                ]);
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | registro de movimiento eliminado.';
            $response->data["alert_text"] = 'registro de movimiento eliminado';
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

}