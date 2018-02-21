<?php 
$mysql_server="localhost";
$mysql_server_port=3306;
$mysql_db="AUF_GED";
$mysql_user="root";
$mysql_pass="root";

$localConnection=true;

function my_var_dump($s){
	error_reporting(E_ALL && !E_WARNING);
	echo '<pre>'.print_r($s,true).'</pre>';
	error_reporting(E_ALL );
}

function curPageURL($parameters=true, $absolute=false) {
	$pageURL="";
	if ($absolute){
		$pageURL.='http';
		if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .="://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
	}
	$pageURL.="?";
	if ($parameters)
		$pageURL.=$_SERVER['QUERY_STRING'];
	return $pageURL;
}


?>