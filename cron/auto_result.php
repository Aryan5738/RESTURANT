<?php
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../games/color.php';
require_once __DIR__ . '/../games/bigsmall.php';
require_once __DIR__ . '/../games/dice.php';
require_once __DIR__ . '/../games/headtail.php';
require_once __DIR__ . '/../games/mines.php';

// Latest round
$stmt     = $pdo->query('SELECT * FROM rounds ORDER BY id DESC LIMIT 1');
$current  = $stmt->fetch();
$now      = time();

if (!$current || strtotime($current['timestamp']) <= $now - 60) {
    if ($current) {
        $gameTypes = ['color', 'bigsmall', 'dice', 'headtail', 'mines'];

        foreach ($gameTypes as $type) {
            switch ($type) {
                case 'color':
                    $result = color_generate_result();
                    // save color result to rounds table
                    $pdo->prepare('UPDATE rounds SET result_color = ? WHERE id = ?')->execute([$result, $current['id']]);
                    break;
                case 'bigsmall':
                    $result = bigsmall_generate_result();
                    break;
                case 'dice':
                    $result = dice_generate_result();
                    break;
                case 'headtail':
                    $result = headtail_generate_result();
                    break;
                case 'mines':
                    $result = mines_generate_result();
                    break;
                default:
                    $result = null;
            }

            // Fetch predictions for this game type
            $predictions = $pdo->prepare('SELECT * FROM predictions WHERE round_id = ? AND game_type = ?');
            $predictions->execute([$current['id'], $type]);
            foreach ($predictions as $pred) {
                $status = 'lost';
                $multiplier = 0;
                switch ($type) {
                    case 'color':
                        $multiplier = color_multiplier($pred['color'], $result);
                        break;
                    case 'bigsmall':
                        $multiplier = bigsmall_multiplier($pred['color'], $result);
                        break;
                    case 'dice':
                        $multiplier = dice_multiplier($pred['color'], $result);
                        break;
                    case 'headtail':
                        $multiplier = headtail_multiplier($pred['color'], $result);
                        break;
                    case 'mines':
                        $multiplier = mines_multiplier($pred['color'], $result);
                        break;
                }
                if ($multiplier > 0) {
                    $win = $pred['amount'] * $multiplier;
                    $pdo->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')->execute([$win, $pred['user_id']]);
                    $status = 'won';
                }
                $pdo->prepare('UPDATE predictions SET status = ? WHERE id = ?')->execute([$status, $pred['id']]);
            }
        }
    }

    // Start new round
    $period = $current ? $current['period_number'] + 1 : 1;
    $pdo->prepare('INSERT INTO rounds (period_number, timestamp) VALUES (?, NOW())')->execute([$period]);
}
?>