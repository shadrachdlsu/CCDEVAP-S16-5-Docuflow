<?php
declare(strict_types=1);

function formatDocumentDuration(string $createdAt, string $completedAt): string
{
    $createdTimestamp = strtotime($createdAt);
    $completedTimestamp = strtotime($completedAt);

    if ($createdTimestamp === false || $completedTimestamp === false) {
        return 'Unavailable';
    }

    return formatDurationSeconds(max(0, $completedTimestamp - $createdTimestamp));
}

function formatDurationSeconds(int $durationSeconds): string
{
    $remainingSeconds = max(0, $durationSeconds);

    if ($remainingSeconds < 60) {
        return 'Less than a minute';
    }

    $days = intdiv($remainingSeconds, 86400);
    $remainingSeconds %= 86400;
    $hours = intdiv($remainingSeconds, 3600);
    $remainingSeconds %= 3600;
    $minutes = intdiv($remainingSeconds, 60);

    $parts = [];

    if ($days > 0) {
        $parts[] = $days . ' day' . ($days === 1 ? '' : 's');
    }

    if ($hours > 0) {
        $parts[] = $hours . ' hour' . ($hours === 1 ? '' : 's');
    }

    if ($minutes > 0) {
        $parts[] = $minutes . ' minute' . ($minutes === 1 ? '' : 's');
    }

    return implode(', ', $parts);
}
