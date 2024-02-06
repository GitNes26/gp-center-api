<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ObjResponse;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
      $user = User::where("users.$field", "$value")->where('users.active', 1)
         ->join("roles", "users.role_id", "=", "roles.id")
         ->select("users.*", "roles.role", "roles.read", "roles.create", "roles.update", "roles.delete", "roles.more_permissions")
         ->orderBy('users.id', 'desc')
         ->first();


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
   public function logout(Response $response)
   {
      try {
         //  DB::table('personal_access_tokens')->where('tokenable_id', $id)->delete();
         auth()->user()->tokens()->delete();

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
    * Mostrar lista de usuarios activos del
    * uniendo con roles.
    *
    * @return \Illuminate\Http\Response $response
    */
   public function index(Int $role_id, Response $response)
   {
      $response->data = ObjResponse::DefaultResponse();
      try {
         // $list = DB::select('SELECT * FROM users where active = 1');
         // User::on('mysql_gp_center')->get();
         //  $list = User::where('users.active', true)->where("role_id", ">=", $role_id)
         $list = User::where("role_id", ">=", $role_id)
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
   public function createOrUpdate(Request $request, Int $role_id = null, Response $response)
   {
      $response->data = ObjResponse::DefaultResponse();
      try {
         $token = $request->bearerToken();
         //  return $request;
         // return  "role_id:$role_id -- el user_id: $request->user_id -- email:$request->email --  y el id:$request->id";
         if ((int)$role_id <= 2) $id = (int)$request->id > 0 ? (int)$request->id : null;
         else $id = (int)$request->user_id > 0 ? (int)$request->user_id : null;
         $minus = "usuario";
         $mayus = "Usuario";
         $controller = null;
         $secondTable = null;
         //  if ($request->role_id == 5) $secondTable="directors";
         //  if ($request->role_id == 6) $secondTable="drivers";

         $duplicate = $this->validateAvailableData($request->username, $request->email, $id, $secondTable);
         if ($duplicate["result"] == true) {
            $response->data = $duplicate;
            return response()->json($response);
         }

         $user = User::find($id);
         if (!$user) $user = new User();

         $user->username = $request->username;
         $user->email = $request->email;
         if (strlen($request->password) > 0) $user->password = Hash::make($request->password);
         $user->role_id = $role_id;

         $user->save();
         $response->data = ObjResponse::CorrectResponse();

         if ($role_id == 1) {
            $minus = "super admin";
            $mayus = "Super Admin";
         } elseif ($role_id == 2) {
            $minus = "admin";
            $mayus = "Admin";
         } elseif ($role_id == 3) {
            $minus = "encargado de almacén";
            $mayus = "Encargado de Almacén";
         } elseif ($role_id == 4) {
            $minus = "mecánico";
            $mayus = "Mecánico";
         } elseif ($role_id == 5) {
            $minus = "director";
            $mayus = "Director";
            $controller = new DirectorController();
         } elseif ($role_id == 6) {
            $minus = "conductor";
            $mayus = "Conductor";
            $controller = new DriverController();
         }

         if ($controller) {
            $obj = $controller->createOrUpdate($user->id, $request);

            if ($obj["result"] == true) {
               $response->data = $obj;
               return response()->json($response);
            }
         }
         $response->data["message"] = $id > 0 ? "peticion satisfactoria | $minus editado." : "peticion satisfactoria | $minus registrado.";
         $response->data["alert_text"] = $id > 0 ? "$mayus editado" : "$mayus registrado";
      } catch (\Exception $ex) {
         $response->data = ObjResponse::CatchResponse($ex->getMessage());
      }
      return response()->json($response, $response->data["status_code"]);
   }

   /**
    * Crear usuario.
    *
    * @param  \Illuminate\Http\Request $request
    * @return \Illuminate\Http\Response $response
    */
   public function create(Request $request, Int $role_id, Response $response)
   {
      $response->data = ObjResponse::DefaultResponse();
      try {
         $token = $request->bearerToken();

         $duplicate = $this->validateAvailableData($request->username, $request->email, null);
         if ($duplicate["result"] == true) {
            $response->data = $duplicate;
            return response()->json($response);
         }

         $new_user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $role_id,
         ]);

         $response->data = ObjResponse::CorrectResponse();
         $response->data["message"] = 'peticion satisfactoria | usuario registrado.';
         $response->data["alert_text"] = "Usuario registrado";

         if ($role_id == 5) {
            $directorController = new DirectorController();
            $director = $directorController->createOrUpdate($new_user->id, $request);

            if ($director["result"] == true) {
               $response->data = $director;
               return response()->json($response);
            }

            $response->data["message"] = 'peticion satisfactoria | director registrado.';
            $response->data["alert_text"] = "Director registrado";
         }
      } catch (\Exception $ex) {
         $response->data = ObjResponse::CatchResponse($ex->getMessage());
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
         $user = User::where('users.id', $request->id)
            ->join('roles', 'users.role_id', '=', 'roles.id')
            // ->join('departments', 'users.department_id', '=', 'departments.id')
            ->select('users.*', 'roles.role')
            // ->select('users.*', 'roles.role', 'departments.department', 'departments.description as department_description')
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
    * Actualizar usuario.
    *
    * @param  \Illuminate\Http\Request $request
    * @return \Illuminate\Http\Response $response
    */
   public function update(Request $request, Response $response)
   {
      $response->data = ObjResponse::DefaultResponse();
      try {
         $duplicate = $this->validateAvailableData($request->username, $request->email, $request->id);
         if ($duplicate["result"] == true) {
            $response->data = $duplicate;
            return response()->json($response);
         }

         // echo "el id: $request->id";
         if ($request->role_id <= 2) {
            if (strlen($request->password) > 0)
               $new_user = User::find($request->id)
                  ->update([
                     'username' => $request->username,
                     'email' => $request->email,
                     'password' => Hash::make($request->password),
                     'role_id' => $request->role_id,
                     'department_id' => 1, //$request->department_id
                  ]);
            else
               $new_user = User::find($request->id)
                  ->update([
                     'username' => $request->username,
                     'email' => $request->email,
                     'role_id' => $request->role_id,
                     'department_id' => 1, //$request->department_id
                  ]);
         } else {
            if (strlen($request->password) > 0)
               $new_user = User::find($request->id)
                  ->update([
                     'username' => $request->username,
                     'email' => $request->email,
                     'password' => Hash::make($request->password),
                     'role_id' => $request->role_id,
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
            else
               $new_user = User::find($request->id)
                  ->update([
                     'username' => $request->username,
                     'email' => $request->email,
                     'role_id' => $request->role_id,
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
         }

         $response->data = ObjResponse::CorrectResponse();
         $response->data["message"] = 'peticion satisfactoria | usuario actualizado.';
         $response->data["alert_text"] = "Usuario actualizado";
      } catch (\Exception $ex) {
         $response->data = ObjResponse::CatchResponse($ex->getMessage());
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


   public function ImgUpload($image, $destination, $dir, $imgName)
   {
      try {
         $type = "JPG";
         $permissions = 0777;

         if (stripos("pdf", $image->getClientOriginalExtension()) !== false) {
            $type = "PDF";
            if (file_exists("$dir/$imgName.pdf")) {
               // Establecer permisos
               if (chmod("$dir/$imgName.pdf", $permissions)) {
                  @unlink("$dir/$imgName.pdf");
                  sleep(2);
               }
               $type = "PDF";
            } elseif (file_exists("$dir/$imgName.PDF")) {
               // Establecer permisos
               if (chmod("$dir/$imgName.PDF", $permissions)) {
                  @unlink("$dir/$imgName.PDF");
                  sleep(2);
               }
               $type = "pdf";
            }
         } else {
            if (file_exists("$dir/$imgName.PNG")) {
               // Establecer permisos
               if (chmod("$dir/$imgName.PNG", $permissions)) {
                  @unlink("$dir/$imgName.PNG");
               }
               $type = "JPG";
            } elseif (file_exists("$dir/$imgName.JPG")) {
               // Establecer permisos
               if (chmod("$dir/$imgName.JPG", $permissions)) {
                  @unlink("$dir/$imgName.JPG");
               }
               $type = "PNG";
            }
         }

         $imgName = "$imgName.$type";
         $image->move($destination, $imgName);
         return "$dir/$imgName";
      } catch (\Error $err) {
         error_log("error en imgUpload(): " . $err->getMessage());
      }
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

   public function checkAvailableData($table, $column, $value, $propTitle, $input, $id, $secondTable = null)
   {
      if ($secondTable) {
         $query = "SELECT count(*) as duplicate FROM $table t INNER JOIN $secondTable u ON t.user_id=u.id WHERE t.$column='$value' AND u.active=1;";
         if ($id != null) $query = "SELECT count(*) as duplicate FROM $table t INNER JOIN $secondTable u ON t.user_id=u.id WHERE t.$column='$value' AND u.active=1 AND t.id!=$id";
      } else {
         $query = "SELECT count(*) as duplicate FROM $table WHERE $column='$value' AND active=1";
         if ($id != null) $query = "SELECT count(*) as duplicate FROM $table WHERE $column='$value' AND active=1 AND id!=$id";
      }
      // echo $query;
      $result = DB::select($query)[0];
      //   var_dump($result->duplicate);
      if ((int)$result->duplicate > 0) {
         // echo "entro al duplicate";
         $response = array(
            "result" => true,
            "status_code" => 409,
            "alert_icon" => 'warning',
            "alert_title" => "$propTitle no esta disponible!",
            "alert_text" => "$propTitle no esta disponible! - $value ya existe, intenta con uno diferente.",
            "message" => "duplicate",
            "input" => $input,
            "toast" => false
         );
      } else {
         $response = array(
            "result" => false,
         );
      }
      return $response;
   }
}
