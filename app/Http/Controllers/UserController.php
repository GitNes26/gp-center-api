<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ObjResponse;
use App\Models\User;
use App\Models\Employee;
use App\Models\UserView;
use App\Models\VoucherRequester;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{

   /**
    * Metodo para validar credenciales e
    * inicar sesión
    * @param Request $request
    * @return \Illuminate\Http\Response $response
    */
   public function login(Request $request, Response $response)
   {
      $field = 'username';
      $value = $request->username;
      if ($request->email) {
         $field = 'email';
         $value = $request->email;
      }

      $request->validate([
         $field => 'required',
         'password' => 'required'
      ]);
      //   $userAuth=Auth::user();

      $user = User::where("users.$field", "$value")->where('users.active', 1)
         ->join("roles", "users.role_id", "=", "roles.id")
         ->select("users.*", "roles.role", "roles.read", "roles.create", "roles.update", "roles.delete", "roles.more_permissions", "roles.page_index")
         ->orderBy('users.id', 'desc')
         ->first();

      // print_r("todo bien hasta aqui-----$user ");
      if ($user->role_id === 5) {
         $user = User::where("users.$field", "$value")->where('users.active', 1)
            ->join("roles", "users.role_id", "=", "roles.id")
            ->leftJoin('directors', 'users.id', '=', 'directors.user_id')
            ->select("users.*", "roles.role", "roles.read", "roles.create", "roles.update", "roles.delete", "roles.more_permissions", "directors.department", "roles.page_index")
            ->orderBy('users.id', 'desc')
            ->first();
      }


      $response->data = ObjResponse::CorrectResponse();
      if (!$user || !Hash::check($request->password, $user->password)) {
         // throw ValidationException::withMessages([
         //    'message' => 'Credenciales incorrectas',
         //    'alert_title' => 'Credenciales incorrectas',
         //    'alert_text' => 'Credenciales incorrectas',
         //    'alert_icon' => 'error',
         // ]);
         $response->data["message"] = 'peticion satisfactoria | usuario NO encontrado.';
         $response->data["result"]["token"] = null;
         $response->data["result"]["user"] = null;
         $response->data["alert_icon"] = "error";
         $response->data["alert_text"] = "Credenciales incorrectas";
      } else {
         $token = $user->createToken($user->email)->plainTextToken;
         $response->data["message"] = 'peticion satisfactoria | usuario logeado.';
         $response->data["result"]["token"] = $token;
         $response->data["result"]["user"] = $user;
      }
      return response()->json($response, $response->data["status_code"]);
   }

   /**
    * Metodo para cerrar sesión.
    * @param int $id
    * @return \Illuminate\Http\Response $response
    */
   public function logout(Response $response, bool $all_sessions = false)
   {
      try {
         //  DB::table('personal_access_tokens')->where('tokenable_id', $id)->delete();
         if (!$all_sessions) Auth::user()->currentAccessToken()->delete(); #Elimina solo el token activo
         else auth()->user()->tokens()->delete(); #Utilizar este en caso de que el usuario desee cerrar sesión en todos lados o cambie informacion de su usuario / contraseña

         $response->data = ObjResponse::CorrectResponse();
         $response->data["message"] = 'peticion satisfactoria | sesión cerrada.';
         $response->data["alert_title"] = "Bye!";
      } catch (\Exception $ex) {
         $response->data = ObjResponse::CatchResponse($ex->getMessage());
      }
      return response()->json($response, $response->data["status_code"]);
   }

   /**
    * Registrarse como jugador.
    *
    * @param  \Illuminate\Http\Request $request
    * @return \Illuminate\Http\Response $response
    */
   public function signup(Request $request, Response $response)
   {
      $response->data = ObjResponse::DefaultResponse();
      try {

         // if (!$this->validateAvailability('username',$request->username)->status) return;

         $new_user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => 3, //usuario normal

            'phone' => $request->phone,
            'license_number' => $request->license_number,
            'license_due_date' => $request->license_due_date,
            'payroll_number' => $request->payroll_number,
            'department_id' => $request->department_id,
            'name' => $request->name,
            'paternal_last_name' => $request->paternal_last_name,
            'maternal_last_name' => $request->maternal_last_name,
            'community_id' => $request->community_id,
            'street' => $request->street,
            'num_ext' => $request->num_ext,
            'num_int' => $request->num_int,
         ]);
         $response->data = ObjResponse::CorrectResponse();
         $response->data["message"] = 'peticion satisfactoria | usuario registrado.';
         $response->data["alert_text"] = "¡Felicidades! ya eres parte de la familia";
      } catch (\Exception $ex) {
         $response->data = ObjResponse::CatchResponse($ex->getMessage());
      }
      return response()->json($response, $response->data["status_code"]);
   }

   /**
    * Cambiar contraseña usuario.
    *
    * @param  \Illuminate\Http\Request $request
    * @return \Illuminate\Http\Response $response
    */
   public function changePasswordAuth(Request $request, Response $response)
   {
      $response->data = ObjResponse::DefaultResponse();
      try {
         $userAuth = Auth::user();
         $user = User::find($userAuth->id);

         $response->data = ObjResponse::CorrectResponse();
         if (!Hash::check($request->password, $user->password)) {
            $response->data["message"] = 'peticion satisfactoria | la contraseña actual no es correcta.';
            $response->data["alert_icon"] = "error";
            $response->data["alert_text"] = "La contraseña actual que ingresas no es correcta";
            return response()->json($response, $response->data["status_code"]);
         }

         $user->password = Hash::make($request->new_password);
         $user->save();

         auth()->user()->tokens()->delete(); #Utilizar este en caso de que el usuario desee cerrar sesión en todos lados o cambie informacion de su usuario / contraseña

         $response->data = ObjResponse::CorrectResponse();
         $response->data["message"] = 'peticion satisfactoria | contraseña actualizada.';
         $response->data["alert_text"] = "Contraseña actualizada - todas tus sesiones se cerraran para aplicar cambios.";
      } catch (\Exception $ex) {
         $response->data = ObjResponse::CatchResponse($ex->getMessage());
      }
      return response()->json($response, $response->data["status_code"]);
   }


   /**
    * Mostrar lista de usuarios activos del
    * uniendo con roles.
    *
    * @return \Illuminate\Http\Response $response
    */
   public function index(Response $response)
   {
      $response->data = ObjResponse::DefaultResponse();
      try {
         $userAuth = Auth::user();
         $list = $userAuth->role_id == 1 ? UserView::all() : UserView::where('active', true);

         $response->data = ObjResponse::CorrectResponse();
         $response->data["message"] = 'peticion satisfactoria | lista de usuarios.';
         $response->data["alert_text"] = "usuarios encontrados";
         $response->data["result"] = $list;
      } catch (\Exception $ex) {
         $response->data = ObjResponse::CatchResponse($ex->getMessage());
      }
      return response()->json($response, $response->data["status_code"]);
   }

   /**
    * Mostrar lista de usuarios activos por role
    * uniendo con roles.
    *
    * @return \Illuminate\Http\Response $response
    */
   public function indexByrole(Int $role_id, Response $response)
   {
      $response->data = ObjResponse::DefaultResponse();
      try {
         // $list = DB::select('SELECT * FROM users where active = 1');
         // User::on('mysql_gp_center')->get();
         $roleAuth = Auth::user()->role_id;
         $signo = "=";
         $signo = $role_id == 2 && $roleAuth == 1 ? "<=" : "=";


         $list = User::where('users.active', true)->where("role_id", $signo, $role_id)
            ->join('roles', 'users.role_id', '=', 'roles.id')
            // ->join('departments', 'users.department_id', '=', 'departments.id')
            ->select('users.*', 'roles.role')
            // ->select('users.*', 'roles.role', 'departments.department', 'departments.description as department_description')
            ->orderBy('users.id', 'desc')
            ->get();

         $response->data = ObjResponse::CorrectResponse();
         $response->data["message"] = 'peticion satisfactoria | lista de usuarios.';
         $response->data["alert_text"] = "usuarios encontrados";
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
         $list = User::where('active', true)
            ->select('users.id as id', 'users.username as label')
            ->orderBy('users.username', 'asc')->get();
         $response->data = ObjResponse::CorrectResponse();
         $response->data["message"] = 'peticion satisfactoria | lista de usuarios.';
         $response->data["alert_text"] = "usuarios encontrados";
         $response->data["result"] = $list;
         $response->data["toast"] = false;
      } catch (\Exception $ex) {
         $response->data = ObjResponse::CatchResponse($ex->getMessage());
      }
      return response()->json($response, $response->data["status_code"]);
   }

   /**
    * Crear o Actualziar usuario.
    *
    * @param  \Illuminate\Http\Request $request
    * @return \Illuminate\Http\Response $response
    */
   public function createOrUpdate(Request $request, Response $response)
   {
      $response->data = ObjResponse::DefaultResponse();
      try {
         // $token = $request->bearerToken();
         $id = $request->id;
         $secondTable = null;
         Log::info("el id: $id");
         DB::beginTransaction();

         #VALIDACIÓN DE CAMPOS EN USER
         $duplicate = $this->validateAvailableData($request->username, $request->email, $id, $secondTable);
         if ($duplicate["result"] == true) {
            $response->data = $duplicate;
            return response()->json($response);
         }

         $message_change_psw = "";
         # INSERT O UPDATE
         $user = User::find($id);
         if (!$user) $user = new User();
         $user->username = $request->username;
         $user->email = $request->email;
         if (strlen($request->password) > 0) {
            $user->password = Hash::make($request->password);

            DB::table('personal_access_tokens')->where('tokenable_id', $id)->delete(); #Utilizar este en caso de que el usuario desee cerrar sesión en todos lados o cambie informacion de su usuario / contraseña
            $message_change_psw = "Contraseña actualizada - todas tus sesiones se cerraran para aplicar cambios.";
         }
         $user->role_id = $request->role_id;
         $user->department_id = $request->department_id;
         $user->employee_id = $request->employee_id;
         $user->save();

         $response->data = ObjResponse::CorrectResponse();

         DB::commit();
         $response->data["message"] = $id > 0 ? "peticion satisfactoria | $request->objName editado." : "peticion satisfactoria | $request->objName registrado.";
         $response->data["alert_text"] = $id > 0 ? "$request->objName editado $message_change_psw" : "$request->objName registrado";
      } catch (\Exception $ex) {
         DB::rollBack();
         $msg = "UserController ~ createOrUpdate " . $ex->getMessage();
         Log::error($msg);
         $response->data = ObjResponse::CatchResponse($msg);
      }
      return response()->json($response, $response->data["status_code"]);
   }

   /**
    * Mostrar usuario.
    *
    * @param   int $id
    * @param  \Illuminate\Http\Request $request
    * @return \Illuminate\Http\Response $response
    */
   public function show(Request $request, Response $response)
   {
      $response->data = ObjResponse::DefaultResponse();
      try {
         // echo "el id: $request->id";
         // $user = User::where('users.id', $request->id)
         //    ->join('roles', 'users.role_id', '=', 'roles.id')
         //    // ->join('departments', 'users.department_id', '=', 'departments.id')
         //    ->select('users.*', 'roles.role')
         //    // ->select('users.*', 'roles.role', 'departments.department', 'departments.description as department_description')
         //    ->first();

         $user = User::where('users.id', $request->id)
            ->join("roles", "users.role_id", "=", "roles.id")
            ->select("users.*", "roles.role", "roles.read", "roles.create", "roles.update", "roles.delete", "roles.more_permissions")
            ->orderBy('users.id', 'desc')
            ->first();


         $response->data = ObjResponse::CorrectResponse();
         $response->data["message"] = 'peticion satisfactoria | usuario encontrado.';
         $response->data["alert_text"] = "Usuario encontrado";
         $response->data["result"] = $user;
      } catch (\Exception $ex) {
         $response = ObjResponse::CatchResponse($ex->getMessage());
      }
      return response()->json($response, $response->data["status_code"]);
   }

   /**
    * "Eliminar" (cambiar estado activo=false) usuario.
    *
    * @param  int $id
    * @return \Illuminate\Http\Response $response
    */
   public function destroy(int $id, Response $response)
   {
      $response->data = ObjResponse::DefaultResponse();
      try {
         User::find($id)
            ->update([
               'active' => false,
               'deleted_at' => date('Y-m-d H:i:s'),
            ]);
         $response->data = ObjResponse::CorrectResponse();
         $response->data["message"] = 'peticion satisfactoria | usuario eliminado.';
         $response->data["alert_text"] = "Usuario eliminado";
      } catch (\Exception $ex) {
         $response->data = ObjResponse::CatchResponse($ex->getMessage());
      }
      return response()->json($response, $response->data["status_code"]);
   }

   /**
    * Eliminar usuario o usuarios.
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
         User::whereIn('id', $request->ids)->update([
            'active' => false,
            'deleted_at' => date('Y-m-d H:i:s'),
         ]);
         $response->data = ObjResponse::CorrectResponse();
         $response->data["message"] = $countDeleted == 1 ? 'peticion satisfactoria | usuario eliminado.' : "peticion satisfactoria | usuarios eliminados ($countDeleted).";
         $response->data["alert_text"] = $countDeleted == 1 ? 'Usuario eliminado' : "Usuarios eliminados  ($countDeleted)";
      } catch (\Exception $ex) {
         $response->data = ObjResponse::CatchResponse($ex->getMessage());
      }
      return response()->json($response, $response->data["status_code"]);
   }

   /**
    * "Activar o Desactivar" (cambiar estado activo) usuario.
    *
    * @param  int $id
    * @return \Illuminate\Http\Response $response
    */
   public function DisEnableUser(Int $id, Int $active, Response $response)
   {
      $response->data = ObjResponse::DefaultResponse();
      try {
         User::where('id', $id)
            ->update([
               'active' => (bool)$active
            ]);

         $description = $active == "0" ? 'desactivado' : 'reactivado';
         $response->data = ObjResponse::CorrectResponse();
         $response->data["message"] = "peticion satisfactoria | usuario $description.";
         $response->data["alert_text"] = "Usuario $description";
      } catch (\Exception $ex) {
         $response->data = ObjResponse::CatchResponse($ex->getMessage());
      }
      return response()->json($response, $response->data["status_code"]);
   }


   private function validateAvailableData($username, $email, $id, $secondTable = null)
   {
      // #VALIDACION DE DATOS REPETIDOS
      $duplicate = $this->checkAvailableData('users', 'username', $username, 'El nombre de usuario', 'username', $id, $secondTable);
      if ($duplicate["result"] == true) return $duplicate;
      $duplicate = $this->checkAvailableData('users', 'email', $email, 'El correo electrónico', 'email', $id, $secondTable);
      if ($duplicate["result"] == true) return $duplicate;
      return array("result" => false);
   }

   // public function checkAvailableData($table, $column, $value, $propTitle, $input, $id, $secondTable = null)
   // {
   //    if ($secondTable) {
   //       $query = "SELECT count(*) as duplicate FROM $table t INNER JOIN $secondTable u ON t.user_id=u.id WHERE t.$column='$value' AND u.active=1;";
   //       if ($id != null) $query = "SELECT count(*) as duplicate FROM $table t INNER JOIN $secondTable u ON t.user_id=u.id WHERE t.$column='$value' AND u.active=1 AND t.id!=$id";
   //    } else {
   //       $query = "SELECT count(*) as duplicate FROM $table WHERE $column='$value' AND active=1";
   //       if ($id != null) $query = "SELECT count(*) as duplicate FROM $table WHERE $column='$value' AND active=1 AND id!=$id";
   //    }
   //    // echo "LA CONSULTA DEL checkAvailableData() -> $query";
   //    $result = DB::select($query)[0];
   //    //   var_dump($result->duplicate);
   //    if ((int)$result->duplicate > 0) {
   //       // echo "entro al duplicate";
   //       $response = array(
   //          "result" => true,
   //          "status_code" => 409,
   //          "alert_icon" => 'warning',
   //          "alert_title" => "$propTitle no esta disponible!",
   //          "alert_text" => "$propTitle no esta disponible! - $value ya existe, intenta con uno diferente.",
   //          "message" => "duplicate",
   //          "input" => $input,
   //          "toast" => false
   //       );
   //    } else {
   //       $response = array(
   //          "result" => false,
   //       );
   //    }
   //    return $response;
   // }
}