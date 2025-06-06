<?php
error_reporting(E_ALL);
ini_set('display_errors', 1); // Please show errors (◕_◕)

$forumsDir = __DIR__ . '/forums';

$forumName = isset($_GET['forum']) ? trim($_GET['forum']) : '';
$forumPath = $forumsDir . '/' . $forumName;
$forumJson = $forumPath . '/forum.json';
$topicsDir = $forumPath . '/topics';

if (!is_dir($forumPath) || !file_exists($forumJson)) {
    die("Forum not found.");
}

$forum = json_decode(file_get_contents($forumJson), true);
if (!is_array($forum)) die("Invalid forum data.");


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['body'] ?? '');

    if ($title !== '' && $body !== '') {
        
        $newId = count($forum['topics']) > 0
            ? max(array_column($forum['topics'], 'id')) + 1
            : 1;

        // Save that sucka
        $topicPath = $topicsDir . '/' . $newId;
        mkdir($topicPath, 0777, true);
        file_put_contents($topicPath . '/body.txt', $body);
        file_put_contents($topicPath . '/replies.json', json_encode([], JSON_PRETTY_PRINT));

        // This feels like giving your forum.json a tiny, invisible backpack full of notes no one will ever read... but it's important!
        $forum['topics'][] = [
            'id' => $newId,
            'title' => $title,
            'created' => date('Y-m-d H:i:s')
        ];
        file_put_contents($forumJson, json_encode($forum, JSON_PRETTY_PRINT));

        header("Location: index.php");
        exit;
    } else {
        $error = "Both title and body are required.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Topic - <?= htmlspecialchars($forumName) ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <h1>New Topic in <?= htmlspecialchars($forumName) ?></h1>

    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="text" name="title" placeholder="Topic title" required><br><br>
        <textarea name="body" rows="10" cols="60" placeholder="Topic content" required></textarea><br><br>
        <button type="submit">Post Topic</button>
    </form>

    <br>
    <a href="index.php">← Back to Forum</a>
</div>
</body>
</html>
