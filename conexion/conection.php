<?php
try{
    $connect = new PDO("mysql:host=127.0.0.1;dbname=u373956292_ctb;charset=utf8","u373956292_ctb","CTBendita_2026*");
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
}catch(PDOException $e){
    die("Error de Conexion.:" . $e->getMessage());
}

?>
