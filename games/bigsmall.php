<?php
function bigsmall_generate_result(): string {
    // Random number 1-6, BIG for 4-6, SMALL for 1-3
    $num = random_int(1, 6);
    return $num >= 4 ? 'BIG' : 'SMALL';
}

function bigsmall_multiplier(string $choice, string $result): float {
    return strtoupper($choice) === $result ? 1.9 : 0;
}
?>