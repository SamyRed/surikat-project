<?php
session_start ();
require ("./db.php");
if (isset ($_POST["auth-subm"])) {
	$errors = [];
    $authErr = false;
    $authErr1 = false;
    if (isset ($_POST["auth-login"]) && !empty ($_POST["auth-login"]) && isset ($_POST["auth-pass"]) && !empty ($_POST["auth-pass"])) {
        $login = $db->real_escape_string ($_POST["auth-login"]);
        $pass = $_POST["auth-pass"];
        if ($q = $db->query ("SELECT * FROM `users` WHERE `login` = '$login'")) {
		if ($q->num_rows) {
			$user = $q->fetch_assoc ();
			if (password_verify ($pass, $user["pass"])) {
				$_SESSION["id"] = $user["id"];
				header ("Location: /");
			} else {
				$errors[] = '<div class="alert alert-danger">Логин, или пароль неправильный!</div>';
			}
		} else {
			$errors[] = '<div class="alert alert-danger">Логин, или пароль неправильный!</div>';
		}
		} else {
			$errors[] = "<div class="alert alert-danger">Ошибка записи в БД! Обратитесь к администратору.</div>";
		}
    } else {
        $errors[] = '<div class="alert alert-danger">Вы не ввели логин, или пароль!</div>';
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
	$errors;
    if (isset ($_POST["reg-login"]) && strlen ($_POST["reg-login"]) > 3) {
        $login = $db->real_escape_string ($_POST["reg-login"]);
        $q = $db->query ("SELECT * FROM `users` WHERE `login` = '$login'");
        if ($q->num_rows) {
            $errors[] = '<div class="alert alert-danger">Логин занят!</div>';
        }
    } else {
        $errors[] = '<div class="alert alert-danger">Логин не должен быть короче 4-х символов!</div>';
    }
    if (isset ($_POST["reg-pass"]) && strlen ($_POST["reg-pass"]) > 3) {
        $pass = $db->real_escape_string ($_POST["reg-pass"]);
    } else {
        $errors[] = '<div class="alert alert-danger">Пароль не может быть короче 4-х символов!</div>';
    }
    if (isset ($_POST["reg-repass"]) && strlen ($_POST["reg-repass"]) > 0) {
        $repass = $db->real_escape_string ($_POST["reg-repass"]);
        if ($repass == $pass) {
        } else {
            $errors[] = '<div class="alert alert-danger">Пароли не совпадают!</div>';
        }
    } else {
        $errors[] = '<div class="alert alert-danger">Вы не повторили пароль!</div>';
    }
    if (isset ($_POST["reg-year"]) && isset ($_POST["reg-mounth"]) && isset ($_POST["reg-day"])) {
        $dateText = $db->real_escape_string ($_POST["reg-year"]."-".$_POST["reg-mounth"]."-".$_POST["reg-day"]);
        $date = strtotime ($dateText);
        if ($date + 31556926 * 5 - time () > 0) {
            $errors[] = '<div class="alert alert-danger">Too young!!</div>';
        } else {
            if (time () - $date >= 31556926 * 150) {
                $errors[] = '<div class="alert alert-danger">Too old!</div>';
            }
        }
    } else {
        $errors[] = '<div class="alert alert-danger">Вы не указали дату</div>';
    }
    if (empty ($errors)) {
        if ($db->query ("INSERT INTO `users` VALUES (NULL, '$login', '".password_hash($pass, PASSWORD_DEFAULT)."', '$date', '0')")) {
            $_SESSION["id"] = $db->insert_id;
            header ("Location: /");
        } else {
            $errors[] = '<div class="alert alert-danger">Ошибка записи в БД! Обратитесь к администратору</div>';
        }
    }
}
?>
<!DOCKTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="./styles.css">
        <title>SurikatProject</title>
    </head>
    <body>
        <header><b>СурикатПроджект!</b> Специально для <a href="https://php.ru/forum/threads/kak-bystro-osvoit-php.52331/#post-419213">php.ru</a> =)</header>
		<table class="records">
			<tr><td class="head" colspan="2">Таблица рекордов</td></tr>
			<tr class="title"><td>Пользователь</td><td>Значение</td></tr>
<?php
	$q = $db->query ("SELECT login, value FROM `users` ORDER BY `value` DESC");
	while ($user = $q->fetch_assoc())	{
?>
			<tr><td><?=$user["login"];?></td><td><?=$user["value"];?></td></tr>
<?php
	}
?>
		</table>
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
    foreach ($errors as $error) {
		echo $error;
	}
?>
                <label>Введите логин: <br><input type="text" placeholder="Login..." name="reg-login"></label><br>
                <label>Введите пароль: <br><input type="password" placeholder="Password..." name="reg-pass"></label><br>
                <label>Повторите пароль: <br><input type="password" placeholder="Password..." name="reg-repass"></label><br>
                <label>Дата рождения: <br></label><br>
				<label>
					<select name="reg-day">
<?php
	for ($i = 1; $i <= 31; $i++) {
		echo '<option value="'.$i.'">'.$i.'</option>';
	}
?>
					</select>
					<select name="reg-mounth">
						<option value="01">Январь</option>
						<option value="02">Февраль</option>
						<option value="03">Март</option>
						<option value="04">Апрель</option>
						<option value="05">Май</option>
						<option value="06">Июнь</option>
						<option value="07">Июль</option>
						<option value="08">Август</option>
						<option value="09">Сентябрь</option>
						<option value="10">Октябрь</option>
						<option value="11">Ноябрь</option>
						<option value="12">Декабрь</option>
					</select>
					<select name="reg-year">
<?php
	for ($i = date ("Y"); $i >= 1800; $i--) {
		echo '<option value="'.$i.'">'.$i.'</option>';
	}
?>
					</select>
				</label>
                <br><input type="submit" name="reg-subm" value="Зарегистрироваться" class="button"><a href="/" class="button">Вернуться</a>
            </form>
        </div>
<?php
    } else {
?>
        <div class="auth">
<?php
    foreach ($errors as $error) {
		echo $error;
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
