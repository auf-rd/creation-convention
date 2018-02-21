<?php
chdir(dirname(__FILE__));
if (file_exists("./connexionMAMP.php")){
	include_once ("./connexionMAMP.php");
}else{ 
	include_once ("./connexion.php");
}

$connexion = new stdClass();
$connexion->mysqlI_server=$mysqlI_server;
$connexion->mysqlI_server_port=$mysqlI_server_port;
$connexion->mysqlI_db=$mysqlI_db;
$connexion->mysqlI_user=$mysqlI_user;
$connexion->mysqlI_pass=$mysqlI_pass;

class myDatabase {
	
	/*private _dbConnectionInfos=array('server'=>'', 'port'=>'', 'username'=>'','password'=>'','dbname'=>''); */
	private $_server="localhost";
	private $_port="3306";
	private $_dbUsername="";
	private $_dbPassword="";
	private $_dbName="AUF_GED";
	
	private $_dbConnection;
	private $_dbLink;
	private $_charset='utf8';
	
	private $_errors=array();
	
	public function __construct($params = array())
    {
    	foreach ($params as $param => $value){
        	switch ($param){
        	case 'host': 
        		$this->_server=$value;
        		break;
        	case 'port': 
        		$this->_port=$value;
        		break;
        	case 'username': 
        		$this->_dbUsername=$value;
        		break;
			case 'password': 
        		$this->_dbPassword=$value;
        		break;	
        	case 'dbname': 
        		$this->_dbName=$value;
        		break;
        	case 'charset': 
        		$this->_charset=$value;
        		break;

        	}
        }
    }
    
	private function dbConnect(){
		
		if (!$this->dbConnectOnly())
			return false;
		mysqli_set_charset($this->_dbLink,$this->_charset);
		return true;
	}
	private function dbConnectOnly(){
		$this->_dbLink = mysqli_connect($this->_server,$this->_dbUsername,$this->_dbPassword,$this->_dbName,$this->_port);
		if (!$this->_dbLink){
			$this->setError('mysqlI_error','Connexion à la base de données `'.$this->_dbName.'` ('.$this->_dbUsername.'@'.$this->_server.':'.$this->_port.') impossible.');
			//echo wrap_error_msg($this->getLastErrorMessage());
			return false;
		}else{
			//var_dump('connexion_ok');
			return true;
		}
		
	}
	
	private function dbDisconnect(){
		$this->_dbConnection="";
		mysqli_close($this->_dbLink);
	}
	
	private function setError($errorType,$message){
		$errIx=sizeof($this->_errors);
		$this->_errors[$errIx]['type']=$errorType;
		$this->_errors[$errIx]['message']=$message;
	}
	
	private function getLastError(){
		$errIx=(sizeof($this->_errors)>0?sizeof($this->_errors)-1:-1);
		if ($errIx>=0) return $this->_errors[$errIx];
		else return false;
	}

	/*** methodes publiques ***/
	
	public function getLastErrorType(){
		if ($err=$this->getLastError())
			return $err['type'];
		return false;
	}
	
	public function getLastErrorMessage(){
		if ($err=$this->getLastError())
			return $err['message'];
		return false;
	}
	
	public function fetchScalar($query){
		if ($this->dbConnect()){
			$rs=mysqli_query($this->_dbLink, $query);
			if ($rs){
				if (mysqli_num_rows($rs)>0){
					$row=mysqli_fetch_array($rs);
					$this->dbDisconnect();
					return $row[0];
				}else{
					return null;
				}
			}else{
				$this->setError('mysqlI_error',mysqli_error($this->_dbLink));	
				$this->dbDisconnect();
				return false;
			}
		}else return false;
	}
	
	public function fetchRow($query,$result_type=MYSQLI_BOTH){
		if ($this->dbConnect()){
			//$rs=mysqlI_query($query);
			$rs=mysqli_query($this->_dbLink, $query);
			if ($rs){
				//$row=mysqlI_fetch_array($rs,$result_type);
				$row=mysqli_fetch_array($rs,$result_type);
				$this->dbDisconnect();
				return $row;
			}else{
				//$this->setError('mysqlI_error',mysqlI_error($this->_dbLink));
				$this->setError('mysqlI_error',mysqli_error($this->_dbLink));
				$this->dbDisconnect();
				return false;
			}
		}else return false;
	}
	public function fetchRowObject($query){
		if ($this->dbConnect()){
			$rs=mysqli_query($this->_dbLink, $query);
			if ($rs!==false){
				$row=mysqli_fetch_object($rs);
				$this->dbDisconnect();
				return $row;
			}else{
				$this->setError('mysqlI_error',mysqli_error($this->_dbLink));
				$this->dbDisconnect();
				return false;
			}
		}else return false;
	}
	
