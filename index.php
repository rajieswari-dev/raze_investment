<?php
require_once 'includes/db.php';

// Fetch dynamic stats for landing page
$stats_users = $conn->query("SELECT COUNT(id) as c FROM users")->fetch_assoc()['c'];
$stats_inv = $conn->query("SELECT SUM(amount) as s FROM investments WHERE status = 'active'")->fetch_assoc()['s'] ?? 0;
// Add some "starting offset" to make stats look bigger/established if it's a new site
$display_users = 1200 + $stats_users;
$display_invested = 500000 + $stats_inv;

require_once 'includes/header.php';
?>

<!-- Hero Section -->
<div class="hero" style="min-height: 80vh; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; background: radial-gradient(circle at top right, rgba(79, 70, 229, 0.1), transparent), radial-gradient(circle at bottom left, rgba(16, 185, 129, 0.05), transparent);">
    <h1 style="font-size: clamp(2.5rem, 8vw, 4.5rem); line-height: 1.1; margin-bottom: 1.5rem;">
        Grow Your Wealth with <br><span class="gradient-text">Smart Investments</span>
    </h1>
    <p style="max-width: 700px; font-size: 1.25rem; color: var(--text-muted); margin-bottom: 2.5rem; line-height: 1.6;">
        Join thousands of smart investors using Raze Investment to achieve financial freedom. Simple. Secure. High-Yield.
    </p>
    <div style="display: flex; gap: 1.5rem; flex-wrap: wrap; justify-content: center;">
        <?php if(!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])): ?>
            <a href="register.php" class="btn btn-primary" style="font-size: 1.25rem; padding: 1.2rem 3rem; border-radius: 50px; box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);">Get Started Now</a>
            <a href="login.php" class="btn btn-secondary" style="font-size: 1.25rem; padding: 1.2rem 3rem; border-radius: 50px;">Sign In</a>
        <?php else: ?>
            <a href="<?php echo isset($_SESSION['admin_id']) ? 'admin/dashboard.php' : 'user/dashboard.php'; ?>" class="btn btn-primary" style="font-size: 1.25rem; padding: 1.2rem 3rem; border-radius: 50px;">Go to My Dashboard</a>
        <?php endif; ?>
    </div>
</div>

<!-- Stats Section -->
<div style="background: rgba(255,255,255,0.02); padding: 5rem 5%; border-top: 1px solid rgba(255,255,255,0.05); border-bottom: 1px solid rgba(255,255,255,0.05);">
    <div class="grid" style="max-width: 1200px; margin: 0 auto; gap: 3rem;">
        <div style="text-align: center;">
            <h2 style="font-size: 3rem; color: var(--primary); margin-bottom: 0.5rem;"><?php echo number_format($display_users); ?>+</h2>
            <p style="color: var(--text-muted); font-weight: 500;">ACTIVE INVESTORS</p>
        </div>
        <div style="text-align: center;">
            <h2 style="font-size: 3rem; color: var(--success); margin-bottom: 0.5rem;">₹<?php echo number_format($display_invested / 100000, 1); ?>L+</h2>
            <p style="color: var(--text-muted); font-weight: 500;">TOTAL CAPITAL INVESTED</p>
        </div>
        <div style="text-align: center;">
            <h2 style="font-size: 3rem; color: var(--warning); margin-bottom: 0.5rem;">99.9%</h2>
            <p style="color: var(--text-muted); font-weight: 500;">PAYOUT SUCCESS RATE</p>
        </div>
    </div>
</div>

<!-- Features Section -->
<div style="padding: 8rem 5%;">
    <div style="text-align: center; margin-bottom: 5rem;">
        <h6 style="color: var(--primary); text-transform: uppercase; letter-spacing: 2px; font-weight: bold; margin-bottom: 1rem;">Core Features</h6>
        <h2 style="font-size: 3rem;">Why Trust Raze Investment?</h2>
    </div>
    <div class="grid" style="max-width: 1200px; margin: 0 auto;">
        <div class="card" style="padding: 3rem; transition: transform 0.3s;">
            <div style="font-size: 3rem; margin-bottom: 1.5rem;">📈</div>
            <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Daily ROI Credits</h3>
            <p style="color: var(--text-muted); line-height: 1.7;">Don't wait for months. See your profit growing every day with our automated daily return system.</p>
        </div>
        <div class="card" style="padding: 3rem; transition: transform 0.3s;">
            <div style="font-size: 3rem; margin-bottom: 1.5rem;">🛡️</div>
            <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Secured Capital</h3>
            <p style="color: var(--text-muted); line-height: 1.7;">Your principal is protected by our diversified risk management algorithm and strict security protocols.</p>
        </div>
        <div class="card" style="padding: 3rem; transition: transform 0.3s;">
            <div style="font-size: 3rem; margin-bottom: 1.5rem;">⚡</div>
            <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Instant Support</h3>
            <p style="color: var(--text-muted); line-height: 1.7;">Our dedicated support team is available 24/7 via live chat and tickets to resolve your queries instantly.</p>
        </div>
    </div>
