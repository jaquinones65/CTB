<?php 

//Incluyo el enlace de conexion
include_once("conexion/conection.php");

if(isset($_POST['id_departamento'])){
    $id_departamento = $_POST['id_departamento'];

    //Hago uso de la consulta creada
    $divipol = $connect->prepare("
        SELECT
            id_ciudad,
            nombre
        FROM ciudades
        WHERE id_departamento = ?
        ORDER BY nombre ASC;
        ");

    #Ejecuto consulta previamente preparada, con el parametro requerido.
    $divipol->execute([$id_departamento]);

    #Obtengo las ciudades retornadas
    $ciudades = $divipol->fetchAll(PDO::FETCH_ASSOC);

    #Visualizo la data obtenida
    echo json_encode($ciudades);
    exit;
}

?>