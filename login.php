<?php
session_start();
include_once("conexion/conection.php");
#Creo variables para uso de mis credenciales de acceso.
#Las variables seran llenadas con la infor que mande el form de index.php
if(isset($_POST['login'])){
  $user = trim($_POST['usuario']);// Como usuario sera la Cédula
  $pass = trim($_POST['password']); // Validación la Contraseña
  $documentoBD = "";
  $idUsuario = "";

  #Gestion de incidencias
  try {

    // 1) Intentar login como PASTOR (nombre + contrasenna)
    $sqlPastor = "
        SELECT
            'pastor'        AS tipo_usuario,
            p.id_pastor     AS id_usuario,
            p.id_rol,
            p.nombre        AS nombre,
            NULL            AS documento
        FROM pastores p
        WHERE p.nombre = ?
        AND p.contrasenna = ?
        LIMIT 1;
    ";

    #preparo la consulta para ser procesada
    $sql = $connect->prepare($sqlPastor);

    #Ejecuto la consulta, pasando los parametros requeridos
    $sql->execute([$user,$pass]);

    #Almaceno un retorno de valor, para continuar con otros procesos
    $usuarioBD = $sql->fetch(PDO::FETCH_ASSOC);

    #########################################################
    ## Ahora valido, si no existe nada en $usuarioBD, quiere decir que es otro login ##
    #########################################################

    // 2) Si no es pastor, intento como EVANGELISTA (documento = usuario y documento = password)
           if (!$usuarioBD) {
            $sqlEvangelista = "
                SELECT
                    'evangelista'           AS tipo_usuario,
                    e.id_evangelistas       AS id_usuario,
                    e.id_rol,
                    e.nombre_completo       AS nombre,
                    e.documento             AS documento
                FROM evangelistas e
                WHERE e.documento = ?
                LIMIT 1;
            ";

            $stmt = $connect->prepare($sqlEvangelista);
            $stmt->execute([$user]);
            $rowEvangelista = $stmt->fetch(PDO::FETCH_ASSOC);

            // Si existe el evangelista y la contraseña es igual al documento
            if ($rowEvangelista && $user === $pass) {
                $usuarioBD = $rowEvangelista;
            }
        }

    // 3) Si tampoco, intento como EVANGELIZADO (documento = usuario y documento = password)
    if(!$usuarioBD){
        $sqlEvangelizador = "
            SELECT
                'evangelizado' AS tipo_usuario,
                ev.id_evangelizado AS id_usuario,
                ev.id_rol,
                ev.nombre_completo AS nombre,
                ev.documento AS documento
            FROM evangelizados ev
            WHERE ev.documento = ?
            AND ev.documento = ?
            LIMIT 1;
        ";

        #preparo la consulta para ser procesada
        $sql = $connect->prepare($sqlEvangelizador);

        #Ejecuto la consulta, pasando los parametros requeridos
        $sql->execute([$user,$pass]);

        #Almaceno un retorno de valor, para continuar con otros procesos
        $usuarioBD = $sql->fetch(PDO::FETCH_ASSOC);
    }

    #Creo las variables de sesion
    if($usuarioBD){
        $_SESSION['id_usuario']   = $usuarioBD['id_usuario'];
        $_SESSION['tipo_usuario'] = $usuarioBD['tipo_usuario'];
        $_SESSION['id_rol']       = $usuarioBD['id_rol'];
        $_SESSION['nombre']       = $usuarioBD['nombre'];
        $_SESSION['documento']    = $usuarioBD['documento'];


        // Puedes redirigir a todos al mismo sitio como antes:
        header("Location: main.php");
    }else{
        // No se encontró en ninguna tabla
        echo "
            <script>
                alert('Usuario y/o Password incorrectos, o usuario no registrado...');
                document.location = ('login.php');
            </script>
            ";
        }
    } catch (Exception $e) {
        // Si algo sale mal en la conexion o el SQL, aqui se ve
        echo "<pre>Error PDO:\n" . $e->getMessage() . "</pre>";
        exit;
    }
  #Consulta para extraer la info del usuario que esta intentando ingresar
  $sql = "SELECT * FROM evangelistas WHERE documento = '$user' AND documento = '$pass'";

  echo "
    <script>
        console.log('Usuario: $user');
        console.log('Password: $pass');
    </script>
    ";
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Colombia Tierra Bendita</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="login-body">
    <div class="login-container">
        <!-- Header del Login -->
        <div class="login-header">
            <div class="login-logo">
                <img src="images/BANNER.WEBP" alt="CTB Logo">
            </div>
            <h1>Iniciar Sesión</h1>
            <p>Ingresa a tu cuenta de Colombia Tierra Bendita</p>
        </div>

        <!-- Formulario de Login -->
        <form id="formulario-login" class="login-form" method="POST" action="">
            <div class="form-group">
                <label for="usuario">Usuario (Cédula)</label>
                <input type="text" id="usuario" name="usuario" required 
                       placeholder="Ingrese su número de cédula">
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Ingrese su contraseña">
                <div class="password-toggle">
                    <button type="button" id="togglePassword">Mostrar 🔓</button>
                </div>
            </div>

            <button type="submit" class="login-button" name="login">
                <span class="button-text">Iniciar Sesión</span>
                <div class="button-loader" style="display: none;">
                    <div class="loader"></div>
                </div>
            </button>
        </form>

               <div class="login-footer">
            <p><a href="index.html">← Volver al Inicio</a></p>
            <p>¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a></p>
            <p>¿Eres una entidad aliada? <a href="login-alianzas.html">Acceso especial</a></p>
        </div>
        <!-- Mensajes de estado -->
        <div id="login-message" class="login-message" style="display: none;"></div>
    </div>
</body>
<script>
    //Mostrar u Ocultar Contraseña
    const togglePassword = document.querySelector("#togglePassword");
    const password = document.querySelector("#password");

    togglePassword.addEventListener("click", () => {
        const isPassword = password.getAttribute("type") === "password"

        // Cambia entre 'password' y 'text'
        password.setAttribute("type", isPassword ? "text" : "password");

        // Cambia el ícono (opcional)
        togglePassword.textContent = isPassword ? "No Mostrar 🔒" : "Mostrar 🔓";
    });
</script>
</html>