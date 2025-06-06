<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


$baseDir     = __DIR__;
$forumsDir   = $baseDir . DIRECTORY_SEPARATOR . 'forums';

$forumName = isset($_GET['forum']) ? trim($_GET['forum']) : '';
$topicId   = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$forumPath  = $forumsDir . DIRECTORY_SEPARATOR . $forumName;
$forumJson  = $forumPath . DIRECTORY_SEPARATOR . 'forum.json';
$topicsDir  = $forumPath . DIRECTORY_SEPARATOR . 'topics';
$topicPath  = $topicsDir . DIRECTORY_SEPARATOR . $topicId;

$bodyFile     = $topicPath . DIRECTORY_SEPARATOR . 'body.txt';
$repliesFile  = $topicPath . DIRECTORY_SEPARATOR . 'replies.json';
$uploadsDir   = $topicPath . DIRECTORY_SEPARATOR . 'uploads';


if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0777, true);
}


if (!file_exists($forumJson) || !file_exists($bodyFile) || !file_exists($repliesFile)) {
    die("Topic not found or corrupted.");
}


$forum = json_decode(file_get_contents($forumJson), true);
if (!is_array($forum)) {
    die("Forum data corrupted.");
}


$topicMeta = null;
foreach ($forum['topics'] as $t) {
    if ((int)$t['id'] === $topicId) {
        $topicMeta = $t;
        break;
    }
}
if (!$topicMeta) {
    die("Topic metadata not found.");
}


$body    = file_get_contents($bodyFile);
$replies = json_decode(file_get_contents($repliesFile), true);
if (!is_array($replies)) $replies = [];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['reply'])) {
    $replyText = trim($_POST['reply']);
    $imagePath = '';

    
// If you're on hosting a website by windows and can't upload gifs do not fucking host it because i tried testing it it would not fucking work.
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['image']['tmp_name']);

        if (in_array($fileType, $allowedTypes)) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $newName = uniqid('img_') . '.' . $ext;
            $targetPath = $uploadsDir . DIRECTORY_SEPARATOR . $newName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                
                $imagePath = 'forums/' . rawurlencode($forumName) . '/topics/' . $topicId . '/uploads/' . $newName;
            }
        }
    }

    $replies[] = [
        'author'    => 'anon',
        'timestamp' => date('Y-m-d H:i:s'),
        'content'   => $replyText,
        'image'     => $imagePath
    ];
    file_put_contents($repliesFile, json_encode($replies, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    
    header("Location: showtopic.php?forum=" . urlencode($forumName) . "&id=" . $topicId);
    exit;
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
            <?php if (!empty($reply['image'])): ?>
                <div class="post-image">
                    <img src="<?= htmlspecialchars($reply['image']) ?>" alt="Reply image" style="max-width: 300px;">
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <form method="post" enctype="multipart/form-data">
        <textarea name="reply" rows="5" cols="60" placeholder="Write a reply..." required></textarea><br>
        <input type="file" name="image" accept="image/*"><br><br>
        <button type="submit">Post Reply</button>
    </form>

    <br>
    <a href="index.php">← Back to Forum</a>
</div>
</body>
</html>
