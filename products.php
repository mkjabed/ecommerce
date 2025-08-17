<?php
// products.php - Get all products or search results
include 'config.php';

// Check if this is a search request
$isSearch = isset($_GET['search']) && !empty($_GET['search']);
$searchTerm = $isSearch ? trim($_GET['search']) : '';

try {
    if ($isSearch) {
        // Search query
        $sql = "SELECT id, name, price, description, image, stock FROM products 
                WHERE name LIKE ? OR description LIKE ? 
                ORDER BY name ASC";
        $searchParam = '%' . $searchTerm . '%';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$searchParam, $searchParam]);
    } else {
        // Get all products
        $stmt = $pdo->query("SELECT id, name, price, description, image, stock FROM products ORDER BY name ASC");
    }
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert price to float for JavaScript
    foreach($products as &$product) {
        $product['price'] = (float)$product['price'];
        // Add default image if missing
        if(empty($product['image'])) {
            $product['image'] = 'default-product.jpg';
        }
    }
    
    if ($isSearch) {
        echo json_encode([
            'success' => true,
            'products' => $products,
            'total' => count($products),
            'search_term' => $searchTerm
        ]);
    } else {
        echo json_encode($products);
    }
    
} catch(PDOException $e) {
    if ($isSearch) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'products' => [],
            'total' => 0
        ]);
    } else {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>