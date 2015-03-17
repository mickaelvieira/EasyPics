<?php
class EasyPics_File
{

    const GZIP_EXTENSION = 'gz';
    const TGZ_EXTENSION  = 'tgz';
    const ZIP_EXTENSION  = 'zip';
    const BZ2_EXTENSION  = 'bz2';
    const TAR_EXTENSION  = 'tar';
    const RAR_EXTENSION  = 'rar';

    const JPG_EXTENSION  = 'jpg';
    const JPEG_EXTENSION = 'jpeg';
    const GIF_EXTENSION  = 'gif';
    const PNG_EXTENSION  = 'png';

    const CLASS_NAMESPACE = 'EasyPics_File_';

    static function factory($path)
    {

        if (!is_dir($path) && !is_file($path)) {
            throw new Zend_Exception('['.$path.'] is not a directory or file');
        }

        $pathinfos = pathinfo($path);        
        $ext = (isset($pathinfos['extension'])) ? mb_convert_case($pathinfos['extension'], MB_CASE_LOWER) : null;

        if (is_dir($path)) {
            $suffixe = "Dir";
        }
        else if (!is_null($ext) && ($ext == self::GZIP_EXTENSION || $ext == self::TGZ_EXTENSION || $ext == self::ZIP_EXTENSION || $ext == self::BZ2_EXTENSION || $ext == self::TAR_EXTENSION || $ext == self::RAR_EXTENSION)) {
            $suffixe = "Archive";
        }
        else if (!is_null($ext) && $ext == self::JPEG_EXTENSION || $ext == self::JPG_EXTENSION || $ext == self::GIF_EXTENSION || $ext == self::PNG_EXTENSION) {
            $suffixe = "Image";
        }
        else {
            $suffixe = "File";
        }

        $classname = self::CLASS_NAMESPACE . $suffixe;

        return (class_exists($classname)) ? new $classname($path) : null;
    }

}
