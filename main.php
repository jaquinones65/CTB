<?php
session_start();
if(!isset($_SESSION['id_usuario']) || $_SESSION['id_usuario'] ==null){

	print "
		<script>
			alert(\"Acceso invalido!\");
			window.location='login.php';
		</script>";
}
/*
* Este Bloque De Codigo Sirve Para Iniciar La Sessión Ya Antes Programada
* Además De Que Verifica Si Va A Entrar Por Url Sin El Login, Lo De Vuelve
* Obligandolo A Iniciar Sessión.
*/
$rol = $_SESSION['id_rol'];

switch ($rol) {
	case '1':
		header("Location: panel-pastor.php");
		//include_once('Usuarios/admin.php');
		break;
	case '2':
		#Redireccionamiento para usuario Supervisor.
		include_once('panel-evangelizador.php');
		break;
	case '3':
		#Redireccionamiento para Usuario Trabajador
		include_once('panel-evangelizado.php');
		break;
	default:
		include_once('error404.php');
		break;
}
?>