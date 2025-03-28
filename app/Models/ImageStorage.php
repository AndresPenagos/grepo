<?php
namespace App\Models;
class ImageStorage
{
    private $target_dir;

    /**
     * Crea una carpeta si no existe
     * 
     * @param folder el nombre de la carpeta donde se almacenará el archivo
     */
    public function __construct($folder)
    {
        $path = $_ENV['APP_PATH'];
        if (!is_dir($path)) {
            if(mkdir($path, 0755, true)){
            }else{
            }

        }
        $storage = $_ENV['APP_STORAGE'];
        $target_dir = $path . $storage .'/'.$folder;
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $this->target_dir = $target_dir;
    }

    /**
     * Toma un objeto de solicitud, un nombre de imagen y un nombre de archivo, y luego mueve la imagen
     * al directorio de destino.
     * 
     * @param request el objeto de solicitud
     * @param image_name el nombre del campo de imagen en el formulario
     * @param file_name El nombre del archivo que se va a cargar.
     * 
     * @return El nombre de archivo de la imagen.
     */
    public function storeImage($request, $image_name, $file_name = null)
    {
        
        $uploadedFiles = $request->getUploadedFiles();
        //validamos $image_name
        if (!isset($image_name)) {
            return null;
        }
        $photo = @$uploadedFiles[$image_name];
        if (!isset($photo)) {
            return null;
        }
        if ($file_name == null) {
            $file_name = $photo->getClientFilename();
        }
        $target_file = $this->target_dir .'/'. $file_name;
        $this->deleteImage($file_name);
        $photo->moveTo($target_file);
        return $file_name;
    }

    /**
     * Elimina la imagen del servidor.
     * 
     * @param file_name El nombre del archivo que desea eliminar.
     * 
     * @return el nombre de archivo de la imagen.
     */
    public function deleteImage($file_name)
    {
        $target_file = $this->target_dir . $file_name;
        
        if (file_exists($target_file)) {
            //peguntamod si es diferente a default.jpg
            if ($file_name != 'default.jpg') {
                return true;
            }
            unlink($target_file);
            return true;
        } else {
            return false;
        }
    }
}