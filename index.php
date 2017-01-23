<?php
session_start ();
require ("./db.php");
if (isset ($_POST["auth-subm"])) {
    $authErr = false;
    $authErr1 = false;
    if (isset ($_POST["auth-login"]) && isset ($_POST["auth-pass"])) {
        $login = $db->real_escape_string ($_POST["auth-login"]);
        $pass = $db->real_escape_string ($_POST["auth-pass"]);
        $q = $db->query ("SELECT * FROM `users` WHERE `login` = '$login' AND `pass` = '".md5(md5($pass))."'") or die ($db->error);
        if ($q->num_rows) {
            $user = $q->fetch_assoc ();
            $_SESSION["id"] = $user["id"];
            header ("Location: /");
        } else {
            $authErr1 = true;
        }
    } else {
        $authErr = true;
    }
}
if (isset ($_POST["add-value"])) {
    $db->query ("UPDATE `users` SET `value` = `value` + 1 WHERE `id` = '{$_SESSION["id"]}'");
}
if (isset ($_POST["logout"])) {
    unset ($_SESSION["id"]);
    header ("Loaction: /");
}
if (isset ($_POST["reg-subm"])) {
    $loginErr = false;
    $loginEmpty = false;
    $passEmpty = false;
    $repassErr = false;
    $repassEmpty = false;
    $dateYong = false;
    $dateOld = false;
    $dateEmpty = false;
    $registered = false;
    $regErr = false;
    
    if (isset ($_POST["reg-login"]) && strlen ($_POST["reg-login"]) > 3) {
        $login = $db->real_escape_string ($_POST["reg-login"]);
        $q = $db->query ("SELECT * FROM `users` WHERE `login` = '$login'");
        if ($q->num_rows) {
            $loginErr = true;
        }
    } else {
        $loginEmpty = true;
    }
    if (isset ($_POST["reg-pass"]) && strlen ($_POST["reg-pass"]) > 3) {
        $pass = $db->real_escape_string ($_POST["reg-pass"]);
    } else {
        $passEmpty = true;
    }
    if (isset ($_POST["reg-repass"]) && strlen ($_POST["reg-repass"]) > 0) {
        $repass = $db->real_escape_string ($_POST["reg-repass"]);
        if ($repass == $pass) {
        } else {
            $repassErr = true;
        }
    } else {
        $repassEmpty = true;
    }
    echo strlen ($_POST["reg-date"]);
    if (isset ($_POST["reg-date"]) && strlen ($_POST["reg-date"]) > 5) {
        $dateText = $db->real_escape_string ($_POST["reg-date"]);
        $date = strtotime ($dateText);
        if ($date + 31556926 * 5 - time () > 0) {
            $dateYong = true;
        } else {
            if (time () - $date >= 31556926 * 150) {
                $dateOld = true;
            }
        }
    } else {
        $dateEmpty = true;
    }
    if (!$loginErr && !$loginEmpty && !$passEmpty && !$repassErr && !$repassEmpty && !$dateYong && !$dateOld && !$dateEmpty) {
        if ($db->query ("INSERT INTO `users` VALUES (NULL, '$login', '".md5(md5($pass))."', '$date', '0')")) {
            $_SESSION["id"] = $db->insert_id;
            header ("Location: /");
        } else {
            $regErr = true;
        }
    } 
}

?>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="./styles.css">
        <title>SurikatProject</title>
    </head>
    <body>
        <div class="header"><b>СурикатПроджект!</b> Специально для <a href="https://php.ru/forum/threads/kak-bystro-osvoit-php.52331/#post-419213">php.ru</a> =)</div>
<?php
if (isset ($_SESSION["id"])) {
    $q = $db->query ("SELECT * FROM `users` WHERE `id` = '{$_SESSION["id"]}' LIMIT 1");
    $user = $q->fetch_assoc ();
?>
        <div class="work">
            <form action="" method="post">
                <h1 id="user-value"><?=$user["value"]?></h1>
                <input type="submit" name="add-value" value="+1" class="button">
            </form>
            <form action="" method="post">
                <input type="submit" name="logout" value="Выход" class="button">
            </form>
        </div>
<?php
} else {
    if (isset ($_GET["register"])) {
?>
        <div class="register">
            <form action="" method="post">
<?php
    if (isset ($loginEmpty) && $loginEmpty == true) {
        echo '<div class="alert alert-danger">Логин не должен быть короче 4-х символов!</div>';
    } else if (isset ($loginErr) && $loginErr == true) {
        echo '<div class="alert alert-danger">Логин занят!</div>';
    }
    if (isset ($passEmpty) && $passEmpty == true) {
        echo '<div class="alert alert-danger">Пароль не может быть короче 4-х символов!</div>';
    }
    if (isset ($repassEmpty) && $repassEmpty == true) {
        echo '<div class="alert alert-danger">Вы не повторили пароль!</div>';
    } else if (isset ($repassErr) && $repassErr == true) {
        echo '<div class="alert alert-danger">Пароли не совпадают!</div>';
    }
    if (isset ($dateEmpty) && $dateEmpty == true) {
        echo '<div class="alert alert-danger">Вы не указали дату</div>';
    } else if (isset ($dateYong) && $dateYong == true) {
        echo '<div class="alert alert-danger">Вам нет 5-ти лет!</div>';
    } else if (isset ($dateOld) && $dateOld == true) {
        echo '<div class="alert alert-danger">Вам не может быть больше 150-ти лет!</div>';
    }
    if (isset ($regErr) && $regErr == true) {
        echo '<div class="alert alert-danger">Ошибка записи в БД! Обратитесь к администратору</div>';
    }
?>
                <label>Введите логин: <br><input type="text" placeholder="Login..." name="reg-login"></label><br>
                <label>Введите пароль: <br><input type="password" placeholder="Password..." name="reg-pass"></label><br>
                <label>Повторите пароль: <br><input type="password" placeholder="Password..." name="reg-repass"></label><br>
                <label>Дата рождения: <br><input type="date" name="reg-date"></label><br>
                <br><input type="submit" name="reg-subm" value="Зарегистрироваться" class="button"><a href="/" class="button">Вернуться</a>
            </form>
        </div>
<?php
    } else {
?>
        <div class="auth">
<?php
        if (isset ($authErr) && $authErr == true) {
            echo '<div class="alert alert-danger">Вы не ввели логин, или пароль!</div>';
        } else if (isset ($authErr1) && $authErr1 == true) {
            echo '<div class="alert alert-danger">Логин, или пароль введён неверно!</div>';
        }
?>
            <form action="" method="post">
                <label>Введите логин: <br><input type="text" placeholder="Login..." name="auth-login"></label><br>
                <label>Введите пароль: <br><input type="password" placeholder="Password..." name="auth-pass"></label><br>
                <br><input type="submit" name="auth-subm" value="Войти" class="button"><a href="/?register" class="button">Регистрация</a>
            </form>
        </div>
<?php
    }
}
?>
    </body>
</html>
