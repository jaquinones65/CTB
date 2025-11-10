<?php
if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

//Incluyo la conexion con la base de datos
include_once('conexion/conection.php');

//Creo una variable para la gstion de variables de inicio de sesion
$rol = $_SESSION['id_rol'] ?? null;

//Con el documento del evangelizado realizo el filtrado en el sql
$evDocumento = $_SESSION['documento'];

if(!isset($_SESSION["id_usuario"]) || $_SESSION["id_usuario"]==null){
    print "
        <script>
            alert(\"Acceso invalido!\");
            window.location='login.php';
        </script>";
}else if($rol != 3){
    print "
        <script>
            alert(\"Acceso invalido!\");
            window.location='./';
        </script>";
}

//Consulto la informacion del evangelizado
$sql = "
    SELECT
        ev.id_evangelizado,
        ev.nombre_completo,
        ev.documento,
        ev.telefono,
        ev.email,
        ci.id_ciudad,
        ci.nombre               as ciudad,
        dp.id_departamento ,
        dp.nombre               as departamento,
        cu.id_curso             as id_curso,
        cu.nombre_curso         as nombre_curso
    FROM evangelizados ev
    LEFT JOIN ciudades ci
        ON ev.id_ciudad = ci.id_ciudad
    LEFT JOIN departamentos dp
        ON ci.id_departamento = dp.id_departamento
    LEFT JOIN cursos cu
        ON ev.id_curso = cu.id_curso
    WHERE documento = ?;
    ";

//Preparo la consulta que esta en forma de cadena a cosulta
$execute = $connect->prepare($sql);

//Ejecuto la consulta ya lista
$execute->execute([$evDocumento]);

//Ahora recibo los datos
$ev = $execute->fetch(PDO::FETCH_ASSOC);

//Departamento y ciudades
$idDeptoActual = $ev['id_departamento'];
$idCiudadActual = $ev['id_ciudad'];
$idCursoActual = $ev['id_curso'];
$nombreCursoActual = $ev['nombre_curso'] ?? null;

//En caso que no pueda encontrar la informacion
if(!$ev){
    echo "No se logro encontrar informacin, por favor contacte a Tecnología";
    exit;
}

// 1. Cargar TODOS los departamentos (catálogo)
$sqlDepartamentos = "
    SELECT id_departamento, nombre 
    FROM departamentos
    ORDER BY nombre
";
$stmtDep = $connect->prepare($sqlDepartamentos);
$stmtDep->execute();
$departamentos = $stmtDep->fetchAll(PDO::FETCH_ASSOC);

// 2. Cargar solo las ciudades del departamento actual
$sqlCiudades = "
    SELECT id_ciudad, nombre
    FROM ciudades
    WHERE id_departamento = ?
    ORDER BY nombre
";
$stmtCiu = $connect->prepare($sqlCiudades);
$stmtCiu->execute([$idDeptoActual]);
$ciudades = $stmtCiu->fetchAll(PDO::FETCH_ASSOC);

/*
* LA LOGICA PARA REALIZAR UN UPDATE EN LA BD SERA.:
* UNA VEZ SE PRECIONE EL BOTON DE ACTUALIZAR, SE PROCEDE CON.
* LA COMPARACION DE LOS DATOS ACTUALES EN LA BD VS LOS DEL FORMULARIO.
* EN DADO CASO QUE NO ENCUENTRE DATOS NUEVOS, MOSTRARA QUE NO SE ACTUALIZO.
* SI DETECTA CUALQUIER DATO NUEVO, MENOS LA CEDULA, ESTO GENERA EL UPDATE.
*/
// Si viene un POST del formulario, procesamos actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {

    // 1. Leer datos nuevos del formulario
    $nombreNuevo   = trim($_POST['nombre_completo'] ?? '');
    $telNuevo      = trim($_POST['telefono_actual'] ?? '');
    $emailNuevo    = trim($_POST['email_actual'] ?? '');
    $idDeptoNuevo  = $_POST['id_departamento'] ?? null;
    $idCiudadNuevo = $_POST['id_ciudad'] ?? null;

    // 2. Validar que no haya campos vacíos (excepto cédula que es readonly)
    if (
        $nombreNuevo === '' ||
        $telNuevo === '' ||
        $emailNuevo === '' ||
        empty($idDeptoNuevo) ||
        empty($idCiudadNuevo)
    ) {
        echo "
            <script>
                alert('Todos los campos son obligatorios. Verifique la información ingresada.');
                window.history.back();
            </script>
        ";
        exit;
    }

    // 3. Comparar contra los valores actuales que ya teníamos en $ev
    $hayCambios = false;

    if ($nombreNuevo !== $ev['nombre_completo']) {
        $hayCambios = true;
    }
    if ($telNuevo !== $ev['telefono']) {
        $hayCambios = true;
    }
    if ($emailNuevo !== $ev['email']) {
        $hayCambios = true;
    }
    if ((int)$idDeptoNuevo !== (int)$idDeptoActual) {
        $hayCambios = true;
    }
    if ((int)$idCiudadNuevo !== (int)$idCiudadActual) {
        $hayCambios = true;
    }

    // 4. Si NO hay cambios, solo mostramos alert y no hacemos UPDATE
    if (!$hayCambios) {
        echo "
            <script>
                alert('No se han generado cambios de información.');
                window.location = 'main.php';
            </script>
        ";
        exit;
    }

    // 5. Si SÍ hay cambios → hacer UPDATE
    // Nota: si en tu tabla evangelizados solo se guarda id_ciudad,
    // el id_departamento se deduce de la ciudad. Ajusta según tu modelo.
    $sqlUpdate = "
        UPDATE evangelizados
        SET nombre_completo = ?,
            telefono        = ?,
            email           = ?,
            id_ciudad       = ?,
            id_departamento = ?
        WHERE id_evangelizado = ?
    ";

    $stmtUpdate = $connect->prepare($sqlUpdate);
    $stmtUpdate->execute([
        $nombreNuevo,
        $telNuevo,
        $emailNuevo,
        $idCiudadNuevo,
        $idDeptoNuevo,
        $ev['id_evangelizado']
    ]);

    echo "
        <script>
            alert('Información actualizada correctamente.');
            window.location = 'main.php';
        </script>
    ";
    exit;
}

/*
* LOGICA PARA LOS CURSOS
*/
// Cursos disponibles (solo activos)
$sqlCursos = "
    SELECT 
        id_curso,
        nombre_curso,
        descripcion
    FROM cursos
    WHERE activo = 'S'
    ORDER BY id_curso
";
$stmtCursos = $connect->prepare($sqlCursos);
$stmtCursos->execute();
$cursos = $stmtCursos->fetchAll(PDO::FETCH_ASSOC);

// Selección de curso por parte del evangelizado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['CurseSelected'])) {

    $idCursoSeleccionado = (int) ($_POST['id_curso'] ?? 0);
    $idEvangelizado      = $ev['id_evangelizado'];

    // Validación básica
    if ($idCursoSeleccionado <= 0) {
        echo "
            <script>
                alert('Debe seleccionar un curso válido.');
                window.location = 'main.php';
            </script>
        ";
        exit;
    }

    // Si ya está inscrito actualmente en ese mismo curso, no tiene sentido cambiar
    if (!empty($idCursoActual) && (int)$idCursoActual === $idCursoSeleccionado) {
        echo "
            <script>
                alert('Ya está inscrito actualmente en este curso.');
                window.location = 'main.php';
            </script>
        ";
        exit;
    }

    try {
        // Iniciar transacción para que todo sea consistente
        $connect->beginTransaction();

        // 1) Finalizar cualquier curso EN_CURSO que tenga este evangelizado
        //    -> estado = FINALIZADO, fecha_fin = NOW()
        $sqlFinalizar = "
            UPDATE evangelizados_cursos
            SET estado = 'FINALIZADO',
                fecha_fin = NOW()
            WHERE id_evangelizado = ?
              AND estado = 'EN_CURSO'
        ";
        $stmtFin = $connect->prepare($sqlFinalizar);
        $stmtFin->execute([$idEvangelizado]);

        // 2) Insertar el nuevo curso como EN_CURSO (fecha_inicio = NOW por default)
        $sqlInsertCurso = "
            INSERT INTO evangelizados_cursos (id_evangelizado, id_curso, estado)
            VALUES (?, ?, 'EN_CURSO')
        ";
        $stmtIns = $connect->prepare($sqlInsertCurso);
        $stmtIns->execute([$idEvangelizado, $idCursoSeleccionado]);

        // 3) Actualizar el curso actual en la tabla evangelizados
        $sqlUpdateCurso = "
            UPDATE evangelizados
            SET id_curso = ?
            WHERE id_evangelizado = ?
        ";
        $stmtUpdateCurso = $connect->prepare($sqlUpdateCurso);
        $stmtUpdateCurso->execute([$idCursoSeleccionado, $idEvangelizado]);

        // Confirmar cambios
        $connect->commit();

        echo "
            <script>
                alert('Curso actualizado correctamente.');
                window.location = 'main.php';
            </script>
        ";
        exit;

    } catch (Exception $e) {
        $connect->rollBack();
        echo "<pre>Error al actualizar curso: " . $e->getMessage() . "</pre>";
        exit;
    }
}

