<?php $api = new API();
class API {
  function __construct() {
    if(!isset($_GET['app'])) $_GET['app'] = '';
    switch($_GET['app']) {
      case 'audio': $audio = new AUDIO(); break;
      default: header('HTTP/1.0 404 Not Found');break;
    }
  }
} #/API
class AUDIO {
  private $d = '';
  private $m = 'meta';
  private $l = 'Library';
  private $p = './mp3/';
  # required: youtube-dl
  # required: libav-tools
  function __construct() {
    if($_SERVER['REQUEST_METHOD']==='POST') {
      $res = false;
      if(isset($_COOKIE)) {
        if(isset($_COOKIE['t'])) {
          $token = $_COOKIE['t'];
          $key = parse_ini_file('../src/conf.ini');
          if(isset($key['key'])) {
            $shrapnel = explode('.',$token);
            if(count($shrapnel) == 2) {
              if($shrapnel[1] == base64_encode(hash_hmac('sha256',$shrapnel[0],$key['key']))) {
                $claim = json_decode(base64_decode($shrapnel[0]));

                $this->d = dirname(__FILE__);
                if(!isset($_POST['req'])) $_POST['req'] = '';
                switch($_POST['req']) {
                  default: $res = $this->fetch((isset($_POST['s'])?$_POST['s']:''),(isset($_POST['f'])?$_POST['f']:''),(isset($_POST['l'])?$_POST['l']:''));break;
                  case 'create': $res = $this->create($_POST['l']);break;
                  case 'update': $res = $this->update($_POST['f'],$_POST['l']);break;
                  case 'delete': $res = $this->delete($_POST['l']);break;
                  case 'download': $res = $this->download($_POST['d'],$_POST['q']);break;
                  case 'list': $res = $this->files($this->p,false);break;
                  case 'search': $res = $this->search($_POST['q']);break;
                  case 'edit': $res = $this->edit($_POST['n'],$_POST['f']);break;
                  case 'google': $res = $this->google($_POST['y']);break;
                  case 'trash': $res = $this->trash($_POST['f']);break;
                  case 'refresh': $res = $this->refresh();break;
                }
              }
            }
          }
        }
      }
      $this->json($res);
    }
  }
  private function create($req='') {
    $res = false;
    if($req!=$this->l && !empty($req) && !file_exists($this->p.$req.'.json')) {
      file_put_contents($this->p.$req.'.json',json_encode(array()));
      $res = true;
    }
    return $res;
  }
  private function update($f='',$l='') {
    $res = false;
    if($l != $this->l && !empty($l)) {
      if(file_exists($this->p.$l.'.json')) {
        $mp3=json_decode(file_get_contents($this->p.$this->l.'.json'),true);
        $list = json_decode(file_get_contents($this->p.$l.'.json'),true);
        $list[$f] = $mp3[$f];
        file_put_contents($this->p.$l.'.json',json_encode($list));
        $res = true;
      }
    }
    return $res;
  }
  private function delete($req='') {
    $res = false;
    if($req!=$this->l && !empty($req)) {
      unlink($this->p.$req.'.json');
      $res = true;
    }
    return $res;
  }
  private function download($url='',$n ='',$p = '') {
    $res = false;
    if(!empty($url) ) {
      exec($p.'youtube-dl --get-id '.$url,$id,$ret);
      if(!$ret) {
        if(count($id) == 1) {
          if(file_exists($this->p.$this->m)) $meta = json_decode(file_get_contents($this->p.$this->m),true);
          $meta[$id[0]] = $n;
          file_put_contents($this->p.$this->m,json_encode($meta));
          chdir($this->p);
          exec($p.'youtube-dl -w -x --id --audio-format mp3 '.$url,$dl,$err);
          if(!$err) {
            chmod($id[0].'.mp3',0666);
            chdir($this->d);
            $res = $this->refresh();
          }
        }else{
          echo 'Multiple IDs: Playlist?';
        }
      }else{
        if(empty($p)) $res = $this->install($url,$n);
      }
    }
    return $res;
  }
  private function install($url='',$n='') {
    $p = '/tmp/';
    $c = 'youtube-dl';
    if(!file_exists($p.$c)) {
      $data = '';
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, 'https://yt-dl.org/downloads/latest/youtube-dl');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 5);
      $data = curl_exec($ch);
      $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);
      if(!empty($data)) {
        file_put_contents($p.$c,$data);
        chmod($p.$c,0755);
      }
    }
    return $this->download($url,$n,$p);
  }
  private function edit($n='',$f='') {
    $res = false;
    if(file_exists($this->p.$this->l.'.json')) {
      $mp3=json_decode(file_get_contents($this->p.$this->l.'.json'),true);
      if(isset($mp3[$f])) {
        if(file_exists($this->p.$this->m)) $meta = json_decode(file_get_contents($this->p.$this->m),true);
        $meta[$mp3[$f]] = $n;
        file_put_contents($this->p.$this->m,json_encode($meta));
        $res = true;
      }
    }
    return $res;
  }
  private function search($req) {
    $res = false;
    if(!empty($req)) {
      $mp3 = json_decode(file_get_contents($this->p.$this->l.'.json'),true);
      if(file_exists($this->p.$this->m)) {
        $meta = json_decode(file_get_contents($this->p.$this->m),true);
      }
      foreach($mp3 as $k => $v) {
        $n = (isset($meta[$v]) ? $meta[$v] : $v);
        if(preg_match('/'.$req.'/i',$n)) {
          $res[] = array( 'f' => $k, 'n' => $n);
        }
      }
    }
    return $res;
  }
  private function fetch($req=false,$id='',$l='') {
    $res=false;
    if(empty($l)) $l = $this->l;
    if(file_exists($this->p.$l.'.json')) {
      $mp3=json_decode(file_get_contents($this->p.$l.'.json'),true);
    }else{
      # ???
      $this->refresh();
    }
    if(empty($id)) {
      $id = key($mp3);
    }else{
      if($req == 'true') {
        $r = array_rand($mp3,2);
        if($r[0]!=$id) {
          $id = $r[0];
        }else{
          $id = $r[1];
        }
      }else{
        while(key($mp3) !== $id) next($mp3);
        next($mp3);
        $id = key($mp3);
        if($id==null) $mp3 = false;
      }
    }
    if($mp3) {
      if(!file_exists($id)) {
        unset($mp3[$id]);
        file_put_contents($this->p.$l.'.json',json_encode($mp3));
        return $this->fetch($req,$id,$l);
      }
      if(file_exists($this->p.$this->m)) $meta=json_decode(file_get_contents($this->p.$this->m),true);
      $res = array('c'=>count($mp3),'f' => $id, 'n' => (isset($meta[$mp3[$id]]) ? $meta[$mp3[$id]] : $mp3[$id]));
    }
    return $res;
  }
  private function trash($req) {
    $res = false;
    if(file_exists($req)) {
      if(file_exists($this->p.$this->l.'.json')) {
        $mp3=json_decode(file_get_contents($this->p.$this->l.'.json'),true);
        if(file_exists($this->p.$this->m)) $meta = json_decode(file_get_contents($this->p.$this->m),true);
        if(isset($meta)) unset($meta[$mp3[$req]]);
        unlink($req);
        if(!file_exists($req)) {
          file_put_contents($this->p.$this->m,json_encode($meta));
          $res = $this->refresh();
        }
      }
    }
    return $res;
  }
  private function google($req) {
    if(!empty($req)) {
      //search soundcloud as well.
      $dom = new DOMDocument('1.0');
      @$dom->loadHTMLFile('https://www.google.com/search?q='.htmlentities('site:www.youtube.com '.$req));
      $res = array();
      $h = $dom->getElementsByTagName('h3');
      $i = $dom->getElementsByTagName('img');
      $c = $dom->getElementsByTagName('cite');
      foreach($h as $k => $v) {
        $res[$k]['n'] = $v->nodeValue;
      }
      foreach($i as $k => $v) {
        $res[$k]['i'] = $v->getAttribute('src');
      }
      foreach($c as $k => $v) {
        $res[$k]['u'] = $v->nodeValue;
      }
    }
    return $res;
  }
  private function files($req,$mp3=true) {
    $res = false;
    if(is_Dir($req)) {
      $res = array(); 
      $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($req));
      if($mp3) {
        foreach($rii as $file) {
          if($file->isDir()|| $file->getExtension() != 'mp3') continue;
          $res[$file->getPathname()] = $file->getBasename('.mp3'); 
        }
      }else{
        foreach($rii as $file) {
          if($file->isDir()|| $file->getExtension() != 'json') continue;
          $res[] = $file->getBasename('.json'); 
        }
      }
    }
    return $res;
  }
  private function refresh() {
    $res = false;
    $req=$this->files($this->p);
    if($req) {
      file_put_contents($this->p.$this->l.'.json',json_encode($req));
      $res = true;
    }
    return $res;
  }
  private function utf8ize($d) {
    if (is_array($d)) {
      foreach ($d as $k => $v) {
        $d[$k] = $this->utf8ize($v);
      }
    } else if (is_string ($d)) {
      return utf8_encode($d);
    }
    return $d;
  }
  private function json($req=false) {
    if($req) {
      header("Content-type: application/json");
      if(array_key_exists('callback', $_GET) == TRUE){
        $req=json_encode($req);
        print $_GET['callback']."(".$this->utf8ize($req).")"; 
      }else{
        echo json_encode($this->utf8ize($req));
      }
    }else{
      header('HTTP/1.0 404 Not Found');
    }
    exit;
  } 
} #/AUDIO ?>
