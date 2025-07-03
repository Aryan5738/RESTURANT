<?php
// Auto Result Generation Cron Job
// This script should be run every minute via cron job
// crontab -e
// * * * * * /usr/bin/php /path/to/your/project/cron/auto_result.php

require_once '../config/functions.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    echo "Starting auto result process...\n";
    
    // Get all active rounds
    $stmt = $pdo->prepare("SELECT * FROM rounds WHERE status = 'active'");
    $stmt->execute();
    $activeRounds = $stmt->fetchAll();
    
    foreach ($activeRounds as $round) {
        $startTime = strtotime($round['start_time']);
        $currentTime = time();
        $elapsed = $currentTime - $startTime;
        
        // Check if 60 seconds have passed
        if ($elapsed >= 60) {
            echo "Processing round {$round['period_number']}...\n";
            
            // Generate result
            $result = generateRandomResult($round['game_type']);
            $resultNumber = null;
            
            if ($round['game_type'] === 'dice') {
                $resultNumber = $result;
                $result = ($result > 3) ? 'big' : 'small';
            }
            
            // Update round with result
            $stmt = $pdo->prepare("UPDATE rounds SET result_color = ?, result_number = ?, status = 'completed', end_time = NOW() WHERE id = ?");
            $stmt->execute([$result, $resultNumber, $round['id']]);
            
            echo "Round {$round['period_number']} completed with result: $result\n";
            
            // Get all predictions for this round
            $stmt = $pdo->prepare("SELECT * FROM predictions WHERE round_id = ? AND status = 'pending'");
            $stmt->execute([$round['id']]);
            $predictions = $stmt->fetchAll();
            
            foreach ($predictions as $prediction) {
                $winAmount = calculatePayout($prediction['color'], $result, $prediction['amount']);
                $status = $winAmount > 0 ? 'win' : 'loss';
                
                // Update prediction status
                $stmt = $pdo->prepare("UPDATE predictions SET status = ?, win_amount = ? WHERE id = ?");
                $stmt->execute([$status, $winAmount, $prediction['id']]);
                
                // If won, add amount to user balance
                if ($winAmount > 0) {
                    updateUserBalance($prediction['user_id'], $winAmount);
                    echo "User {$prediction['user_id']} won ₹{$winAmount}\n";
                }
            }
            
            // Create new round for the same game type
            $newRoundId = createNewRound($round['game_type']);
            echo "Created new round {$newRoundId} for game type {$round['game_type']}\n";
        }
    }
    
    echo "Auto result process completed successfully.\n";
    
} catch (Exception $e) {
    echo "Error in auto result process: " . $e->getMessage() . "\n";
    error_log("Auto result error: " . $e->getMessage());
}

// Optional: Clean up old rounds (keep only last 100)
try {
    $stmt = $pdo->prepare("DELETE FROM rounds WHERE status = 'completed' AND id NOT IN (SELECT id FROM (SELECT id FROM rounds WHERE status = 'completed' ORDER BY id DESC LIMIT 100) AS temp)");
    $stmt->execute();
    echo "Cleaned up old rounds.\n";
} catch (Exception $e) {
    echo "Error cleaning up rounds: " . $e->getMessage() . "\n";
}

echo "Process finished at " . date('Y-m-d H:i:s') . "\n";
?>