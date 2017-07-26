<?php

final class ScrollHandler {
  
  private $limit = 10;
  
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
  
  
  function select($start=null){
    if($start === null){
      $query = "select * from `statti` order by `id` desc limit $this->limit";
      $stmt = $this->db->prepare($query);
    }else{
      $query = "select * from `statti` where `id` between ? and ? order by `id` desc";
      $stmt = $this->db->prepare($query);
      $stmt->bindValue(1, $start - $this->limit, PDO::PARAM_INT);
      $stmt->bindValue(2, $start - 1, PDO::PARAM_INT);
    }
    
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