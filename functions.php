<?php

declare(strict_types=1);

function redirect(string $to): void
{
    header("Location: {$to}");
    exit;
}

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
