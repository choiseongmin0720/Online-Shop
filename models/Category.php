<?php
class Category {
    static function topLevel($db) {
        return $db->query('SELECT * FROM category WHERE parent_id IS NULL ORDER BY category_id')->fetchAll(PDO::FETCH_ASSOC);
    }
    static function findById($db, $id) {
        $s = $db->prepare('SELECT * FROM category WHERE category_id = ?');
        $s->execute([$id]);
        return $s->fetch(PDO::FETCH_ASSOC);
    }
}
