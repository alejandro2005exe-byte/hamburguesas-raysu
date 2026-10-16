
<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/funciones.php';
require_once __DIR__ . '/auth.php';

exigirAcceso('inicio');
$usuarioActual = usuarioActual();

// Funcion para consultar un total de forma segura
function obtenerTotal($conexion, $sql) {
    $resultado = $conexion->query($sql);

    if (!$resultado) {
        error_log("Error SQL: " . $conexion->error);
        return 0;
    }

    $fila = $resultado->fetch_assoc();
    return $fila['total'] ?? 0;
}

// Estadisticas generales
$totalPedidos = obtenerTotal(
    $conexion,
    "SELECT COUNT(*) AS total
     FROM Pedido_General
     WHERE estado <> 'Devuelto'"
);

$totalVendido = obtenerTotal(
    $conexion,
    "SELECT IFNULL(SUM(subtotal), 0) AS total
     FROM v_detalle_neto"
);

$totalClientes = obtenerTotal(
    $conexion,
    "SELECT COUNT(*) AS total FROM Cliente"
);

// Ingrediente con menor cantidad
$resultadoIngrediente = $conexion->query(
    "SELECT nombre, cantidad_actual, unidad_medida
     FROM Ingrediente
     ORDER BY cantidad_actual ASC
     LIMIT 1"
);

$ingredienteBajo = $resultadoIngrediente
    ? $resultadoIngrediente->fetch_assoc()
    : null;

// Producto mas vendido
$resultadoProducto = $conexion->query(
    "SELECT p.nombre, SUM(d.cantidad) AS total_vendido
     FROM v_detalle_neto d
     INNER JOIN Producto p
         ON d.id_producto = p.id_producto
     GROUP BY p.id_producto, p.nombre
     ORDER BY total_vendido DESC
     LIMIT 1"
);

$productoMasVendido = $resultadoProducto
    ? $resultadoProducto->fetch_assoc()
    : null;

$titulo = 'Inicio';
$mostrarBanner = true;

include __DIR__ . '/menu.php';
?>

<h2>Panel de Control</h2>

<p class="bienvenida">
    Bienvenido/a,
    <?php echo h($usuarioActual['nombre'] ?? 'Usuario'); ?>
    (<?php echo h($usuarioActual['rol'] ?? ''); ?>)
</p>

<?php if (esSupervisor()) { ?>

<div class="tarjetas-stats">

    <div class="stat">
        <div class="numero">
            <?php echo (int) $totalPedidos; ?>
        </div>
        <div class="etiqueta">Pedidos Realizados</div>
    </div>

    <div class="stat">
        <div class="numero">
            <?php echo number_format((float) $totalVendido, 2, '.', ','); ?>
        </div>
        <div class="etiqueta">Total Vendido (Bs)</div>
    </div>

    <div class="stat">
        <div class="numero">
            <?php echo (int) $totalClientes; ?>
        </div>
        <div class="etiqueta">Clientes Registrados</div>
    </div>

    <div class="stat">
        <div class="numero">
            <?php
            echo $ingredienteBajo
                ? h($ingredienteBajo['cantidad_actual'] . ' ' .
                    $ingredienteBajo['unidad_medida'])
                : '-';
            ?>
        </div>
        <div class="etiqueta">
            <?php
            echo $ingredienteBajo
                ? 'Stock mas bajo: ' . h($ingredienteBajo['nombre'])
                : 'Sin ingredientes';
            ?>
        </div>
    </div>

    <div class="stat">
        <div class="numero">
            <?php
            echo $productoMasVendido
                ? (int) $productoMasVendido['total_vendido']
                : 0;
            ?>
        </div>
        <div class="etiqueta">
            <?php
            echo $productoMasVendido
                ? 'Mas vendido: ' . h($productoMasVendido['nombre'])
                : 'Sin ventas aun';
            ?>
        </div>
    </div>

</div>

<?php } ?>

<h2>Accesos Rapidos</h2>

<div class="tarjetas">

<?php
$accesos = [
    ['clientes', 'Clientes', 'Registrar y administrar clientes', 'clientes.php'],
    ['empleados', 'Empleados', 'Personal que atiende pedidos', 'empleados.php'],
    ['proveedores', 'Proveedores', 'Quienes suministran ingredientes', 'proveedores.php'],
    ['ingredientes', 'Ingredientes', 'Inventario de materia prima', 'ingredientes.php'],
    ['productos', 'Productos', 'Hamburguesas y limonada a la venta', 'productos.php'],
    ['recetas', 'Recetas', 'Ingredientes que lleva cada producto', 'recetas.php'],
    ['abastecimiento', 'Abastecimiento', 'Registrar compras de ingredientes', 'abastecimiento.php'],
    ['gastos', 'Gastos', 'Servicios, fletes y otros costos fijos', 'gastos.php'],
    ['pedidos', 'Pedidos', 'Registrar y ver ventas', 'pedidos.php'],
    ['finanzas', 'Finanzas', 'Margen bruto, equilibrio e inversion', 'finanzas.php'],
    ['reportes', 'Reportes', 'Ventas, inventario y compras. Imprimir y Excel', 'reportes.php']
];

foreach ($accesos as $acceso) {
    if (puede($acceso[0], 'ver')) {
?>
    <a href="<?php echo h($acceso[3]); ?>" class="tarjeta">
        <div class="titulo-tarjeta">
            <?php echo h($acceso[1]); ?>
        </div>
        <div class="descripcion-tarjeta">
            <?php echo h($acceso[2]); ?>
        </div>
    </a>
<?php
    }
}
?>

</div>

<?php include __DIR__ . '/pie.php'; ?>
