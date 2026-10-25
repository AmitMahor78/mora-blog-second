<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

require_once "../../includes/db.php";

$stmt = $conn->prepare("SELECT * FROM about_settings WHERE id = 1 LIMIT 1");
$stmt->execute();
$about = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$about) {
    die("About page settings record not found. Please verify about_settings table.");
}

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$message = $_SESSION['about_message'] ?? '';
$messageType = $_SESSION['about_message_type'] ?? 'success';
unset($_SESSION['about_message'], $_SESSION['about_message_type']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage About Page - Admin Panel</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #333;
        }

        .topbar {
            margin-left: 260px;
            padding: 20px 25px;
            background: #fff;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .topbar h2 { margin: 0 0 5px; }
        .topbar p { margin: 0; color: #666; font-size: 14px; }

        .btn-view {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            border-radius: 5px;
            background: #0f172a;
            color: #fff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }
        .btn-view:hover { background: #1e293b; color: #fff; }

        .container {
            margin-left: 260px;
            padding: 25px;
        }

        .panel {
            max-width: 1050px;
            padding: 28px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
        }

        .section-block {
            background: #fbfcfd;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .full { grid-column: 1 / -1; }

        label { font-weight: 600; font-size: 13.5px; color: #334155; }

        input, textarea, select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccd0d5;
            border-radius: 5px;
            font: inherit;
            font-size: 14px;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,.15);
        }

        textarea {
            min-height: 90px;
            resize: vertical;
            line-height: 1.5;
        }

        .hint {
            color: #64748b;
            font-size: 12px;
        }

        .btn-submit {
            display: inline-block;
            padding: 13px 26px;
            border: 0;
            border-radius: 6px;
            background: #2563eb;
            color: #fff;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(37,99,235,.3);
            transition: background .2s ease;
        }

        .btn-submit:hover {
            background: #1d4ed8;
        }

        .notice {
            padding: 13px 16px;
            margin-bottom: 22px;
            border-radius: 6px;
            font-size: 14px;
        }

        .notice.success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .notice.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .tab-builder-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .tab-card {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 16px;
        }

        .tab-card h4 {
            margin: 0 0 12px;
            font-size: 14px;
            color: #1e293b;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .badge-tab {
            font-size: 11px;
            background: #e2e8f0;
            color: #475569;
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: 600;
        }

        .img-preview {
            max-width: 260px;
            max-height: 140px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            margin-bottom: 8px;
            display: block;
        }

        @media (max-width: 900px) {
            .tab-builder-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .topbar, .container { margin-left: 220px; }
            .form-grid { grid-template-columns: 1fr; }
            .full { grid-column: auto; }
        }

        @media (max-width: 480px) {
            .topbar, .container {
                margin-left: 200px;
                padding: 15px 10px;
            }
            .panel { padding: 15px; }
        }
    </style>
</head>
<body>

<?php include_once __DIR__ . '/../sidebar.php'; ?>

<div class="topbar">
    <div>
        <h2>Manage About Page</h2>
        <p>Edit hero tagline, banner, featured image, story, mission tabs, and topic options.</p>
    </div>
    <div>
        <a href="../../about.php" target="_blank" class="btn-view">
            🔗 View Public About Page
        </a>
    </div>
</div>

<div class="container">
    <div class="panel">

        <?php if ($message !== ''): ?>
            <div class="notice <?= $messageType === 'error' ? 'error' : 'success' ?>">
                <?= e($message) ?>
            </div>
        <?php endif; ?>

        <form action="update.php" method="POST" enctype="multipart/form-data">

            <!-- 1. HEADER & HERO SECTION -->
            <div class="section-block">
                <div class="section-title">
                    📌 Page Header & Hero Section
                </div>

                <div class="form-grid">
                    <div class="field">
                        <label for="page_title">Page Title *</label>
                        <input
                            type="text"
                            id="page_title"
                            name="page_title"
                            maxlength="255"
                            required
                            value="<?= e($about['page_title']) ?>"
                        >
                        <span class="hint">Appears in the banner heading (e.g. "About Us").</span>
                    </div>

                    <div class="field">
                        <label for="breadcrumb_title">Breadcrumb Title</label>
                        <input
                            type="text"
                            id="breadcrumb_title"
                            name="breadcrumb_title"
                            maxlength="255"
                            value="<?= e($about['breadcrumb_title']) ?>"
                        >
                        <span class="hint">Appears in breadcrumbs (e.g. "Home / About").</span>
                    </div>

                    <div class="field full">
                        <label for="hero_headline">Main Headline / Tagline *</label>
                        <textarea
                            id="hero_headline"
                            name="hero_headline"
                            rows="3"
                            required
                        ><?= e($about['hero_headline']) ?></textarea>
                        <span class="hint">Large prominent statement shown at the top of the About page content.</span>
                    </div>

                    <div class="field full">
                        <label for="hero_image">Featured Image</label>
                        <?php if (!empty($about['hero_image'])): ?>
                            <div>
                                <img
                                    src="../../<?= e(ltrim($about['hero_image'], '/')) ?>"
                                    alt="Current About Image"
                                    class="img-preview"
                                    onerror="this.style.display='none'"
                                >
                            </div>
                        <?php endif; ?>
                        <input
                            type="file"
                            id="hero_image"
                            name="hero_image"
                            accept="image/jpeg,image/png,image/webp,image/gif"
                        >
                        <span class="hint">Recommended size: 1140x500. Formats: JPG, PNG, WEBP, GIF. Max 3MB. Leave empty to keep existing image.</span>
                    </div>

                    <div class="field full">
                        <label for="main_story">Main Introduction / Story Paragraph *</label>
                        <textarea
                            id="main_story"
                            name="main_story"
                            rows="5"
                            required
                        ><?= e($about['main_story']) ?></textarea>
                        <span class="hint">Introductory paragraph displayed beneath the featured image.</span>
                    </div>
                </div>
            </div>


            <!-- 2. SECTION TABS BUILDER -->
            <div class="section-block">
                <div class="section-title">
                    🗂️ About Details & Mission Tabs
                </div>
                <p style="margin-top:0; color:#64748b; font-size:13.5px;">
                    Customize titles and descriptions for each interactive tab.
                </p>

                <div class="tab-builder-grid">

                    <!-- Tab 1: Mission -->
                    <div class="tab-card">
                        <h4>
                            <span>Tab 1: Mission</span>
                            <span class="badge-tab">Pill #1</span>
                        </h4>
                        <div class="field" style="margin-bottom:10px;">
                            <label for="mission_title">Tab Title</label>
                            <input type="text" id="mission_title" name="mission_title" value="<?= e($about['mission_title']) ?>">
                        </div>
                        <div class="field">
                            <label for="mission_content">Tab Content</label>
                            <textarea id="mission_content" name="mission_content" rows="4"><?= e($about['mission_content']) ?></textarea>
                        </div>
                    </div>

                    <!-- Tab 2: Organization -->
                    <div class="tab-card">
                        <h4>
                            <span>Tab 2: Organization</span>
                            <span class="badge-tab">Pill #2</span>
                        </h4>
                        <div class="field" style="margin-bottom:10px;">
                            <label for="organization_title">Tab Title</label>
                            <input type="text" id="organization_title" name="organization_title" value="<?= e($about['organization_title']) ?>">
                        </div>
                        <div class="field">
                            <label for="organization_content">Tab Content</label>
                            <textarea id="organization_content" name="organization_content" rows="4"><?= e($about['organization_content']) ?></textarea>
                        </div>
                    </div>

                    <!-- Tab 3: Responsibilities -->
                    <div class="tab-card">
                        <h4>
                            <span>Tab 3: Responsibilities</span>
                            <span class="badge-tab">Pill #3</span>
                        </h4>
                        <div class="field" style="margin-bottom:10px;">
                            <label for="responsibilities_title">Tab Title</label>
                            <input type="text" id="responsibilities_title" name="responsibilities_title" value="<?= e($about['responsibilities_title']) ?>">
                        </div>
                        <div class="field">
                            <label for="responsibilities_content">Tab Content</label>
                            <textarea id="responsibilities_content" name="responsibilities_content" rows="4"><?= e($about['responsibilities_content']) ?></textarea>
                        </div>
                    </div>

                    <!-- Tab 4: Funding -->
                    <div class="tab-card">
                        <h4>
                            <span>Tab 4: Funding</span>
                            <span class="badge-tab">Pill #4</span>
                        </h4>
                        <div class="field" style="margin-bottom:10px;">
                            <label for="funding_title">Tab Title</label>
                            <input type="text" id="funding_title" name="funding_title" value="<?= e($about['funding_title']) ?>">
                        </div>
                        <div class="field">
                            <label for="funding_content">Tab Content</label>
                            <textarea id="funding_content" name="funding_content" rows="4"><?= e($about['funding_content']) ?></textarea>
                        </div>
                    </div>

                    <!-- Tab 5: Lumens / Vision -->
                    <div class="tab-card">
                        <h4>
                            <span>Tab 5: Vision & Lumens</span>
                            <span class="badge-tab">Pill #5</span>
                        </h4>
                        <div class="field" style="margin-bottom:10px;">
                            <label for="vision_title">Tab Title</label>
                            <input type="text" id="vision_title" name="vision_title" value="<?= e($about['vision_title']) ?>">
                        </div>
                        <div class="field">
                            <label for="vision_content">Tab Content</label>
                            <textarea id="vision_content" name="vision_content" rows="4"><?= e($about['vision_content']) ?></textarea>
                        </div>
                    </div>

                    <!-- Tab 6: Lumen Distribution -->
                    <div class="tab-card">
                        <h4>
                            <span>Tab 6: Distribution</span>
                            <span class="badge-tab">Pill #6</span>
                        </h4>
                        <div class="field" style="margin-bottom:10px;">
                            <label for="distribution_title">Tab Title</label>
                            <input type="text" id="distribution_title" name="distribution_title" value="<?= e($about['distribution_title']) ?>">
                        </div>
                        <div class="field">
                            <label for="distribution_content">Tab Content</label>
                            <textarea id="distribution_content" name="distribution_content" rows="4"><?= e($about['distribution_content']) ?></textarea>
                        </div>
                    </div>

                    <!-- Tab 7: Community / Direct -->
                    <div class="tab-card">
                        <h4>
                            <span>Tab 7: Community / Direct Signup</span>
                            <span class="badge-tab">Pill #7</span>
                        </h4>
                        <div class="field" style="margin-bottom:10px;">
                            <label for="community_title">Tab Title</label>
                            <input type="text" id="community_title" name="community_title" value="<?= e($about['community_title']) ?>">
                        </div>
                        <div class="field">
                            <label for="community_content">Tab Content</label>
                            <textarea id="community_content" name="community_content" rows="4"><?= e($about['community_content']) ?></textarea>
                        </div>
                    </div>

                    <!-- Tab 8: Jobs -->
                    <div class="tab-card">
                        <h4>
                            <span>Tab 8: Jobs & Careers</span>
                            <span class="badge-tab">Pill #8</span>
                        </h4>
                        <div class="field" style="margin-bottom:10px;">
                            <label for="jobs_title">Tab Title</label>
                            <input type="text" id="jobs_title" name="jobs_title" value="<?= e($about['jobs_title']) ?>">
                        </div>
                        <div class="field">
                            <label for="jobs_content">Tab Content</label>
                            <textarea id="jobs_content" name="jobs_content" rows="4"><?= e($about['jobs_content']) ?></textarea>
                        </div>
                    </div>

                    <!-- Tab 9: Team -->
                    <div class="tab-card full">
                        <h4>
                            <span>Tab 9: Team & Leadership</span>
                            <span class="badge-tab">Pill #9</span>
                        </h4>
                        <div class="field" style="margin-bottom:10px;">
                            <label for="team_title">Tab Title</label>
                            <input type="text" id="team_title" name="team_title" value="<?= e($about['team_title']) ?>">
                        </div>
                        <div class="field">
                            <label for="team_content">Tab Content</label>
                            <textarea id="team_content" name="team_content" rows="4"><?= e($about['team_content']) ?></textarea>
                        </div>
                    </div>

                </div>
            </div>


            <!-- 3. TOPICS & CATEGORIES -->
            <div class="section-block">
                <div class="section-title">
                    🏷️ Topics / Categories Section
                </div>

                <div class="form-grid">
                    <div class="field">
                        <label for="show_topics">Display Topics Section</label>
                        <select id="show_topics" name="show_topics">
                            <option value="1" <?= (int)$about['show_topics'] === 1 ? 'selected' : '' ?>>
                                Yes — Display dynamic categories from database
                            </option>
                            <option value="0" <?= (int)$about['show_topics'] === 0 ? 'selected' : '' ?>>
                                No — Hide topics section
                            </option>
                        </select>
                        <span class="hint">Categories will automatically be pulled from your database categories.</span>
                    </div>

                    <div class="field">
                        <label for="topics_title">Topics Section Heading</label>
                        <input
                            type="text"
                            id="topics_title"
                            name="topics_title"
                            maxlength="150"
                            value="<?= e($about['topics_title']) ?>"
                        >
                    </div>
                </div>
            </div>

            <div style="margin-top: 25px; display:flex; gap:12px; align-items:center;">
                <button type="submit" class="btn-submit">💾 Save About Page Changes</button>
                <a href="../../about.php" target="_blank" style="color:#2563eb; text-decoration:none; font-size:14px; font-weight:600;">
                    Preview About Page ↗
                </a>
            </div>

        </form>

    </div>
</div>

</body>
</html>
