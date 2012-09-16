<?php

class Application_Model_LibraryLoader extends Application_Model_Music {

    public function getTrackinfo($file) {

        $info = array();
        try {
            $id3 = new Zend_Media_Id3v2($file);
            $info['track_number'] = $id3->TRCK->text;
            $info['title'] = $id3->tit2->text;
            $info['album'] = $id3->TALB->text;
            //$info['composer'] = $id3->TCOM->text;
            $info['artist'] = $id3->TPE1->text;
            $info['year'] = $id3->TYER->text;
            return $info;
        } catch (Zend_Media_Id3_Exception $e) {
            //throw new Exception($e->getMessage());
            Zend_Registry::get('log')->info('Error parsing mp3 file: ' . $file);
            return false;
        }
    }

    public function reload($path=MUSIC_PATH) {
        
        set_time_limit('21600');

        $CurrentWorkingDirectory = dir($path);
        //read the current dir cotents
        while ($entry = $CurrentWorkingDirectory->read()) {
            //is the directory empty
            if ($entry != "." && $entry != "..") {
                //is the directory content as subdirectory, if it is load it's contents by calling this function again
                $current_entry = $path . DS . $entry;
                if (is_dir($current_entry)) {
                    #echo "<p><strong>" .$current_entry. "</strong></p>";
                    $this->reload($current_entry);
                } else {
                    //else foreach file that is an mp3                    
                    $file_info = pathinfo($current_entry);
                    if (strtolower($file_info['extension']) == 'mp3') {

                        //$file = preg_replace('[' . MUSIC_PATH . '/]', '', $current_entry);
                        //$file = utf8_encode($file);       
                        
                        $file = utf8_encode($current_entry); 
                        
                        if (!$this->checkIfTrackAlreadyInDb($file)) {
                             $track = $this->getTrackinfo($current_entry);
                             $track['file'] = $file;
                             $this->saveTrackInfoToDb($track);
                           /* try {
                                $track = $this->getTrackinfo($current_entry);
                                $track['file'] = $file;
                                $this->saveTrackInfoToDb($track);
                            } catch (Exception $e) {
                                Zend_Registry::get('log')->info('Error parsing ' . $current_entry . ' ' . $e->getMessage());
                                continue;
                            } */
                            # print '</pre>';                       
                        } 
                    }
                }
            }
        }
        $CurrentWorkingDirectory->close();
        //finalize the task by removing database entries to files that no longer exists        
    }
    
    public function removeDeadFileLinksFromDb () {
        $res = $this->getAllTracks();       
        foreach($res as $dbentry) {           
            //exit(print_s(MUSIC_PATH.'/'.$dbentry['file']));
            $file = $dbentry['file'];
            if(!file_exists(utf8_decode($file))) {
                //exit(print_s($file));               
                //$this->delete('id', $dbentry['id']);  
                $where = $this->getAdapter()->quoteInto('id = ?', $dbentry['id']);
                $this->delete($where);
                
                print_s('Deleted');
                print_s($dbentry);
            }
        }
        
    }

    protected function saveTrackInfoToDb($data) {
        
       
       /* 
        * Found some garbage on some songs I bought from amazon so I had to 
        * apply the fix below...it may not work so this may need to be revisited
        * Here we are replacing the track number and year to remove ??
        */      
        foreach($data as $k=>$v) {
            if($k == 'track_number' || $k == 'year') {   
                $data[$k] = utf8_decode($v);
                /*if(preg_match('{\?+}', $data[$k])) {
                   $data[$k] = preg_replace('{\?+}', '', $data[$k]);
                } */
                
                if(preg_match('/[^0-9,]|,[0-9]*$/',$data[$k])) {
                    $data[$k] = preg_replace('/[^0-9,]|,[0-9]*$/', '', $data[$k]);
                }
               
            }
        }

        $album = new Application_Model_Album;

        if (strlen($data['album'])) {

            $albumID = $album->getAlbumsUsingSearchTerm($data['album']);

            if (count($albumID) < 1) {
                $res = $album->insert(array("album_name" => $data['album']));
            } else {
                $res = $albumID[0]['album_id'];
            }
        } else {
            $res = 0;
        }     
        
        $data['album'] = $res;      

        if(!strlen($data['title'])) {
            $title = preg_replace('/.mp3/i', '', basename($data['file']));
            $data['title'] = $title;
        }       
       
        try {
            $this->insert($data);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

}

