<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raze Investment</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
    <script>
        function confirmAction(msg) {
            return confirm(msg);
        }
        
        // Theme initialization
        if (localStorage.getItem('theme') === 'light') {
            document.documentElement.classList.add('light-mode');
        } else if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.remove('light-mode');
        } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) {
            // Auto-detect system light mode if no manual preference set
            document.documentElement.classList.add('light-mode');
        }
        
        function toggleTheme() {
            if (document.documentElement.classList.contains('light-mode')) {
                document.documentElement.classList.remove('light-mode');
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.classList.add('light-mode');
                localStorage.setItem('theme', 'light');
            }
        }
    </script>
</head>
<body id="app-body">
    <script>
        // Apply to body immediately to prevent flashing
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'light' || (!savedTheme && window.matchMedia('(prefers-color-scheme: light)').matches)) {
            document.body.classList.add('light-mode');
        }
    </script>
    <nav class="navbar">
        <a href="<?php echo BASE_URL; ?>/index.php" class="nav-brand"><span class="gradient-text">Raze</span> Investment</a>
        <div class="nav-links" style="display:flex; align-items:center; gap:1.5rem;">
            <button onclick="toggleTheme(); document.body.classList.toggle('light-mode');" style="background:none; border:none; color:var(--text-main); font-size:1.2rem; cursor:pointer;" title="Toggle Light/Dark Mode">🌓</button>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="<?php echo BASE_URL; ?>/user/dashboard.php" class="btn btn-secondary">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>/logout.php" class="btn btn-primary">Logout</a>
            <?php elseif(isset($_SESSION['admin_id'])): ?>
                <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="btn btn-secondary">Admin Panel</a>
                <a href="<?php echo BASE_URL; ?>/logout.php" class="btn btn-primary">Logout</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-secondary">Login</a>
                <a href="<?php echo BASE_URL; ?>/register.php" class="btn btn-primary">Register</a>
            <?php endif; ?>
        </div>
    </nav>

    <?php
    // Fetch active announcements
    $news_q = $conn->query("SELECT message FROM announcements WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
    if($news_q && $news_q->num_rows > 0):
        $news = $news_q->fetch_assoc();
    ?>
    <div style="background: var(--primary); color: white; padding: 0.5rem; overflow: hidden; position: relative; white-space: nowrap; font-size: 0.9rem;">
        <div style="display:inline-block; animation: marquee 20s linear infinite;">
            📢 <strong>LATEST NEWS:</strong> <?php echo htmlspecialchars($news['message']); ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 🚀 <strong>RAZE INVESTMENT:</strong> Grow your wealth with us! &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </div>
    </div>
    <style>
        @keyframes marquee {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
    </style>
    <?php endif; ?>

