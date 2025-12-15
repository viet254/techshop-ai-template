<?php
/**
 * Cart helper functions shared across cart-related APIs.
 * Centralizes logic for syncing session/DB carts and calculating totals.
 */

if (!function_exists('cart_get_user_id')) {
    function cart_get_user_id(): ?int
    {
        return isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
    }

    function cart_ensure_session_bucket(string $key): void
    {
        if (!isset($_SESSION[$key]) || !is_array($_SESSION[$key])) {
            $_SESSION[$key] = [];
        }
    }

    function cart_fetch_product(mysqli $conn, int $productId): ?array
    {
        $stmt = $conn->prepare("SELECT id, name, price, sale_price, stock, image FROM products WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        if (!$product) {
            return null;
        }
        $product['id'] = (int)$product['id'];
        $product['price'] = (float)$product['price'];
        $product['sale_price'] = isset($product['sale_price']) ? (float)$product['sale_price'] : null;
        $product['stock'] = isset($product['stock']) ? (int)$product['stock'] : 0;
        $effective = $product['price'];
        if ($product['sale_price'] !== null && $product['sale_price'] < $product['price']) {
            $effective = $product['sale_price'];
        }
        $product['effective_price'] = $effective;
        return $product;
    }

    function cart_set_session_item(array $product, int $quantity): void
    {
        cart_ensure_session_bucket('cart');
        $_SESSION['cart'][$product['id']] = [
            'product_id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['effective_price'],
            'quantity' => $quantity
        ];
    }

    function cart_remove_session_item(int $productId): void
    {
        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
        }
    }

    function cart_get_session_item_quantity(int $productId): int
    {
        return isset($_SESSION['cart'][$productId]) ? (int)$_SESSION['cart'][$productId]['quantity'] : 0;
    }

    function cart_get_db_item_quantity(mysqli $conn, int $userId, int $productId): int
    {
        $stmt = $conn->prepare("SELECT quantity FROM cart_items WHERE user_id = ? AND product_id = ? LIMIT 1");
        $stmt->bind_param('ii', $userId, $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? (int)$row['quantity'] : 0;
    }

    function cart_upsert_db_item(mysqli $conn, int $userId, int $productId, int $quantity): void
    {
        $stmt = $conn->prepare("SELECT id FROM cart_items WHERE user_id = ? AND product_id = ? LIMIT 1");
        $stmt->bind_param('ii', $userId, $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $update = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
            $update->bind_param('ii', $quantity, $row['id']);
            $update->execute();
        } else {
            $insert = $conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $insert->bind_param('iii', $userId, $productId, $quantity);
            $insert->execute();
        }
    }

    function cart_remove_db_item(mysqli $conn, int $userId, int $productId): void
    {
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param('ii', $userId, $productId);
        $stmt->execute();
    }

    function cart_normalize_item(array $product, int $quantity): array
    {
        $effective = (float)$product['effective_price'];
        $price = (float)$product['price'];
        $sale = $product['sale_price'] !== null ? (float)$product['sale_price'] : null;
        $stock = max(0, (int)($product['stock'] ?? 0));
        $quantity = max(0, $quantity);
        $needsAdjustment = $quantity > $stock && $stock >= 0;
        return [
            'product_id' => (int)$product['id'],
            'name' => $product['name'],
            'image' => $product['image'] ?? null,
            'price' => $price,
            'sale_price' => $sale,
            'effective_price' => $effective,
            'quantity' => $quantity,
            'stock' => $stock,
            'line_total' => $effective * $quantity,
            'needs_adjustment' => $needsAdjustment,
            'is_out_of_stock' => $stock <= 0,
            'max_quantity' => $stock,
            'unit_saving' => ($sale !== null && $sale < $price) ? ($price - $sale) : 0
        ];
    }

    function cart_fetch_items(mysqli $conn): array
    {
        $userId = cart_get_user_id();
        $items = [];
        if ($userId) {
            $stmt = $conn->prepare("
                SELECT ci.product_id, ci.quantity, p.name, p.price, p.sale_price, p.stock, p.image
                FROM cart_items ci
                JOIN products p ON ci.product_id = p.id
                WHERE ci.user_id = ?
            ");
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $product = [
                    'id' => (int)$row['product_id'],
                    'name' => $row['name'],
                    'price' => (float)$row['price'],
                    'sale_price' => isset($row['sale_price']) ? (float)$row['sale_price'] : null,
                    'stock' => isset($row['stock']) ? (int)$row['stock'] : 0,
                    'image' => $row['image'],
                    'effective_price' => ($row['sale_price'] !== null && (float)$row['sale_price'] < (float)$row['price'])
                        ? (float)$row['sale_price'] : (float)$row['price']
                ];
                $quantity = (int)$row['quantity'];
                $items[] = cart_normalize_item($product, $quantity);
                cart_set_session_item($product, $quantity);
            }
        } else {
            cart_ensure_session_bucket('cart');
            foreach ($_SESSION['cart'] as $pid => $sessionItem) {
                $product = cart_fetch_product($conn, (int)$pid);
                if (!$product) {
                    cart_remove_session_item((int)$pid);
                    continue;
                }
                $quantity = (int)($sessionItem['quantity'] ?? 0);
                $items[] = cart_normalize_item($product, $quantity);
                cart_set_session_item($product, $quantity); // refresh price snapshot
            }
        }
        return $items;
    }

    function cart_items_to_map(array $items): array
    {
        $map = [];
        foreach ($items as $item) {
            $map[$item['product_id']] = [
                'product_id' => $item['product_id'],
                'name' => $item['name'],
                'price' => $item['effective_price'],
                'quantity' => $item['quantity']
            ];
        }
        return $map;
    }

    function cart_calculate_summary(array $items): array
    {
        $subtotal = 0;
        $itemCount = 0;
        $warnings = [];
        foreach ($items as $item) {
            $subtotal += $item['line_total'];
            $itemCount += $item['quantity'];
            if ($item['is_out_of_stock']) {
                $warnings[] = $item['name'] . ' đã hết hàng.';
            } elseif ($item['needs_adjustment']) {
                $warnings[] = $item['name'] . ' chỉ còn ' . $item['stock'] . ' sản phẩm.';
            }
        }
        return [
            'item_count' => $itemCount,
            'line_count' => count($items),
            'subtotal' => $subtotal,
            'warnings' => $warnings,
            'warning_count' => count($warnings)
        ];
    }

    function cart_fetch_saved_items(mysqli $conn): array
    {
        $userId = cart_get_user_id();
        $saved = [];
        if ($userId) {
            $stmt = $conn->prepare("
                SELECT si.product_id, si.quantity, p.name, p.price, p.sale_price, p.image
                FROM saved_items si
                JOIN products p ON si.product_id = p.id
                WHERE si.user_id = ?
            ");
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $price = (float)$row['price'];
                $sale = isset($row['sale_price']) ? (float)$row['sale_price'] : null;
                $saved[] = [
                    'product_id' => (int)$row['product_id'],
                    'name' => $row['name'],
                    'price' => $sale !== null && $sale < $price ? $sale : $price,
                    'original_price' => $price,
                    'sale_price' => $sale,
                    'quantity' => (int)$row['quantity'],
                    'image' => $row['image']
                ];
            }
        } else {
            cart_ensure_session_bucket('saved');
            foreach ($_SESSION['saved'] as $pid => $item) {
                $product = cart_fetch_product($conn, (int)$pid);
                if (!$product) {
                    unset($_SESSION['saved'][$pid]);
                    continue;
                }
                $saved[] = [
                    'product_id' => $product['id'],
                    'name' => $product['name'],
                    'price' => $product['effective_price'],
                    'original_price' => $product['price'],
                    'sale_price' => $product['sale_price'],
                    'quantity' => (int)($item['quantity'] ?? 0),
                    'image' => $product['image']
                ];
            }
        }
        return $saved;
    }
}

