    <footer style="text-align: center; padding: 2rem; color: var(--text-muted); border-top: 1px solid rgba(255,255,255,0.05); margin-top: auto;">
        <p>&copy; <?php echo date('Y'); ?> Raze Investment. All rights reserved. <br> <span style="font-size: 0.8rem; margin-top: 5px; display: inline-block; opacity: 0.7;">Designed and Developed By <span style="color: var(--primary); font-weight: bold;">Sujitha Rajeswari G</span></span></p>
        <div style="margin-top: 0.5rem;">
            <a href="<?php echo BASE_URL; ?>/terms.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.85rem;">Terms & Conditions</a>
            <span style="margin: 0 10px; opacity: 0.3;">|</span>
            <a href="<?php echo BASE_URL; ?>/privacy.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.85rem;">Privacy Policy</a>
            <span style="margin: 0 10px; opacity: 0.3;">|</span>
            <a href="<?php echo BASE_URL; ?>/user/faq.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.85rem;">Help Center</a>
        </div>
    </footer>

    <!-- Floating Support Widget -->
    <div id="support-widget" style="position: fixed; bottom: 30px; right: 30px; z-index: 9999;">
        <button onclick="toggleSupport()" style="background: var(--primary); color: white; width: 60px; height: 60px; border-radius: 50%; border: none; cursor: pointer; box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            💬
        </button>
        <div id="support-menu" style="display: none; position: absolute; bottom: 80px; right: 0; width: 280px; background: var(--card-bg); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); overflow: hidden;">
            <div style="background: var(--primary); color: white; padding: 15px; font-weight: bold;">
                How can we help?
            </div>
            <div style="padding: 10px;">
                <a href="<?php echo BASE_URL; ?>/user/support.php" style="display: block; padding: 12px; color: var(--text-main); text-decoration: none; border-bottom: 1px solid rgba(255,255,255,0.05);">🎫 Open Support Ticket</a>
                <a href="<?php echo BASE_URL; ?>/user/faq.php" style="display: block; padding: 12px; color: var(--text-main); text-decoration: none; border-bottom: 1px solid rgba(255,255,255,0.05);">❓ Browse FAQs</a>
                <a href="https://wa.me/919876543210" target="_blank" style="display: block; padding: 12px; color: #25D366; text-decoration: none; font-weight: bold;">🟢 WhatsApp Support</a>
            </div>
        </div>
    </div>

    <script>
        function toggleSupport() {
            const menu = document.getElementById('support-menu');
            menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
        }
        // Close menu when clicking outside
        window.addEventListener('click', function(e) {
            if (!document.getElementById('support-widget').contains(e.target)) {
                document.getElementById('support-menu').style.display = 'none';
            }
        });
    </script>
</body>
</html>
