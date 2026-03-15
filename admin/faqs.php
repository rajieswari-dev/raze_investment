<?php
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) redirect('../login.php');

$msg = '';

// Handle Delete
if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM faqs WHERE id = $id");
    $msg = "FAQ deleted successfully.";
}

// Handle Add/Edit
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_faq'])) {
    $question = sanitize($conn, $_POST['question']);
    $answer = sanitize($conn, $_POST['answer']);
    $category = sanitize($conn, $_POST['category']);
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if($id > 0) {
        $stmt = $conn->prepare("UPDATE faqs SET question=?, answer=?, category=? WHERE id=?");
        $stmt->bind_param("sssi", $question, $answer, $category, $id);
        $stmt->execute();
        $msg = "FAQ updated.";
    } else {
        $stmt = $conn->prepare("INSERT INTO faqs (question, answer, category) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $question, $answer, $category);
        $stmt->execute();
        $msg = "FAQ added.";
    }
}

require_once '../includes/header.php';
?>

<div class="dashboard">
    <?php require_once '../includes/admin_sidebar.php'; ?>
    <main class="main-content">
        <h2 style="margin-bottom: 2rem;">Manage FAQs</h2>

        <?php if($msg): ?><div class="alert alert-success"><?php echo $msg; ?></div><?php endif; ?>

        <div class="grid">
            <!-- Form Card -->
            <div class="card">
                <h3>Add / Edit FAQ</h3>
                <form method="POST" style="margin-top:1.5rem;">
                    <input type="hidden" name="id" id="faq_id" value="0">
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" id="faq_cat" required>
                            <option value="General">General</option>
                            <option value="Payments">Payments</option>
                            <option value="Withdrawals">Withdrawals</option>
                            <option value="KYC">KYC</option>
                            <option value="Investments">Investments</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Question</label>
                        <input type="text" name="question" id="faq_ques" required placeholder="e.g. How to withdraw?">
                    </div>
                    <div class="form-group">
                        <label>Answer</label>
                        <textarea name="answer" id="faq_ans" rows="4" required placeholder="Type the answer here..."></textarea>
                    </div>
                    <button type="submit" name="save_faq" class="btn btn-primary" style="width:100%;">Save FAQ</button>
                    <button type="button" onclick="resetForm()" class="btn btn-secondary" style="width:100%; margin-top:0.5rem;">Clear Form</button>
                </form>
            </div>

            <!-- List Card -->
            <div class="card">
                <h3>Existing FAQs</h3>
                <div style="margin-top:1.5rem; overflow-y:auto; max-height: 500px;">
                    <?php
                    $faqs = $conn->query("SELECT * FROM faqs ORDER BY category ASC, id DESC");
                    while($f = $faqs->fetch_assoc()):
                    ?>
                    <div style="padding:1rem; border: 1px solid rgba(255,255,255,0.05); border-radius:8px; margin-bottom:1rem;">
                        <span class="badge" style="font-size:0.7rem; background:var(--primary);"><?php echo $f['category']; ?></span>
                        <h4 style="margin: 0.5rem 0; font-size:0.95rem;"><?php echo htmlspecialchars($f['question']); ?></h4>
                        <div style="display:flex; gap:0.5rem; margin-top:0.5rem;">
                            <button onclick='editFaq(<?php echo json_encode($f); ?>)' class="btn btn-success" style="padding:0.2rem 0.6rem; font-size:0.75rem;">Edit</button>
                            <a href="faqs.php?delete=<?php echo $f['id']; ?>" class="btn btn-danger" style="padding:0.2rem 0.6rem; font-size:0.75rem;" onclick="return confirm('Delete this FAQ?')">Delete</a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
function editFaq(faq) {
    document.getElementById('faq_id').value = faq.id;
    document.getElementById('faq_ques').value = faq.question;
    document.getElementById('faq_ans').value = faq.answer;
    document.getElementById('faq_cat').value = faq.category;
}

function resetForm() {
    document.getElementById('faq_id').value = '0';
    document.getElementById('faq_ques').value = '';
    document.getElementById('faq_ans').value = '';
    document.getElementById('faq_cat').value = 'General';
}
</script>

<?php require_once '../includes/footer.php'; ?>
