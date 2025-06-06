<?php
// Summon this folder from the ether, provided it hasn't already manifested.
if (!is_dir('data')) {
    mkdir('data', 0777, true);
}


$forums = [
    [
        "id" => 1,
        "name" => "General",
        "topics" => [
            [
                "id" => 1,
                "title" => "Welcome to the General Forum!",
                "posts" => [
                    [
                        "author" => "admin",
                        "content" => "This is the first topic in the General forum. Feel free to reply!",
                        "timestamp" => date("Y-m-d H:i:s")
                    ]
                ]
            ]
        ]
    ]
];

// also save that sucker
file_put_contents('data/forums.json', json_encode($forums, JSON_PRETTY_PRINT));
echo "Forum initialized successfully!";
?>
