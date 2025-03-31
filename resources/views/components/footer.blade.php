<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3>CP Atelier</h3>
            <p>Your trusted partner in quality products and services.</p>
        </div>
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="{{ route('welcome') }}">Home</a></li>
                <li><a href="{{ route('about') }}">About Us</a></li>
                <li><a href="{{ route('Contact') }}">Contact</a></li>
                <li><a href="{{ route('FAQ') }}">FAQ</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Contact Info</h3>
            <p>Email: info@cpatelier.com</p>
            <p>Phone: +1 234 567 890</p>
            <p>Address: 123 Business Street</p>
        </div>
        <div class="footer-section">
            <h3>Follow Us</h3>
            <div class="social-links">
                <a href="#" class="social-link">Facebook</a>
                <a href="#" class="social-link">Twitter</a>
                <a href="#" class="social-link">Instagram</a>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; {{ date('Y') }} CP Atelier. All rights reserved.</p>
    </div>
</footer> 