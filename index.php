<?php
session_start();

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/views/layout.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Admin.php';
require_once __DIR__ . '/models/Category.php';
require_once __DIR__ . '/models/Product.php';
require_once __DIR__ . '/models/Cart.php';
require_once __DIR__ . '/models/Order.php';
require_once __DIR__ . '/models/Review.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/ProductController.php';
require_once __DIR__ . '/controllers/CartController.php';
require_once __DIR__ . '/controllers/OrderController.php';
require_once __DIR__ . '/controllers/ReviewController.php';
require_once __DIR__ . '/controllers/AdminController.php';

function auth() {
    if (empty($_SESSION['user'])) {
        header('Location: index.php?page=login&redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

$page = $_GET['page'] ?? 'home';
$id   = $_GET['id']   ?? null;

match($page) {
    // 사용자
    'home'          => ProductController::index($db),
    'product'       => ProductController::show($db, $id),
    'cart'          => CartController::index($db),
    'cart_add'      => CartController::add($db),
    'cart_update'   => CartController::update($db),
    'cart_remove'   => CartController::remove($db),
    'orders'        => OrderController::index($db),
    'order_detail'  => OrderController::show($db, $id),
    'order_create'  => OrderController::create($db),
    'order_confirm' => OrderController::confirm($db, $id),
    'review_add'    => ReviewController::create($db),
    'login'         => AuthController::login($db),
    'register'      => AuthController::register($db),
    'logout'        => (session_destroy() && header('Location: index.php')) ? null : null,

    // 관리자
    'admin'                 => AdminController::dashboard($db),
    'admin_products'        => AdminController::products($db),
    'admin_product_new'     => AdminController::productForm($db),
    'admin_product_edit'    => AdminController::productForm($db, $id),
    'admin_product_create'  => AdminController::productCreate($db),
    'admin_product_update'  => AdminController::productUpdate($db, $id),
    'admin_product_delete'  => AdminController::productDelete($db),
    'admin_categories'      => AdminController::categories($db),
    'admin_category_create' => AdminController::categoryCreate($db),
    'admin_category_delete' => AdminController::categoryDelete($db),
    'admin_orders'          => AdminController::orders($db),
    'admin_order_status'    => AdminController::orderStatus($db),
    'admin_users'           => AdminController::users($db),
    'admin_accounts'        => AdminController::accounts($db),
    'admin_account_create'  => AdminController::accountCreate($db),
    'admin_account_delete'  => AdminController::accountDelete($db),

    default => ProductController::index($db),
};
