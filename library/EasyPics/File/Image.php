<?php
class EasyPics_File_Image extends EasyPics_File_File
{

    protected $_mimeType;


    protected $_type;


    protected $_width;


    protected $_height;


    protected $_iptc = array();


    protected $_exifType;


    protected $_exif = array();


    protected $_exifFilter = array();


    protected $_image = null; // resource GD

    function __construct($path)
    {

        parent::__construct($path);


        $this->_exifFilter = $this->_getExifFilter();

        $iptc = array(); // 
        $info = @getimagesize($this->_path, $iptc);
        $this->_parseIptc($iptc);

        $this->_width = $info[0];
        $this->_height = $info[1];
        $this->_type = $info[2];
        $this->_mimeType = $info['mime'];
        $this->_size = filesize($this->_path);
        


        if (function_exists("exif_read_data")) {
            if ($this->_validForExif()) {

                $sections = @exif_read_data($this->_path, null, true, false);

                //Zend_Debug::dump($sections);

                $exif = $this->_exifSectionToArray($sections);

                foreach ($this->_exifFilter as $filter => $default) {

                    $this->_exif[$filter] = (array_key_exists($filter, $exif)) ? $exif[$filter] : $default;

                    if ($filter == "DateTimeOriginal" || $filter == "DateTimeDigitized") {
                        $this->_exif[$filter] = $this->_exifToSqlDate($this->_exif[$filter]);
                    }
                    else if ($filter == "Make") {
                        $this->_exif[$filter] = ucwords(mb_convert_case($this->_exif[$filter], MB_CASE_LOWER));
                    }
                    else if ($filter == "LightSource") {
                        $this->_exif['lightsource_value'] = $this->_getExifHumanValue($filter, $this->_exif[$filter]);
                    }
                    else if ($filter == "ExposureMode") {
                        $this->_exif['exposuremode_value'] = $this->_getExifHumanValue($filter, $this->_exif[$filter]);
                    }
                    else if ($filter == "ExposureProgram") {
                        $this->_exif['exposureprogram_value'] = $this->_getExifHumanValue($filter, $this->_exif[$filter]);
                    }
                    else if ($filter == "WhiteBalance") {
                        $this->_exif['whitebalance_value'] = $this->_getExifHumanValue($filter, $this->_exif[$filter]);
                    }
                    else if ($filter == "ExposureTime") {
                        $this->_exif['exposuretime_value'] = $this->_getExposureTime($this->_exif[$filter]);
                    }
                    else if ($filter == "FNumber") {
                        $this->_exif['fnumber_value'] = $this->_getFNumber($this->_exif[$filter]);
                    }
                }

                $this->_exifType = exif_imagetype($this->_path);
            }
        }

        $this->getDimensionAfterReorientation();
    }

    // iptc
    protected function _parseIptc($infoIptc)
    {
        if (isset($infoIptc) && is_array($infoIptc) && isset($infoIptc["APP13"])) {
            if ($iptc = iptcparse($infoIptc["APP13"])) {

                $entries = array(
                    "2#005" => "titre",
                    "2#025" => "keywords",
                    "2#040" => "instructions",
                    "2#055" => "date", // YYYYMMDD
                    "2#080" => "auteur",
                    "2#085" => "fonction",
                    "2#090" => "ville",
                    "2#095" => "departement",
                    "2#101" => "pays",
                    "2#105" => "titre",
                    "2#103" => "identificant_tache",
                    "2#110" => "fournisseur",
                    "2#115" => "source",
                    "2#116" => "copyright",
                    "2#120" => "description",
                    "2#122" => "auteur_description"
                );
                foreach ($iptc as $kEntry => $entry) {
                    if (is_array($entry)) {
                        if (array_key_exists($kEntry, $entries)) {
                            $str = "";
                            $value = $entries[$kEntry];
                            foreach ($entry as $k => $v) {
                                if (!empty($str)) {
                                    $str .= "|";
                                }
                                $str .= $v;
                            }
                            $this->_iptc[$value] = mb_convert_encoding($str, "UTF-8");
                        }
                    }
                }
            }
        }
    }

