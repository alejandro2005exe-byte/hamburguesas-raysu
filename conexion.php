
<?php
// Conexion a la base de datos del sistema de pedidos

$servidor = getenv("DB_HOST") ?: "localhost";
$usuario  = getenv("DB_USER") ?: "root";
$clave    = getenv("DB_PASSWORD") ?: "";
$base     = getenv("DB_NAME") ?: "SistemaPedidos";
$puerto   = (int) (getenv("DB_PORT") ?: 3306);

mysqli_report(MYSQLI_REPORT_OFF);

$conexion = @new mysqli(
    $servidor,
    $usuario,
    $clave,
    $base,
    $puerto
);

if ($conexion->connect_error) {
    error_log("Error de conexion MySQL: " . $conexion->connect_error);
    http_response_code(500);
    exit("No se pudo conectar a la base de datos. Verifica la configuracion del servidor.");
}

if (!$conexion->set_charset("utf8mb4")) {
    error_log("No se pudo configurar utf8mb4.");
    http_response_code(500);
    exit("Error al configurar la conexion.");
}
?>
