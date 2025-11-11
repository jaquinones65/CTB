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
}else if($rol != 1){
    print "
        <script>
            alert(\"Acceso invalido!\");
            window.location='./';
        </script>";
}


/*código requerido para alimentar las cifras desde la base de datos*/


// Fechas
$fecha_meta = new DateTime('2026-12-15');
$fecha_actual = new DateTime();

// Calculo los dias restantes
$dias_restantes = $fecha_actual->diff($fecha_meta)->days;

// Obtengo el total de almas comprometidas por todos los evangelizadores
$stmt = $connect->query("SELECT SUM(numero_almas) AS total_almas FROM evangelistas");
$resultado = $stmt->fetch(PDO::FETCH_ASSOC);
$total_almas = $resultado['total_almas'] ?? 0;

// Calcular porcentaje
$meta_total = 1000000;
$porcentaje = ($total_almas / $meta_total) * 100;

/*TOP 5 EVANGELIZADOS CON MAS ALMAS POR ALCANZAR*/
$sqlTop = "
    SELECT
        e.nombre_completo,
        e.numero_almas,
        d.nombre                AS departamento,
        c.nombre                AS ciudad
    FROM evangelistas e
    LEFT JOIN departamentos d
        ON d.id_departamento = e.id_departamento
    LEFT JOIN ciudades c
        ON c.id_ciudad = e.id_ciudad
    ORDER BY e.numero_almas DESC
    LIMIT 5;
";

$stmtTop = $connect->prepare($sqlTop);
$stmtTop->execute();
$topEvangelistas = $stmtTop->fetchAll(PDO::FETCH_ASSOC);

/* TOP 3 DEPARTAMENTOS CON MÁS EVANGELIZADOS */
$sqlTopDeptos = "
    SELECT 
        d.id_departamento,
        d.nombre AS departamento,
        COUNT(DISTINCT ev.Evangelistas_documento) AS total_evangelizadores,
        COUNT(ev.id_evangelizado)                 AS personas_alcanzadas
    FROM evangelizados ev
    JOIN departamentos d 
        ON d.id_departamento = ev.id_departamento
    GROUP BY d.id_departamento, d.nombre
    ORDER BY personas_alcanzadas DESC
    LIMIT 3;
";

$stmtTopDeptos = $connect->prepare($sqlTopDeptos);
$stmtTopDeptos->execute();
$topDepartamentos = $stmtTopDeptos->fetchAll(PDO::FETCH_ASSOC);

/*CONSULTA DATATABLE*/
$sqlEvTabla = "
    SELECT 
        e.id_evangelistas,
        e.nombre_completo,
        e.telefono,
        e.email,
        e.numero_almas,
        COUNT(ev.id_evangelizado) AS cantidad_evangelizados,
        d.nombre AS departamento,
        c.nombre AS ciudad
    FROM evangelistas e
    LEFT JOIN evangelizados ev 
        ON ev.Evangelistas_documento = e.documento
    LEFT JOIN departamentos d 
        ON d.id_departamento = e.id_departamento
    LEFT JOIN ciudades c 
        ON c.id_ciudad = e.id_ciudad
    GROUP BY 
        e.id_evangelistas,
        e.nombre_completo,
        e.telefono,
        e.email,
        e.numero_almas,
        d.nombre,
        c.nombre
    ORDER BY cantidad_evangelizados DESC, e.nombre_completo ASC;
";

$stmtEvTabla = $connect->prepare($sqlEvTabla);
$stmtEvTabla->execute();
$listaEvangelistas = $stmtEvTabla->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Ministerio - CTB</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet"
      href="https://cdn.datatables.net/2.0.3/css/dataTables.dataTables.min.css">
</head>
<body>
      <header class="ctb-header">
        <div class="header-top">
            <div class="container">
                <div class="header-contact">
                    <span>Telefono: 322 7721323</span>
                    <span>Email: pastor@colombiatierrabendita.org</span>
                </div>
            </div>
        </div>
        
        <nav class="ctb-nav">
            <div class="container">
                <div class="nav-brand">
                    <div class="logo">CTB</div>
                    <div class="brand-text">
                        <h1>Dashboard Ministerio</h1>
                        <p>Métricas de Evangelización</p>
                    </div>
                </div>

                <div class="nav-image">
                    <img src="images/ctbim1.JPG" alt="Logo CTB">
                </div>
                
                <ul class="nav-menu">
                    <li><a href="main.php">Inicio</a></li>
                    <li><a href="logout.php">Cerrar Sesión</a></li>
                </ul>
            </div>
        </nav>
    </header>
    <main class="container">
        <!-- Progreso Meta 1 Millón de Almas -->
        <section class="meta-section">
            <div class="meta-header">
                <h2>Meta: 1.000.000 de Almas en 1000 Días</h2>
                <div class="meta-info">
                    <span class="days-remaining">Días restantes: 
                        <strong><?php echo $dias_restantes; ?></strong>
                    </span>
                    <span class="completion-date">Fecha meta: 
                        <?php echo $fecha_meta->format('d M Y'); ?>
                    </span>
                </div>
            </div>
            <div class="progress-container">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $porcentaje; ?>%;"></div>
                </div>
                <div class="progress-stats">
                    <div class="progress-number">
                        <?php echo number_format($total_almas, 0, ',', '.'); ?>
                    </div>
                    <div class="progress-label">Almas alcanzadas (<?php echo number_format($porcentaje, 1); ?>%)</div>
                </div>
            </div>
        </section>

        <!-- TOP 5 -->
        <section class="results-section">
            <h2>Resultados del Seguimiento - Top 5</h2>
            <div class="evangelizadores-results" id="resultados-evangelizadores">
                <?php if (!empty($topEvangelistas)): ?>
                    <?php foreach ($topEvangelistas as $ev): ?>
                        <div class="evangelizador-result-card">
                            <h4><?= htmlspecialchars($ev['nombre_completo']) ?></h4>
                            <div class="evangelizador-info">
                                <div class="evangelizador-stat">
                                    <span class="stat-label">Departamento:</span>
                                    <span class="stat-value">
                                        <?= htmlspecialchars($ev['departamento'] ?? 'Sin información') ?>
                                    </span>
                                </div>
                                <div class="evangelizador-stat">
                                    <span class="stat-label">Ciudad:</span>
                                    <span class="stat-value">
                                        <?= htmlspecialchars($ev['ciudad'] ?? 'Sin información') ?>
                                    </span>
                                </div>
                                <div class="evangelizador-stat">
                                    <span class="stat-label">Personas alcanzadas:</span>
                                    <span class="stat-value">
                                        <?= number_format((int)$ev['numero_almas'], 0, ',', '.'); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="evangelizador-result-card">
                        <h4>Sin registros</h4>
                        <div class="evangelizador-info">
                            <div class="evangelizador-stat">
                                <span class="stat-label">Información:</span>
                                <span class="stat-value">Aún no hay evangelistas registrados con número de almas.</span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Áreas de Mayor Crecimiento - TOP 3-->
        <section class="results-section">
            <h2>Áreas de Mayor Crecimiento</h2>
            <div class="evangelizadores-results">
                <?php if (!empty($topDepartamentos)): ?>
                    <?php foreach ($topDepartamentos as $dep): ?>
                        <div class="evangelizador-result-card">
                            <h4><?= htmlspecialchars($dep['departamento']) ?></h4>
                            <div class="evangelizador-info">
                                <div class="evangelizador-stat">
                                    <span class="stat-label">Total evangelizadores:</span>
                                    <span class="stat-value">
                                        <?= (int)$dep['total_evangelizadores'] ?>
                                    </span>
                                </div>
                                <div class="evangelizador-stat">
                                    <span class="stat-label">Personas alcanzadas:</span>
                                    <span class="stat-value">
                                        <?= number_format((int)$dep['personas_alcanzadas'], 0, ',', '.'); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="evangelizador-result-card">
                        <h4>Sin datos disponibles</h4>
                        <div class="evangelizador-info">
                            <div class="evangelizador-stat">
                                <span class="stat-label">Información:</span>
                                <span class="stat-value">
                                    Aún no se registran evangelizados para calcular las áreas de mayor crecimiento.
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Detalle de Evangelizadores (DataTable) -->
        <section class="results-section">
            <h2>Detalle de Evangelizadores</h2>
            <p>Listado de evangelizadores con sus datos de contacto y resultados de evangelización.</p>

            <div class="evangelizadores-results">
                <div class="evangelizador-result-card" style="width:100%;">
                    <div class="evangelizador-info">
                        <div class="table-wrapper tabla-pastores">
                            <table id="tabla-evangelistas" class="display cell-border stripe" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th>Nombre completo</th>
                                        <th>Teléfono</th>
                                        <th>Email</th>
                                        <th>Personas comprometidas<br>(numero_almas)</th>
                                        <th>Cantidad evangelizados</th>
                                        <th>Departamento</th>
                                        <th>Ciudad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($listaEvangelistas as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
                                            <td><?= htmlspecialchars($row['telefono']) ?></td>
                                            <td><?= htmlspecialchars($row['email']) ?></td>
                                            <td><?= number_format((int)$row['numero_almas'], 0, ',', '.') ?></td>
                                            <td><?= (int)$row['cantidad_evangelizados'] ?></td>
                                            <td><?= htmlspecialchars($row['departamento'] ?? 'Sin info') ?></td>
                                            <td><?= htmlspecialchars($row['ciudad'] ?? 'Sin info') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>                
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="ctb-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Dashboard Ministerio CTB</h3>
                    <p>Actualizado hace 2 horas - Próxima actualización: 18:00</p>
                </div>
                <div class="footer-section">
                    <button class="btn btn-outline">Descargar Reporte</button>
                    <button class="btn btn-outline">Exportar Datos</button>
                </div>
            </div>
        </div>
    </footer>
    <!-- jQuery (requerido por DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/2.0.3/js/dataTables.min.js"></script>
    <script>
        // DATA TABLE
        $('#tabla-evangelistas').DataTable({
            pageLength: 5,
            lengthMenu: [ [5, 10, 25, 50, 100, 200], [5, 10, 25, 50, 100, 200] ],
            order: [[4, 'desc']]
        });
    </script>
</body>
</html>