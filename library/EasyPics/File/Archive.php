<?php
class EasyPics_File_Archive extends EasyPics_File_File
{

    const GZIP_EXTENSION = 'gz';
    const TGZ_EXTENSION  = 'tgz';
    const ZIP_EXTENSION  = 'zip';
    const BZ2_EXTENSION  = 'bz2';
    const TAR_EXTENSION  = 'tar';
    const RAR_EXTENSION  = 'rar';

    public function isArchive()
    {
        if ($this->_extension == self::GZIP_EXTENSION ||
            $this->_extension == self::TGZ_EXTENSION ||
            $this->_extension == self::ZIP_EXTENSION ||
            $this->_extension == self::TAR_EXTENSION ||
            $this->_extension == self::RAR_EXTENSION)
        {
            return true;
        }
        return false;
    }

    public function extract($destination)
    {
        if (!is_dir($destination)) {
            throw new Zend_Exception("Destination [".$destination."] is not a directory");
        }

        if ($this->isArchive()) {

            $extractor = new EasyPics_Archive($this->getPath(), $destination);
            $extractor->process();
        }
        else {
            throw new Zend_Exception('['.$this->getPath().'] is not an archive');
        }
    }
}
