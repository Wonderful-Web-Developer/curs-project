<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $sql = "
        SELECT 
            r.*, 
            u.full_name, 
            sc.name as service_name,
            r.created_at
        FROM reviews r
        JOIN orders o ON r.order_id = o.id
        JOIN users u ON o.user_id = u.id
        JOIN tariffs t ON o.tariff_id = t.id
        JOIN service_categories sc ON t.category_id = sc.id
        ORDER BY r.created_at DESC
    ";
    
    $stmt = $pdo->query($sql);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($reviews);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}