<?php
require_once __DIR__ . '/../config/functions.php';

// Latest round
$stmt     = $pdo->query('SELECT * FROM rounds ORDER BY id DESC LIMIT 1');
$current  = $stmt->fetch();
$now      = time();

if (!$current || strtotime($current['timestamp']) <= $now - 60) {
    // Close current round & generate result
    if ($current) {
        $colors       = ['RED', 'GREEN', 'VIOLET'];
        $result_color = $colors[array_rand($colors)];

        // Update round
        $pdo->prepare('UPDATE rounds SET result_color = ? WHERE id = ?')
            ->execute([$result_color, $current['id']]);

        // Payouts
        $predictions = $pdo->prepare('SELECT * FROM predictions WHERE round_id = ?');
        $predictions->execute([$current['id']]);
        foreach ($predictions as $pred) {
            $status     = 'lost';
            $multiplier = 0;
            if ($pred['color'] === $result_color) {
                $multiplier = $result_color === 'VIOLET' ? 5 : 1.5;
                $win        = $pred['amount'] * $multiplier;
                $pdo->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')
                    ->execute([$win, $pred['user_id']]);
                $status = 'won';
            }
            $pdo->prepare('UPDATE predictions SET status = ? WHERE id = ?')->execute([$status, $pred['id']]);
        }
    }

    // Start new round
    $period = $current ? $current['period_number'] + 1 : 1;
    $pdo->prepare('INSERT INTO rounds (period_number, timestamp) VALUES (?, NOW())')->execute([$period]);
}
?>