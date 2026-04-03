<?php

declare(strict_types=1);

function getVisibleEntriesForUser(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare("
        SELECT
            id,
            activity_date,
            title,
            sport_type,
            training_type,
            distance_km,
            duration_min,
            rpe,
            fitness_feeling,
            notes,
            source
        FROM training_entries
        WHERE user_id = :user_id
          AND is_hidden = 0
        ORDER BY activity_date DESC, id DESC
    ");

    $stmt->execute([
        'user_id' => $userId,
    ]);

    return $stmt->fetchAll();
}
