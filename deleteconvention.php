<?php
    if (!file_exists('connexionMAMP.php'))
        exit();
    require ('connexionMAMP.php') ;
    require ('db.class.php');
    require ('utils.php');
    
    $tab = $_GET['table'];
    $where = empty($_GET['where'])?'':' WHERE '.$_GET['where'];
    $myDB = new myDatabase(array('server'=>$mysql_server, 'port'=>$mysql_server_port, 'username'=>$mysql_user,'password'=>$mysql_pass,'dbname'=>$mysql_db));
    $q = 'DELETE FROM ' . '`convention__clause_param` ' . $where;
    $myDB->executeNonQuery($q); 
    echo $q;
?>