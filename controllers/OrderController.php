<?php
$statusLabel = ['pending'=>'결제완료','shipped'=>'배송중','delivered'=>'배송완료','confirmed'=>'구매확정'];

class OrderController {
    static function index($db) {
        auth();
        global $statusLabel;
        $orders = Order::findByUser($db, $_SESSION['user']['user_id']);
        $list = '';
        foreach ($orders as $o) {
            $t     = array_sum(array_map(fn($p) => $p['price'] * $p['count'], $o['product']));
            $items = implode(', ', array_map(fn($p) => $p['name'].' x'.$p['count'], $o['product']));
            $list .= "<li>#{$o['order_id']} | {$items} | " . number_format($t) . "원 | {$statusLabel[$o['status']]} | {$o['created_at']} <a href='index.php?page=order_detail&id={$o['order_id']}'>[상세]</a></li>";
        }
        layout('주문내역', "<h2>주문내역</h2>" . ($list ? "<ul>{$list}</ul>" : "<p>주문 내역이 없습니다.</p>"));
    }

    static function show($db, $id) {
        auth();
        global $statusLabel;
        $order = Order::findById($db, $id);
        if (!$order || $order['user_id'] != $_SESSION['user']['user_id']) { echo '접근 불가'; exit; }
        $list = '';
        foreach ($order['product'] as $p) $list .= "<li><a href='index.php?page=product&id={$p['product_id']}'>{$p['name']}</a> x{$p['count']} - " . number_format($p['price'] * $p['count']) . "원</li>";
        $confirmBtn = $order['status'] === 'delivered'
            ? "<form method='POST' action='index.php?page=order_confirm&id={$id}'><button type='submit'>구매확정</button></form>" : '';
        layout("주문 #{$id}", "<h2>주문 상세 #{$order['order_id']}</h2>
            <p>상태: {$statusLabel[$order['status']]}</p>
            <p>배송지: {$order['address']}</p>
            <p>연락처: {$order['phone']}</p>
            <ul>{$list}</ul>{$confirmBtn}");
    }

    static function create($db) {
        auth();
        $uid   = $_SESSION['user']['user_id'];
        $items = Cart::findByUser($db, $uid);
        if (!$items) { header('Location: index.php?page=cart'); exit; }
        $oid = Order::create($db, $uid, $items);
        foreach ($items as $i) Product::decreaseStock($db, $i['product_id'], $i['count']);
        Cart::clear($db, $uid);
        header("Location: index.php?page=order_detail&id={$oid}"); exit;
    }

    static function confirm($db, $id) {
        auth();
        $order = Order::findById($db, $id);
        if ($order && $order['user_id'] == $_SESSION['user']['user_id'] && $order['status'] === 'delivered')
            Order::updateStatus($db, $id, 'confirmed');
        header("Location: index.php?page=order_detail&id={$id}"); exit;
    }
}
