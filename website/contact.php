<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simulate form processing (in production, send email or store in DB)
    $name = htmlspecialchars($_POST['name'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $message = htmlspecialchars($_POST['message'] ?? '');
    // Security: Validate email, add CSRF token, etc.
    echo "<p>Thank you, $name! Your message has been received.</p>";
    exit; // Prevent form resubmission
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - NBM</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Contact Us</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="contact.php">Contact</a>
            <div id="cart">Cart: <span id="cart-count">0</span> items</div>
        </nav>
    </header>
    
    <main>
        <section class="contact-form">
            <h2>Get in Touch</h2>
            <form id="contactForm" method="POST">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
                
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                
                <label for="message">Message:</label>
                <textarea id="message" name="message" required></textarea>
                
                <button type="submit">Submit</button>
            </form>
        </section>
    </main>
    
    <footer>
        <p>&copy; 2026 New Business Machine Ltd. All rights reserved.</p>
    </footer>
    
    <script src="script.js"></script>
</body>
</html>
