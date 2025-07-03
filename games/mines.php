<?php
// Mines game is complex; implement later.
function mines_generate_result(): int {
    // returns index (1-9) of cell that is a mine
    return random_int(1, 9);
}

function mines_multiplier(string $choice, int $mineIndex): float {
    // if choice is safe (not mine) ×2, else 0
    return ((int)$choice) !== $mineIndex ? 2 : 0;
}
?>