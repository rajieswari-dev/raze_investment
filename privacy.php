<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
?>

<div class="container" style="max-width: 900px; margin: 4rem auto; padding: 0 1rem;">
    <div class="card" style="padding: 3rem;">
        <h1 style="margin-bottom: 1.5rem; color: var(--primary);">Privacy Policy</h1>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">Last Updated: <?php echo date('d M Y'); ?></p>

        <section style="margin-bottom: 2rem;">
            <h3 style="color: var(--text-main); margin-bottom: 1rem;">1. Information We Collect</h3>
            <p style="color: var(--text-muted); line-height: 1.6;">
                We collect information you provide directly to us, such as your name, email address, phone number, and KYC documents (government ID, etc.) when you create an account or verify your identity.
            </p>
        </section>

        <section style="margin-bottom: 2rem;">
            <h3 style="color: var(--text-main); margin-bottom: 1rem;">2. How We Use Your Information</h3>
            <p style="color: var(--text-muted); line-height: 1.6;">
                We use the information we collect to:
                <ul style="margin-left: 1.5rem; margin-top: 0.5rem; list-style-type: disc;">
                    <li>Provide, maintain, and improve our services;</li>
                    <li>Process your transactions and investments;</li>
                    <li>Verify your identity and prevent fraud;</li>
                    <li>Send you technical notices, updates, and support messages.</li>
                </ul>
            </p>
        </section>

        <section style="margin-bottom: 2rem;">
            <h3 style="color: var(--text-main); margin-bottom: 1rem;">3. Data Security</h3>
            <p style="color: var(--text-muted); line-height: 1.6;">
                We take reasonable measures to help protect information about you from loss, theft, misuse, and unauthorized access. We use encryption (SSL) and follow industry standards to safeguard your data.
            </p>
        </section>

        <section style="margin-bottom: 2rem;">
            <h3 style="color: var(--text-main); margin-bottom: 1rem;">4. Information Sharing</h3>
            <p style="color: var(--text-muted); line-height: 1.6;">
                We do not sell or rent your personal information to third parties. We may share information only as needed to comply with legal obligations or to facilitate payments through highly secure third-party processors.
            </p>
        </section>

        <section style="margin-bottom: 2rem;">
            <h3 style="color: var(--text-main); margin-bottom: 1rem;">5. Security Logs</h3>
            <p style="color: var(--text-muted); line-height: 1.6;">
                As part of our security measures, we track login attempts, IP addresses, and user-agent details. This data is used solely for the purpose of account protection and auditing.
            </p>
        </section>

        <section style="margin-bottom: 2rem;">
            <h3 style="color: var(--text-main); margin-bottom: 1rem;">6. Cookies</h3>
            <p style="color: var(--text-muted); line-height: 1.6;">
                We use session cookies to keep you logged in and remember your preferences (like your theme choice). These are essential for the platform to function correctly.
            </p>
        </section>

        <div style="margin-top: 3rem; text-align: center;">
            <p style="margin-bottom: 1rem; color: var(--text-muted);">Have questions about your data?</p>
            <a href="user/support.php" class="btn btn-secondary">Contact Support</a>
            <p style="margin-top: 1.5rem;"><a href="index.php" style="color: var(--accent); text-decoration: none;">&larr; Back to Home</a></p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
