<?php 
$path = '../adminer/';
$db = 'adminer.php';
if(file_exists($path.$db)) {
  include($path.$db);
}else{
  chdir($path);
  if(is_writable(getcwd())) {
    $src = 'compile.php';
    if(file_exists($src)) {
      exec('php '.$src);
      if(file_exists($db)) {
        include($db);
      }
    }
  }else{
    echo 'Error: directory read only, unable to compile adminer';
  }
}
?>
