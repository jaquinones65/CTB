<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
//Incluyo el enlace de conexion
include_once("conexion/conection.php");


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_departamento = $_POST['id_departamento'] ?? null;
    $id_ciudad = $_POST['id_ciudad'] ?? null;

    // Validación mínima
    if (!$id_departamento || !$id_ciudad) {
        echo json_encode([]);
        exit;
    }

    // Consulta a evangelistas según ciudad y departamento
    $sql = "
        SELECT 
            e.id_evangelista,
            e.nombre_completo
        FROM evangelistas e
        INNER JOIN ciudades c ON e.id_ciudad = c.id_ciudad
        INNER JOIN departamentos d ON c.id_departamento = d.id_departamento
        WHERE c.id_ciudad = :id_ciudad
          AND d.id_departamento = :id_departamento
        ORDER BY e.nombre_completo ASC
    ";
    $stmt = $connect->prepare($sql);
    $stmt->execute([
        ':id_ciudad' => $id_ciudad,
        ':id_departamento' => $id_departamento
    ]);

    $evangelizadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Si no hay registros, devolvemos un arreglo vacío
    echo json_encode($evangelizadores);
}
?>