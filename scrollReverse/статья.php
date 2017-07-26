Это техника называется «ленивая загрузка» (lazy loading).<br />
Начнём разбор полёта, пожалуй, с файла js/script.js:

<pre>
  
/* Переменная-флаг для отслеживания того, происходит ли в данный момент ajax-запрос. В самом начале даем ей значение false, т.е. запрос не в процессе выполнения */
var inProgress = false;

/* Обработчик скролла страницы */
$(document).on('scroll', function() {
  /* Если высота окна + высота прокрутки больше или равны высоте всего документа и ajax-запрос в настоящий момент не выполняется, то запускаем ajax-запрос */
  if($(window).scrollTop() + $(window).height() >= $(document).height() && !inProgress) {

    $.ajax({
      method: 'POST',
      data: {
        "start" : $("div").last().data("id") // берём id последней статьи (см. чуть ниже в index.php)
      },
      /* что нужно сделать до отправки запрса */
      beforeSend: function() {
        /* меняем значение флага на true, т.е. запрос сейчас в процессе выполнения */
        inProgress = true;
      }
      /* что нужно сделать по факту выполнения запроса */
      }).done(function(data){
        /* Преобразуем результат, пришедший от обработчика - преобразуем json-строку обратно в массив */
        data = jQuery.parseJSON(data);

        /* Если массив не пуст (т.е. статьи там есть) */
        if (data.length > 0) {

          /* Делаем проход по каждому результату, оказвашемуся в массиве, где в index попадает индекс текущего элемента массива, а в data - сама статья */
          $.each(data, function(index, data){

          /* Вставляем полученные данные на страницу, отформатировав их в html */
          $("<div data-id='" + data.id + "'><b>" + data.title + "</b><br />" + data.text + "</div>").appendTo("body").hide().fadeIn(1111);
          });

        /* По факту окончания запроса снова меняем значение флага на false */
        inProgress = false;
      
      }
    });
  }
});  

</pre>

index.php:

<pre>
  
<?php

  require 'php/ScrollHandler.php';
  $scrollHandler = new ScrollHandler();
   // здесь обработаем AJAX-запрос
  if($_POST['start'] && $scrollHandler->is_ajax()){
    $start = intval($_POST['start']);
    $statti = $scrollHandler->select($start);
    exit(json_encode($statti));
  }
   // без параметра $start будет сделана выборка последних статей 
  $statti = $scrollHandler->select();

?>


<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>AJAX-подгрузка контента</title>
  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/normalize/2.0.1/normalize.css">
  <link rel="stylesheet" href="css/style.css" />
</head>

<body>

  <?php
     // сделаем division-у атрибут data-id, который будет передаваться аяксом если до него прокрутили и скажет php-обработчику, с какой статьи нужно начинать выборку
    foreach($statti as $post){
      echo "<div data-id='".$post['id']."'>
        <b>".$post['title']."</b><br>"
        .$post['text'].
      "</div>";
    }
  ?>

  <script src="http://cdnjs.cloudflare.com/ajax/libs/prefixfree/1.0.6/prefixfree.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.0.0/jquery.min.js" type="text/javascript" charset="utf-8"></script>
  <script type="text/javascript" src="js/script.js"></script>
</body>
</html>

</pre>

Файл-обработчик php/ScrollHandler.php:

<pre>
  
<?php

final class ScrollHandler {
  
   // сколько статей выбирать из таблицы
  public $limit = 10;
  
  
   // подключение к бд
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
  
  
   // главная функция-обработчик, которая и делает выборку статей
  function select($start=null){
     
    if($start === null){
      $query = "select * from `statti` order by `id` desc limit $this->limit";
      $stmt = $this->db->prepare($query);
    }else{
      $query = "select * from `statti` where `id` between ? and ? order by `id` desc";
      $stmt = $this->db->prepare($query);
       // обезвреживание пришедших данных
      $stmt->bindValue(1, $start - $this->limit, PDO::PARAM_INT);
      $stmt->bindValue(2, $start - 1, PDO::PARAM_INT);
    }
    
    $stmt->execute();
     // возвратит массив из выбранных строк
    return $stmt->fetchAll();
  }
  
  
   // для выяснения самого факта аякс-запроса
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
  
</pre>