// =============================================
// CURSOS DEL EVANGELIZADO (HISTÓRICO)
// =============================================
$sqlMisCursos = "
    SELECT 
        ec.id_evangelizado_curso,
        c.nombre_curso,
        ec.fecha_inicio,
        ec.fecha_fin,
        ec.estado
    FROM evangelizados_cursos ec
    JOIN cursos c ON c.id_curso = ec.id_curso
    WHERE ec.id_evangelizado = ?
    ORDER BY ec.fecha_inicio DESC
";

$stmtMisCursos = $connect->prepare($sqlMisCursos);
$stmtMisCursos->execute([$ev['id_evangelizado']]);
$misCursos = $stmtMisCursos->fetchAll(PDO::FETCH_ASSOC);


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - CTB</title>
    <link rel="stylesheet" href="styles.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.3/css/dataTables.dataTables.min.css">
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
                        <h1>Mi Perfil Evangelizado</h1>
                        <p>Actualice sus datos y acceda a cursos</p>
                    </div>
                </div>

                <div class="nav-image">
                    <img src="images/ctbim1.JPG" alt="Logo CTB">
                </div>
                
                <ul class="nav-menu">
                    <li>
                        <a href="main.php" class="active">Mi Perfil</a>
                    </li>                   
                    <li>
                        <a href="logout.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="container">
        <!-- Header del Perfil -->
        <div class="profile-header">
            <div class="profile-avatar">
                <div class="avatar-icon">👤</div>
            </div>
            <div class="profile-info">
                <h1>Mi Perfil</h1>
                <p>Actualice sus datos personales y acceda a formación</p>
            </div>
            <div class="profile-status">
                <span class="status-badge active">Activo</span>
            </div>
        </div>

        <div class="profile-layout">
            <!-- Columna Izquierda: Datos Personales -->
            <div class="left-column">
                <!-- Formulario de Datos Personales -->
                <section class="form-section">
                    <div class="section-header">
                        <h2>Información Personal</h2>
                        <p>Complete y verifique sus datos de contacto</p>
                    </div>

                    <form id="formulario-perfil" class="modern-form" method="POST">
                        <div class="form-group">
                            <label for="nombre_completo">Nombre Completo *</label>
                            <input type="text" name="nombre_completo" value="<?= htmlspecialchars($ev['nombre_completo'],ENT_QUOTES) ?>" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="cedula_actual">Cédula</label>
                                <input type="text" name="cedula_actual" value="<?= htmlspecialchars($ev['documento'],ENT_QUOTES) ?>"readonly 
                                       class="readonly-field" placeholder="Cédula">
                            </div>
                            
                            <div class="form-group">
                                <label for="telefono_actual">Teléfono WhatsApp *</label>
                                <input type="tel" name="telefono_actual" value="<?= htmlspecialchars($ev['telefono'],ENT_QUOTES) ?>" required 
                                       placeholder="+57 300 123 4567">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email_actual">Correo Electrónico *</label>
                            <input type="email" name="email_actual" value="<?= htmlspecialchars($ev['email'],ENT_QUOTES) ?>" required 
                                   placeholder="ejemplo@correo.com">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="departamento">Departamento *</label>
                                <select name="id_departamento" id="departamento" required>
                                    <option value="">Seleccione un Departamento</option>
                                    <?php foreach ($departamentos as $dep): ?>
                                        <option value="<?= $dep['id_departamento'] ?>"
                                                <?= ($dep['id_departamento'] == $idDeptoActual) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($dep['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="ciudad">Ciudad/Municipio *</label>
                                <select name="id_ciudad" id="ciudad" required>
                                    <option value="">Seleccione una Ciudad</option>
                                    <?php foreach ($ciudades as $ciu): ?>
                                        <option value="<?= $ciu['id_ciudad'] ?>"
                                            <?= ($ciu['id_ciudad'] == $idCiudadActual) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($ciu['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>                            
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-full" id="btn-actualizar" name="update">
                                <span class="button-text">Guardar Cambios</span>
                                <div class="button-loader" style="display: none;">
                                    <div class="loader"></div>
                                </div>
                            </button>
                            <div class="form-note">
                                <small>* La calidad y veracidad de la información, depende única y exclusivamente de usted.</small>
                            </div>
                        </div>
                    </form>
                </section>
            </div>

            <!-- Columna Derecha: Cursos Disponibles -->
            <div class="right-column">
                <!-- Sistema de Cursos con Pestañas -->
                <section class="cursos-section">
                    <div class="section-header">
                        <h2>Cursos Disponibles</h2>
                        <p>Seleccione los programas de formación que desea tomar</p>
                    </div>

                    <div class="cursos-tabs">
                        <div class="tabs-header">
                            <button class="tab-button active" data-tab="cicap">CICAP</button>
                            <button class="tab-button" data-tab="smallcircle">SMALL CIRCLE</button>
                            <button class="tab-button" data-tab="millonalmas">1.000.000 Almas</button>
                        </div>

                        <!-- Pestaña CICAP -->
                        <div class="tab-content active" id="cicap-content">
    <div class="curso-card">
        <div class="curso-header">
            <h3>Capellanía Social CICAP</h3>
            <span class="curso-badge gratis">Gratuito</span>
        </div>
        <div class="curso-description">
            <p>Certificación internacional en capellanía social con aval de derechos humanos. Formación especializada para el servicio ministerial.</p>
        </div>
        <div class="curso-features">
            <div class="feature-item">
                <span class="feature-icon">📜</span>
                <span>Certificación internacional</span>
            </div>
            <div class="feature-item">
                <span class="feature-icon">⚖️</span>
                <span>Enfoque en derechos humanos</span>
            </div>
            <div class="feature-item">
                <span class="feature-icon">👥</span>
                <span>Formación práctica</span>
            </div>
        </div>
        <div class="curso-video">
            <!-- VIDEO INSERTADO CON LA RUTA CORRECTA -->
            <video controls poster="images/CICAP.JPG" class="video-player">
                <source src="images/capellania.mp4" type="video/mp4">
                Tu navegador no soporta el elemento de video.
            </video>
        </div>
        <div class="curso-actions">
            <form method="POST">
                <input type="hidden" name="id_curso" value="1">
                <button type="submit" class="btn btn-success btn-full" name="CurseSelected">
                    Seleccionar este Curso
                </button>
            </form>
        </div>
    </div>
</div>

                        <!-- Pestaña SMALL CIRCLE -->
                        
<div class="tab-content" id="smallcircle-content">
    <div class="curso-card">
        <div class="curso-header">
            <h3>Small Circle - Discipulado</h3>
            <span class="curso-badge gratis">Gratuito</span>
        </div>
        <div class="curso-description">
            <p>Discipulado transformador mediante aplicación móvil y seguimiento personalizado. Crecimiento espiritual en comunidad.</p>
        </div>
        <div class="curso-features">
            <div class="feature-item">
                <span class="feature-icon">📱</span>
                <span>Aplicación móvil incluida</span>
            </div>
            <div class="feature-item">
                <span class="feature-icon">🔍</span>
                <span>Seguimiento personalizado</span>
            </div>
            <div class="feature-item">
                <span class="feature-icon">👨‍👩‍👧‍👦</span>
                <span>Grupos pequeños</span>
            </div>
        </div>
        <div class="curso-video">
            <!-- VIDEO INSERTADO AQUÍ -->
            <video controls poster="images/smallcircle image1.png" class="video-player">
                <source src="images/smallcircle.mp4" type="video/mp4">
                Tu navegador no soporta el elemento de video.
            </video>
        </div>
        <div class="curso-actions">
            <form method="POST">
                <input type="hidden" name="id_curso" value="2">
                <button type="submit" class="btn btn-success btn-full" name="CurseSelected">
                    Seleccionar este Curso
                </button>
            </form>
        </div>
    </div>
</div>

                        <!-- Pestaña Programa 1.000.000 de Almas -->
                        <div class="tab-content" id="millonalmas-content">
    <div class="curso-card">
        <div class="curso-header">
            <h3>Programa 1.000.000 de Almas</h3>
            <span class="curso-badge gratis">Gratuito</span>
        </div>
        <div class="curso-description">
            <p>Formación para evangelizadores con meta de 1 millón de almas en 1000 días. Estrategias efectivas para el ministerio.</p>
        </div>
        <div class="curso-features">
            <div class="feature-item">
                <span class="feature-icon">🎯</span>
                <span>Formación especializada</span>
            </div>
            <div class="feature-item">
                <span class="feature-icon">📚</span>
                <span>Material completo</span>
            </div>
            <div class="feature-item">
                <span class="feature-icon">🌐</span>
                <span>Red de evangelizadores</span>
            </div>
        </div>
        <div class="curso-video">
            <!-- VIDEO INSERTADO AQUÍ -->
            <video controls poster="images/unmillondepredicadoresjpeg.jpeg" class="video-player">
                <source src="images/video4.mp4" type="video/mp4">
                Tu navegador no soporta el elemento de video.
            </video>
        </div>
        <div class="curso-actions">
            <div class="curso-actions">
                <form method="POST">
                    <input type="hidden" name="id_curso" value="3">
                    <button type="submit" class="btn btn-success btn-full"name="CurseSelected">
                        Seleccionar este Curso
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
                    </div>
                </section>

                <!-- Mis Cursos Seleccionados -->
                <section class="mis-cursos-section">
    <div class="section-header">
        <h2>Mis Cursos Seleccionados</h2>
        <p>Programas que ha elegido para su formación</p>
    </div>

    <div class="cursos-seleccionados" id="cursos-seleccionados">
        <?php if (!empty($misCursos)): ?>
            <div class="table-wrapper">
                <table id="tabla-cursos" class="display">
                    <thead>
                        <tr>
                            <th>Curso</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($misCursos as $curso): ?>
                            <tr>
                                <td><?= htmlspecialchars($curso['nombre_curso']) ?></td>
                                <td><?= htmlspecialchars($curso['fecha_inicio']) ?></td>
                                <td>
                                    <?= $curso['fecha_fin'] ? htmlspecialchars($curso['fecha_fin']) : '—' ?>
                                </td>
                                <td>
                                    <?php if ($curso['estado'] === 'EN_CURSO'): ?>
                                        <span class="estado-curso" style="color: #0a7f2e;font-weight:bold;">
                                            En curso
                                        </span>
                                    <?php else: ?>
                                        <span class="estado-curso" style="color: #6c757d;">
                                            Finalizado
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-cursos">
                <div class="no-data-icon">📚</div>
                <p>Aún no ha seleccionado cursos</p>
                <small>Explore las opciones disponibles arriba</small>
            </div>
        <?php endif; ?>
    </div>
</section>
            </div>
        </div>

        <!-- Mensajes de confirmación -->
        <div id="mensaje-actualizacion" class="success-message" style="display: none;">
            <div class="success-content">
                <div class="success-icon">✅</div>
                <div class="success-text">
                    <h3>Datos Actualizados</h3>
                    <p>Su información personal ha sido guardada correctamente.</p>
                </div>
            </div>
        </div>

        <div id="mensaje-curso" class="success-message" style="display: none;">
            <div class="success-content">
                <div class="success-icon">✅</div>
                <div class="success-text">
                    <h3>Curso Agregado</h3>
                    <p>El curso ha sido agregado a su lista de formación.</p>
                </div>
            </div>
        </div>
    </main>

    <footer class="ctb-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Colombia Tierra Bendita</h3>
                    <p>Perfil de Evangelizado</p>
                </div>

                <div class="footer-section">
                    <h4>Soporte</h4>
                    <p>Telefono: 304 5510438</p>
                    <p>Email: soporte@colombiatierrabendita.org</p>
                </div>
            </div>

            <div class="footer-bottom">
                <p>CTB 2025 - Usuario Evangelizado</p>
            </div>
        </div>
    </footer>

    <!-- jQuery (requerido por DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/2.0.3/js/dataTables.min.js"></script>
    <script>
        /* HAGO USO DE UN DATATABLE PARA VER LA LISTA DE CURSOS*/
        $(document).ready(function () {
            if ($('#tabla-cursos').length) {
                $('#tabla-cursos').DataTable({
                    language: {
                        url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
                    },
                    pageLength: 5,
                    lengthChange: false
                });
            }
        });


        //Llamo la funcion para navegar entre pestannas
        configurarTabs();

        // Sistema de pestañas
        function configurarTabs() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');

            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    
                    // Remover clase active de todos los botones y contenidos
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Agregar clase active al botón y contenido actual
                    this.classList.add('active');
                    document.getElementById(tabId + '-content').classList.add('active');
                });
            });
        }

        // Efectos de video
        document.querySelectorAll('.video-placeholder').forEach(placeholder => {
            placeholder.addEventListener('click', function() {
                this.innerHTML = `
                    <div class="video-playing">
                        <div class="playing-icon">🎬</div>
                        <p>Reproduciendo video...</p>
                        <small>Funcionalidad en desarrollo</small>
                    </div>
                `;
            });
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
</body>
</html>