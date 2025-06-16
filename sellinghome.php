<?php
session_start();

// Database connection
$host = "sql305.infinityfree.com";        // From your screenshot
$db   = "if0_39218569_redstore_db";       // Replace with your actual DB name
$user = "if0_39218569";                   // From your screenshot
$pass = "cQNv6p985h0xT";       // Click the eye icon in your panel to reveal


$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize defaults
$current_user_id = null;
$user_name = "Guest";

// Get current user ID
if (isset($_SESSION['user_id'])) {
    $current_user_id = $_SESSION['user_id'];
} elseif (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
    $user_query = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
    if ($user_query) {
        $user_query->bind_param("s", $email);
        $user_query->execute();
        $user_result = $user_query->get_result();
        if ($user_result->num_rows > 0) {
            $user_row = $user_result->fetch_assoc();
            $current_user_id = $user_row['id'];
            $_SESSION['user_id'] = $current_user_id;
            $user_name = $user_row['name'];
        }
        $user_query->close();
    }
}

// Get user's products
$user_products = [];
if ($current_user_id) {
    $sql = "SELECT products.*, categories.name AS category_name 
            FROM products 
            JOIN categories ON products.category_id = categories.id 
            WHERE products.seller_id = ? 
            ORDER BY products.created_at DESC";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $current_user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $user_products = $result->fetch_all(MYSQLI_ASSOC);
        }
        $stmt->close();
    }
}

// Seller stats
$active_listings = 0;
$monthly_revenue = 0;
$total_orders = 0;
$seller_rating = 4.7; // Default fallback

