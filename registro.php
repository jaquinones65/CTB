<!--
ESTA SECCION ES CREADA PARA QUE CARGUE LOS QUERYS Y SE ALIMENTE
TODOS LOS SELECTS.
-->
<?php 
//Incluyo el enlace de conexion
include_once("conexion/conection.php");

#Ahora creo la consulta que genera la informacion del departamento
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
    <title>Registro - Colombia Tierra Bendita</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="login-body">
    <div class="login-container">
        <!-- Header del Registro -->
        <div class="login-header">
            <div class="login-logo">
                <img src="images/BANNER.WEBP" alt="CTB Logo">
            </div>
            <h1>Crear Cuenta</h1>
            <p>Únete a Colombia Tierra Bendita</p>
        </div>

        <!-- Formulario de Registro -->
        <form id="formulario-registro" class="login-form" method="POST" action="">
            <!-- Información Personal -->
            <div class="form-section-title">
                <h3>Información Personal</h3>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="nombre">Nombre Completo *</label>
                    <input type="text" id="nombre" name="nombre" required 
                           placeholder="Ingrese su nombre completo">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="cedula">Número de Cédula *</label>
                    <input type="text" id="cedula" name="cedula" required 
                           placeholder="Solo números" pattern="[0-9]+">
                    <small class="form-text">Solo se permiten números</small>
                </div>
                
                <div class="form-group">
                    <label for="telefono">Teléfono WhatsApp *</label>
                    <input type="tel" id="telefono" name="telefono" required 
                           placeholder="+57 300 123 4567">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Correo Electrónico *</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="ejemplo@correo.com">
                </div>
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

            <!-- Campo condicional para Evangelizador -->
            <div id="campo-evangelizador" class="form-group role-dependent">
               <div class="form-group">
                    <label for="almas_ganar">¿Cuántas almas está dispuesto a ganar? *</label>
                <input type="number" id="almas_ganar" name="almas_ganar" min="200" placeholder="Ej: 200">
                <small class="form-text">Mínimo 200 almas para solicitar biblias donadas</small>
               </div>
            </div>

            <!-- Aviso para Evangelizados -->
            <div id="aviso-evangelizado" class="form-notice role-dependent" style="display: none;">
                <div class="notice-box">
                    <h4>Importante para Evangelizados</h4>
                    <p>Su registro debe ser previamente autorizado por un evangelizador registrado en la plataforma.</p>
                </div>
            </div>

            <!-- Políticas y Términos -->
            <div class="form-group">
                <div class="checkbox-container large">
                    <input type="checkbox" id="politicas" name="politicas" required>
                    <span class="checkmark"></span>
                    <label for="politicas">
                        Acepto la política de tratamiento de datos y los términos del servicio
                    </label>
                </div>
                <div class="privacy-link">
                    <small>Al hacer clic en el checkbox podrá leer nuestra política de privacidad completa</small>
                </div>
            </div>

            <button type="submit" class="login-button" name="insert">
                <span class="button-text">Crear Cuenta</span>
                <div class="button-loader" style="display: none;">
                    <div class="loader"></div>
                </div>
            </button>
        </form>

                <div class="login-footer">
            <p><a href="index.html">← Volver al Inicio</a></p>
            <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
            <p>¿Eres una entidad aliada? <a href="login-alianzas.html">Acceso especial</a></p>
        </div>

        <!-- Mensajes de estado -->
        <div id="registro-message" class="login-message" style="display: none;"></div>
    </div>

    <!-- Modal de Política de Privacidad -->
    <div id="modal-politica" class="modal-politica" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Política de Tratamiento de Datos</h2>
                <button class="modal-close" id="cerrar-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="politica-scroll">
                    <div class="politica-section">
                        <h3>1. Responsable del Tratamiento</h3>
                        <p><strong>COLOMBIA TIERRA BENDITA - CTB</strong><br>
                        Dirección: Cali, Valle del Cauca, Colombia<br>
                        Teléfono: +57 322 7721323<br>
                        Email: privacidad@colombiatierrabendita.org</p>
                    </div>

                    <div class="politica-section">
                        <h3>2. Finalidad del Tratamiento</h3>
                        <p>Sus datos serán utilizados para:</p>
                        <ul>
                            <li>Gestionar su registro en la plataforma CTB</li>
                            <li>Facilitar su participación en programas de evangelización</li>
                            <li>Coordinar la distribución de biblias donadas</li>
                            <li>Brindar acceso a cursos de formación ministerial</li>
                            <li>Generar reportes para donantes internacionales</li>
                            <li>Medir el impacto del programa "1 Millón de Almas"</li>
                        </ul>
                    </div>

                    <div class="politica-section">
                        <h3>3. Datos que Recopilamos</h3>
                        <div class="datos-lista">
                            <div class="dato-item">
                                <strong>Datos Identificativos:</strong> Nombre, cédula
                            </div>
                            <div class="dato-item">
                                <strong>Datos de Contacto:</strong> Teléfono, email, dirección
                            </div>
                            <div class="dato-item">
                                <strong>Datos Demográficos:</strong> Ciudad, departamento, iglesia
                            </div>
                            <div class="dato-item">
                                <strong>Datos Ministeriales:</strong> Compromiso de almas, cursos
                            </div>
                        </div>
                    </div>

                    <div class="politica-section">
                        <h3>4. Compartición de Datos</h3>
                        <p>Sus datos podrán ser compartidos con:</p>
                        <ul>
                            <li><strong>Vision Beyond Borders:</strong> Para gestionar donaciones de biblias</li>
                            <li><strong>Autoridades competentes:</strong> Cuando sea requerido por ley</li>
                        </ul>
                        <p><em>No comercializamos sus datos.</em></p>
                    </div>

                    <div class="politica-section">
                        <h3>5. Sus Derechos</h3>
                        <p>Usted tiene derecho a:</p>
                        <div class="derechos-grid">
                            <span class="derecho">Conocer sus datos</span>
                            <span class="derecho">Actualizar información</span>
                            <span class="derecho">Suprimir datos</span>
                            <span class="derecho">Revocar consentimiento</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <p class="aceptacion-text">Al hacer clic en "Aceptar Política", usted autoriza el tratamiento de sus datos conforme a lo establecido.</p>
                <p class="aceptacion-text"><u>Si no se aceptan las políticas, no se podrá continuar con el registro de los datos.</u></p>
                <div class="modal-actions">
                    <button type="button" class="btn btn-outline" id="rechazar-politica">Rechazar</button>
                    <button type="button" class="btn btn-primary" id="aceptar-politica">Aceptar Política</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Overlay para el modal -->
    <div id="modal-overlay" class="modal-overlay" style="display: none;"></div>

    <script>
        
        // Modal de Política de Privacidad
        document.getElementById('politicas').addEventListener('click', function(e) {
            if (this.checked) {
                abrirModalPolitica();
                this.checked = false;
            }
        });

        function abrirModalPolitica() {
            document.getElementById('modal-politica').style.display = 'block';
            document.getElementById('modal-overlay').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function cerrarModalPolitica() {
            document.getElementById('modal-politica').style.display = 'none';
            document.getElementById('modal-overlay').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Event listeners para el modal
        document.getElementById('cerrar-modal').addEventListener('click', cerrarModalPolitica);
        document.getElementById('modal-overlay').addEventListener('click', cerrarModalPolitica);

        document.getElementById('rechazar-politica').addEventListener('click', function() {
            cerrarModalPolitica();
            document.getElementById('politicas').checked = false;
        });

        document.getElementById('aceptar-politica').addEventListener('click', function() {
            document.getElementById('politicas').checked = true;
            cerrarModalPolitica();
        });

        // Cerrar modal con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cerrarModalPolitica();
                document.getElementById('politicas').checked = false;
            }
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

        /*

        // Validación del formulario
        document.getElementById('formulario-registro').addEventListener('submit', function(e) {
            //e.preventDefault();
            
            const button = document.querySelector('.login-button');
            const buttonText = document.querySelector('.button-text');
            const loader = document.querySelector('.button-loader');
            const message = document.getElementById('registro-message');
            
            // Mostrar loading
            buttonText.style.display = 'none';
            loader.style.display = 'flex';
            button.disabled = true;
            
            // Validaciones básicas
            const cedula = document.getElementById('cedula').value;
            const email = document.getElementById('email').value;
            const rol = document.getElementById('rol').value;
            const almasGanar = document.getElementById('almas_ganar').value;
            const politicas = document.getElementById('politicas').checked;
            
            // Validar cédula solo números
            if (!/^\d+$/.test(cedula)) {
                showMessage('La cédula debe contener solo números', 'error');
                resetButton();
                return;
            }
            
            // Validar email
            if (!email.includes('@') || !email.includes('.')) {
                showMessage('Por favor ingrese un email válido', 'error');
                resetButton();
                return;
            }
            
            // Validar campo de almas para evangelizadores
            if (rol === 'evangelizador' && (!almasGanar || almasGanar < 1)) {
                showMessage('Los evangelizadores deben especificar cuántas almas ganarán', 'error');
                resetButton();
                return;
            }

            // Validar políticas aceptadas
            if (!politicas) {
                showMessage('Debe aceptar la política de tratamiento de datos para continuar', 'error');
                resetButton();
                return;
            }
            /*
            // Simular envío exitoso
            setTimeout(() => {
                showMessage('¡Registro exitoso! Será redirigido al login', 'success');
                
                setTimeout(() => {
                   // window.location.href = 'login.html';
                }, 2000);
            }, 1500);
        });

        function showMessage(text, type) {
            const message = document.getElementById('registro-message');
            message.textContent = text;
            message.className = `login-message ${type}`;
            message.style.display = 'block';
        }

        function resetButton() {
            const buttonText = document.querySelector('.button-text');
            const loader = document.querySelector('.button-loader');
            const button = document.querySelector('.login-button');
            
            buttonText.style.display = 'block';
            loader.style.display = 'none';
            button.disabled = false;
        }

        // Efectos de focus en inputs
        document.querySelectorAll('input, select').forEach(element => {
            element.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            element.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.classList.remove('focused');
                }
            });
        });*/
    </script>

</body>
</html>

<!-- INICIO DEL CODIGO PHP -->
<?php 

//Incluyo archivo de conexion
include_once("conexion/conection.php");

//Logica para guardar
if (isset($_POST['insert'])) {
    //Variables requeridas
    $fechaCreacion = date('Y-m-d');
    $nombre = $_POST['nombre'];
    $documento = $_POST['cedula'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    if($num_almas = $_POST['almas_ganar'] < 200) {
        echo "
            <script>
                console.log('Recuerda que el minimo de almas, debe de ser 200; Has puesto: $num_almas');
            </script>
        ";
    }else{
        $num_almas = $_POST['almas_ganar'];
    }
    if($ter_condiciones = $_POST['politicas'] == "on"){
        $ter_condiciones = "S";
    }
    $rol = 2;
    $departamento = $_POST['departamento'];
    $ciudad = $_POST['ciudad'];

    #Test de evaluación mediante la consola del navegador
    /*
    echo "
            <script>
                console.log('FechaCreacion: $fechaCreacion');
                console.log('Nombre: $nombre');
                console.log('Documento: $documento');
                console.log('Teléfono: $telefono');
                console.log('Email: $email');
                console.log('Num_Almas: $num_almas');
                console.log('Acepta: $ter_condiciones');
                console.log('Rol: $rol');
                console.log('Departamento: $departamento');
                console.log('Ciudad: $ciudad');
            </script>
        ";
    */
    
    //Valido que el usuario que se este registrando no exista

    //Preparo consulta para su ejecucion
    $query = $connect->prepare("SELECT id_evangelistas FROM evangelistas WHERE documento = ? LIMIT 1;");

    //Ejecuto la consulta pasando los parametros solicitados
    $query->execute([$documento]);

    //Convierto el resultado en valor numerico, mejor manejo
    $cantRegistros = $query->rowCount();

    //Ahora, si el resultado es cero, no existe, de lo contrario ya existe.
    if($cantRegistros <= 0){
        //Si ingresa aqui, no existe, por ende, lo creo

        //Preparo la insercion
        $insert = $connect->prepare("INSERT INTO evangelistas (fecha_registro, nombre_completo, documento, telefono, email, numero_almas, acepta, id_rol, id_departamento, id_ciudad) VALUES (?,?,?,?,?,?,?,?,?,?)");

        //Ejecuto la cadena preparada
        $insert->execute([$fechaCreacion, $nombre, $documento, $telefono, $email, $num_almas, $ter_condiciones, $rol, $departamento, $ciudad]);

        echo "
            <script language='JavaScript'>
                alert('El Evangelizador ' + '$nombre' + ' $apellido' + ' Fue Registrado Exitosamente!!!');
            </script>
            ";
            ?>
            <script type="text/javascript">
              window.location.href='login.php';//Ruta a donde direcciona si ya existe el trabajador
            </script>
            <?php


    }else{
        //Esto se visualizara si el usuario ya existe
            echo "
                <script>
                    alert('⚠️ Lo siento, el Evangelista $nombre ya se encuentra registrado con la cédula $documento.');
                </script>
            ";
    }




}
?>