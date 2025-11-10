<?php
if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

//Incluyo la conexion con la base de datos
include_once('conexion/conection.php');

//Creo una variable para la gstion de variables de inicio de sesion
$rol = $_SESSION['id_rol'] ?? null;


if(!isset($_SESSION["id_usuario"]) || $_SESSION["id_usuario"]==null){
    print "
        <script>
            alert(\"Acceso invalido!\");
            window.location='login.php';
        </script>";
}else if($rol != 2){
    print "
        <script>
            alert(\"Acceso invalido!\");
            window.location='./';
        </script>";
    }

#ESTA SECCION ES CREADA PARA QUE CARGUE LOS QUERYS Y SE ALIMENTE
#TODOS LOS SELECTS.

#Ahora creo la consulta que genera la informacion del departamento
$divipol = $connect->query("
    SELECT
        id_departamento   as id_departamento,
        nombre            as departamento
    FROM departamentos
    ORDER BY nombre ASC;
    ");

//Voy a realizar la logica para solicitar la biblia.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['solicitudBiblia'])) {
    $cantidad = trim($_POST['cantidad']);

    // Validar que sea número
    if (!is_numeric($cantidad) || $cantidad <= 0) {
        $mensaje = "⚠️ Por favor ingresa una cantidad válida de biblias.";
    } elseif (!isset($_SESSION['id_usuario'])) {
        $mensaje = "❌ No se pudo identificar al evangelista. Inicia sesión nuevamente.";
    } else {
        try {
            $id_evangelista = $_SESSION['id_usuario'];
            
            $stmt = $connect->prepare("INSERT INTO biblias_solicitadas (fecha_solicitud, cantidad, id_evangelistas)
                           VALUES (NOW(), :cantidad, :id_evangelistas)");
            
            $stmt->execute([
                ':cantidad' => $cantidad,
                ':id_evangelistas' => $id_evangelista
            ]);

            print"
                <script>
                    alert(\"✔️Solicitud registrada exitosamente.\");
                    window.location='main.php';
                </script>";
        } catch (PDOException $e) {
            print"
                <script>
                    alert(\"❌Losiento, no se pudo procesar tu solicitud, intentalo nuevamente en 5 minutos.\");
                    window.location='main.php';
                </script>";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Evangelizador - CTB</title>
    <link rel="stylesheet" href="styles.css"
</head>
<body>
      <header class="ctb-header">
        <div class="header-top">
            <div class="container">
                <div class="header-contact">
                    <span>Telefono: 322 7721323</span>
                    <span>Nuestro numero de contacto</span>
                </div>
            </div>
        </div>

        <div class="header-top">
            <div class="container">
                <div class="header-name">
                    <span><?php echo $_SESSION['nombre'];?></span>
                    <span>Bienvenido</span>
                </div>
            </div>
        </div>

        <nav class="ctb-nav">
            <div class="container">
                <div class="nav-brand">
                    <div class="logo">CTB</div>
                    <div class="brand-text">
                        <h1>Panel Evangelizador</h1>
                        <p>Gestión de Evangelizados</p>
                    </div>
                </div>

                <div class="nav-image">
                    <img src="images/ctbim1.JPG" alt="Logo CTB">
                </div>
                
                <ul class="nav-menu">
                    <li><a href="main.php" class="active">Mi Panel</a></li>
                    <li><a href="logout.php">Cerrar Sesión</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="container">
        <!-- Layout Horizontal Principal -->
        <div class="evangelizador-layout">
            <!-- Columna Izquierda: Estadísticas y Formulario -->
            <div class="left-column">
                <!-- Tarjetas de Estadísticas -->
                <section class="stats-section">
                    <h2>Mi Progreso</h2>
                    <div class="stats-cards">
                        <div class="stat-card">
                            <div class="stat-icon">👥</div>
                            <div class="stat-content">
                                <div class="stat-number total-evangelizados" id="total-evangelizados">0</div>
                                <div class="stat-label">Evangelizados Registrados</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">🎯</div>
                            <div class="stat-content">
                                <div class="stat-number almas-comprometidas" id="almas-comprometidas">0</div>
                                <div class="stat-label">Almas Comprometidas</div>
                            </div>
                        </div>
                    </div>
                </section>
                
                <!-- SOLICITUD DE BIBLIAS -->
                <section class="stats-section">
                    <h2>Solicitud de Biblias</h2>
                    <form id="formulario-inscripcion" class="modern-form" method="POST" action="">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="cantidad">Cantidad de Biblias *</label>
                                    <input type="text" id="cantidad" name="cantidad" required 
                                           placeholder="Solo números" pattern="[0-9]+">
                                </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary btn-full" name="solicitudBiblia">
                                    <span class="button-text">Solicitar Biblias</span>
                                    <div class="button-loader" style="display: none;">
                                        <div class="loader"></div>
                                    </div>
                                </button>
                            </div>
                        </form>
                </section>
            </div>

            <!-- Columna Derecha: Lista de Evangelizados -->
            <div class="right-column">
                <section class="lista-section">
                    <!-- Formulario de Inscripción Mejorado -->
                    <section class="form-section">
                        <div class="form-header">
                            <h2>Inscribir Nuevo Evangelizado</h2>
                            <p>Complete la información para pre-inscribir</p>
                        </div>

                        <form id="formulario-inscripcion" class="modern-form" method="POST" action="">
                            <div class="form-group">
                                <label for="nombre_evangelizado">Nombre Completo *</label>
                                <input type="text" id="nombre_evangelizado" name="nombre_evangelizado" required 
                                   placeholder="Ingrese el nombre completo">
                            </div>
                        
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="cedula_evangelizado">Cédula *</label>
                                    <input type="text" id="cedula_evangelizado" name="cedula_evangelizado" required 
                                           placeholder="Solo números" pattern="[0-9]+">
                                </div>
                                
                                <div class="form-group">
                                    <label for="telefono_evangelizado">WhatsApp *</label>
                                    <input type="text" id="telefono_evangelizado" name="telefono_evangelizado" required 
                                           placeholder="+57 300 123 4567">
                                </div>

                                 <div class="form-group">
                                    <label for="email">Correo *</label>
                                    <input type="email" id="email" name="email" required 
                                           placeholder="evangelizado@gmail.com">
                                </div>

                                <div class="form-group">
                                    <label for="departamento">Seleccione su departamento *</label>
                                    <select id="departamento" name="departamento" required>
                                        <option value="">Seleccione un Departamento</option>
                                        <?php
                                        //Traigo los registros de la consulta ya generada
                                        while($row = $divipol->fetch(PDO::FETCH_ASSOC)){
                                            echo "<option value='{$row['id_departamento']}'>{$row['departamento']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="ciudad">Ciudad/Municipio *</label>
                                    <select id="ciudad" name="ciudad" required="">
                                        <option value="">Seleccione una Ciudad/Municipio</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary btn-full" name="inscribir">
                                    <span class="button-text">Inscribir Evangelizado</span>
                                    <div class="button-loader" style="display: none;">
                                        <div class="loader"></div>
                                    </div>
                                </button>
                            </div>
                        </form>
                    </section>
                </section>
            </div>
        </div>
    </main>
    <footer class="ctb-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Colombia Tierra Bendita</h3>
                    <p>Panel de Evangelizador</p>
                </div>

                <div class="footer-section">
                    <h4>Soporte</h4>
                    <p>+57 304 5510438</p>
                    <p>soporte@colombiatierrabendita.org</p>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; CTB 2025 - Usuario Evangelizador</p>
            </div>
        </div>
    </footer>
</body>
<!-- ALIMENTACION DE DATOS DE LOS OPTIONS -->
<script>
    document.addEventListener("DOMContentLoaded", () => {
        fetch("get_estadisticas.php")
            .then(response => response.json())
            .then(data => {
                console.log("Datos recibidos:", data); // 👈 Para verificar qué llega

                if (data.error) {
                    console.error("Error:", data.error);
                    return;
                }

                document.querySelectorAll('.total-evangelizados').forEach(el => {
                    el.textContent = data.evangelizados ?? 0;
                });
                document.querySelectorAll('.almas-comprometidas').forEach(el => {
                    el.textContent = data.almas ?? 0;
                });
                document.querySelectorAll('.biblias-solicitadas').forEach(el => {
                    el.textContent = data.biblias ?? 0;
                });
            })
            .catch(error => console.error("Error al cargar estadísticas:", error));
        });

    // Actualizacion de ciudades, segun seleccion del departamento.
        document.getElementById('departamento').addEventListener('change'
            , function() {
            const idDepartamento = this.value;
            const ciudadSelect = document.getElementById('ciudad');
            ciudadSelect.innerHTML = `<option value = ""> Cargando...</option>`;

            //Validacion de seleccion
            if(idDepartamento !== '') {
                fetch('get_ciudades.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `id_departamento=${encodeURIComponent(idDepartamento)}`
                })
                .then(response => response.json())
                .then(data => {
                    ciudadSelect.innerHTML = `<option value=""> Seleccione una Ciudad/Municipio. </option>`;
                    data.forEach(ciudad => {
                        ciudadSelect.innerHTML += `<option value="${ciudad.id_ciudad}">${ciudad.nombre}</option>`
                    });
                })
                .catch(error =>{
                    ciudadSelect.innerHTML = `<option value=""> Error al Cargar los Datos. </option>`;
                    console.error('Error Cargando Ciudades:', error);
                });
            }else{
                ciudadSelect.innerHTML = `<option value=""> Primero Debe Selecionar un Departamento </option>`;
            }

        });
</script>
</html>

<!-- INICIO DEL CODIGO PHP -->
<?php 

//Incluyo archivo de conexion
include_once("conexion/conection.php");

//Logica para guardar
if (isset($_POST['inscribir'])) {
    //Variables requeridas
    $fechaCreacion = date('Y-m-d');
    $nombre = $_POST['nombre_evangelizado'];
    $documento = $_POST['cedula_evangelizado'];
    $telefono = $_POST['telefono_evangelizado'];
    $email = $_POST['email'];
    $rol = 3;
    $documentoEvangelista = $_SESSION['documento'];
    $departamento = $_POST['departamento'];
    $ciudad = $_POST['ciudad'];

    #Test de evaluación mediante la consola del navegador
    
    echo "
            <script>
                console.log('FechaCreacion: $fechaCreacion');
                console.log('Nombre: $nombre');
                console.log('Documento: $documento');
                console.log('Teléfono: $telefono');
                console.log('Email: $email');
                console.log('Rol: $rol');
                console.log('Curso: $idCurso');
                console.log('DocumentoEvangelista: $documentoEvangelista');
                console.log('Departamento: $departamento');
                console.log('Ciudad: $ciudad');
            </script>
        ";
    
    
    //Valido que el usuario que se este registrando no exista

    //Preparo consulta para su ejecucion
    $query = $connect->prepare("
        SELECT
            id_evangelizado
        FROM evangelizados
        WHERE documento = ? LIMIT 1;");

    //Ejecuto la consulta pasando los parametros solicitados
    $query->execute([$documento]);

    //Convierto el resultado en valor numerico, mejor manejo
    $cantRegistros = $query->rowCount();

    //Ahora, si el resultado es cero, no existe, de lo contrario ya existe.
    if($cantRegistros <= 0){
        //Si ingresa aqui, no existe, por ende, lo creo

        //Preparo la insercion
        $insert = $connect->prepare("INSERT INTO evangelizados (fecha_creacion, nombre_completo, documento, telefono, email, id_rol, Evangelistas_documento, id_ciudad, id_departamento) VALUES (?,?,?,?,?,?,?,?,?)");

        //Ejecuto la cadena preparada
        $insert->execute([$fechaCreacion, $nombre, $documento, $telefono, $email, $rol, $documentoEvangelista, $ciudad, $departamento]);

        echo "
            <script language='JavaScript'>
                alert('El Evangelizado ' + '$nombre' + ' $apellido' + ' Fue Registrado Exitosamente!!!');
            </script>
            ";

            ?>
            <script type="text/javascript">
              window.location.href='main.php';
            </script>
            <?php

    }else{
        //Esto se visualizara si el usuario ya existe
        echo "
            <script>
                alert('⚠️ Lo siento, el Evangelizado $nombre ya se encuentra registrado con la cédula $documento.');
            </script>
            ";
        }
    }
?>