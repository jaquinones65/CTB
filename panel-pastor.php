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

//Para traer los departamentos
$divipol = $connect->query("
    SELECT
        id_departamento   as id_departamento,
        nombre            as departamento
    FROM departamentos
    ORDER BY nombre ASC;
    ");

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Ministerio - CTB</title>
    <link rel="stylesheet" href="styles.css">
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
                    <li><a href="index.html">Inicio</a></li>
                    <li><a href="panel-pastor.html" class="active">Dashboard</a></li>
                    <li><a href="solicitudes-vbb.html">Solicitudes VBB</a></li>
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

        <!-- Sistema de Filtros Mejorado -->
        <section class="filters-section">
            <h2>Filtros de Seguimiento</h2>
            <div class="filters-grid">
                <div class="filter-group">
                    <label for="departamento">Departamentos</label>
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
                
                <div class="filter-group">
                    <label for="ciudad">Ciudades/Municipios</label>
                    <select id="ciudad" name="ciudad" required="">
                        <option value="">Seleccione una Ciudad/Municipio</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="evangelizador">Todos los Evangelizadores</label>
                    <select id="evangelizador" name="evangelizador" required="">
                        <option value="">Seleccione un Evangelizador</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <button class="btn btn-primary" id="btn-aplicar-filtros">Aplicar Filtros</button>
                </div>
            </div>
        </section>

        <!-- Resultados de Filtros -->
        <section class="results-section">
            <h2>Resultados del Seguimiento</h2>
            <div class="evangelizadores-results" id="resultados-evangelizadores">
                <div class="evangelizador-result-card">
                    <h4>Carlos Rodríguez</h4>
                    <div class="evangelizador-info">
                        <div class="evangelizador-stat">
                            <span class="stat-label">Departamento:</span>
                            <span class="stat-value">Valle del Cauca</span>
                        </div>
                        <div class="evangelizador-stat">
                            <span class="stat-label">Ciudad:</span>
                            <span class="stat-value">Cali</span>
                        </div>
                        <div class="evangelizador-stat">
                            <span class="stat-label">Personas alcanzadas:</span>
                            <span class="stat-value">245</span>
                        </div>
                        <div class="evangelizador-stat">
                            <span class="stat-label">Crecimiento mensual:</span>
                            <span class="stat-value">+15%</span>
                        </div>
                    </div>
                </div>
                
                <div class="evangelizador-result-card">
                    <h4>María González</h4>
                    <div class="evangelizador-info">
                        <div class="evangelizador-stat">
                            <span class="stat-label">Departamento:</span>
                            <span class="stat-value">Valle del Cauca</span>
                        </div>
                        <div class="evangelizador-stat">
                            <span class="stat-label">Ciudad:</span>
                            <span class="stat-value">Palmira</span>
                        </div>
                        <div class="evangelizador-stat">
                            <span class="stat-label">Personas alcanzadas:</span>
                            <span class="stat-value">189</span>
                        </div>
                        <div class="evangelizador-stat">
                            <span class="stat-label">Crecimiento mensual:</span>
                            <span class="stat-value">+12%</span>
                        </div>
                    </div>
                </div>
                
                <div class="evangelizador-result-card">
                    <h4>Pedro López</h4>
                    <div class="evangelizador-info">
                        <div class="evangelizador-stat">
                            <span class="stat-label">Departamento:</span>
                            <span class="stat-value">Bogotá D.C.</span>
                        </div>
                        <div class="evangelizador-stat">
                            <span class="stat-label">Ciudad:</span>
                            <span class="stat-value">Bogotá</span>
                        </div>
                        <div class="evangelizador-stat">
                            <span class="stat-label">Personas alcanzadas:</span>
                            <span class="stat-value">312</span>
                        </div>
                        <div class="evangelizador-stat">
                            <span class="stat-label">Crecimiento mensual:</span>
                            <span class="stat-value">+18%</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Áreas de Mayor Crecimiento -->
        <section class="results-section">
            <h2>Áreas de Mayor Crecimiento</h2>
            <div class="evangelizadores-results">
                <div class="evangelizador-result-card">
                    <h4>Valle del Cauca - Cali</h4>
                    <div class="evangelizador-info">
                        <div class="evangelizador-stat">
                            <span class="stat-label">Total evangelizadores:</span>
                            <span class="stat-value">45</span>
                        </div>
                        <div class="evangelizador-stat">
                            <span class="stat-label">Personas alcanzadas:</span>
                            <span class="stat-value">15,234</span>
                        </div>
                        <div class="evangelizador-stat">
                            <span class="stat-label">Crecimiento trimestral:</span>
                            <span class="stat-value">+28%</span>
                        </div>
                    </div>
                </div>
                
                <div class="evangelizador-result-card">
                    <h4>Bogotá D.C.</h4>
                    <div class="evangelizador-info">
                        <div class="evangelizador-stat">
                            <span class="stat-label">Total evangelizadores:</span>
                            <span class="stat-value">38</span>
                        </div>
                        <div class="evangelizador-stat">
                            <span class="stat-label">Personas alcanzadas:</span>
                            <span class="stat-value">12,567</span>
                        </div>
                        <div class="evangelizador-stat">
                            <span class="stat-label">Crecimiento trimestral:</span>
                            <span class="stat-value">+22%</span>
                        </div>
                    </div>
                </div>
                
                <div class="evangelizador-result-card">
                    <h4>Antioquia - Medellín</h4>
                    <div class="evangelizador-info">
                        <div class="evangelizador-stat">
                            <span class="stat-label">Total evangelizadores:</span>
                            <span class="stat-value">32</span>
                        </div>
                        <div class="evangelizador-stat">
                            <span class="stat-label">Personas alcanzadas:</span>
                            <span class="stat-value">10,845</span>
                        </div>
                        <div class="evangelizador-stat">
                            <span class="stat-label">Crecimiento trimestral:</span>
                            <span class="stat-value">+19%</span>
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
    <script>
        //Actualizar las ciudades en base al departamento
        document.getElementById('departamento').addEventListener('change', function() {
            const idDepartamento = this.value;
            const ciudadSelect = document.getElementById('ciudad');
            const evangelizadorSelect = document.getElementById('evangelizador');
            ciudadSelect.innerHTML = `<option value="">Cargando...</option>`;
            evangelizadorSelect.innerHTML = `<option value="">Seleccione una Ciudad/Municipio</option>`;
            
            if (idDepartamento !== '') {
                fetch('get_ciudades.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `id_departamento=${encodeURIComponent(idDepartamento)}`
                })
                .then(response => response.json())
                .then(data => {
                    ciudadSelect.innerHTML = `<option value="">Seleccione una Ciudad/Municipio</option>`;
                    data.forEach(ciudad => {
                        ciudadSelect.innerHTML += `<option value="${ciudad.id_ciudad}">${ciudad.nombre}</option>`;
                    });
                })
                .catch(error => {
                    ciudadSelect.innerHTML = `<option value="">Error al cargar las ciudades</option>`;
                    console.error('Error:', error);
                });
            } else {
                ciudadSelect.innerHTML = `<option value="">Seleccione primero un Departamento</option>`;
            }
        });
        
        // Nuevo: actualizar evangelizadores al cambiar ciudad
        document.getElementById('ciudad').addEventListener('change', function() {
            const idCiudad = this.value;
            const idDepartamento = document.getElementById('departamento').value;
            const evangelizadorSelect = document.getElementById('evangelizador');
            evangelizadorSelect.innerHTML = `<option value="">Cargando evangelizadores...</option>`;
            
            if (idCiudad !== '' && idDepartamento !== '') {
                fetch('get_evangelizadores.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `id_departamento=${encodeURIComponent(idDepartamento)}&id_ciudad=${encodeURIComponent(idCiudad)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        evangelizadorSelect.innerHTML = `<option value="">Seleccione un Evangelizador</option>`;
                        data.forEach(e => {
                            evangelizadorSelect.innerHTML += `<option value="${e.id_evangelista}">${e.nombre_completo}</option>`;
                        });
                    } else {
                        evangelizadorSelect.innerHTML = `<option value="">No existen evangelizadores</option>`;
                    }
                })
                .catch(error => {
                    evangelizadorSelect.innerHTML = `<option value="">Error al cargar evangelizadores</option>`;
                    console.error('Error:', error);
                });
            } else {
                evangelizadorSelect.innerHTML = `<option value="">Seleccione primero un departamento y ciudad</option>`;
            }
        });
    </script>

</body>
</html>