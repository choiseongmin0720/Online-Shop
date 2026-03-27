<?php
class Admin {
    static function findByAccount($db, $account) {
        $s = $db->prepare('SELECT * FROM admin WHERE admin_account = ?');
        $s->execute([$account]);
        return $s->fetch(PDO::FETCH_ASSOC);
    }
    static function findAll($db) {
        return $db->query('SELECT admin_id, admin_account, role FROM admin')->fetchAll(PDO::FETCH_ASSOC);
    }
    static function create($db, $account, $password, $role) {
        $db->prepare('INSERT INTO admin (admin_account, password, role) VALUES (?, ?, ?)')->execute([$account, $password, $role]);
    }
    static function delete($db, $id) {
        $db->prepare('DELETE FROM admin WHERE admin_id = ?')->execute([$id]);
    }
}
