<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

// Verificar que el usuario está autenticado y es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Validar ID
$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID no válido']);
    exit;
}

$db = new Database();
$conn = $db->connect();

try {
    $stmt = $conn->prepare("
        SELECT id, nombre, apellidos, correo, documento, created_at 
        FROM usuarios 
        WHERE id = ? AND rol_id = 2
    ");
    $stmt->execute([$id]);
    $profesor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$profesor) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Profesor no encontrado']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'profesor' => $profesor
    ]);

} catch (PDOException $e) {
    error_log("Error al obtener profesor: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener los datos del profesor'
    ]);
} 