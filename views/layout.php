<?php
function layout($title, $body) {
    $user = $_SESSION['user'] ?? null;
    $nav = $user
        ? htmlspecialchars($user['name'])."님 | <a href='index.php?page=cart'>장바구니</a> | <a href='index.php?page=orders'>주문내역</a> | <a href='index.php?page=logout'>로그아웃</a>"
        : "<a href='index.php?page=login'>로그인</a> | <a href='index.php?page=register'>회원가입</a>";
    echo "<!DOCTYPE html><html lang='ko'><head><link rel='stylesheet' href='public/style.css'><meta charset='UTF-8'><title>{$title} - SHOP</title></head><body>
    <a href='index.php'>SHOP</a> | {$nav}<hr>{$body}</body></html>";
}
