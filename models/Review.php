<?php
class Review {
    static function findByProduct($db, $pid) {
        $s = $db->prepare('SELECT r.*, u.name user_name FROM review r JOIN users u ON r.user_id = u.user_id WHERE r.product_id = ? ORDER BY r.created_at DESC');
        $s->execute([$pid]);
        return $s->fetchAll(PDO::FETCH_ASSOC);
    }
    static function canReview($db, $uid, $pid) {
        $s = $db->prepare("SELECT order_id FROM orders WHERE user_id = ? AND status = 'confirmed' AND JSON_CONTAINS(product, JSON_OBJECT('product_id', ?))");
        $s->execute([$uid, $pid]);
        return (bool)$s->fetch();
    }
    static function alreadyReviewed($db, $uid, $pid) {
        $s = $db->prepare('SELECT review_id FROM review WHERE user_id = ? AND product_id = ?');
        $s->execute([$uid, $pid]);
        return (bool)$s->fetch();
    }
    static function create($db, $data) {
        $db->prepare('INSERT INTO review (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)')->execute([$data['user_id'], $data['product_id'], $data['rating'], $data['comment']]);
    }
}
