<?php
// Simulated product data (same as index.php for consistency)
$products = [
    1 => ['name' => 'Laptop Pro', 'price' => 999.99, 'description' => 'High-performance laptop for professionals. Features: 16GB RAM, 512GB SSD.', 'image' => 'https://via.placeholder.com/300x200?text=Laptop'],
    2 => ['name' => 'Smartphone X', 'price' => 699.99, 'description' => 'Latest smartphone with advanced camera. Features: 5G, 128GB storage.', 'image' => 'https://via.placeholder.com/300x200?text=Smartphone'],
    3 => ['name' => 'Desktop Computer', 'price' => 1299.99, 'description' => 'Powerful desktop for gaming and work. Features: RTX GPU, 1TB HDD.', 'image' => 'https://via.placeholder.com/300x200?text=Desktop'],
];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 1; // Sanitize ID as integer
$product = isset($products[$id]) ? $products[$id] : ['name' => 'Not Found', 'description' => 'Product not found.'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - NBM</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Product Details</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="contact.php">Contact</a>
            <div id="cart">Cart: <span id="cart-count">0</span> items</div>
        </nav>
    </header>
    
    <main>
        <section class="product-details">
            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
            <p>$<?php echo isset($product['price']) ? number_format($product['price'], 2) : 'N/A'; ?></p>
            <p><?php echo htmlspecialchars($product['description']); ?></p>
            <button onclick="addToCart(<?php echo $id; ?>)">Add to Cart</button>
        </section>
    </main>
    
    <footer>
        <p>&copy; 2026 New Business Machine Ltd. All rights reserved.</p>
    </footer>
    
    <script src="script.js"></script>
</body>
</html>
