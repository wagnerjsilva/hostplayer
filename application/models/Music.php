<?php

class Application_Model_Music extends Zend_Db_Table_Abstract {

    protected $_name = 'songs';
    protected $_primary = 'id';

    public function getAllTracks() {

        $select = $this->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
        $select->setIntegrityCheck(false)
                ->joinLeft('album', 'album_id = album')
                ->order(array('album', 'track_number ASC'));

        $res = $this->fetchAll($select)->toArray();
        return $res;
    }

    public function getTrackUsingId($id) {

        if (!is_numeric($id)) {
            throw new Exception('You need to provide a numeric value for retrieving a song');
        } else {

            $select = $this->select();
            $select->where($this->getAdapter()->quoteInto('id = ?', $id));
            $res = $this->fetchAll($select)->toArray();

            return $res;
        }
    }

    public function getSongsUsingSearchTerm($term) {

        $select = $this->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
        $select->setIntegrityCheck(false)
                ->where('title LIKE "%' . $term . '%" OR album_name LIKE "%' . $term . '%" OR artist LIKE "%' . $term . '%" OR file LIKE "%' . $term . '%"')
                ->joinLeft('album', 'album_id = album')
                ->order(array('album', 'track_number ASC'));

        return $this->fetchAll($select)->toArray();
    }

    protected function getTrackUsingFileName($file) {

        $select = $this->select();
        $select->where('file = ?', $file);
        $res = $this->fetchAll($select)->toArray();
        return $res;
    }

    public function checkIfTrackAlreadyInDb($file) {

        $tracks = $this->getTrackUsingFileName($file);

        $res = (count($tracks) > 0) ? true : false;

        return $res;
    }
    
    
   
    public function smartReadFile($location, $filename, $mimeType = 'application/octet-stream') {        
        
        $location = utf8_decode($location);
        $filename = utf8_decode($filename);
        $filename = str_replace(array('"', "'", ' ', ','), '_', $filename);
        if (!file_exists($location)) {
            header("HTTP/1.1 404 Not Found");
            return;
        }

        $size = filesize($location);
        $time = date('r', filemtime($location));

        $fm = @fopen($location, 'rb');
        if (!$fm) {
            header("HTTP/1.1 505 Internal server error");
            return;
        }      
        
        $_SERVER['HTTP_RANGE'] = false;

        $begin = 0;
        $end = $size - 1;

        if (isset($_SERVER['HTTP_RANGE'])) {
            if (preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $_SERVER['HTTP_RANGE'], $matches)) {
                $begin = intval($matches[1]);
                if (!empty($matches[2])) {
                    $end = intval($matches[2]);
                }
            }
        }
        
        if (isset($_SERVER['HTTP_RANGE'])) {
            header('HTTP/1.1 206 Partial Content');
        } else {
            header('HTTP/1.1 200 OK');
        }

        header("Content-Type: $mimeType", true);
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Accept-Ranges: bytes');
        header('Content-Length:' . (($end - $begin) + 1));
        if (isset($_SERVER['HTTP_RANGE'])) {
            header("Content-Range: bytes $begin-$end/$size");
        }
        header("Content-Disposition: inline; filename=song.mp3");
        header("Content-Transfer-Encoding: binary");
        header("Last-Modified: $time");

        $cur = $begin;
        fseek($fm, $begin, 0);

        while (!feof($fm) && $cur <= $end && (connection_status() == 0)) {
            print fread($fm, min(1024 * 16, ($end - $cur) + 1));
            $cur += 1024 * 16;
        }
    }

}

?>