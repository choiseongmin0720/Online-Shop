<?php
function adminAuth() {
    if (empty($_SESSION['admin'])) {
        header('Location: index.php?page=login');
        exit;
    }
}

function adminNav() {
    $account = $_SESSION['admin']['account'];
    $role    = $_SESSION['admin']['role'];
    return "<a href='index.php?page=admin'>대시보드</a> | 
            <a href='index.php?page=admin_products'>상품관리</a> | 
            <a href='index.php?page=admin_categories'>카테고리</a> | 
            <a href='index.php?page=admin_orders'>주문관리</a> | 
            <a href='index.php?page=admin_users'>회원목록</a> | 
            " . ($role === 'superadmin' ? "<a href='index.php?page=admin_accounts'>관리자계정</a> | " : '') . "
            {$account}({$role}) | <a href='index.php?page=logout'>로그아웃</a>";
}

function adminLayout($title, $body) {
    $nav = adminNav();
    echo "<!DOCTYPE html><html lang='ko'><head><meta charset='UTF-8'><title>{$title} - 관리자</title><link rel='stylesheet' href='public/style.css'></head><body>
        {$nav}<hr>{$body}</body></html>";
}

class AdminController {

    // 대시보드
    static function dashboard($db) {
        adminAuth();
        $productCount  = $db->query('SELECT COUNT(*) FROM product')->fetchColumn();
        $orderCount    = $db->query('SELECT COUNT(*) FROM orders')->fetchColumn();
        $userCount     = $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
        $recentOrders  = $db->query('SELECT o.*, u.name user_name FROM orders o JOIN users u ON o.user_id=u.user_id ORDER BY created_at DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
        $statusLabel   = ['pending'=>'결제완료','shipped'=>'배송중','delivered'=>'배송완료','confirmed'=>'구매확정'];
        $rows = '';
        foreach ($recentOrders as $o) $rows .= "<tr><td>#{$o['order_id']}</td><td>{$o['user_name']}</td><td>{$statusLabel[$o['status']]}</td><td>{$o['created_at']}</td></tr>";
        adminLayout('대시보드', "<h2>대시보드</h2>
            <p>상품: {$productCount} | 주문: {$orderCount} | 회원: {$userCount}</p><hr>
            <h3>최근 주문</h3>
            <table border='1'><tr><th>주문번호</th><th>회원</th><th>상태</th><th>주문일</th></tr>{$rows}</table>");
    }

    // 상품 목록
    static function products($db) {
        adminAuth();
        $products = Product::findAll($db);
        $rows = '';
        foreach ($products as $p) {
            $rows .= "<tr><td>{$p['product_id']}</td><td>{$p['name']}</td><td>" . number_format($p['price']) . "원</td><td>{$p['count']}</td><td>{$p['category_name']}</td>
                <td><a href='index.php?page=admin_product_edit&id={$p['product_id']}'>[수정]</a>
                <form method='POST' action='index.php?page=admin_product_delete' style='display:inline'>
                    <input type='hidden' name='id' value='{$p['product_id']}'>
                    <button type='submit' onclick=\"return confirm('삭제?')\">삭제</button>
                </form></td></tr>";
        }
        adminLayout('상품관리', "<h2>상품관리</h2>
            <a href='index.php?page=admin_product_new'>[새 상품 등록]</a><br><br>
            <table border='1'><tr><th>ID</th><th>상품명</th><th>가격</th><th>재고</th><th>카테고리</th><th></th></tr>{$rows}</table>");
    }

    // 상품 등록/수정 폼
    static function productForm($db, $id = null) {
        adminAuth();
        $product = $id ? Product::findById($db, $id) : null;
        // 하위 카테고리만
        $all = $db->query('SELECT * FROM category ORDER BY category_id')->fetchAll(PDO::FETCH_ASSOC);
        $parentIds = array_column(array_filter($all, fn($c) => $c['parent_id']), 'parent_id');
        $leaves = array_filter($all, fn($c) => $c['parent_id'] && !in_array($c['category_id'], $parentIds));
        $options = '';
        foreach ($leaves as $c) {
            $sel = $product && $product['category_id'] == $c['category_id'] ? 'selected' : '';
            $options .= "<option value='{$c['category_id']}' {$sel}>{$c['name']}</option>";
        }
        $action = $id ? "index.php?page=admin_product_update&id={$id}" : 'index.php?page=admin_product_create';
        $v = $product ?? ['name'=>'','description'=>'','price'=>'','count'=>''];
        adminLayout($id ? '상품 수정' : '상품 등록', "<h2>" . ($id ? '상품 수정' : '상품 등록') . "</h2>
            <form method='POST' action='{$action}'>
                상품명: <input type='text' name='name' value='{$v['name']}' required><br>
                설명: <textarea name='description'>{$v['description']}</textarea><br>
                가격: <input type='number' name='price' value='{$v['price']}' required><br>
                재고: <input type='number' name='count' value='{$v['count']}' required><br>
                카테고리: <select name='category_id'>{$options}</select><br>
                <button type='submit'>저장</button>
            </form>");
    }

    static function productCreate($db) {
        adminAuth();
        $db->prepare('INSERT INTO product (category_id, name, description, price, count) VALUES (?, ?, ?, ?, ?)')->execute([$_POST['category_id'], $_POST['name'], $_POST['description'], $_POST['price'], $_POST['count']]);
        header('Location: index.php?page=admin_products'); exit;
    }

    static function productUpdate($db, $id) {
        adminAuth();
        $db->prepare('UPDATE product SET category_id=?, name=?, description=?, price=?, count=? WHERE product_id=?')->execute([$_POST['category_id'], $_POST['name'], $_POST['description'], $_POST['price'], $_POST['count'], $id]);
        header('Location: index.php?page=admin_products'); exit;
    }

    static function productDelete($db) {
        adminAuth();
        $db->prepare('DELETE FROM product WHERE product_id = ?')->execute([$_POST['id']]);
        header('Location: index.php?page=admin_products'); exit;
    }

    // 카테고리
    static function categories($db) {
        adminAuth();
        $all = $db->query('SELECT c.*, pc.name parent_name FROM category c LEFT JOIN category pc ON c.parent_id = pc.category_id ORDER BY c.category_id')->fetchAll(PDO::FETCH_ASSOC);
        $rows = '';
        foreach ($all as $c) {
            $rows .= "<tr><td>{$c['category_id']}</td><td>{$c['name']}</td><td>" . ($c['parent_name'] ?? '-') . "</td>
                <td><form method='POST' action='index.php?page=admin_category_delete' style='display:inline'>
                    <input type='hidden' name='id' value='{$c['category_id']}'>
                    <button type='submit' onclick=\"return confirm('삭제?')\">삭제</button>
                </form></td></tr>";
        }
        $options = "<option value=''>-- 최상위 --</option>";
        foreach ($all as $c) $options .= "<option value='{$c['category_id']}'>{$c['name']}</option>";
        adminLayout('카테고리', "<h2>카테고리</h2>
            <table border='1'><tr><th>ID</th><th>이름</th><th>상위</th><th></th></tr>{$rows}</table><br>
            <h3>카테고리 등록</h3>
            <form method='POST' action='index.php?page=admin_category_create'>
                이름: <input type='text' name='name' required>
                상위: <select name='parent_id'>{$options}</select>
                <button type='submit'>등록</button>
            </form>");
    }

    static function categoryCreate($db) {
        adminAuth();
        $db->prepare('INSERT INTO category (name, parent_id) VALUES (?, ?)')->execute([$_POST['name'], $_POST['parent_id'] ?: null]);
        header('Location: index.php?page=admin_categories'); exit;
    }

    static function categoryDelete($db) {
        adminAuth();
        $db->prepare('DELETE FROM category WHERE category_id = ?')->execute([$_POST['id']]);
        header('Location: index.php?page=admin_categories'); exit;
    }

    // 주문 관리
    static function orders($db) {
        adminAuth();
        $orders = $db->query('SELECT o.*, u.name user_name FROM orders o JOIN users u ON o.user_id=u.user_id ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
        $statusLabel = ['pending'=>'결제완료','shipped'=>'배송중','delivered'=>'배송완료','confirmed'=>'구매확정'];
        $rows = '';
        foreach ($orders as $o) {
            $options = '';
            foreach ($statusLabel as $k => $v) {
                $sel = $o['status'] === $k ? 'selected' : '';
                $options .= "<option value='{$k}' {$sel}>{$v}</option>";
            }
            $rows .= "<tr><td>#{$o['order_id']}</td><td>{$o['user_name']}</td><td>{$o['created_at']}</td>
                <td><form method='POST' action='index.php?page=admin_order_status' style='display:inline'>
                    <input type='hidden' name='id' value='{$o['order_id']}'>
                    <select name='status'>{$options}</select>
                    <button type='submit'>변경</button>
                </form></td></tr>";
        }
        adminLayout('주문관리', "<h2>주문관리</h2>
            <table border='1'><tr><th>주문번호</th><th>회원</th><th>주문일</th><th>상태</th></tr>{$rows}</table>");
    }

    static function orderStatus($db) {
        adminAuth();
        $db->prepare('UPDATE orders SET status = ? WHERE order_id = ?')->execute([$_POST['status'], $_POST['id']]);
        header('Location: index.php?page=admin_orders'); exit;
    }

    // 회원 목록
    static function users($db) {
        adminAuth();
        $users = $db->query('SELECT * FROM users ORDER BY user_id')->fetchAll(PDO::FETCH_ASSOC);
        $rows = '';
        foreach ($users as $u) $rows .= "<tr><td>{$u['user_id']}</td><td>{$u['name']}</td><td>{$u['e_mail']}</td><td>{$u['password']}</td><td>{$u['phone']}</td><td>{$u['address']}</td></tr>";
        adminLayout('회원목록', "<h2>회원목록</h2>
            <table border='1'><tr><th>ID</th><th>이름</th><th>이메일</th><th>비밀번호</th><th>전화</th><th>주소</th></tr>{$rows}</table>");
    }

    // 관리자 계정 (superadmin만)
    static function accounts($db) {
        adminAuth();
        if ($_SESSION['admin']['role'] !== 'superadmin') { echo '권한 없음'; exit; }
        $admins = Admin::findAll($db);
        $rows = '';
        foreach ($admins as $a) {
            $rows .= "<tr><td>{$a['admin_id']}</td><td>{$a['admin_account']}</td><td>{$a['role']}</td>
                <td><form method='POST' action='index.php?page=admin_account_delete' style='display:inline'>
                    <input type='hidden' name='id' value='{$a['admin_id']}'>
                    <button type='submit' onclick=\"return confirm('삭제?')\">삭제</button>
                </form></td></tr>";
        }
        adminLayout('관리자계정', "<h2>관리자 계정</h2>
            <table border='1'><tr><th>ID</th><th>계정</th><th>권한</th><th></th></tr>{$rows}</table><br>
            <h3>계정 추가</h3>
            <form method='POST' action='index.php?page=admin_account_create'>
                계정: <input type='text' name='admin_account' required>
                비밀번호: <input type='text' name='password' required>
                권한: <select name='role'><option value='editor'>editor</option><option value='superadmin'>superadmin</option></select>
                <button type='submit'>추가</button>
            </form>");
    }

    static function accountCreate($db) {
        adminAuth();
        if ($_SESSION['admin']['role'] !== 'superadmin') { echo '권한 없음'; exit; }
        Admin::create($db, $_POST['admin_account'], $_POST['password'], $_POST['role']);
        header('Location: index.php?page=admin_accounts'); exit;
    }

    static function accountDelete($db) {
        adminAuth();
        if ($_SESSION['admin']['role'] !== 'superadmin') { echo '권한 없음'; exit; }
        if ($_POST['id'] == $_SESSION['admin']['admin_id']) { echo '자신의 계정은 삭제할 수 없습니다.'; exit; }
        Admin::delete($db, $_POST['id']);
        header('Location: index.php?page=admin_accounts'); exit;
    }
}
