<?php
class AuthController {
    static function login($db) {
        $err = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $account  = $_POST['account'] ?? '';
            $password = $_POST['password'] ?? '';

            // 관리자 로그인
            $admin = Admin::findByAccount($db, $account);
            if ($admin && $admin['password'] === $password) {
                $_SESSION['admin'] = ['admin_id' => $admin['admin_id'], 'account' => $admin['admin_account'], 'role' => $admin['role']];
                header('Location: index.php?page=admin'); exit;
            }

            // 회원 로그인 (이메일 + 비밀번호)
            $user = User::findByEmail($db, $account);
            if ($user && $user['password'] === $password) {
                $_SESSION['user'] = ['user_id' => $user['user_id'], 'name' => $user['name']];
                header('Location: ' . ($_POST['redirect'] ?? 'index.php')); exit;
            }

            $err = '이메일 또는 비밀번호가 올바르지 않습니다.';
        }
        $redirect = htmlspecialchars($_GET['redirect'] ?? 'index.php');
        echo "<!DOCTYPE html><html lang='ko'><head><meta charset='UTF-8'><title>로그인</title></head><body>
            <h2>로그인</h2>" . ($err ? "<p style='color:red'>{$err}</p>" : '') . "
            <form method='POST'>
                <input type='hidden' name='redirect' value='{$redirect}'>
                이메일 (관리자는 계정ID): <input type='text' name='account' required><br>
                비밀번호: <input type='password' name='password' required><br>
                <button type='submit'>로그인</button>
            </form>
            <p><a href='index.php?page=register'>회원가입</a></p>
        </body></html>";
    }

    static function register($db) {
        $err = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (User::findByEmail($db, $_POST['e_mail'])) {
                $err = '이미 사용 중인 이메일입니다.';
            } elseif ($_POST['password'] !== $_POST['password_confirm']) {
                $err = '비밀번호가 일치하지 않습니다.';
            } else {
                $id = User::create($db, $_POST);
                $_SESSION['user'] = ['user_id' => $id, 'name' => $_POST['name']];
                header('Location: index.php'); exit;
            }
        }
        echo "<!DOCTYPE html><html lang='ko'><head><meta charset='UTF-8'><title>회원가입</title></head><body>
            <h2>회원가입</h2>" . ($err ? "<p style='color:red'>{$err}</p>" : '') . "
            <form method='POST'>
                이름: <input type='text' name='name' required><br>
                이메일: <input type='email' name='e_mail' required><br>
                비밀번호: <input type='password' name='password' required><br>
                비밀번호 확인: <input type='password' name='password_confirm' required><br>
                전화번호: <input type='text' name='phone' required><br>
                주소: <input type='text' name='address' required><br>
                <button type='submit'>가입하기</button>
            </form>
            <p><a href='index.php?page=login'>로그인</a></p>
        </body></html>";
    }

    static function logout() {
        session_destroy();
        header('Location: index.php'); exit;
    }
}