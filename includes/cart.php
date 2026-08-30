<?php
declare(strict_types=1);

function cart_quantities(): array
{
    $stored = $_SESSION['cart'] ?? [];

    if (!is_array($stored)) {
        $_SESSION['cart'] = [];

        return [];
    }

    $normalized = [];

    foreach ($stored as $key => $value) {
        $id = is_array($value)
            ? (int) ($value['id'] ?? $key)
            : (int) $key;

        $quantity = is_array($value)
            ? (int) ($value['qty'] ?? 1)
            : (int) $value;

        if ($id > 0 && $quantity > 0) {
            $normalized[$id] = min(
                10,
                $quantity
            );
        }
    }

    $_SESSION['cart'] = $normalized;

    return $normalized;
}

function cart_count(): int
{
    return array_sum(
        cart_quantities()
    );
}

function load_cart_items(
    PDO $pdo
): array {
    $quantities = cart_quantities();

    if ($quantities === []) {
        return [];
    }

    $ids = array_keys($quantities);

    $placeholders = implode(
        ',',
        array_fill(
            0,
            count($ids),
            '?'
        )
    );

    $statement = $pdo->prepare(
        "SELECT
            id,
            title,
            price_basic,
            thumbnail
        FROM services
        WHERE is_active = 1
        AND id IN ({$placeholders})"
    );

    $statement->execute($ids);

    $rows = $statement->fetchAll();

    $items = [];
    $validIds = [];

    foreach ($rows as $row) {
        $id = (int) $row['id'];

        $quantity =
            $quantities[$id] ?? 0;

        $price = (float) (
            $row['price_basic']
        );

        if (
            $quantity < 1
            || $price < 0
        ) {
            continue;
        }

        $validIds[$id] = $quantity;

        $items[] = [
            'id' => $id,

            'title' =>
                (string) $row['title'],

            'price' => $price,

            'thumbnail' =>
                (string) (
                    $row['thumbnail']
                    ?? ''
                ),

            'quantity' => $quantity,

            'line_total' => round(
                $price * $quantity,
                2
            ),
        ];
    }

    $_SESSION['cart'] = $validIds;

    return $items;
}

function cart_total(
    array $items
): float {
    $total = 0.0;

    foreach ($items as $item) {
        $total += (float) (
            $item['line_total']
            ?? 0
        );
    }

    return round($total, 2);
}