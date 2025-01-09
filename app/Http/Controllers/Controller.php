<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Funcion para guardar imagenes acorde al modelo.
     * @param Request $request
     * @param File $requestFile
     * @param String $dirPath
     * @param Number $id
     * @param String $PosFix
     * @param Boolean $create
     * @param String $nameFake
     * 
     * @return string
     */
    public function ImageUp($request, $requestFile, $dirPath, $id, $posFix, $create, $nameFake)
    {
        try {
            $dir = public_path($dirPath);
            $img_name = "";
            if ($request->hasFile($requestFile)) {
                // return "ImageUp->aqui todo bien 3";
                $img_file = $request->file($requestFile);
                $dir_path = "$dirPath/$id";
                $destination = "$dir/$id";
                $img_name = $this->ImgUpload($img_file, $destination, $dir_path, "$id-$posFix");
            } else {
                if ($create) $img_name = "$dirPath/$nameFake";
            }
            return $img_name;
        } catch (\Exception $ex) {
            $msg =  "Error al cargar imagen de documentos data: " . $ex->getMessage();
            error_log("$msg");
            return "$msg";
        }
    }
    /**
     * Funcion para guardar una imagen en directorio fisico, elimina y guarda la nueva al editar la imagen para no guardar muchas
     * imagenes y genera el path que se guardara en la BD
     * 
     * @param $image File es el archivo de la imagen
     * @param $destination String ruta donde se guardara fisicamente el archivo
     * @param $dir String ruta que mandara a la BD
     * @param $imgName String Nombre de como se guardará el archivo fisica y en la BD
     */
    public function ImgUpload($image, $destination, $dir, $imgName)
    {
        try {
            // return "ImgUpload->aqui todo bien";
            $type = "JPG";
            $permissions = 0777;

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
            $imgName = "$imgName.$type";
            $image->move($destination, $imgName);
            return "$dir/$imgName";
        } catch (\Error $err) {
            $msg = "error en imgUpload(): " . $err->getMessage();
            error_log($msg);
            return "$msg";
        }
    }

    /**
     * Funcion para verificar que los datos NO se dupliquen en las tablas correspondientes.
     * 
     * @return ObjRespnse|false
     */
    public function checkAvailableData($table, $column, $value, $propTitle, $input, $id, $secondTable = null)
    {
        if ($secondTable) {
            $query = "SELECT count(*) as duplicate FROM $table INNER JOIN $secondTable ON rol_id=rols.id WHERE $column='$value' AND active=1;";
            if ($id != null) $query = "SELECT count(*) as duplicate FROM $table t INNER JOIN $secondTable ON t.rol_id=rols.id WHERE t.$column='$value' AND active=1 AND t.id!=$id";
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
