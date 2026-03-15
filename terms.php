<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
?>

<div class="container" style="max-width: 900px; margin: 4rem auto; padding: 0 1rem;">
    <div class="card" style="padding: 3rem;">
        <h1 style="margin-bottom: 1.5rem; color: var(--primary);">Terms and Conditions</h1>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">Last Updated: <?php echo date('d M Y'); ?></p>

        <section style="margin-bottom: 2rem;">
            <h3 style="color: var(--text-main); margin-bottom: 1rem;">1. Acceptance of Terms</h3>
            <p style="color: var(--text-muted); line-height: 1.6;">
                By accessing or using the Raze Investment platform, you agree to be bound by these Terms and Conditions. If you do not agree with any part of these terms, you must not use our services.
            </p>
        </section>

        <section style="margin-bottom: 2rem;">
            <h3 style="color: var(--text-main); margin-bottom: 1rem;">2. Eligibility</h3>
            <p style="color: var(--text-muted); line-height: 1.6;">
                Users must be at least 18 years of age to participate in investment plans. You represent and warrant that you have the legal capacity to enter into a binding agreement.
            </p>
        </section>

        <section style="margin-bottom: 2rem;">
            <h3 style="color: var(--text-main); margin-bottom: 1rem;">3. Investment Risks</h3>
            <p style="color: var(--text-muted); line-height: 1.6;">
                Investment in financial markets involves risk. While Raze Investment strives to provide consistent returns, past performance is not indicative of future results. Capital is at risk, and returns are not guaranteed.
            </p>
        </section>

        <section style="margin-bottom: 2rem;">
            <h3 style="color: var(--text-main); margin-bottom: 1rem;">4. Account Security</h3>
            <p style="color: var(--text-muted); line-height: 1.6;">
                You are responsible for maintaining the confidentiality of your account credentials. Any activity under your account is your sole responsibility. We recommend enabling 2FA for enhanced security.
            </p>
        </section>

        <section style="margin-bottom: 2rem;">
            <h3 style="color: var(--text-main); margin-bottom: 1rem;">5. KYC Verification</h3>
            <p style="color: var(--text-muted); line-height: 1.6;">
                To comply with financial regulations, all users must complete Know Your Customer (KYC) verification. Withdrawals may be restricted until KYC documents are approved by our administration.
            </p>
        </section>

        <section style="margin-bottom: 2rem;">
            <h3 style="color: var(--text-main); margin-bottom: 1rem;">6. Withdrawal Policy</h3>
            <p style="color: var(--text-muted); line-height: 1.6;">
                Withdrawal requests are processed according to the limits set by the administrator. Processing times may vary between 24-72 hours depending on the payment method and verification status.
            </p>
        </section>

        <section style="margin-bottom: 2rem;">
            <h3 style="color: var(--text-main); margin-bottom: 1rem;">7. Termination</h3>
            <p style="color: var(--text-muted); line-height: 1.6;">
                We reserve the right to suspend or terminate accounts that violate our terms, engage in fraudulent activity, or provide false information.
            </p>
        </section>

        <div style="margin-top: 3rem; text-align: center;">
            <a href="register.php" class="btn btn-primary">Join Raze Investment Today</a>
            <p style="margin-top: 1rem;"><a href="index.php" style="color: var(--accent); text-decoration: none;">&larr; Back to Home</a></p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
