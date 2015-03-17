<?php
/**
 * 
 * 
 * @author mike
 *
 */

class EasyPics_Filter_Compress_Zip extends Zend_Filter_Compress_Zip {


    /**
     *
     * Parcours une archive zip retourne la liste des fichiers filtrés par extensions listées dans $allowed
     * Copie mes fichiers dans target si copied est à true
     * @param String $content
     * @param Boolean $copied
     * @param Array $allowed
     */
    public function parse($content, $copied = false, $allowed = array()){

        $oFile = null;
        $files = array();
        $tempDir = "../temp/";
        $archive = $this->getArchive();

        if (file_exists($content)){
            $archive = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, realpath($content));
        }
        elseif (empty($archive) || !file_exists($archive)) {
            require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('ZIP Archive not found');
        }
        $zip = new ZipArchive();
        $res = $zip->open($archive);

        $target = $this->getTarget();
        if (!empty($target) && !is_dir($target)) {
            $target = dirname($target);
        }
        if (!empty($target)) {
            $target = rtrim($target, '/\\') . DIRECTORY_SEPARATOR;
        }
        if (empty($target) || !is_dir($target)) {
            require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('No target for ZIP decompression set');
        }
        if ($res !== true){
            require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception($this->_errorString($res));
        }
        //var_dump($res);
        $numFiles = $zip->numFiles;
        for ($i = 0; $i < $numFiles; $i++){
            $stat = $zip->statIndex($i);
            $oFile = new stdClass();
            $oFile->name 	  = $stat['name'];
            $oFile->size 	  = $stat['size'];
            $oFile->comp_size = $stat['comp_size'];
            $oFile->mtime     = $stat['mtime'];
         //   var_dump($oFile);
            $parts = explode("/", $oFile->name);
            $oFile->name = trim($parts[count($parts) - 1]);
            $parts = explode(".", $oFile->name);
            $oFile->ext = mb_convert_case($parts[count($parts) - 1], MB_CASE_LOWER);
            
            if (!empty($allowed) && !in_array($oFile->ext, $allowed)){
                continue;
            }
            if ($stat['size'] > 0){
                // calcul le pourcentage de compression
                $oFile->percent = sprintf("%3.2f", (($stat['size'] - $stat['comp_size']) / $stat['size']) * 100);
                if ($copied && !is_null($target)){
                    // copie des données dans un fichier temporaire
                    $tempFile = tempnam($tempDir, "ADVANCED");
                    $fp = fopen($tempFile, "wb");
                    fwrite($fp, $zip->getFromIndex($i));
                    fclose($fp);
                    // copie du fichier temporaire vers l'emplacement final
                    if (@copy($tempFile, $target . $oFile->name)){
                        $oFile->copied = 1;
                        $oFile->path   = $target . $oFile->name;
                    }
                    else{
                        $oFile->copied = 0;
                        $oFile->path   = "";
                    }
                }
                else{
                    $oFile->copied = 0;
                    $oFile->path   = "";
                }
                array_push($files, $oFile);
            }  
        }
        $zip->close();  
        return $files;
    }
}
?>
