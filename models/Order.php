<?php
class Order {
    static function findByUser($db, $uid) {
        $s = $db->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
        $s->execute([$uid]);
        $rows = $s->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) $r['product'] = json_decode($r['product'], true);
        return $rows;
    }
    static function findById($db, $id) {
        $s = $db->prepare('SELECT o.*, u.address, u.phone FROM orders o JOIN users u ON o.user_id = u.user_id WHERE o.order_id = ?');
        $s->execute([$id]);
        $row = $s->fetch(PDO::FETCH_ASSOC);
        if ($row) $row['product'] = json_decode($row['product'], true);
        return $row;
    }
    static function create($db, $uid, $items) {
        $product = json_encode(array_map(fn($i) => [
            'product_id' => $i['product_id'], 'name' => $i['name'],
            'price' => (float)$i['price'], 'count' => $i['count'],
        ], $items));
        $s = $db->prepare('INSERT INTO orders (user_id, product, status) VALUES (?, ?, "pending")');
        $s->execute([$uid, $product]);
        return $db->lastInsertId();
    }
    static function updateStatus($db, $id, $status) {
        $db->prepare('UPDATE orders SET status = ? WHERE order_id = ?')->execute([$status, $id]);
    }
}
