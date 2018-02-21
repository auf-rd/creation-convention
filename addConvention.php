<?php
    if (!file_exists('connexionMAMP.php'))
        exit();
    require ('connexionMAMP.php') ;
    require ('db.class.php');
    require ('utils.php');
    
    $tab = $_GET['table'];
    $field = $_GET['field'];
    $values = $_GET['values'];
    $where = empty($_GET['where'])?'':' WHERE '.$_GET['where'];
    $prefix = empty($_GET['prefix'])?'convention__':$_GET['prefix'];
    $order = empty($_GET['order_by'])?'':' ORDER BY '.$_GET['order_by'];
    $myDB = new myDatabase(array('server'=>$mysql_server, 'port'=>$mysql_server_port, 'username'=>$mysql_user,'password'=>$mysql_pass,'dbname'=>$mysql_db));
    $req = 'INSERT INTO ' . $tab . $field . 'VALUES ' . $values;
    $id = $myDB->executeInsert($req);
    echo $id;
?>
