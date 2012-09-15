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

    /*
     * First give it the front controller object being used to manipulate the request
     * 
     * Then give it the full file path for a track on the server and it will return it as a http response
     */
    
    public function getTrackHttpResponseHeaders(Zend_Controller_Action $controller, $track) {
      
        
        $file = utf8_decode(MUSIC_PATH . "/" . $track['file']);
        $file_size = filesize($file);
        //$_SERVER['HTTP_RANGE'] = true;
        if (isset($_SERVER['HTTP_RANGE'])) {
            $partial_content = true;
            $range = explode("-", $_SERVER['HTTP_RANGE']);
            $offset = intval($range[0]);
            $length = intval($range[1]) - $offset;
        } else {
            $partial_content = false;
            $offset = 0;
            $length = $file_size;
        }

        //read the data from the file
        $handle = fopen($file, 'r');
        $buffer = '';
        fseek($handle, $offset);
        $buffer = fread($handle, $length);
        $md5_sum = md5($buffer);
        if ($partial_content)
            $data_size = intval($range[1]) - intval($range[0]);
        else
            $data_size = $file_size;
        fclose($handle);
        
        $controller->getResponse()
                ->setHeader('Content-Length', $data_size)
                ->setHeader('Content-md5', $md5_sum)
                ->setHeader('Accept-Ranges', 'bytes');

        if ($partial_content)
            $controller->getResponse()
                    ->setHeader('Content-Range', 'bytes ' . $offset . '-' . ($offset + $length) . '/' . $file_size);

        $controller->getResponse()
                ->setHeader('Connection', 'close')
                ->setHeader('Content-type', 'audio/mpeg')
                ->setHeader('Content-Disposition', 'inline; filename='.$track['id'].'.mp3');
        
        
      /*  $controller->getResponse()->setHeader('Expires', '', true);
        $controller->getResponse()->setHeader('Cache-Control', 'private', true);
        $controller->getResponse()->setHeader('Cache-Control', 'max-age=1');
        $controller->getResponse()->setHeader('Pragma', '', false); */



        $controller->getResponse()->setBody($buffer);
        flush();
    }

}

?>