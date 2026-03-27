<?php
class Cart {
    static function findByUser($db, $uid) {
        $s = $db->prepare('SELECT c.*, p.name, p.price FROM cart c JOIN product p ON c.product_id = p.product_id WHERE c.user_id = ?');
        $s->execute([$uid]);
        return $s->fetchAll(PDO::FETCH_ASSOC);
    }
    static function addItem($db, $uid, $pid, $cnt) {
        $s = $db->prepare('SELECT * FROM cart WHERE user_id = ? AND product_id = ?');
        $s->execute([$uid, $pid]);
        $ex = $s->fetch(PDO::FETCH_ASSOC);
        if ($ex) $db->prepare('UPDATE cart SET count = count + ? WHERE cart_id = ?')->execute([$cnt, $ex['cart_id']]);
        else $db->prepare('INSERT INTO cart (user_id, product_id, count) VALUES (?, ?, ?)')->execute([$uid, $pid, $cnt]);
    }
    static function updateCount($db, $cid, $cnt) {
        $db->prepare('UPDATE cart SET count = ? WHERE cart_id = ?')->execute([$cnt, $cid]);
    }
    static function remove($db, $cid) {
        $db->prepare('DELETE FROM cart WHERE cart_id = ?')->execute([$cid]);
    }
    static function clear($db, $uid) {
        $db->prepare('DELETE FROM cart WHERE user_id = ?')->execute([$uid]);
    }
}
