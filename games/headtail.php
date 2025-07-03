<?php
function headtail_generate_result(): string {
    return random_int(0, 1) ? 'HEAD' : 'TAIL';
}

function headtail_multiplier(string $choice, string $result): float {
    return strtoupper($choice) === $result ? 1.9 : 0;
}
?>