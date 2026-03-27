<?php
class User {
    static function findByEmail($db, $email) {
        $s = $db->prepare('SELECT * FROM users WHERE e_mail = ?');
        $s->execute([$email]);
        return $s->fetch(PDO::FETCH_ASSOC);
    }
    static function create($db, $data) {
        $s = $db->prepare('INSERT INTO users (name, e_mail, phone, address, password) VALUES (?, ?, ?, ?, ?)');
        $s->execute([
            $data['name'],
            $data['e_mail'],
            $data['phone'],
            $data['address'],
            $data['password']
        ]);
        return $db->lastInsertId();
    }
}