<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$forumsDir = __DIR__ . '/forums';
$defaultForumName = 'General';
$defaultForumPath = $forumsDir . '/' . $defaultForumName;
$defaultForumFile = $defaultForumPath . '/forum.json';

// Create forums/General/ and default forum.json if missing
if (!is_dir($defaultForumPath)) {
    mkdir($defaultForumPath . '/topics', 0777, true);

    $defaultForumData = [
        'name' => $defaultForumName,
        'topics' => []
    ];

    file_put_contents($defaultForumFile, json_encode($defaultForumData, JSON_PRETTY_PRINT));
}

// Scan all forum folders
$forumFolders = array_filter(glob($forumsDir . '/*'), 'is_dir');

// Load each forum's forum.json
$forums = [];
foreach ($forumFolders as $forumPath) {
    $forumFile = $forumPath . '/forum.json';
    if (file_exists($forumFile)) {
        $forumData = json_decode(file_get_contents($forumFile), true);
        if (is_array($forumData)) {
            $forums[] = $forumData;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PortableBB</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <h1>PortableBB</h1>

    <?php if (empty($forums)): ?>
        <p><em>No forums found.</em></p>
    <?php endif; ?>

    <?php foreach ($forums as $forum): ?>
        <div class="forum">
            <h2 class="forum-title">
    <?= htmlspecialchars($forum['name']) ?>
    <a href="new_topic.php?forum=<?= urlencode($forum['name']) ?>" class="new-topic-button">+ New Topic</a>
</h2>


            <?php if (!empty($forum['topics'])): ?>
                <?php foreach ($forum['topics'] as $topic): ?>
                    <div class="topic">
                        <div class="topic-title">
                            <a href="showtopic.php?forum=<?= urlencode($forum['name']) ?>&id=<?= $topic['id'] ?>">

                                <?= htmlspecialchars($topic['title']) ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p><em>No topics yet.</em></p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>
