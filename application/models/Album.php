<?php
class Application_Model_Album extends Zend_Db_Table_Abstract {
    
    
    protected $_name = 'album';
    protected $_primary = 'album_id';
   
    
    public function getAllAlbums () {
        
        $select = $this->select()
                       ->order('name');

        return $this->fetchAll($select);
    }
    
    
   public function getAlbumsUsingSearchTerm ($term) {    
      
        $select = $this->select()
                       ->where('album_name = "'.$term .'"');
        
        return $this->fetchAll($select)->toArray();
       
   }
 
}

?>