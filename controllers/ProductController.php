<?php
class ProductController {
    static function index($db) {
        $catId = $_GET['category_id'] ?? null;
        $tops     = Category::topLevel($db);
        $products = Product::findAll($db, $catId);
        $tabs = '<a href="index.php">전체</a>';
        foreach ($tops as $c) $tabs .= " | <a href='index.php?category_id={$c['category_id']}'>{$c['name']}</a>";
        $list = '';
        foreach ($products as $p) {
            $stock = $p['count'] > 0 ? "재고 {$p['count']}" : '품절';
            $list .= "<li><a href='index.php?page=product&id={$p['product_id']}'>{$p['name']}</a> - " . number_format($p['price']) . "원 ({$stock})</li>";
        }
        layout('상품 목록', "<p>{$tabs}</p><hr><ul>{$list}</ul>");
    }

    static function show($db, $id) {
        $product = Product::findById($db, $id);
        if (!$product) { echo '상품 없음'; exit; }
        $reviews = Review::findByProduct($db, $id);
        $uid = $_SESSION['user']['user_id'] ?? null;
        $can     = $uid ? Review::canReview($db, $uid, $id) : false;
        $already = $uid ? Review::alreadyReviewed($db, $uid, $id) : false;
        $cat = ($product['parent_name'] ? $product['parent_name'].' > ' : '') . $product['category_name'];

        $cartForm = '';
        if ($product['count'] > 0 && $uid) {
            $cartForm = "<form method='POST' action='index.php?page=cart_add'>
                <input type='hidden' name='product_id' value='{$id}'>
                수량: <input type='number' name='count' value='1' min='1' max='{$product['count']}' style='width:50px'>
                <button type='submit'>장바구니 담기</button></form>";
        } elseif (!$uid) {
            $cartForm = "<a href='index.php?page=login'>로그인 후 구매</a>";
        }

        $reviewForm = '';
        if ($can && !$already) {
            $reviewForm = "<form method='POST' action='index.php?page=review_add'>
                <input type='hidden' name='product_id' value='{$id}'>
                평점: <select name='rating'><option>5</option><option>4</option><option>3</option><option>2</option><option>1</option></select><br>
                내용: <textarea name='comment' required></textarea><br>
                <button type='submit'>등록</button></form>";
        }

        $reviewList = '';
        foreach ($reviews as $r) $reviewList .= "<li>{$r['user_name']} - {$r['rating']}점: " . htmlspecialchars($r['comment']) . "</li>";

        layout($product['name'], "<h2>{$product['name']}</h2>
            <p>카테고리: {$cat}</p>
            <p>가격: " . number_format($product['price']) . "원</p>
            <p>설명: " . htmlspecialchars($product['description']) . "</p>
            <p>재고: " . ($product['count'] > 0 ? $product['count'].'개' : '품절') . "</p>
            {$cartForm}<hr>
            <h3>리뷰 (" . count($reviews) . ")</h3>
            {$reviewForm}<ul>{$reviewList}</ul>");
    }
}
