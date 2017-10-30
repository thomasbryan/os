<?php $api = new API();
class API {
  private $sys = array();
  private $roles = array();
  function __construct() {
		#$this->debug();
    date_default_timezone_set('America/Chicago');
    $res = false;
    if($_SERVER['REQUEST_METHOD']==='POST') {
      $this->sys = parse_ini_file('../src/sys.ini');
      $this->roles = parse_ini_file('../src/roles.ini');
      if(isset($_COOKIE)) {
        if(isset($_COOKIE['t'])) {
          $token = $_COOKIE['t'];
          $shrapnel = explode('.',$token);
          if(count($shrapnel) == 2) {
            if($shrapnel[1] == base64_encode(hash_hmac('sha256',$shrapnel[0],$this->sys['key']))) {
              $req = json_decode(base64_decode($shrapnel[0]));
              if(!isset($_GET['app'])) $_GET['app'] = 'auth';
              switch($_GET['app']) {
                case 'audio': $res = $this->audio($req); break;
                case 'auth': $res = $this->auth($req); break;
                case 'automagic': $res = $this->automagic($req); break;
                case 'edit': $res = new EDIT($req); break;
                case 'git': $res = $this->git($req); break;
                case 'post': $res = new POST($req); break;
                case 'ssh': $res = new SSH($req); break;
                case 'video': $res = new VIDEO($req); break;
              }
            }
          }
        }else{
          $res = $this->token();
        }
      }else{
        $res = $this->token();
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
  private function token() {
    $res = false;
    $user = false;
    $pass = false;
    if(isset($_POST['u'])) $user = $_POST['u'];
    if(isset($_POST['p'])) $pass = $_POST['p'];
    if($user && $pass) {
      $file = '../src/users.ini';
      $users = parse_ini_file($file);
      if(isset($users[$user])) {
        if(password_verify($pass,$users[$user])) {
          $res = $this->profile($user);
        }
      }else{
        if(count($users) < $this->sys['users']) {
          $users[$user] = password_hash($pass,PASSWORD_DEFAULT);
          $ini = '';
          foreach($users as $k => $v) {
            $ini.=$k.' = '.$v."\n";
          }
          if(file_put_contents($file,$ini)) {
            $this->roles[$user] = '';
            $ini = '';
            foreach($this->roles as $k => $v) {
              $ini.=$k.' = '.$v."\n";
            }
            if(file_put_contents('../src/roles.ini',$ini)) {
              mkdir('../src/audio/'.$user,0777);
              mkdir('../src/magic/'.$user);
              mkdir('../src/users/'.$user.'/public_html/',0755,true);
              mkdir('../src/video/'.$user);
              exec('ssh-keygen -b 2048 -t rsa -f ~/.ssh/'.$user.' -q -N "" -C "'.$user.'"',$res);
              $res = $this->profile($user);
            }
          }
        }
      }
    }
    return $res;
  }
  private function profile($req) {
    if(!isset($_COOKIE['t'])) {
      $json = array('User' => $req,'Expr' => date('U',strtotime('+1 day')));
      $claim = base64_encode(json_encode($json));
      file_put_contents('../src/auth.log','['.date('Y-m-d H:i:s').'] '.$req.':'.$_SERVER['REMOTE_ADDR'].':'.str_replace(':','=',$_SERVER['HTTP_USER_AGENT'])."\n",FILE_APPEND);
      setcookie('t',$claim.'.'.base64_encode(hash_hmac('sha256',$claim,$this->sys['key'])), time() + 86400);
    }
    $pub = '';
    $ssh = '/var/www/.ssh/'.$req.'.pub';
    if(!file_exists($ssh)) {
      exec('ssh-keygen -b 2048 -t rsa -f ~/.ssh/'.$req.' -q -N "" -C "'.$req.'"',$exec);
    }
    if(file_exists($ssh)) {
      $pub = file_get_contents($ssh);
    }
    exec('grep "'.$req.'" ../src/auth.log',$logs);
    return array(
      'user' => $req,
      'pub' => $pub,
      'logs' => array_slice($logs,-5),
    );
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
        case 'list': $res = $audio->files(false);break;
        case 'search': $res = $audio->search($_POST['q']);break;
        case 'edit': $res = $audio->edit($_POST['n'],$_POST['f']);break;
        case 'google': $res = $audio->google($_POST['y']);break;
        case 'trash': $res = $audio->trash($_POST['f']);break;
        case 'refresh': $res = $audio->refresh();break;
    }
    return $res;
  }
  private function auth($req) {
    $res = false;
    if(!isset($_POST['req'])) $_POST['req'] = '';
    switch($_POST['req']) {
      case 'softwareupdate': $res = $this->authSoftwareUpdate();break;
      case 'logout': $res = $this->authLogout();break;
      default: 
      case 'read': $res = $this->profile($req->User);break;
      case 'update': $res = $this->authUpdate();break;
      case 'delete': $res = $this->authDelete();break;
    }
    return $res;
  }
  private function authLogout() {
    if(isset($_COOKIE['t'])) {
      //todo clear logs.
      setcookie('t','',0);
      return true;
    }
  }
  private function authSoftwareUpdate() {
		return touch('../src/update');
  }
  private function authUpdate() {
    //update password
    //update git host ?
    //update ???
    $res = false;
    return $res;
  }
  private function authDelete($req='') {
    $res = false;
    return $res;
  }
  private function automagic($req) {
    $res = false;
    chdir('../src/magic/'.$req->User.'/');
    if(!isset($_POST['req'])) $_POST['req'] = '';
    switch($_POST['req']) {
      case 'createWorkflows':
        $file = $_POST['n'].'.json';
        if(!file_exists($file)) {
          $data = '';
          if(isset($_POST['d'])) $data = $_POST['d'];
          if(file_put_contents($file,json_encode($data))) {
            $res = 'workflows';
          }
        }
      break;
      case 'listWorkflows':
        $res = array('workflows'=>array());
        foreach(glob('*.json') as $file) {
          $res['workflows'][] = substr($file,0,-5);
        }
      break;
      case 'readWorkflows':
        $file = $_POST['n'].'.json';
        if(file_exists($file)) {
          $out = glob($file.'-*');
          $resp = '';
          if(count($out) > 0) $resp = file_get_contents(end($out));
          $res = array(
            'n'=>$_POST['n'],
            'req'=>json_decode(file_get_contents($file)),
            'res'=>$resp,
          );
        }
      break;
      case 'runWorkflows':
        $file = $_POST['n'].'.json';
        if(file_exists($file)) {
          exec('/usr/bin/php '.dirname(dirname(__FILE__)).'/src/app.php '.$req->User.'/'.$file.' > /dev/null 2>/dev/null &');
          $res = 'Workflow Ran';
        }
      break;
      case 'deleteWorkflows':
        $file = $_POST['n'].'.json';
        if(file_exists($file)) {
          if(unlink($file)) {
            $res = 'workflows';
          }
        }
      break;
      case 'exportWorkflows':
      break;
      case 'importWorkflows':
      break;
      case 'createActions':
        $err = 'Unable to Save Action';
        $name = '';
        if(isset($_POST['n'])) $name = $_POST['n'];
        $method = '';
        if(isset($_POST['m'])) $method = $_POST['m'];
        switch($method) {
          case 'say':
          case 'ssh':
          case 'escape':
          case 'insert':
          case 'single':
          case 'select':
          case 'update':
            $data = '';
            if(isset($_POST['d'])) $data = $_POST['d'];
            if(!empty($data)) {
              $valid = json_decode($data);
              switch(json_last_error()) {
                case JSON_ERROR_NONE: break;
                default: $data = json_encode($data); break;
              }
              if(!is_dir($method)) mkdir($method);
              $file = $method.'/'.$name.'.json';
              if(!file_exists($file)) {
                if(file_put_contents($file,$data)) {
                  $err = '';
                  $res = 'actions';
                  unlink($res);
                }
              }
            }
          break;
        }
        if(!empty($err)) echo $err;
      break;
      case 'listActions':
        $f = 'actions';
        if(file_exists($f)) {
          $res = json_decode(file_get_contents($f));
        }else{
          $res = array('actions'=>array());
          foreach(glob('*/*.json') as $file) {
            $res['actions'][] = substr($file,0,-5);
          }
          file_put_contents($f,json_encode($res));
        }
      break;
      case 'readActions':
        if(isset($_POST['f'])) {
          $f = $_POST['f'].'.json';
          if(file_exists($f)) {
            $res = json_decode(file_get_contents($f));
          }
        }    
      break;
      case 'deleteActions':
        if(isset($_POST['f'])) {
          $f = $_POST['f'].'.json';
          if(file_exists($f)) {
            if(unlink($f)) {
              $res = true;
            }
          }
        }
      break;
      case 'listMethods':
        $res = array('methods' => array(
          array(
            'n'=> 'Say',
            'i'=> 'say',
            'a'=> array('textarea'=>'Say'),
          ),
          array(
            'n'=> 'SSH',
            'i'=> 'ssh',
            'a'=> array('textarea'=>'SSH'),
          ),
          array(
            'n'=> 'Escape',
            'i'=> 'escape',
            'a'=> array('textarea'=>'Escape'),
          ),
          array(
            'n'=> 'Insert',
            'i'=> 'insert',
          //insert object {"sql","tbl"}
          ),
          array(
            'n'=> 'Single',
            'i'=> 'single',
            'a'=> array('textarea'=>'Single'),
          ),
          array(
            'n'=> 'Select',
            'i'=> 'select',
            'a'=> array('textarea'=>'Select'),
          ),
          array(
            'n'=> 'Update',
            'i'=> 'update',
          //update object {"set","tbl","sql"}
          ),
        ));
      break;
    }
    return $res;
  }
  private function git($req) {
		$res = false;
		if(!isset($_POST['req'])) $_POST['req'] = ''; 
    switch($_POST['req']) {
      case 'cache': $res = $this->gitCache($req->User);break;
      case 'config': $res = $this->gitConfig($req->User);break;
      case 'diff': $res = $this->gitDiff($req->User);break;
			case 'grep': $res = $this->gitGrep($req->User);break;
      case 'status': $res = $this->gitStatus($req->User);break;
      case 'log': $res = $this->gitLog($req->User);break;
      case 'all': $res = $this->gitAll($req->User);break;
      case 'add': $res = $this->gitAdd($req->User);break;
      case 'rem': $res = $this->gitRem($req->User);break;
      case 'pull': $res = $this->gitPull($req->User);break;
			case 'push': $res = $this->gitPush($req->User);break;
    }
    return $res;
  }
  private function gitCache($user) {
		$cache = '../src/repos/'.$user;
    if(file_exists($cache)) {
      unlink($cache);
    }
    return true;
  }
  private function gitConfig($user) {
    exec('git config --list',$res);
    return $res;
  }
  private function gitDiff($user) {
    $res = false;
    $status = $this->gitStatus($user);
    foreach($status as $k => $v) {
      if($v['r'] == $_POST['project']) {
        $path = dirname(__FILE__);
        chdir($path);
        chdir('../src/users/'.$user.'/'.$_POST['project']);
        $exec = 'git diff';
        exec($exec,$ret);
        if($ret) {
          $res = array(
            'repo' => $_POST['project'],
            'diff' => $ret,
          );
          //return mustache template formed data
        }
        continue;
      }
    }
    return $res;
  }
  private function gitGrep($user) {
		$res = false;
		$status = $this->gitStatus($user);
		foreach($status as $k => $v) {
			if($v['r'] == $_POST['project']) {
				$path = dirname(__FILE__);
				chdir($path);
				chdir('../src/users/'.$user.'/'.$_POST['project']);
				//todo properly escape '
				$exec = 'git grep -n \''.str_replace('\'','',$_POST['grep']).'\'';
				exec($exec,$ret);
				if($ret) {
					$res = array(
						'repo'=> $_POST['project'],
						'grep' => $ret,
					);
				}
			}
		}
		return $res;
  }
  private function gitStatus($user) {
    $res = false;
    $cache = '../src/repos/'.$user;
    if(!file_exists($cache)) {
      exec('find ../src/users/'.$user.' -type d -name ".git"',$dir);
      $path = dirname(__FILE__);
      $len = strlen(dirname(getcwd()));
      $res = array();
      foreach($dir as $k => $v) {
        $res[] = $this->gitGitstatus($v,$len,$path,$user);
      }
      $git = $res;
      chdir($path);
      file_put_contents($cache,serialize($git));
    }else{
      $git = unserialize(file_get_contents($cache));
    }
    if(isset($_POST['git'])) {
      if(isset($git[$_POST['git']])) {
        #todo 
        #$git[$_POST['git']] = $this->gitstatus();
        #file_put_contents($file,serialize($git));
        #$res = $git[$_POST['git']];
      }
    }else{
      $res = $git;
    }
    return $res;
  }
  private function gitGitstatus($req,$len,$path,$user) {
    chdir($path);
    chdir(dirname($req.'/'));
    $r = substr(getcwd(),($len+12+strlen($user)));
    exec('git status -sb',$e);
    $b = '';
    $s = array();
    $n = array();
    $u = array();
    foreach($e as $kk => $vv) {
      switch(substr($vv,0,1)) {
        case ' ':break;
        case '#':
        $p = strpos($vv,'...');
        $b = substr($vv,3,($p===false?strlen($vv):$p-3));
        break;
        case '?': $u[] = substr($vv,3); break;
        default: if(strlen($vv) > 0 ) { $s[] = substr($vv,3); } break;
      }
      switch(substr($vv,1,1)) {
        case '?': case '#': case ' ': break;
        default: if(strlen($vv) > 0 ) { $n[] = substr($vv,3); } break;
      }
    }
    return array('r'=>$r,'b'=>$b,'s'=>$s,'n'=>$n,'u'=>$u);
  }
  private function gitLog($user) {
		$res = false;
    $status = $this->gitStatus($user);
    foreach($status as $k => $v) {
      if($v['r'] == $_POST['project']) {
				$prefix = '../src/users/'.$user.'/';
				$this->gitCache($user);
				$path = dirname(__FILE__);
				$len = strlen(dirname(getcwd()));
				chdir($path);
				chdir($prefix.$_POST['project']);
				exec('git log',$log);
				$res = array('repo'=>$_POST['project'],'log'=>$log);
				continue;
      }
    }
    return $res;
  }
  private function gitAll($user) {
    $res = false;
    $status = $this->gitStatus($user);
    $repo = array();
    $valid = false;
    foreach($status as $k => $v) {
      if($v['r'] == $_POST['project']) {
        $repo = $v;
        foreach($repo['n'] as $k => $v) {
          $valid = $this->gitGitAdd(array('repo'=>$_POST['project'],'user'=>$user,'file'=>$v));
        }
        foreach($repo['u'] as $k => $v) {
          $valid = $this->gitGitAdd(array('repo'=>$_POST['project'],'user'=>$user,'file'=>$v));
        }
				$res = $_POST['project'];
        continue;
      }
    }
    return $res;
  }
  private function gitAdd($user) {
    $res = false;
    $status = $this->gitStatus($user);
    // TODO create index for cache so i can isset instead of foreach
    $repo = array();
    $valid = false;
    foreach($status as $k => $v) {
      if($v['r'] == $_POST['project']) {
        $repo = $v;
        foreach($repo['n'] as $k => $v) {
          if($v == $_POST['file']) $valid = true;
        }
        foreach($repo['u'] as $k => $v) {
          if($v == $_POST['file']) $valid = true;
        }
        continue;
      }
    }
    if($valid) {
      $res = $this->gitGitAdd(array('repo'=>$_POST['project'],'user'=>$user,'file'=>$_POST['file']));
    }
    return $res;
  }
  private function gitGitAdd($req) {
    $repo = $req['repo'];
    $user = $req['user'];
    $file = $req['file'];
    $prefix = '../src/users/'.$user.'/';
    $this->gitCache($user);
    $path = dirname(__FILE__);
    $len = strlen(dirname(getcwd()));
    chdir($path);
    chdir($prefix.$repo);
    exec('git add '.$file);
    //todo return exit status
    return true;
  }
  private function gitRem($user) {
    $res = false;
    $status = $this->gitStatus($user);
    $repo = array();
    $valid = false;
    foreach($status as $k => $v) {
      if($v['r'] == $_POST['project']) {
        $repo = $v;
        foreach($repo['s'] as $k => $v) {
          if($v == $_POST['file']) $valid = true;
        }
        continue;
      }
    }
    if($valid) {
      $prefix = '../src/users/'.$user.'/';
      $this->gitCache($user);
      $path = dirname(__FILE__);
      $len = strlen(dirname(getcwd()));
      chdir($path);
      chdir($prefix.$_POST['project']);
      exec('git reset HEAD "'.$_POST['file'].'"');
      $res = true;
    }
    return $res;
  }
  private function gitPull($user) {
    $res = false;
    $status = $this->gitStatus($user);
    foreach($status as $k => $v) {
      if($v['r'] == $_POST['project']) {
        $prefix = '../src/users/'.$user.'/';
        $this->gitCache($user);
        $path = dirname(__FILE__);
        $len = strlen(dirname(getcwd()));
        chdir($path);
        chdir($prefix.$_POST['project']);
        exec('git pull origin '.$v['b']);
        $res = true;
        continue;
      }
    }
    return $res;
  }
  private function gitPush($user) {
    $res = false;
    $status = $this->gitStatus($user);
    foreach($status as $k => $v) {
      if($v['r'] == $_POST['project']) {
        $count = count($v['s']);
        if($count) {
          $prefix = '../src/users/'.$user.'/';
          $this->gitCache($user);
          $path = dirname(__FILE__);
          $len = strlen(dirname(getcwd()));
          chdir($path);
          chdir($prefix.$_POST['project']);
          $msg = 'Update ';
          if($count>1) {
            $msg .= $count.' files';
          }else{
            $msg .= str_replace('"','',$v['s'][0]);
          }
          $cfg = '';
          /*
          if(isset($_POST['name'])) {
            if(!empty($_POST['name'])) {
              if(! preg_match('/[^a-zA-Z\ ]/', $_POST['name'])) {
                $cfg .= '-c user.name="'.$_POST['name'].'" ';
              }
            }
          }
          if(isset($_POST['email'])) {
            if(!empty($_POST['email'])) {
              if(! preg_match('/[^a-zA-Z0-9\@\.]/', $_POST['email'])) {
                $cfg .= '-c user.email='.$_POST['email'].' ';
              }
            }
          }
          */
          $exec = 'git commit '.$cfg.' -m  "'.$msg.'" && git push origin '.$v['b'];
          exec($exec,$res);
          $res = true;
          //todo echo error 
        }
        continue;
      }
    }
    return $res;
  }
  # DEBUG MODE #
  private function debug() {
    set_time_limit(30);
    error_reporting(E_ALL);
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors',1);
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
class EDIT {
} #/EDIT
class POST {
} #/POST
class SSH {
} #/SSH
class VIDEO {
} #/VIDEO
?>
