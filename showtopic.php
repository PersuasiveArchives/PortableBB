<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$forumsDir = __DIR__ . '/forums';

$forumName = isset($_GET['forum']) ? trim($_GET['forum']) : '';
$topicId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$forumPath = $forumsDir . '/' . $forumName;
$forumJson = $forumPath . '/forum.json';
$topicsDir = $forumPath . '/topics';
$topicFolder = $topicsDir . '/' . $topicId;

$bodyFile = $topicFolder . '/body.txt';
$repliesFile = $topicFolder . '/replies.json';

// Validate
if (!file_exists($forumJson) || !file_exists($bodyFile) || !file_exists($repliesFile)) {
    die("Topic not found or corrupted.");
}

$forum = json_decode(file_get_contents($forumJson), true);
if (!is_array($forum)) {
    die("Forum data corrupted.");
}

// Find topic metadata
$topicMeta = null;
foreach ($forum['topics'] as $t) {
    if ($t['id'] == $topicId) {
        $topicMeta = $t;
        break;
    }
}
if (!$topicMeta) {
    die("Topic metadata not found.");
}

// Load topic
$body = file_get_contents($bodyFile);
$replies = json_decode(file_get_contents($repliesFile), true);
if (!is_array($replies)) $replies = [];

// Handle reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['reply'])) {
    $replyText = trim($_POST['reply']);
    if ($replyText !== '') {
        $replies[] = [
            'author' => 'anon',
            'timestamp' => date('Y-m-d H:i:s'),
            'content' => $replyText
        ];
        file_put_contents($repliesFile, json_encode($replies, JSON_PRETTY_PRINT));
        header("Location: showtopic.php?forum=" . urlencode($forumName) . "&id=" . $topicId);
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($topicMeta['title']) ?> - <?= htmlspecialchars($forum['name']) ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <h1><?= htmlspecialchars($forum['name']) ?></h1>
    <h2><?= htmlspecialchars($topicMeta['title']) ?></h2>

    <div class="post">
        <div class="post-header">
            <span class="author">anon</span>
            <span class="timestamp"><?= $topicMeta['created'] ?></span>
        </div>
        <div class="post-content"><?= nl2br(htmlspecialchars($body)) ?></div>
    </div>

    <?php foreach ($replies as $reply): ?>
        <div class="post">
            <div class="post-header">
                <span class="author"><?= htmlspecialchars($reply['author']) ?></span>
                <span class="timestamp"><?= $reply['timestamp'] ?></span>
            </div>
            <div class="post-content"><?= nl2br(htmlspecialchars($reply['content'])) ?></div>
        </div>
    <?php endforeach; ?>

    <form method="post">
        <textarea name="reply" rows="5" cols="60" placeholder="Write a reply..." required></textarea><br>
        <button type="submit">Post Reply</button>
    </form>

    <br>
    <a href="index.php">← Back to Forum</a>
</div>
</body>
</html>
