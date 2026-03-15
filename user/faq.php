<?php
require_once '../includes/db.php';
if (!isset($_SESSION['user_id'])) redirect('../login.php');

require_once '../includes/header.php';
?>

<div class="dashboard">
    <?php require_once '../includes/user_sidebar.php'; ?>
    <main class="main-content">
        <h2 style="margin-bottom: 2rem;">Help Center & FAQ</h2>

        <div class="card" style="max-width: 900px;">
            <p style="color:var(--text-muted); margin-bottom: 2rem;">Find answers to common questions about our platform, investments, and more.</p>
            
            <div class="faq-container">
                <?php
                $faqs = $conn->query("SELECT * FROM faqs ORDER BY category ASC, created_at DESC");
                $current_cat = "";
                while($f = $faqs->fetch_assoc()):
                    if($current_cat != $f['category']):
                        $current_cat = $f['category'];
                        echo "<h3 style='margin: 2rem 0 1rem 0; color:var(--primary); font-size:1.1rem; text-transform:uppercase; letter-spacing:1px;'>$current_cat</h3>";
                    endif;
                ?>
                <div class="faq-item" style="margin-bottom: 1rem; border: 1px solid rgba(255,255,255,0.05); border-radius: 8px; overflow: hidden;">
                    <div class="faq-question" style="padding: 1.2rem; background: rgba(255,255,255,0.02); cursor: pointer; display: flex; justify-content: space-between; align-items: center;" onclick="this.nextElementSibling.classList.toggle('active'); this.querySelector('.arrow').classList.toggle('rotated');">
                        <span style="font-weight: 500;"><?php echo htmlspecialchars($f['question']); ?></span>
                        <span class="arrow" style="transition: transform 0.3s;">▼</span>
                    </div>
                    <div class="faq-answer" style="max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; background: rgba(255,255,255,0.01);">
                        <div style="padding: 1.2rem; color: var(--text-muted); line-height: 1.6; border-top: 1px solid rgba(255,255,255,0.05);">
                            <?php echo nl2br(htmlspecialchars($f['answer'])); ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <div style="margin-top: 3rem; text-align: center; padding: 2rem; background: rgba(79, 70, 229, 0.05); border-radius: 12px; border: 1px dashed var(--primary);">
                <h4>Still need help?</h4>
                <p style="margin-bottom: 1.5rem; color: var(--text-muted);">If you can't find what you're looking for, our support team is here to help.</p>
                <a href="support.php" class="btn btn-primary">Open a Support Ticket</a>
            </div>
        </div>
    </main>
</div>

<style>
    .faq-answer.active {
        max-height: 500px; /* Large enough to fit content */
    }
    .arrow.rotated {
        transform: rotate(180deg);
    }
    .faq-question:hover {
        background: rgba(255,255,255,0.05) !important;
    }
</style>

<?php require_once '../includes/footer.php'; ?>
