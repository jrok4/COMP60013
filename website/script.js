// Simple Cart Functionality
let cartCount = localStorage.getItem('cartCount') || 0;
document.getElementById('cart-count').innerText = cartCount;

function addToCart(id) {
    cartCount++;
    localStorage.setItem('cartCount', cartCount);
    document.getElementById('cart-count').innerText = cartCount;
    alert('Product added to cart!');
}

// Form Validation (for contact.php)
const form = document.getElementById('contactForm');
if (form) {
    form.addEventListener('submit', function(event) {
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const message = document.getElementById('message').value.trim();
        
        if (!name || !email || !message) {
            alert('Please fill out all fields.');
            event.preventDefault();
        } else if (!/\S+@\S+\.\S+/.test(email)) {
            alert('Invalid email format.');
            event.preventDefault();
        }
    });
}
