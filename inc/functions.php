<?php

define('ROOT', getenv("DOCUMENT_ROOT").'/');

$sk = new skclass();
$tpl = new template();

if ($_SERVER["REQUEST_URI"]) $skinscript = $_SERVER["REQUEST_URI"];
else $skinscript = getenv("SCRIPT_NAME");

class template {

	function pagetit($pagetit, $tree) {
		echo '<div class="pageTit"><span>'.$pagetit.'</span>'.$tree.'</div>';
	}

}

function getPluginDetails() {
    global $_SERVER;
    $as = explode('/',$_SERVER['REQUEST_URI']);
    if(!(@stat('/usr/local/directadmin/plugins/'.$as[2]))) $as[2] = '';
    $fp = file_get_contents('/usr/local/directadmin/plugins/'.$as[2].'/plugin.conf');
	$ip = implode('&', preg_split("/[\r\n]+/",$fp));
	parse_str($ip, $retval);
    return $retval;
}

function getpath() {
	global $root;
	$skinpath = ROOT;
	if(preg_match("/\/home\/(.+)\/skins\/(.+)/", $skinpath)) {
		$return['owner'] = 'reseller';
		$return['path'] = $skinpath.'/inc/';
	} elseif(preg_match("/\/usr\/local\/directadmin\/data\/skins\/(.+)/", $skinpath)) {
		$return['owner'] = 'admin';
		if(file_exists('/usr/local/directadmin/data/skin_data/capri')) {
			$return['path']="/usr/local/directadmin/data/skin_data/capri/";
		} else {
			$return['path'] = $skinpath.'/inc/';
		}
	} else {
		if(file_exists('/usr/local/directadmin/data/skin_data/capri')) {
			$return['owner'] = 'admin';
			$return['path'] = '/usr/local/directadmin/data/skin_data/capri/';
			return $return;
		} else {
			return false;
		}
	}
	return $return;
}

function openfile($file) {
	if(file_exists($file))
		if($data = @file_get_contents($file))
			return $data;
	return false;
}

function whitefile($str, $file) {
	if($al = @fopen($file,'w')) {
		if(@is_writable($file)) {
			@fwrite($al,$str);
			return true;
		}
	}
	@fclose($al);
	return false;
}

class skclass{

	function api_get($cmd,$post=false){
		if(is_array($post)) {
			$args = http_build_query($post,'','&');
			$header  = "POST {$cmd} HTTP/1.1\r\n";
			$header .= "Host: 127.0.0.1:{$_SERVER[SERVER_PORT]}\r\n";
			$header .= "Cookie: session={$_SERVER[SESSION_ID]}; key={$_SERVER[SESSION_KEY]}\r\n";
			$header .= "Content-type: application/x-www-form-urlencoded\r\n";
			$header .= "Content-length: ".strlen($args)."\r\n\r\n";
			$header .= $args;
		} else {
			$header  = "GET {$cmd} HTTP/1.1\r\n";
			$header .= "Host: 127.0.0.1:{$_SERVER[SERVER_PORT]}\r\n";
			$header .= "Cookie: session={$_SERVER[SESSION_ID]}; key={$_SERVER[SESSION_KEY]}\r\n\r\n";
		}
		if ($_SERVER['SSL'] == 1) $before = 'ssl://127.0.0.1'; else $before = '127.0.0.1';
		$socket = @fsockopen($before, $_SERVER['SERVER_PORT'], $sock_errno, $sock_errstr, 2);
		if ($sock_errno || $sock_errstr) return '';
		fwrite($socket, $header, strlen($header));
		$status = socket_get_status($socket);
		if($status['timed_out']) return '';
		$result = '';
		while (!feof($socket)) {
			$result .= fgets($socket,32768);
		}
		@fclose($socket);
		$result = explode("\r\n\r\n",$result,2);
		return $result[1];
	}

