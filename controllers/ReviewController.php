<?php
class ReviewController {
    static function create($db) {
        auth();
        $pid = $_POST['product_id'];
        $uid = $_SESSION['user']['user_id'];
        if (Review::canReview($db, $uid, $pid) && !Review::alreadyReviewed($db, $uid, $pid))
            Review::create($db, ['user_id'=>$uid, 'product_id'=>$pid, 'rating'=>$_POST['rating'], 'comment'=>$_POST['comment']]);
        header("Location: index.php?page=product&id={$pid}"); exit;
    }
}
