<?php
// search.php - Handle product search requests
include 'config.php';

// Get search parameters
$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 999999;
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'name';

try {
    // Base query
    $sql = "SELECT id, name, price, description, image, stock FROM products WHERE 1=1";
    $params = [];

    // Add search term filter
    if (!empty($searchTerm)) {
        $sql .= " AND (name LIKE ? OR description LIKE ?)";
        $searchParam = '%' . $searchTerm . '%';
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    // Add price range filter
    if ($minPrice > 0 || $maxPrice < 999999) {
        $sql .= " AND price BETWEEN ? AND ?";
        $params[] = $minPrice;
        $params[] = $maxPrice;
    }

    // Add category filter (if you have categories in your database)
    if (!empty($category)) {
        $sql .= " AND category = ?";
        $params[] = $category;
    }

    // Add sorting
    switch ($sortBy) {
        case 'price_low':
            $sql .= " ORDER BY price ASC";
            break;
        case 'price_high':
            $sql .= " ORDER BY price DESC";
            break;
        case 'name':
            $sql .= " ORDER BY name ASC";
            break;
        default:
            $sql .= " ORDER BY name ASC";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert price to float for JavaScript
    foreach($products as &$product) {
        $product['price'] = (float)$product['price'];
        // Add default image if missing
        if(empty($product['image'])) {
            $product['image'] = 'default-product.jpg';
        }
    }
    
    echo json_encode([
        'success' => true,
        'products' => $products,
        'total' => count($products),
        'search_term' => $searchTerm
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'products' => [],
        'total' => 0
    ]);
}
?>