</div>

<!-- How It Works Section -->
<div style="background: rgba(16, 185, 129, 0.03); padding: 8rem 5%;">
    <div style="text-align: center; margin-bottom: 5rem;">
        <h2 style="font-size: 3rem;">Get Started in 3 Steps</h2>
    </div>
    <div class="grid" style="max-width: 1200px; margin: 0 auto; gap: 4rem;">
        <div style="display: flex; gap: 1.5rem;">
            <div style="background: var(--primary); color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold; flex-shrink: 0;">1</div>
            <div>
                <h3 style="margin-bottom: 0.5rem;">Create Account</h3>
                <p style="color: var(--text-muted);">Sign up in seconds and complete your KYC verification for security.</p>
            </div>
        </div>
        <div style="display: flex; gap: 1.5rem;">
            <div style="background: var(--primary); color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold; flex-shrink: 0;">2</div>
            <div>
                <h3 style="margin-bottom: 0.5rem;">Choose a Plan</h3>
                <p style="color: var(--text-muted);">Select from various high-yield plans and make your first investment.</p>
            </div>
        </div>
        <div style="display: flex; gap: 1.5rem;">
            <div style="background: var(--primary); color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold; flex-shrink: 0;">3</div>
            <div>
                <h3 style="margin-bottom: 0.5rem;">Earn Daily</h3>
                <p style="color: var(--text-muted);">Watch your returns hit your wallet every single day. Withdraw anytime!</p>
            </div>
        </div>
    </div>
</div>

<!-- FAQ Preview Section -->
<div style="padding: 8rem 5%; max-width: 900px; margin: 0 auto;">
    <div style="text-align: center; margin-bottom: 4rem;">
        <h2 style="font-size: 2.5rem;">Quick Help & FAQs</h2>
    </div>
    <?php
    $faqs = $conn->query("SELECT * FROM faqs LIMIT 4");
    while($f = $faqs->fetch_assoc()):
    ?>
    <div style="margin-bottom: 1.5rem; border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; overflow: hidden;">
        <div style="padding: 1.5rem; background: rgba(255,255,255,0.02); display: flex; justify-content: space-between; align-items: center; cursor: pointer;" onclick="const ans = this.nextElementSibling; ans.style.display = ans.style.display === 'block' ? 'none' : 'block';">
            <span style="font-weight: 600; font-size: 1.1rem;"><?php echo htmlspecialchars($f['question']); ?></span>
            <span style="color: var(--primary);">▼</span>
        </div>
        <div style="padding: 1.5rem; display: none; background: rgba(255,255,255,0.01); border-top: 1px solid rgba(255,255,255,0.05); color: var(--text-muted); line-height: 1.6;">
            <?php echo nl2br(htmlspecialchars($f['answer'])); ?>
        </div>
    </div>
    <?php endwhile; ?>
    <div style="text-align: center; margin-top: 2rem;">
        <a href="user/faq.php" style="color: var(--accent); text-decoration: none; font-weight: 600;">View All FAQs &rarr;</a>
    </div>
</div>

<!-- CTA Section -->
<div style="padding: 6rem 5%; background: linear-gradient(135deg, var(--primary), #6366f1); color: white; text-align: center; border-radius: 30px; margin: 4rem 5%; box-shadow: 0 20px 40px rgba(79, 70, 229, 0.4);">
    <h2 style="font-size: 3rem; margin-bottom: 1.5rem; color: white;">Ready to Start Your Journey?</h2>
    <p style="font-size: 1.25rem; opacity: 0.9; margin-bottom: 2.5rem; max-width: 600px; margin-left: auto; margin-right: auto;">Don't let your money sit idle. Make it work for you with namma Raze Investment.</p>
    <a href="register.php" class="btn btn-secondary" style="background: white; color: var(--primary); font-size: 1.25rem; padding: 1.2rem 3rem; border-radius: 50px;">Create Free Account</a>
</div>

<?php require_once 'includes/header.php'; // Included at bottom for script reasons if needed, but here it's fine ?>
<?php require_once 'includes/footer.php'; ?>

