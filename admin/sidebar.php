<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$currentFolder = basename(dirname($_SERVER['PHP_SELF']));

$adminBase = '/admin/';

function sidebarActive($folder, $page, $currentFolder, $currentPage) {
    if ($folder === 'dashboard') {
        return $currentPage === 'dashboard.php' ? 'active' : '';
    }

    return $currentFolder === $folder ? 'active' : '';
}
?>

<style>
.sidebar {
    width: 260px;
    height: 100vh;
    background: linear-gradient(180deg, #111827 0%, #0f172a 100%);
    color: #fff;
    position: fixed;
    top: 0;
    left: 0;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    overflow-x: hidden;
    box-sizing: border-box;
    padding: 0 14px 20px;
    font-family: 'Segoe UI', Arial, sans-serif;
    border-right: 1px solid #263247;
    box-shadow: 4px 0 18px rgba(0, 0, 0, .12);
    z-index: 1000;
}

.sidebar::-webkit-scrollbar {
    width: 5px;
}

.sidebar::-webkit-scrollbar-track {
    background: #111827;
}

.sidebar::-webkit-scrollbar-thumb {
    background: #374151;
    border-radius: 10px;
}

.sidebar_logo {
    font-size: 25px;
    font-weight: 800;
    letter-spacing: .5px;
    color: #fff;
    padding: 25px 12px;
    margin-bottom: 15px;
    border-bottom: 1px solid #263247;
    display: flex;
    align-items: center;
    gap: 10px;
}

.sidebar_logo::before {
    content: "M";
    width: 40px;
    height: 40px;
    flex-shrink: 0;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: #fff;
}

.sidebar_menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar_menu li {
    list-style: none;
    margin: 4px 0;
}

.sidebar_menu .menu_heading {
    color: #94a3b8;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    padding: 22px 12px 9px;
    margin-top: 5px;
}

.sidebar_menu li a {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 12px 13px;
    color: #cbd5e1;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    border-radius: 9px;
    border: 1px solid transparent;
    transition: all .25s ease;
    white-space: nowrap;
}

.sidebar_menu li a:hover {
    background: #1e293b;
    color: #fff;
    border-color: #334155;
    transform: translateX(3px);
}

.sidebar_menu li a.active {
    background: linear-gradient(135deg, #4f46e5, #6366f1);
    color: #fff;
    font-weight: 600;
    border-color: #818cf8;
    box-shadow: 0 4px 12px rgba(79, 70, 229, .25);
}

.sidebar_menu li a.logout:hover {
    background: rgba(239, 68, 68, .12);
    color: #fca5a5;
    border-color: rgba(239, 68, 68, .25);
}

.main-content {
    margin-left: 260px;
    padding: 25px;
    min-height: 100vh;
    box-sizing: border-box;
}

@media (max-width: 768px) {
    .sidebar {
        width: 220px;
        padding-left: 10px;
        padding-right: 10px;
    }

    .sidebar_logo {
        font-size: 22px;
    }

    .sidebar_menu li a {
        font-size: 13px;
        padding: 11px 10px;
    }

    .main-content {
        margin-left: 220px;
        padding: 15px;
    }
}

@media (max-width: 480px) {
    .sidebar {
        width: 200px;
    }

    .main-content {
        margin-left: 200px;
        padding: 12px;
    }
}
</style>

<aside class="sidebar">
    <div class="sidebar_logo">Mora CMS</div>

    <ul class="sidebar_menu">

        <li>
            <a href="<?= $adminBase ?>dashboard.php"
               class="<?= sidebarActive('dashboard', '', $currentFolder, $currentPage) ?>">
                📊 Dashboard
            </a>
        </li>

        <li class="menu_heading">Content</li>

        <li>
            <a href="<?= $adminBase ?>posts/index.php"
               class="<?= sidebarActive('posts', '', $currentFolder, $currentPage) ?>">
                📝 Posts
            </a>
        </li>

        <li>
            <a href="<?= $adminBase ?>categories/index.php"
               class="<?= sidebarActive('categories', '', $currentFolder, $currentPage) ?>">
                📂 Categories
            </a>
        </li>

        <li>
            <a href="<?= $adminBase ?>comments/index.php"
               class="<?= sidebarActive('comments', '', $currentFolder, $currentPage) ?>">
                💬 Comments
            </a>
        </li>

        <li>
            <a href="<?= $adminBase ?>videos/index.php"
               class="<?= sidebarActive('videos', '', $currentFolder, $currentPage) ?>">
                🎥 Videos
            </a>
        </li>

        <li>
            <a href="<?= $adminBase ?>about/index.php"
               class="<?= sidebarActive('about', '', $currentFolder, $currentPage) ?>">
                📄 About Page
            </a>
        </li>

        <li class="menu_heading">Management</li>

        <li>
            <a href="<?= $adminBase ?>advertisements/index.php"
               class="<?= sidebarActive('advertisements', '', $currentFolder, $currentPage) ?>">
                📢 Advertisements
            </a>
        </li>

        <li>
            <a href="<?= $adminBase ?>social-links/index.php"
               class="<?= sidebarActive('social-links', '', $currentFolder, $currentPage) ?>">
                🔗 Social Links
            </a>
        </li>

        <li>
            <a href="<?= $adminBase ?>products/index.php"
               class="<?= sidebarActive('products', '', $currentFolder, $currentPage) ?>">
                🛍️ Products
            </a>
        </li>

         <li>
            <a href="<?= $adminBase ?>orders/index.php"
               class="<?= sidebarActive('orders', '', $currentFolder, $currentPage) ?>">
                🛍️ Orders
            </a>
        </li>

        <li class="menu_heading">Users</li>

        <li>
            <a href="<?= $adminBase ?>users/index.php"
               class="<?= sidebarActive('users', '', $currentFolder, $currentPage) ?>">
                👥 Registered Users
            </a>
        </li>

        <li>
            <a href="<?= $adminBase ?>subscribers/index.php"
               class="<?= sidebarActive('subscribers', '', $currentFolder, $currentPage) ?>">
                📧 Subscribers
            </a>
        </li>

        <li>
            <a href="<?= $adminBase ?>messages/index.php"
               class="<?= sidebarActive('messages', '', $currentFolder, $currentPage) ?>">
                ✉️ Contact Messages
            </a>
        </li>

        <li class="menu_heading">System</li>

        <li>
            <a href="<?= $adminBase ?>settings/index.php"
               class="<?= sidebarActive('settings', '', $currentFolder, $currentPage) ?>">
                ⚙️ Site Settings
            </a>
        </li>

        <li>
            <a href="<?= $adminBase ?>logout.php" class="logout">
                🚪 Logout
            </a>
        </li>

    </ul>
</aside>