	function iconmenu($title, $items){
		$output="";
		for($i=0; $i<count($items); $i++){
			if($items[$i]['plugin']){
				$itemimg='/IMG_IC_PLUGIN';
				$plugintxt=$items[$i]['plugin'];
				if(stristr($plugintxt, 'stat'))			$itemimg='/IMG_IC_STATS';
				if(stristr($plugintxt, 'awstats'))		$itemimg='/IMG_IC_AWSTATS';
				if(stristr($plugintxt, 'smtp'))			$itemimg='/IMG_IC_STATS';
				if(stristr($plugintxt, 'ruby'))			$itemimg='/IMG_IC_RUBY';
				if(stristr($plugintxt, 'rails'))		$itemimg='/IMG_IC_RUBY';
				if(stristr($plugintxt, 'smtp'))			$itemimg='/IMG_IC_SMTP_CONTROL';
				if(stristr($plugintxt, 'billing'))		$itemimg='/IMG_IC_BILLING';
				if(stristr($plugintxt, 'bill'))			$itemimg='/IMG_IC_BILLING';
				if(stristr($plugintxt, 'payment'))		$itemimg='/IMG_IC_BILLING';
				if(stristr($plugintxt, 'hotlink'))		$itemimg='/IMG_IC_HOTLINK';
				if(stristr($plugintxt, 'itron'))		$itemimg='/IMG_IC_ITRON';
				if(stristr($plugintxt, 'installatron'))	$itemimg='/IMG_IC_ITRON';
				if(stristr($plugintxt, 'tomcat'))		$itemimg='/IMG_IC_TOMCAT';
				if(stristr($plugintxt, 'pear'))			$itemimg='/IMG_IC_PEAR';
				if(stristr($plugintxt, 'pgsql'))		$itemimg='/IMG_IC_PGSQL';
				if(stristr($plugintxt, 'Postgre'))		$itemimg='/IMG_IC_PGSQL';
				if(stristr($plugintxt, 'softaculous'))	$itemimg='/IMG_IC_SOFTAC';
				if(stristr($plugintxt, 'phpvs'))		$itemimg='/IMG_IC_PHPVS';
				if(stristr($plugintxt, 'php version selector'))	$itemimg='/IMG_IC_PHPVS';
				$pluglink = preg_replace("/<a(.*?)href=\"(.*?)\"(.*?)>(.*?)<\/a>/", "<a href=\"\\2\"><img src=\"{$itemimg}\"><br>\\4</a>", $items[$i]['plugin']);
				$output .= $pluglink;
			} else {
				$output .= '<a href="'.$items[$i]['link'].'"';
				if($items[$i]['js']) $output .= ' onclick="'.$items[$i]['js'].'"';
				$output .= '><img src="'.$items[$i]['img'].'"><br>'.$items[$i]["txt"]."</a>\r\n";
			}
		}
		echo '<fieldset class="buttons-box"><legend>'.$title."</legend>\r\n".$output."</fieldset>\r\n";
	}

	function submenu($title, $items, $footer = false){
		for($i=0; $i<count($items); $i++){
			$output .= '<td width="20%" align="center"><a href="'.$items[$i]['link'].'" class="subitem" '.$items[$i]['js'].'>';
			$output .= '<img src="'.$items[$i]['img'].'" width="32" height="32" border="0"><br>'.$items[$i]["txt"].'</a></td>';
			if(!(($i+1)%5)) $output .= "</tr>\r\n<tr>";
		}
		if($footer) $foot_menu = '<tr><td class="listend">'.$footer.'</td></tr>'; else $foot_menu = '';
		echo '<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
<tr><td class="listtitle" height="22" style="padding-left:3px;"><b>'.$title.'</b></td></tr>
<tr><td class=list><table border="0" cellspacing="0" cellpadding="0" align="center">
<tr>'.$output.'</tr></table></td></tr>'.$foot_menu.'</table>';
	}
	
	function uptime($color = false) {
		$loads = urldecode($this->api_get("/CMD_API_LOAD_AVERAGE"));
		parse_str($loads);
		settype($one, "float");
		settype($five, "float");
		settype($fifteen, "float");
		if ($color) {
			if ($one >= 5) $one = '<span style="color:red;"><b>'.number_format($load1, 2, '.', '').'</b></span>';
			if ($five >= 5) $five = '<span style="color:red;"><b>'.number_format($load5, 2, '.', '').'</b></span>';
			if ($fifteen >= 5) $fifteen = '<span style="color:red;"><b>'.number_format($load15, 2, '.', '').'</b></span>';
        }
        return "{$one}, {$five}, {$fifteen}";
	}

	function getServices() {
		$str = $this->api_get('/CMD_API_SHOW_SERVICES');
		if(strpos($str, 'httpd') == false) return false;
		parse_str(urldecode($str), $servArr);
		return $servArr;
	}

	function getLogo($creator, $username) {
		$logo = '/IMG_SKIN_HEADER';
		$arrPath = getpath();
		if(file_exists("{$arrPath[path]}logos/{$creator}")) $logo = '/IMG_RESLOGO_'.$creator;
		if(file_exists("{$arrPath[path]}logos/{$username}")) $logo = '/IMG_RESLOGO_'.$creator;
		return $logo;
	}

}

function getLanguages($root) {
	$langdir = "{$root}lang/";
	$languages = array();
	if($handle = opendir($langdir)) {
		while(false != ($file = readdir($handle)))
			if ($file!='.' && $file!='..' && is_dir($langdir.$file))
				$languages[] = $file;
		closedir($handle);
	}
	return $languages;
}

function getDomainsList() {
	global $sk;
	$ret = array();
	$r = $sk->api_get('/CMD_API_DOMAIN_OWNERS');
	$domainsOwn = @urldecode($r);
	@parse_str($domainsOwn, $domains);
	if (is_array($domains) && count($domains) > 0)
		foreach ($domains as $domain => $ouwner)
			$ret[str_replace('_', '.', $domain)] = $ouwner;
	return $ret;
}

function server_addr() {
	return $_SERVER['SERVER_ADDR'] ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'];
}

?>