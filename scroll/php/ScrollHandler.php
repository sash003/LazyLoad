<?php

class ScrollHandler {
  
  public $limit = 10;
  
  function __construct() {
   try{
      $this->db=new PDO('mysql: host=localhost; dbname=scroll', 'root', '');
      $this->db->exec("SET NAMES 'utf8'"); 
      $this->db->exec("SET CHARACTER SET 'utf8'");
      $this->db->exec("SET SESSION collation_connection = 'utf8_general_ci'");
      
      $this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
      $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); 

    }catch(PDOException $err){
      echo 'Ошибка соединения ' . $err->getMessage(). '<br> 
            в файле '.$err->getFile().", строка ".$err->getLine(); 
      exit; 
    }
  }
  
  
  function select($values=array()){
    $query = "select * from `statti` limit ?, $this->limit";
    $stmt = $this->db->prepare($query);
    $stmt->bindValue(1, $values[0], PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
  }
  
  
  function is_ajax(){

		if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) &&
		   !empty($_SERVER["HTTP_X_REQUESTED_WITH"]) &&
		   strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest"){

			return true;
		}
  }
  
  
  function __destruct(){
   $this->db=null;
   unset($this);
  }
}