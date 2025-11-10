<?php
#Inicio la sesion
session_start();

#Realizo la conexion con la base de datos
include_once("conexion/conection.php");

#Paso lenguaje a trabajar
header('Content-Type: application/json; charset=utf-8');

#valido una sesion activa
if(!isset($_SESSION['documento'])){
	echo json_encode(['error'=>'No hay sesion activa']);
	exit;
}
//Continuo si, si existe una sesion activa.

//Creo una variable con el valor del documento
$documento = $_SESSION['documento'];

#Gestion de errores
try {

	#############################
	# EVANGELIZADOS REGISTRADOS #
	#############################

	#Realizo la consulta sql
	$sqlEvangelizados = "
		SELECT COUNT(*) as total_evangelizados
		FROM evangelizados
		WHERE Evangelistas_documento = ?";

	#Preparo la consulta
	$tempSql = $connect -> prepare($sqlEvangelizados);
	#Ejecuto la consulta
	$tempSql->execute([$documento]);
	#Recibo los datos
	$evangelizados = $tempSql->fetch()['total_evangelizados'];

	#######################
	# ALMAS COMPROMETIDAS #
	#######################

	#Realizo la consulta sql
	$sqlAlmas = "
		SELECT
			numero_almas
		FROM evangelistas
		WHERE documento = ?";

	#Preparo la consulta
	$tempSql = $connect -> prepare($sqlAlmas);
	#Ejecuto la consulta
	$tempSql->execute([$documento]);
	#Recibo los datos
	$almasComprometidas = $tempSql->fetch()['numero_almas'] ?? 0;

	//////////////////////////////////////
	// ENVIO LOS DATOS EN FORMATO JSON //
	//////////////////////////////////////
	echo json_encode([
		'evangelizados' => $evangelizados,
		'almas' => $almasComprometidas
	]);

	
} catch (PDOException $e) {
	echo json_encode([
		'error'=> $e->getMessage()
	]);
}

?>