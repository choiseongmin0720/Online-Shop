<?php
class CartController {
    static function index($db) {
        auth();
        $uid   = $_SESSION['user']['user_id'];
        $items = Cart::findByUser($db, $uid);
        $total = array_sum(array_map(fn($i) => $i['price'] * $i['count'], $items));
        $list  = '';
        foreach ($items as $i) {
            $list .= "<li>{$i['name']} - " . number_format($i['price']) . "원 x
                <form method='POST' action='index.php?page=cart_update' style='display:inline'>
                    <input type='hidden' name='cart_id' value='{$i['cart_id']}'>
                    <input type='number' name='count' value='{$i['count']}' min='1' style='width:50px'>
                    <button type='submit'>변경</button>
                </form> = " . number_format($i['price'] * $i['count']) . "원
                <form method='POST' action='index.php?page=cart_remove' style='display:inline'>
                    <input type='hidden' name='cart_id' value='{$i['cart_id']}'>
                    <button type='submit'>삭제</button>
                </form></li>";
        }
        $body = "<h2>장바구니</h2>";
        $body .= $items
            ? "<ul>{$list}</ul><p>합계: " . number_format($total) . "원</p><form method='POST' action='index.php?page=order_create'><button type='submit'>주문하기</button></form>"
            : "<p>장바구니가 비어있습니다.</p>";
        layout('장바구니', $body);
    }

    static function add($db) {
        auth();
        Cart::addItem($db, $_SESSION['user']['user_id'], $_POST['product_id'], (int)$_POST['count']);
        header('Location: index.php?page=cart'); exit;
    }

    static function update($db) {
        auth();
        Cart::updateCount($db, $_POST['cart_id'], (int)$_POST['count']);
        header('Location: index.php?page=cart'); exit;
    }

    static function remove($db) {
        auth();
        Cart::remove($db, $_POST['cart_id']);
        header('Location: index.php?page=cart'); exit;
    }
}