if ($current_user_id) {
    // Active listings
    $active_sql = "SELECT COUNT(*) as count FROM products WHERE seller_id = ? AND stock > 0";
    $active_stmt = $conn->prepare($active_sql);
    if ($active_stmt) {
        $active_stmt->bind_param("i", $current_user_id);
        $active_stmt->execute();
        $active_result = $active_stmt->get_result();
        if ($active_result && $active_result->num_rows > 0) {
            $row = $active_result->fetch_assoc();
            $active_listings = $row['count'];
        }
        $active_stmt->close();
    }

    // Monthly revenue
    $revenue_sql = "SELECT SUM(price * quantity) as revenue 
                    FROM orders 
                    WHERE seller_id = ? 
                    AND MONTH(order_date) = MONTH(CURRENT_DATE())";
    $revenue_stmt = $conn->prepare($revenue_sql);
    if ($revenue_stmt) {
        $revenue_stmt->bind_param("i", $current_user_id);
        $revenue_stmt->execute();
        $revenue_result = $revenue_stmt->get_result();
        if ($revenue_result && $revenue_result->num_rows > 0) {
            $row = $revenue_result->fetch_assoc();
            $monthly_revenue = $row['revenue'] ?? 0;
        }
        $revenue_stmt->close();
    }

    // Total orders
    $orders_sql = "SELECT COUNT(*) as count FROM orders WHERE seller_id = ?";
    $orders_stmt = $conn->prepare($orders_sql);
    if ($orders_stmt) {
        $orders_stmt->bind_param("i", $current_user_id);
        $orders_stmt->execute();
        $orders_result = $orders_stmt->get_result();
        if ($orders_result && $orders_result->num_rows > 0) {
            $row = $orders_result->fetch_assoc();
            $total_orders = $row['count'];
        }
        $orders_stmt->close();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard | WeCommerce</title>
    <link rel="stylesheet" href="sellerstyle.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
            <!-- Navigation bar -->
            <div class="navbar">
                <div class="logo">
                    <img src="images/logo.png.png" alt="RedStore logo" width=125px>
                </div>
                <nav> 
                    <ul id="MenuItems">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="">Contact</a></li>
                        <li><a href="login.php">Account</a></li>
                    </ul>
                </nav>
                <img src="images/cart.png" alt="cart" width=30px height=30px>
            </div>
        </div>
   
    <!-- Header with Background -->
    <header class="header">
        <div class="container1">
            <?php if (isset($_SESSION['name']) || isset($_SESSION['email'])): ?>
            <div class="welcome-container">
                <p class="welcome-msg">
                    Welcome, 
                    <?php 
                    if (isset($_SESSION['name'])) {
                        echo htmlspecialchars($_SESSION['name']);
                    } elseif (isset($_SESSION['email'])) {
                        echo htmlspecialchars($_SESSION['email']);
                    } 
                    ?>!
                </p>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
            <?php endif; ?>
            
            <div class="header-content">
                <h1>Welcome to Your Seller Dashboard</h1>
                <p>Manage your products, track your sales, and grow your business with our seller tools</p>
                <a href="Selling.php" class="btn">
                    <i class="fas fa-plus-circle"></i>List a Product
                </a>
            </div>
        </div>
    </header>

    <!-- Stats Section -->
    <div class="container">
        <div class="stats-section">
            <div class="stat-card">
                <i class="fas fa-box-open"></i>
                <div class="number"><?php echo $active_listings; ?></div>
                <div class="label">Active Listings</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-chart-line"></i>
                <div class="number">$<?php echo number_format($monthly_revenue, 2); ?></div>
                <div class="label">Monthly Revenue</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-shopping-cart"></i>
                <div class="number"><?php echo $total_orders; ?></div>
                <div class="label">Total Orders</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-star"></i>
                <div class="number"><?php echo $seller_rating; ?></div>
                <div class="label">Seller Rating</div>
            </div>
        </div>
    </div>

    <!-- Products Section -->
    <section class="products-section">
        <div class="container">
            <div class="section-title">
                <h2>Your Listed Products</h2>
            </div>
            
            <!-- PHP Product Listing Integration -->
            <div class="products-grid">
                <?php if (!$current_user_id): ?>
                    <p class="empty-message">Please log in to view your products.</p>
                <?php elseif (empty($user_products)): ?>
                    <p class="empty-message">No products listed yet. Start by listing your first product!</p>
                <?php else: ?>
                    <?php foreach ($user_products as $product): ?>
                        <?php
                        // Determine badge status based on stock
                        $badge_class = "active";
                        $badge_text = "Active";
                        if ($product['stock'] <= 0) {
                            $badge_class = "sold";
                            $badge_text = "Sold Out";
                        } elseif ($product['stock'] < 5) {
                            $badge_class = "pending";
                            $badge_text = "Low Stock";
                        }
                        
                        // Format rating stars
                        $rating = isset($product['rating']) ? $product['rating'] : 4;
                        $full_stars = floor($rating);
                        $has_half_star = ($rating - $full_stars) >= 0.5;
                        $empty_stars = 5 - $full_stars - ($has_half_star ? 1 : 0);
                        
                        $stars_html = '';
                        for ($i = 0; $i < $full_stars; $i++) {
                            $stars_html .= '<i class="fas fa-star"></i>';
                        }
                        if ($has_half_star) {
                            $stars_html .= '<i class="fas fa-star-half-alt"></i>';
                        }
                        for ($i = 0; $i < $empty_stars; $i++) {
                            $stars_html .= '<i class="far fa-star"></i>';
                        }
                        ?>
                        <div class="product-card">
                            <div class="product-badge"><?php echo $badge_text; ?></div>
                            <div class="product-image">
                                <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                            </div>
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($product['product_name']); ?></h3>
                                <span class="product-price">$<?php echo number_format($product['price'], 2); ?></span>
                                <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                                <div class="product-meta">
                                    <div class="product-rating">
                                        <?php echo $stars_html; ?>
                                        (<?php echo isset($product['rating_count']) ? $product['rating_count'] : '0'; ?>)
                                    </div>
                                    <div class="product-actions">
                                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="action-btn">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="action-btn" onclick="return confirm('Are you sure you want to delete this product?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <p>© 2023 Seller Dashboard. All rights reserved.</p>
                <p>Designed with ❤️ for WeCommerce sellers</p>
            </div>
        </div>
    </footer>

    <script>
        // Simple hover effects
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-10px)';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = '';
            });
        });
        
        // Button animation
        document.querySelector('.btn')?.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px)';
        });
        
        document.querySelector('.btn')?.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>