	public function fetchArray($query,$result_type=MYSQLI_ASSOC){
		$resultArray=array();
		if ($this->dbConnect()){
			$rs=mysqli_query($this->_dbLink, $query);$rs=mysqli_query($this->_dbLink, $query);
			if ($rs!==false){
				while ($row=mysqli_fetch_array($rs,$result_type)){
					$resultArray[]=$row;
				}
				$this->dbDisconnect();
				return $resultArray;
			}else{
				$this->setError('mysqli_error',mysqli_error($this->_dbLink).'<br />'.$query);
				$this->dbDisconnect();
				return false;
			}
		}else return false;
	}
	
	public function fetchSingleColumnInArray($query){
		$resultArray=array();
		if ($this->dbConnect()){
			$rs=mysqli_query($this->_dbLink, $query);$rs=mysqli_query($this->_dbLink, $query);
			if ($rs!==false){
				while ($row=mysqli_fetch_array($rs,MYSQLI_NUM)){
					$resultArray[]=stripslashes($row[0]);
				}
				$this->dbDisconnect();
				return $resultArray;
			}else{
				$this->setError('mysqli_error',mysqli_error($this->_dbLink).'<br />'.$query);
				$this->dbDisconnect();
				return false;
			}
		}else return false;
	}
	
	public function fetchObjectArray($query){
		$resultArray=array();
		if ($this->dbConnect()){
			$rs=mysqli_query($this->_dbLink, $query);
			if ($rs){
				while ($row=mysqli_fetch_object($rs)){
					$resultArray[]=$row;
				}
				$this->dbDisconnect();
				return $resultArray;
			}else{
				$this->setError('mysqli_error',mysqli_error($this->_dbLink).'<br />'.$query);
				$this->dbDisconnect();
				return false;
			}
		}else return false;
	}
	
	/**
	 * Execute une requête INSERT et retourne identifiant de l'insertion (mysqli_insert_id) ou FALSE en cas d'echec
	 * En cas de retour FALSE l'erreur mysql peut etre récupérée avec la méthode ->getLastErrorMessage() de la classe
	 * @param string $query
	 * @return int insert_id | boolean FALSE
	 */
	public function executeInsert($query){
		if ($this->dbConnect()){
			//$rs=mysqlI_query($query);
			$rs=mysqli_query($this->_dbLink, $query);
			if (!$rs){		
				$this->setError('mysqli_error',mysqli_error($this->_dbLink).'<br />'.$query);
				$this->dbDisconnect($this->_dbLink);
				return false;
			}elseif(mysqli_affected_rows($this->_dbLink)==0){
				return 0;
			}else {
				$id = mysqli_insert_id($this->_dbLink);
				if ($id===0) return true; 
				elseif($id>0) return $id;
			}
		}else {
			$this->setError('mysqli_error',mysqli_error($this->_dbLink).'<br />'.$query);
			$this->dbDisconnect();
			return false;
		}

	}
	/**
	 * 
	 * @param string $query Requête à exécuter
	 */
	public function executeNonQuery($query){
		if ($this->dbConnect()){
			$rs=mysqli_query($this->_dbLink, $query);
			if ($rs===false){
				$myqslErr=mysqli_error($this->_dbLink);
				//$this->setError('mysqli_error',mysqli_error($this->_dbLink).'<br />'.$query);
				if ($myqslErr!=""){
					$this->setError('mysqli_error',mysqli_error($this->_dbLink).'<br />'.$query);
					$this->dbDisconnect($this->_dbLink);
					return false;
				}else return 0; //0 rows affected
			}else {
				$nb_affected=mysqli_affected_rows($this->_dbLink);
				$this->dbDisconnect($this->_dbLink);
				return $nb_affected;
			}
		}else {
			$this->setError('mysqli_error',mysqli_error($this->_dbLink).'<br />'.$query);
			$this->dbDisconnect();
			return false;
		}
	
	}
	
	public function insertValueInTableCol($table,$column,$value,$quotes=true){	
		$quot=($quotes)?"'":"";
		$value=addslashes($value);
		$q="insert into $table ($column) values (".$quot.$value.$quot.")";
		return $this->executeInsert($q);
	}
	
	public function idIfValueExists($table,$column,$value,$quotes=true,$id_col='id'){
		$value=addslashes($value);
		$quot=($quotes)?"'":"";
		$conditions=$column." = ".$value;
		if ($quotes)
			$condition=$column." LIKE ".$quot.$value.$quot;
		$q="select $id_col from $table where ".$condition;
//var_dump($id_col.' ::: '.$q);
		$rs=$this->fetchArray($q);
		if (is_array($rs)){ 
			if (sizeof($rs)===0) //n'existe pas
				return false;
			else return $rs[0][$id_col];
		}else{
			$this->setError('mysqli_error',mysqli_error($this->_dbLink).'<br />'.$q);
			return false;
		}
	}
	