    protected function _validForExif()
    {
        return ($this->_type == IMAGETYPE_JPEG || $this->_type == IMAGETYPE_TIFF_MM || $this->_type == IMAGETYPE_TIFF_II);
    }
    
    protected function _exifSectionToArray($exif)
    {
        $array = array();
        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($exif));

        foreach($iterator as $k => $v) {

            if (!is_numeric($k)) {
                $k = preg_replace("/\./", "_", $k);
                $array[$k] = $v;
            }
        }
        return $array;
    }

    protected function _exifToSqlDate($exifDate)
    {		
        $sqlDate = "";
        $split = preg_split("/ /", $exifDate);
        if (count($split) > 0) {
            $sqlDate = preg_replace("/\:/", "-", $split[0]);
            if (count($split) > 1) {
                $sqlDate .= " ".$split[1];
            }
        }
        return $sqlDate;
    }

    public function getWidth()
    {
        return $this->_width;
    }

    public function getHeight()
    {
        return $this->_height;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function getSize()
    {
        return $this->_size;
    }

    public function getMimetype()
    {
        return $this->_mimeType;
    }
    
    public function getKeyUrl($user_id)
    {
        $filter = new EasyPics_Filter_StringUrl();
        $path = $filter->filter($this->getPath());

        return md5($user_id . "-" . $path . date("Y-m-d H:i:s"));
    }

    public function isLandscape()
    {
        return ($this->getWidth() > $this->getHeight());
    }

    public function isPortrait()
    {
        return ($this->getWidth() < $this->getHeight());
    }

    public function getIptc($name = "")
    {
        if (!empty($name)) {
            if (array_key_exists($name, $this->_iptc)) {
                return $this->_iptc[$name];
            }
        }
        else {
            return $this->_iptc;
        }
        return null;
    }

    // exif
    public function getExifData($name = null)
    {
        if (!is_null($name)) {
            return (array_key_exists($name, $this->_exif)) ? $this->_exif[$name] : '';
        }
        else {
            return $this->_exif;
        }
    }
    
    public function setExifData($name, $value)
    {
        if (isset($name) && isset($value)) {
            if (array_key_exists($name, $this->_exif)) {
                $this->_exif[$name] = $value;
            }
        }
        return $this;
    }

    public function getExifType()
    {
        return $this->_exifType;
    }

    public function resizeToHeight($filename = null, $height, $compression = 100)
    {
        $ratio = $height / $this->getHeight();
        $width = $this->getWidth() * $ratio;
        return $this->resize($filename, $width, $height, $compression);
    }

    public function getWidthByHeight($height)
    {
        $ratio = $height / $this->getHeight();
        $width = $this->getWidth() * $ratio;
        return $width;
    }

    public function resizeToWidth($filename = null, $width, $compression = 100)
    {
        $ratio = $width / $this->getWidth();
        $height = $this->getheight() * $ratio;
        return $this->resize($filename, $width, $height, $compression);
    }

    public function getHeightByWidth($width)
    {
        $ratio = $width / $this->getWidth();
        $height = $this->getheight() * $ratio;
        return $height;
    }

    public function scale($filename = null, $scale, $compression = 100)
    {
        $width = $this->getWidth() * $scale / 100;
        $height = $this->getheight() * $scale / 100;
        return $this->resize($filename, $width, $height, $compression);
    }

    public function resize($filename = null, $width, $height, $compression = 100)
    {
        $this->input();
        $this->orient(null);

        if ($this->isLandscape()) {
            $height = $this->getHeightByWidth($width);
        }
        else if ($this->isPortrait()) {
            $width = $this->getWidthByHeight($height);
        }

        $newImage = imagecreatetruecolor($width, $height);
        imagecopyresampled($newImage, $this->_image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());

        if (!is_null($filename)) {
            return ($this->output($newImage, $filename, $compression)) ? $this : null;
        }
        else {
            $this->_image = $newImage;
            return $this->_image;
        }
    }

    public function resizeAndCrop($filename = null, $width, $height, $compression = 100)
    {

        //$this->input(); // load resource from path
        //$this->orient(null);

        if ($this->isLandscape()) {

            $this->resizeToHeight(null, $height);
            $posX = (imagesx($this->_image) / 2) - ($width / 2);
            $posY = 0;
        }
        else if ($this->isPortrait()) {

            $this->resizeToWidth(null, $width);
            $posX = 0;
            $posY = (imagesy($this->_image) / 2) - ($height / 2);
        }
        else {

            $this->resize(null, $width, $height);
            $posX = 0;
            $posY = 0;
        }
        //$this->output($this->_image, $filename, $compression);
        //return ($this->output($this->_image, $filename, $compression)) ? $this : null;

        $newImage = imagecreatetruecolor($width, $height);
        imagecopyresampled($newImage, $this->_image, 0, 0, $posX, $posY, $width, $height, $width, $height);

        if (!is_null($filename)) {
            return ($this->output($newImage, $filename, $compression)) ? $this : null;
        }
        else {
            $this->_image = $newImage;
            return $this->_image;
        }
    }

    public function orient($destination = null)
    {
        $orientation = $this->getExifData("Orientation");
        switch ($orientation) {
            case 1: // nothing
            break;

            case 2: // horizontal flip
                return $this->flip($destination, 1);
            break;

            case 3: // 180 rotate left
                return $this->rotate($destination, 180);
            break;

            case 4: // vertical flip
                $this->flip($destination, 2);
            break;

            case 5: // vertical flip + 90 rotate right
                $this->flip($destination, 2);
                return $this->rotate($destination, -90);
            break;

            case 6: // 90 rotate right
                return $this->rotate($destination, -90);
            break;

            case 7: // horizontal flip + 90 rotate right
                $this->flip($destination, 1);
                return $this->rotate($destination, -90);
            break;

            case 8: // 90 rotate left
                return $this->rotate($destination, 90);
            break;
        }
    }

    public function rotate($filename = null, $degrees, $compression = 100)
    {
        $this->input();

        $newImage = imagerotate($this->_image, $degrees, 0);

        $this->setExifData('Orientation', 1);

        if (!is_null($filename)) {
            return ($this->output($newImage, $filename, $compression)) ? $this : null;
        }
        else {
            $this->_image = $newImage;
            return $this->_image;
        }
    }

    public function flip($filename = null, $flipType, $compression = 100)
    {

        $this->input();

        // http://php.net/manual/en/function.imagecopy.php#42803

        $mirror_horizontal = 1;
        $mirror_vertical = 2;
        $mirror_both = 3;

        $width = $this->getWidth();
        $height = $this->getHeight();
        $newImage = imagecreatetruecolor($width, $height);

        for ($x = 0 ; $x < $width ; $x++) {
            for ($y = 0 ; $y < $height ; $y++) {
                if ($flipType == $mirror_horizontal) {
                    imagecopy($newImage, $this->_image, $width-$x-1, $y, $x, $y, 1, 1);
                }
                if ($flipType == $mirror_vertical) {
                    imagecopy($newImage, $this->_image, $x, $height-$y-1, $x, $y, 1, 1);
                }
                if ($flipType == $mirror_both) {
                    imagecopy($newImage, $this->_image, $width-$x-1, $height-$y-1, $x, $y, 1, 1);
                }
            }
        }

        $this->setExifData('Orientation', 1);

        if (!is_null($filename)) {
            return ($this->output($newImage, $filename, $compression)) ? $this : null;
        }
        else {
            $this->_image = $newImage;
            return $this->_image;
        }
    }

    public function getDimensionAfterReorientation()
    {
        // http://www.impulseadventure.com/photo/exif-orientation.html
        $orientation = $this->getExifData("Orientation");

        if (!is_null($orientation) &&
            ($orientation == 5 ||
            $orientation == 6 ||
            $orientation == 7 ||
            $orientation == 8)) {

            $width = $this->getWidth();
            $height = $this->getHeight();

            $this->_width = $height;
            $this->_height = $width;
        }
        return $this;
    }

    public function input()
    {
        if (is_null($this->_image)) {
            try {

                if ($this->getType() == IMAGETYPE_JPEG) {
                    $this->_image = imagecreatefromjpeg($this->getPath());
                }
                else if ($this->getType() == IMAGETYPE_GIF) {
                    $this->_image = imagecreatefromgif($this->getPath());
                }
                else if ($this->getType() == IMAGETYPE_PNG) {
                    $this->_image = imagecreatefrompng($this->getPath());
                }
            }
            catch (Exception $e) {
                throw new Zend_Exception($e->getMessage());
            }
        }

        /*Zend_Debug::dump($this->_image);
        Zend_Debug::dump($this->_image);
        exit;*/

        if (!is_resource($this->_image) || get_resource_type($this->_image) != "gd") {
            throw new Zend_Exception("Invalid GD resource");
        }
        return $this->_image;
    }
    /*

    good : 100
    bad : 0

    */
    public function output($image, $filename, $compression = 100)
    {

        $result = false;

        if ($compression > 100) {
            $compression = 100;
        }
        else if ($compression < 0) {
            $compression = 0;
        }

        if (!is_resource($image) || get_resource_type($image)) {
            new Zend_Exception("Invalid GD resource");
        }
        /*var_dump($compression);
        exit;*/
        if ($this->getType() == IMAGETYPE_JPEG) {
            imageinterlace($image, true);

            $result = @imagejpeg($image, $filename, $compression);
        }
        else if ($this->getType() == IMAGETYPE_GIF) {
            $result = @imagegif($image, $filename);
        }
        else if ($this->getType() == IMAGETYPE_PNG) {

            $compression = ceil($compression / 10);
            if ($compression < 1) {
                $compression = 1;
            }
            $compression = (10 - $compression);

            $result = @imagepng($image, $filename, $compression);
        }
        imagedestroy($image);

        return $result;
    }

    protected function _getExposureTime($exposure)
    {
        $time = "";

    //	var_dump(preg_match("/\//", $exposure));

        if (preg_match("/\//", $exposure)) {

            $split = preg_split("/\//", $exposure);

            if (count($split) == 2) {

                $numerator = sprintf("%d", $split[0]);
                $denominator = sprintf("%d", $split[1]);
                if ($denominator > 0) {
                    //$time = round($numerator / $denominator, 4); // * 1000 -> ms

                    $time = round((($numerator / $denominator) * 1000), 4);

                }
            }
        }
        return $time;
    }

    protected function _getFNumber($value)
    {
        $fnumber = "";

    //	var_dump(preg_match("/\//", $exposure));

        if (preg_match("/\//", $value)) {

            $split = preg_split("/\//", $value);

            if (count($split) == 2) {

                $numerator = sprintf("%d", $split[0]);
                $denominator = sprintf("%d", $split[1]);
                if ($denominator > 0) {
                    $fnumber = round($numerator / $denominator, 1);
                }
            }
        }
        return $fnumber;
    }

    protected function _getExifHumanValue($exifData, $exifValue)
    {
        $correspondance = array(
            'LightSource' => array(
                0 => 'Unknown',
                1 => 'Daylight',
                2 => 'Fluorescent',
                3 => 'Tungsten', // Tungsten (incandescent light)
                4 => 'Flash',
                9 => 'Fine weather',
                10 => 'Cloudy weather',
                11 => 'Shade',
                12 => 'Daylight fluorescent', // Daylight fluorescent (D 5700 - 7100K)
                13 => 'Day white fluorescent', // Day white fluorescent (N 4600 - 5400K)
                14 => 'Cool white fluorescent', // Cool white fluorescent (W 3900 - 4500K)
                15 => 'White fluorescent', // White fluorescent (WW 3200 - 3700K)
                17 => 'Standard light A',
                18 => 'Standard light B',
                19 => 'Standard light C',
                20 => 'D55',
                21 => 'D65',
                22 => 'D75',
                23 => 'D50',
                24 => 'ISO studio tungsten',
                255 => 'Other light source',
            ),
            'Flash' => array(),
            'ExposureMode' => array(
                0 => 'Auto',
                1 => 'Manual',
                2 => 'Auto bracket'
            ),
            'ExposureProgram' => array(
                0 => 'Not defined',
                1 => 'Manual',
                2 => 'Normal', // Normal program
                3 => 'Aperture priority',
                4 => 'Shutter priority',
                5 => 'Creative', // (biased toward depth of field)
                6 => 'Action', //(biased toward fast shutter speed)
                7 => 'Portrait mode', //(for closeup photos with the background out of focus)
                8 => 'Landscape mode' //(for landscape photos with the background in focus)
            ),
            'WhiteBalance' => array(
                0 => 'Auto',
                1 => 'Manual'
            )
        );

        $value = "";
        if (array_key_exists($exifData, $correspondance)) {
            if (array_key_exists($exifValue, $correspondance[$exifData])) {
                $value = $correspondance[$exifData][$exifValue];
            }
        }
        return $value;
    }


    protected function _getExifFilter()
    {
        //http://www.exif.org/specifications.html
        //http://www.awaresystems.be/imaging/tiff/tifftags/privateifd/exif.html

        return array(
            //"FileName" => "",
            //"FileDateTime" => time(),
            //"FileSize" => 0,
            //"FileType",
            //"MimeType",
            /*"SectionsFound",*/
            //"html",
            //"Height",
            //"Width",
            "IsColor" => "N/A",
            //"ByteOrderMotorola",
            //"ApertureFNumber" => "",
            //"Thumbnail.FileType",
            //"Thumbnail.MimeType",
            "Make" => "N/A",
            "Model" => "N/A",
            "Orientation" => 1,
            //"XResolution",
            //"YResolution",
            //"ResolutionUnit",
            //"Software",
            //"DateTime" => time(),
            //"YCbCrPositioning",
            //"Exif_IFD_Pointer",
            //"GPS_IFD_Pointer",
            //"Compression",
            //"JPEGInterchangeFormat",
            //"JPEGInterchangeFormatLength",
            "ExposureTime" => "N/A",
            "FNumber" => "N/A",
            "ExposureProgram" => "N/A",
            "ISOSpeedRatings" => "N/A",
            //"ExifVersion",
            "DateTimeOriginal" => date("Y:m:d h:m:s"), // 2010:08:22 10:51:11
            "DateTimeDigitized" => date("Y:m:d h:m:s"), // 2010:08:22 10:51:11
            //"CompressedBitsPerPixel",
            //"ExposureBiasValue",
            //"MaxApertureValue",
            //"MeteringMode",
            "LightSource" => 0, // 0 = Unknown http://www.awaresystems.be/imaging/tiff/tifftags/privateifd/exif/lightsource.html
            "Flash" => 0, // 0 = Flash did not fire - http://www.awaresystems.be/imaging/tiff/tifftags/privateifd/exif/flash.html
            "FocalLength" => "N/A", // http://www.awaresystems.be/imaging/tiff/tifftags/privateifd/exif/focallength.html
            //"SubSecTime",
            //"SubSecTimeOriginal",
            //"SubSecTimeDigitized",
            //"FlashPixVersion",
            //"ColorSpace",
            //"ExifImageWidth",
            //"ExifImageLength",
            //"InteroperabilityOffset",
            //"SensingMethod",
            /*"FileSource",
            "SceneType",*/
            //"CustomRendered",
            "ExposureMode" => "N/A", // 0 = Auto exposure http://www.awaresystems.be/imaging/tiff/tifftags/privateifd/exif/exposuremode.html
            "WhiteBalance" => "N/A",  // 0 = Auto white balance http://www.awaresystems.be/imaging/tiff/tifftags/privateifd/exif/whitebalance.html
            //"DigitalZoomRatio",
            //"FocalLengthIn35mmFilm",
            //"SceneCaptureType",
            //"GainControl",
            //"Contrast",
            //"Saturation",
            //"Sharpness",
            //"SubjectDistanceRange",
            //"InterOperabilityIndex",
            //"InterOperabilityVersion"
        );
    }
}
