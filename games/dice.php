<?php
function dice_generate_result(): int {
    return random_int(1, 6);
}

function dice_multiplier(string $choice, int $result): float {
    return ((int)$choice) === $result ? 5 : 0; // Payout ×5
}
?>