	public function insertIfNotExist($table,$column,$value,$quotes=true,$id_col='id'){
		
		$id_value=$this->idIfValueExists($table,$column,$value,$quotes,$id_col);
		if (!$id_value) //n'existe pas
			return $this->insertValueInTableCol($table,$column,$value,$quotes);
		else return $id_value;
	}
	/**
	 * 
	 * @param string $table
	 * @param array $record
	 */
	public function insertValues($table, array $record){
		$columns = implode(',',$record['column']);
		$values = implode(',',$record['value']);
		$q="INSERT into $table ($columns) VALUES ($values)";
//echo '<p style="color:red">'.$q.'</p>';
		return $this->executeInsert($q);
	}
	
	/**
	 *
	 * @param string $table Nom de la table a mettre a jour
	 * @param string $condition Condition de la mise à jour 
	 * @param array $colVal Couples colonne-valeur (se trouvent au meme indice des tableaux $colVal['column'] et $colVal['value'])
	 */
	public function updateValues($table, $condition, array $colVal){
		$setArgs=array();
		if (trim($condition)=='' || strpos($condition,'=')===false){
			echo "Aucune condition n'est fournie pour la mise à jour ou la condition est incorrecte!";
			return false;
		}
		foreach ($colVal['column'] as $key => $colName){
			if ($colVal['value'][$key]===NULL){
				$setArgs[]=" ".$colName."=NULL";
			}else{
				$escaped=true;
				if (strpos($colVal['value'][$key],"\'")===false && strpos($colVal['value'][$key],"'")!==false)
					$escaped=false;
				if ($escaped)
					$setArgs[]=" ".$colName."='".$colVal['value'][$key]."'";
				else
					$setArgs[]=" ".$colName."='".$this->real_escape_string($colVal['value'][$key])."'";
			}
		}
		$querySetArgs = implode(',',$setArgs);
		$q="UPDATE $table SET $querySetArgs WHERE $condition";
//var_dump($q);
		$return_affected=$this->executeNonQuery($q);
		if ($return_affected===false){
			//echo wrap_error_msg($this->getLastErrorMessage());
			return false;
		}else 
			return $return_affected;
	}
	
	/**
	 * 
	 * @param string $string Chaine a echapper pour eviter le SQL injection
	 * @param Boolean trim Specifie si oui ou non on souhaite faire un trim (Vrai par defaut)
	 * @return string Chaine echapee et passee par un trim
	 */
	public function real_escape_string($string, $trim=true){
		$this->dbConnectOnly();
		
		try{
			$escapedString=mysqli_real_escape_string($this->_dbLink, $string);
		}catch(Exception $e){
			$escapedString=addslashes($string);	
		}
		if ($trim)
			$escapedString=trim($escapedString);
		
		$this->dbDisconnect();
		return $escapedString;
	}
	
	public function getSingeColResultToArray($arrayOfResultRows){
		$a=array();
		foreach ($arrayOfResultRows as $arrayOfColumns){
			$a[]=$arrayOfColumns[0];
		}
		return $a;
	}
	
	public function beginTransaction(){
		
		if ($this->dbConnect()){
			if ($this->transactionQuery("BEGIN")!==false){
				return true;
			}else
				throw new Exception("Failed to start transaction.");	
		}else
			throw new Exception("Connection to database failed to initialize.");
	}
	
	public function commitTransaction(){
		if ($this->transactionQuery("COMMIT")){
			$this->dbDisconnect();
		}else{
			$this->dbDisconnect();
			throw new Exception("Failed to commit database transaction.");
		}
		return true;
	}
	
	public function rollbackTransaction(){
		if ($this->transactionQuery("ROLLBACK")){
			$this->dbDisconnect();
		}else{
			$this->dbDisconnect();
			throw new Exception("Failed to Rollback database transaction.");
		}
		return true;
	}
	
	public function transactionQuery($query){
		$querytype=strtolower(substr($query,0,strpos($query,' ')));
		$rs=mysqli_query($this->_dbLink, $query);
		if ($rs===false){
			$myqslErr=mysqli_error($this->_dbLink);
			//$this->setError('mysqli_error',mysqli_error($this->_dbLink).'<br />'.$query);;
			if ($myqslErr!=""){
				$this->setError('mysqli_error',mysqli_error($this->_dbLink).'<br />'.$query);
				throw new Exception($myqslErr);
			}else return 0; //0 rows affected
		}else {
			if ($querytype=='update' || $querytype=='delete'){
				$nb_affected=mysqli_affected_rows($this->_dbLink);
				return $nb_affected;
			}elseif($querytype=='insert'){
				return mysqli_insert_id($this->_dbLink);
			}else return true;
		}
	}

}

?>
