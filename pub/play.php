<?php 
  $p = './mp3/';
if(isset($_GET['playlist'])) {
  $f = urldecode($_GET['playlist']);
  if(file_exists($p.$f.'.json')) {
    foreach(json_decode(file_get_contents($p.$f.'.json')) as $k => $v) {
      echo '//';
      echo $_SERVER['HTTP_HOST'];
      echo substr($k,1);
      echo "\r\n";
    }
    //header("Content-Disposition: attachment; filename=".$f.".m3u");
  }
}else{
  foreach( glob($p.'*.json') as $k => $v) {
    $f = substr($v,6,-5);
    echo '<h2><a href="//';
    echo $_SERVER['HTTP_HOST'];
    echo $_SERVER['REQUEST_URI'];
    echo '?playlist=';
    echo urlencode($f);
    echo '">';
    echo $f;
    echo '</a></h2>';
  }
}
?>
