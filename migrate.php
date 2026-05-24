<?php
require_once 'includes/db.php';

$queries = [
    // Video Watch Progress
    "ALTER TABLE lesson_completions ADD COLUMN progress INT DEFAULT 0",
    "ALTER TABLE lesson_completions ADD COLUMN is_completed TINYINT(1) DEFAULT 0",
    
    // Daily Streaks tracking
    "ALTER TABLE users ADD COLUMN last_login_date DATE NULL",
    "ALTER TABLE users ADD COLUMN current_streak INT DEFAULT 0",

    // Coding challenges
    "ALTER TABLE lessons ADD COLUMN lesson_type ENUM('video', 'document', 'coding') DEFAULT 'video'",
    "ALTER TABLE lessons MODIFY COLUMN lesson_link VARCHAR(500) DEFAULT NULL",
    "ALTER TABLE lessons ADD COLUMN coding_language VARCHAR(50) DEFAULT NULL",
    "ALTER TABLE lessons ADD COLUMN coding_boilerplate TEXT DEFAULT NULL",
    "ALTER TABLE lessons ADD COLUMN coding_expected_output TEXT DEFAULT NULL"
];

foreach ($queries as $q) {
    if ($conn->query($q)) {
        echo "Success: $q\n";
    } else {
        echo "Error on ($q): " . $conn->error . "\n";
    }
}
echo "Migration finished.\n";
?>
