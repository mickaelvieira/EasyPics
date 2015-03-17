<?php
class EasyPics_Controller_Action_Helper_Import extends Zend_Controller_Action_Helper_Abstract
{

    public function __construct()
    {

    }

    public function addArchiveToAlbum(EasyPics_File_Archive $archive, Application_Model_Row_Album $album, $user)
    {
        $total = 0;
        $temp  = EasyPics::getTempDirectory($user);

        $archive->extract($temp->getPath());
        $files = $temp->read(array("jpg"));

        foreach ($files as $k => $path) {
            $source = EasyPics::getFile($path);
            if ($source instanceof EasyPics_File_Image) {
                $picture = $this->addPictureToAlbum($source, $album, $user);
                $total++;
            }
        }
        return $total;
    }

    public function addPictureToAlbum(EasyPics_File_Image $srcFile, Application_Model_Row_Album $album, $user = null)
    {
        $destName  = mb_convert_case($srcFile->getBasename(), MB_CASE_LOWER);
        $directory = EasyPics::getAlbumsPath($user, $album->id);


        if (!is_dir($directory)) {
            @mkdir($directory, 0777);
        }

        $destPath = $directory . $destName;

        if ($destPath = $srcFile->copy($destPath)) {

            $destFile = EasyPics::getFile($destPath);

            if ($destFile instanceof EasyPics_File_Image) {

                $datas = array();
                $mapperExif = $this->_getMapperExifDatas();
                $dimensions = $this->_getFinalDimensions($srcFile);
                $pictures = EasyPics::getModel("pictures");
                $pictures->setUser($user);

                foreach ($mapperExif as $data => $exif) {
                    $datas[$data] = $destFile->getExifData($exif);
                }

                $datas['name']               = $destFile->getBasename();
                $datas['title']              = $destFile->getBasename();
                $datas['album']              = $album->id;
                $datas['privacy']            = $album->privacy;
                $datas['path_to_original']   = $destFile->getRelativePath(EasyPics::getBaseUrl());
                $datas['mimetype']           = $destFile->getMimetype();
                $datas['original_file_size'] = $destFile->getSize();
                $datas['original_width']     = $destFile->getWidth();
                $datas['original_height']    = $destFile->getHeight();
                $datas['optimized_width']    = $dimensions->width;
                $datas['optimized_height']   = $dimensions->height;
                $datas['date_created']       = new Zend_Db_Expr('NOW()');
                $datas['date_modified']      = new Zend_Db_Expr('NOW()');

                $picture = $pictures->addPicture($datas);
            }
        }
        return $picture;
    }

    protected function _getMapperExifDatas()
    {
        return array(
            'is_color'              => 'IsColor',
            'with_flash'            => 'Flash',
            'aperture'              => 'FNumber',
            'aperture_value'        => 'fnumber_value',
            'exposure'              => 'ExposureTime',
            'exposuretime'          => 'exposuretime_value',
            'exposuremode'          => 'ExposureMode',
            'exposuremode_value'    => 'exposuremode_value',
            'exposureprogram'       => 'ExposureProgram',
            'exposureprogram_value' => 'exposureprogram_value',
            'lightsource'           => 'LightSource',
            'lightsource_value'     => 'lightsource_value',
            'focallength'           => 'FocalLength',
            'whitebalance'          => 'WhiteBalance',
            'whitebalance_value'    => 'whitebalance_value',
            'iso_speed_rating'      => 'ISOSpeedRatings',
            'manufacturer'          => 'Make',
            'model'                 => 'Model',
            'original_date_time'    => 'DateTimeOriginal',
            'digitized_date_time'   => 'DateTimeDigitized'
        );
    }

    protected function _getFinalDimensions(EasyPics_File_Image $image)
    {
        $config = EasyPics::getAppConfig();

        $dimension = new stdClass();
        $dimension->width = $config->easypics->imgSettings->full->width;
        $dimension->height = $config->easypics->imgSettings->full->height;

        if ($image->isLandscape()) { // landscape
            $dimension->height = $image->getHeightByWidth($dimension->width);
        }
        else if ($image->isPortrait()) { // portrait
            $dimension->width = $image->getWidthByHeight($dimension->height);
        }

        return $dimension;
    }

}
