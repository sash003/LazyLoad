<?php

  require 'php/ScrollHandler.php';
  $scrollHandler = new ScrollHandler();
  
  if($_POST['start'] && $scrollHandler->is_ajax()){
    $start = intval($_POST['start']);
    $statti = $scrollHandler->select($start);
    exit(json_encode($statti));
  }
  
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