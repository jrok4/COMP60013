<?php
// Simulated product data (in a real setup, fetch from Database for security and scalability)
$products = [
    ['id' => 1, 'name' => 'Laptop Pro', 'price' => 999.99, 'description' => 'High-performance laptop for professionals.', 'image' => 'https://picsum.photos/seed/laptop/300/200'],
    ['id' => 2, 'name' => 'Smartphone X', 'price' => 699.99, 'description' => 'Latest smartphone with advanced camera.', 'image' => 'https://picsum.photos/seed/smartphone/300/200'],
    ['id' => 3, 'name' => 'Desktop Computer', 'price' => 1299.99, 'description' => 'Powerful desktop for gaming and work.', 'image' => 'https://picsum.photos/seed/desktop-computer/300/200'],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Business Machine Ltd. - Home</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>NBM</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="contact.php">Contact</a>
            <div id="cart">Cart: <span id="cart-count">0</span> items</div>
        </nav>
    </header>
    
    <main>
        <section class="products">
            <h2>Our Products</h2>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p>$<?php echo number_format($product['price'], 2); ?></p>
                        <a href="product.php?id=<?php echo $product['id']; ?>">View Details</a>
                        <button onclick="addToCart(<?php echo $product['id']; ?>)">Add to Cart</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    
    <footer>
        <p>&copy; 2026 New Business Machine Ltd. All rights reserved.</p>
    </footer>
    
    <script src="script.js"></script>
</body>
</html>
