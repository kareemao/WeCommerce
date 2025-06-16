<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add to cart
function addToCart($product) {
    // Check if item exists in cart
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $product['id']) {
            $item['quantity'] += $product['quantity'];
            $found = true;
            break;
        }
    }

    if (!$found) {
        $_SESSION['cart'][] = $product;
    }
}

// Remove from cart
function removeFromCart($id) {
    foreach ($_SESSION['cart'] as $i => $item) {
        if ($item['id'] == $id) {
            unset($_SESSION['cart'][$i]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);
            break;
        }
    }
}

// Update quantity
function updateQuantity($id, $quantity) {
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $id) {
            $item['quantity'] = max(1, (int)$quantity);
            break;
        }
    }
}

// Get cart total
function getCartTotal() {
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

// Process actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $product = [
                    'id' => $_POST['id'],
                    'name' => $_POST['name'],
                    'price' => $_POST['price'],
                    'image' => $_POST['image'],
                    'quantity' => $_POST['quantity'] ?? 1
                ];
                addToCart($product);
                header("Location: cart.php");
                exit;
                
            case 'update':
                updateQuantity($_POST['id'], $_POST['quantity']);
                header("Location: cart.php");
                exit;
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    removeFromCart($_GET['id']);
    header("Location: cart.php");
    exit;
}
?>