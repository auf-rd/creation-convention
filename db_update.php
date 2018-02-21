<?php
include 'connexionMAMP.php';
include 'db.class.php';
include 'utils.php';

$base_url="http://references.auf.org/export/";
// $base_url="";

$json = [file_get_contents($base_url.'implantations.json'), file_get_contents($base_url.'etablissements.json'), file_get_contents($base_url.'regions.json'), file_get_contents($base_url.'pays.json')];

$table = ["dbauf__ref_implantation", "dbauf__ref_etablissement", "dbauf__ref_region", "dbauf__ref_pays"];

$myDB = new myDatabase(array('server'=>$mysql_server, 'port'=>$mysql_server_port, 'username'=>$mysql_user,'password'=>$mysql_pass,'dbname'=>$mysql_db));

foreach ($json as $index=>$file) 
{
	$i = 0;
	$obj = json_decode($file, $assoc = true);
	$id_list = array();
	foreach ($obj as $row) {
		$request_str = "";
		$id_list[] = $row["id"];
		$keys_str = '('.implode(', ', array_keys($row)).')';
		$value_to_Db = "";
		$val = "";		
		foreach ($row as $key => $value){
			if (is_numeric($value)) {
				$value_to_Db .= $myDB->real_escape_string($value).',';
				$request_str .= $key."=".$myDB->real_escape_string($value).", ";
			} else if (is_null($value)) {
				$value_to_Db .= "NULL".', ';
				$request_str .= $key."=NULL, ";
			} else if (is_bool($value)) {
				$value_to_Db .= ($value ? 'true' : 'false').',';
				$request_str .= $key."=".($value ? 'true' : 'false').", ";
			} else {
				$value_to_Db .= "'".$myDB->real_escape_string($value)."'".',';
				$request_str .= $key."='".$myDB->real_escape_string($value)."', ";
			}
		}
		$val = str_replace(",)", ")", '('.$value_to_Db.')');
		$val2 = str_replace(", )", "",$request_str.')');
		$q= "INSERT INTO ".$table[$index]." ".$keys_str." VALUES ".$val." ON DUPLICATE KEY UPDATE ".$val2."";
		$i++;
		$myDB->executeInsert($q);
	};
	print_r($table[$index]." mise a jour"."\n");
	$q_actif = "UPDATE ".$table[$index]." SET actif=0 where id not in (".implode(",", $id_list).")";
	$myDB->executeInsert($q_actif);
}
?>