<?php $api = new API();
class API {
  function __construct() {
    $res = false;
    if($_SERVER['REQUEST_METHOD']==='POST') {
      if(isset($_GET['app'])) {
        if(isset($_COOKIE)) {
          if(isset($_COOKIE['t'])) {
            $token = $_COOKIE['t'];
            $key = parse_ini_file('../src/conf.ini');
            if(isset($key['key'])) {
              $shrapnel = explode('.',$token);
              if(count($shrapnel) == 2) {
                if($shrapnel[1] == base64_encode(hash_hmac('sha256',$shrapnel[0],$key['key']))) {
                  $req = json_decode(base64_decode($shrapnel[0]));
                  switch($_GET['app']) {
                    case 'audio': $res = $this->audio($req); break;
                    case 'auth': $res = new AUTH($req); break;
                    case 'edit': $res = new EDIT($req); break;
                    case 'git': $res = new GIT($req); break;
                    case 'post': $res = new POST($req); break;
                    case 'ssh': $res = new SSH($req); break;
                    case 'video': $res = new VIDEO($req); break;
                  }
                }
              }
            }
          }
        }
      }else{
        $res = new AUTH(false);
      }
    }
    if($res) {
      header('Content-type: application/json');
      if(array_key_exists('callback', $_GET) == TRUE) {
        $res=json_encode($res);
        print $_GET['callback'].'('.$this->utf8ize($res).')'; 
      }else{
        echo json_encode($this->utf8ize($res));
      }
    }else{
      http_response_code(400);
    }
    exit;
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
  private function audio($req) {
    $res = false;
    $audio = new AUDIO($req);
    if(!isset($_POST['req'])) $_POST['req'] = '';
    switch($_POST['req']) {
      default: $res = $audio->fetch((isset($_POST['s'])?$_POST['s']:''),(isset($_POST['f'])?$_POST['f']:''),(isset($_POST['l'])?$_POST['l']:''));break;
        case 'create': $res = $audio->create($_POST['l']);break;
        case 'update': $res = $audio->update($_POST['f'],$_POST['l']);break;
        case 'delete': $res = $audio->delete($_POST['l']);break;
        case 'download': $res = $audio->download($_POST['d'],$_POST['q']);break;
        case 'list': $res = $this->files(false);break;
        case 'search': $res = $audio->search($_POST['q']);break;
        case 'edit': $res = $audio->edit($_POST['n'],$_POST['f']);break;
        case 'google': $res = $audio->google($_POST['y']);break;
        case 'trash': $res = $audio->trash($_POST['f']);break;
        case 'refresh': $res = $audio->refresh();break;
    }
    return $res;
  }
} #/API
class AUDIO {
  private $d = '';
  private $m = 'meta';
  private $l = 'Library';
  private $p = './mp3/';
  private $req = false;
  public $res = false;
  # required: youtube-dl
  # required: libav-tools
  function __construct($req) {
    $this->d = dirname(__FILE__);
  }
  #TODO don't expose all functions, move to API class?
  public function create($req='') {
    $res = false;
    if($req!=$this->l && !empty($req) && !file_exists($this->p.$req.'.json')) {
      file_put_contents($this->p.$req.'.json',json_encode(array()));
      $res = true;
    }
    return $res;
  }
  public function update($f='',$l='') {
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
  public function delete($req='') {
    $res = false;
    if($req!=$this->l && !empty($req)) {
      unlink($this->p.$req.'.json');
      $res = true;
    }
    return $res;
  }
  public function download($url='',$n ='',$p = '') {
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
  public function install($url='',$n='') {
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
  public function edit($n='',$f='') {
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
  public function search($req) {
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
  public function fetch($req=false,$id='',$l='') {
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
  public function trash($req) {
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
  public function google($req) {
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
  public function files($mp3=true) {
    $res = false;
    if(is_Dir($this->p)) {
      $res = array(); 
      $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->p));
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
  public function refresh() {
    $res = false;
    $req=$this->files();
    if($req) {
      file_put_contents($this->p.$this->l.'.json',json_encode($req));
      $res = true;
    }
    return $res;
  }
} #/AUDIO 
class AUTH {
  private $conf = '../src/conf.ini';
  private $auth = array();
  function __construct($req) {
      //set auth key ???
      $res = false;
      if(file_exists($this->conf)) {
        $this->auth = parse_ini_file($this->conf);
      }else{
        $s=str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
        $key='';
        foreach(array_rand($s, 32) as $k) $key .= $s[$k];
        $auth='[app]'."\n".'key = '.$key."\n";
        file_put_contents($this->conf,$auth);
        if(file_exists($this->conf)) {
          $this->auth = parse_ini_file($this->conf);
        }
      }
      if(!isset($_POST['req'])) $_POST['req'] = '';
      switch($_POST['req']) {
        case 'create': $res = $this->create();break;
        default: 
        case 'read': $res = $this->read(); break;
        case 'update': $res = $this->update();break;
        case 'delete': $res = $this->delete();break;
      }
      return $res;
  }
  private function create() {
    // create user only or host???
    $res = false;
    $user = false;
    $pass = false;
    if(isset($_POST['u'])) $user = $_POST['u'];
    if(isset($_POST['p'])) $pass = $_POST['p'];
    // || valid create token 
    if($user && $pass && (  count($this->auth) == 1 )) {
      $users = '[app]'."\n";
      foreach($this->auth as $k => $v) {
        $users.=$k.' = '.$v."\n";
      }
      $users.=$user.' = '.password_hash($pass,PASSWORD_DEFAULT)."\n";
      if(file_put_contents($this->conf,$users)) {
        mkdir('../src/audio/'.$user,0777);
        mkdir('../src/users/'.$user.'/public_html/',0755,true);
        mkdir('../src/video/'.$user);
        exec('ssh-keygen -b 2048 -t rsa -f ~/.ssh/'.$user.' -q -N "" -C "'.$user.'"',$res);
      }
    }
    return $res;
  }
  private function read() {
    //todo consider auth test
    $res = false;
    $token = false;
    if(isset($_COOKIE['t'])) $token = $_COOKIE['t'];
    if($token) {
      if(isset($this->auth['key'])) {
        $shrapnel = explode('.',$token);
        if(count($shrapnel) == 2) {
          if($shrapnel[1] == base64_encode(hash_hmac('sha256',$shrapnel[0],$this->auth['key']))) {
            $claim = json_decode(base64_decode($shrapnel[0]));
            $res = $this->profile($claim->User);
          }
        }
      }
    }else{
      $user = false;
      $pass = false;
      if(isset($_POST['u'])) $user = $_POST['u'];
      if(isset($_POST['p'])) $pass = $_POST['p'];
      if(isset($this->auth[$user])&&isset($this->auth['key'])) {
        if(password_verify($pass,$this->auth[$user])) {
          $res = $this->profile($user);
        }
      }
      if(count($this->auth)==1) {
        if(!$res) {
          if($this->create()) {
            $res = $this->profile($user);
          }
        }
      }
    }
    return $res;
  }
  private function profile($req) {
    $res = false;
    $json = array('User' => $req,'Expr' => date('U',strtotime('+1 day')));
    $claim = base64_encode(json_encode($json));
    $pub = '';
    $ssh = '/var/www/.ssh/'.$req.'.pub';
    if(file_exists($ssh)) {
      $pub = file_get_contents($ssh);
    }
    $res = array(
      'user' => $req,
      'pub' => $pub
    );
    setcookie('t',$claim.'.'.base64_encode(hash_hmac('sha256',$claim,$this->auth['key'])),time() + 86400);
    return $res;
  }
  private function update() {
    //update password
    //update git host ?
    //update ???
    $res = false;
    return $res;
  }
  private function delete($req='') {
    $res = false;
    return $res;
  }
} #/AUTH
class EDIT {
} #/EDIT
class GIT {
} #/GIT
class POST {
} #/POST
class SSH {
} #/SSH
class VIDEO {
} #/VIDEO
?>
