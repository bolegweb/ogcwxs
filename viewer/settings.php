<?php
error_reporting(E_ALL ^ E_NOTICE);
session_start();
global $c, $ir, $settings, $now, $loginMsg, $kod;
$dbserver = "localhost";
$dbuser = "ogcwxs";
$dbpass = "only4ogcwxs";
$dbname = "ogcwxs";
$kod = microtime().md5(microtime());

if(!$_SESSION['kod']){
	$_SESSION['kod'] = $kod;
}

date_default_timezone_set("Europe/Bratislava");

$now=time(); // timestamp
$c=mysql_connect($dbserver, $dbuser, $dbpass);
if(!mysql_select_db($dbname, $c)){exit("DB ERROR!!! App is down. Contact administrator, or try again later...");};
mysql_query("SET CHARACTER SET utf8");
function md6($str)
{
	$str1=substr($str, 0, 3);
	$str2=substr($str, 3, 6);
	$str3=substr($str, 6, 9);
	$str4=substr($str, 9, 200);
	
	
	// 152 znakov
	return substr(sha1($str1.$str3).sha1($str).sha1($str2.$str4).md5(sha1($str).sha1($str1.$str3).sha1($str2.$str4)), 0, 250);
}


function myUrlDecode($string) {
    if(!is_array($string))
    {
    $entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
    $replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
    return str_replace($entities, $replacements, urldecode($string));
    }
   return $string;
}
foreach($_GET as $key=>$val)
{
	if($val)
	{
		if(is_array($val))
		{
			foreach($val as $key2=>$val2)
			{
				if(is_array($val2))
				{
					foreach($val2 as $key3=>$val3)
					{
						$_GET[$key][$key2][$key3]=mysql_real_escape_string(myUrlDecode($val3));
					}
				}
				else
				{
					$_GET[$key][$key2]=mysql_real_escape_string(myUrlDecode($val2));
				}
			}
		}
		else
		{
			$_GET[$key]=mysql_real_escape_string(myUrlDecode($val));
		}
	}
}
foreach($_POST as $key=>$val)
{
	if($val)
	{	
		if(is_array($val))
		{
			foreach($val as $key2=>$val2)
			{
				if(is_array($val2))
				{
					foreach($val2 as $key3=>$val3)
					{
						$_POST[$key][$key2][$key3]=mysql_real_escape_string(myUrlDecode($val3));
					}
				}
				else
				{
					$_POST[$key][$key2]=mysql_real_escape_string(myUrlDecode($val2));
				}
				
			}
		}
		else
		{
			$_POST[$key]=mysql_real_escape_string(myUrlDecode($val));
		}
	}
}


function dbq($q)
{
	global $c, $ir;
	$_SESSION['queries']=$_SESSION['queries']+1;
	$_SESSION['total_queries']=$_SESSION['total_queries']+1;
	$now=time(); // timestamp
	$url=curPageURL();
	$brw=$_SERVER['HTTP_USER_AGENT'];
	$que = mysql_real_escape_string($q);
	$ip1 = mysql_real_escape_string(myUrlDecode($_SERVER['HTTP_X_FORWARDED_FOR']));
    $ip2 = $_SERVER['REMOTE_ADDR'];
	mysql_query("INSERT INTO logs (time, text, uid, url, brw, ip1, ip2) VALUES ($now, '$que', '{$ir['id']}', '$url', '$brw', '{$ip1}', '{$ip2}')", $c);
	return mysql_query($q, $c);
}


function wrlog($txt, $ir)
{
	$now=time(); // timestamp
	$url=curPageURL();
	$ip1 = mysql_real_escape_string(myUrlDecode($_SERVER['HTTP_X_FORWARDED_FOR']));
    $ip2 = $_SERVER['REMOTE_ADDR'];
    dbq("INSERT INTO logs (time, uid, text, ip1, ip2, url) VALUES ('$now', {$ir['id']}, '$txt', '$ip1', '$ip2', '$url')");
}
function log_audit($tpe, $txt, $ir)
{
	$now=time(); // timestamp
	$url=curPageURL();
	$ip1 = mysql_real_escape_string(myUrlDecode($_SERVER['HTTP_X_FORWARDED_FOR']));
    $ip2 = $_SERVER['REMOTE_ADDR'];
    $txt = mysql_real_escape_string($txt);
    dbq("INSERT INTO logs_audit (type, user, ip1, ip2, text, time, url) VALUES ('$tpe', '{$ir['id']}', '$ip1', '$ip2', '$txt', '$now', '$url')");
}

function curPageURL() {
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}

function session_call ($ir)
{
	$now=time();
	$ip1 = mysql_real_escape_string(myUrlDecode($_SERVER['HTTP_X_FORWARDED_FOR']));
    $ip2 = $_SERVER['REMOTE_ADDR'];
    $minuty = $now - 1800;
    dbq("DELETE FROM sessions WHERE lastclick < $minuty");
    if (mysql_num_rows(dbq("SELECT * FROM sessions WHERE uid={$ir['id']}")))
    {
		dbq("UPDATE sessions SET lastclick=$now, clicks=clicks+1 WHERE uid={$ir['id']}");
	}
	else
	{
		dbq("INSERT INTO sessions (uid, ip1, ip2, lastclick, logintime) VALUES ('{$ir['id']}','$ip1','$ip2', '$now', '$now')");
	}
}

?>