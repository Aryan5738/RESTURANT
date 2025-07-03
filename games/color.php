<?php
// This file can hold specific functions or config for Color Prediction game.

function color_generate_result(): string {
    $colors = ['RED', 'GREEN', 'VIOLET'];
    return $colors[array_rand($colors)];
}

function color_multiplier(string $choice, string $result): float {
    $choice = strtoupper($choice);
    if ($choice !== $result) return 0;
    return $result === 'VIOLET' ? 5 : 1.5;
}
?>