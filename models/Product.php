<?php
class Product {
    static function findAll($db, $catId = null) {
        $sql = 'SELECT p.*, c.name category_name, pc.name parent_name
                FROM product p
                JOIN category c ON p.category_id = c.category_id
                LEFT JOIN category pc ON c.parent_id = pc.category_id';
        if ($catId) {
            $s = $db->prepare($sql . ' WHERE p.category_id = ? OR c.parent_id = ?');
            $s->execute([$catId, $catId]);
        } else {
            $s = $db->query($sql);
        }
        return $s->fetchAll(PDO::FETCH_ASSOC);
    }
    static function findById($db, $id) {
        $s = $db->prepare('SELECT p.*, c.name category_name, pc.name parent_name
                           FROM product p
                           JOIN category c ON p.category_id = c.category_id
                           LEFT JOIN category pc ON c.parent_id = pc.category_id
                           WHERE p.product_id = ?');
        $s->execute([$id]);
        return $s->fetch(PDO::FETCH_ASSOC);
    }
    static function decreaseStock($db, $id, $qty) {
        $db->prepare('UPDATE product SET count = count - ? WHERE product_id = ? AND count >= ?')->execute([$qty, $id, $qty]);
    }
}
