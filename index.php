<?php
/**
 * Реализовать возможность входа с паролем и логином с использованием
 * сессии для изменения отправленных данных в предыдущей задаче,
 * пароль и логин генерируются автоматически при первоначальной отправке формы.
 */

// Отправляем браузеру правильную кодировку,
// файл index.php должен быть в кодировке UTF-8 без BOM.
header('Content-Type: text/html; charset=UTF-8');

// В суперглобальном массиве $_SERVER PHP сохраняет некторые заголовки запроса HTTP
// и другие сведения о клиненте и сервере, например метод текущего запроса $_SERVER['REQUEST_METHOD'].
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Массив для временного хранения сообщений пользователю.
    $messages = array();
    // В суперглобальном массиве $_COOKIE PHP хранит все имена и значения куки текущего запроса.
    // Выдаем сообщение об успешном сохранении.
    if (!empty($_COOKIE['save'])) {
        // Удаляем куку, указывая время устаревания в прошлом.
        setcookie('save', '', 100000);
        setcookie('login', '', 100000);
        setcookie('pass', '', 100000);
        // Выводим сообщение пользователю.
        $messages[] = 'Спасибо, результаты сохранены. ';
        // Если в куках есть пароль, то выводим сообщение.
        if (!empty($_COOKIE['pass'])) {
            $messages[] = sprintf('Вы можете <a href="login.php">войти</a> с логином <strong>%s</strong>
        и паролем <strong>%s</strong> для изменения данных.',
                strip_tags($_COOKIE['login']),
                strip_tags($_COOKIE['pass']));
        }
    }

    // Складываем признак ошибок в массив.
    $errors = array();
    $errors['names'] = !empty($_COOKIE['name_error']);
    $errors['phone'] = !empty($_COOKIE['phone_error']);
    $errors['email'] = !empty($_COOKIE['email_error']);
    $errors['dates'] = !empty($_COOKIE['data_error']);
    $errors['gender'] = !empty($_COOKIE['gender_error']);
    $errors['agree'] = !empty($_COOKIE['agree_error']);

    // Выдаем сообщения об ошибках.
    if ($errors['names']) {
        // Удаляем куку, указывая время устаревания в прошлом.
        setcookie('names_error', '', 100000);
        // Выводим сообщение.
        $messages[] = '<div>Заполните имя.</div>';
    }
    if ($errors['phone']) {
        setcookie('phone_error', '', 100000);
        $messages[] = '<div>Некорректный телефон.</div>';
    }
    if ($errors['email']) {
        setcookie('email_error', '', 100000);
        $messages[] = '<div>Некорректный email.</div>';
    }
    if ($errors['dates']) {
        setcookie('data_error', '', 100000);
        $messages[] = '<div>Выберите год рождения.</div>';
    }
    if ($errors['gender']) {
        setcookie('gender_error', '', 100000);
        $messages[] = '<div>Выберите пол.</div>';
    }
    if ($errors['agree']) {
        setcookie('agree_error', '', 100000);
        $messages[] = '<div>Поставьте галочку.</div>';
    }

    // Складываем предыдущие значения полей в массив, если есть.
    // При этом санитизуем все данные для безопасного отображения в браузере.
    $values = array();
$values['names'] = isset($_COOKIE['names_value']) ? strip_tags($_COOKIE['names_value']) : '';
$values['phone'] = isset($_COOKIE['phone_value']) ? strip_tags($_COOKIE['phone_value']) : '';
$values['email'] = isset($_COOKIE['email_value']) ? strip_tags($_COOKIE['email_value']) : '';
$values['dates'] = isset($_COOKIE['data_value']) ? $_COOKIE['data_value'] : '';
$values['gender'] = isset($_COOKIE['gender_value']) ? $_COOKIE['gender_value'] : '';
$values['biography'] = isset($_COOKIE['biography_value']) ? strip_tags($_COOKIE['biography_value']) : '';
$values['agree'] = isset($_COOKIE['agree_value']) ? $_COOKIE['agree_value'] : ''; 
if (empty($_COOKIE['language_value'])) {
        $values['language'] = array();
    } else {
        $values['language'] = json_decode($_COOKIE['language_value'], true);  
    }

    // Убедимся, что переменная $language определена.
    $language = isset($language) ? $language : array();

    // Если нет предыдущих ошибок ввода, есть кука сессии, начали сессию и
    // ранее в сессию записан факт успешного логина.
    if (!empty($_SESSION['login'])) {
    printf('Вход с логином %s, uid %d', $_SESSION['login'], $_SESSION['uid']);
}
    session_start();
    if (!empty($_COOKIE[session_name()]) && !empty($_SESSION['login'])) {
        // загрузить данные пользователя из БД
        // и заполнить переменную $values,
        // предварительно санитизовав.
        $db = new PDO('mysql:host=localhost;dbname=u67371', 'u67371', '3920651', array(PDO::ATTR_PERSISTENT => true));
        
        $stmt = $db->prepare("SELECT * FROM application WHERE id = ?");
        $stmt->execute([$_SESSION['uid']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $values['names'] = strip_tags($row['names']);
        $values['phone'] = isset($_COOKIE['phone']) ? strip_tags($_COOKIE['phone']) : '';
        $values['email'] = strip_tags($row['email']);
        $values['dates'] = isset($_COOKIE['dates']) ? $_COOKIE['dates'] : '';
        $values['gender'] = $row['gender'];
        $values['biography'] = strip_tags($row['biography']);
        $values['agree'] = true; 

        $stmt = $db->prepare("SELECT * FROM languages WHERE id = ?");
        $stmt->execute([$_SESSION['uid']]);
        $ability = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $values['language'] = isset($_COOKIE['language_value']) ? json_decode($_COOKIE['language_value'], true) : array();
        }
        $values['language'] = $language;
        
        printf('Вход с логином %s, uid %d', $_SESSION['login'], $_SESSION['uid']);
    }

    // Включаем содержимое файла form.php.
    // В нем будут доступны переменные $messages, $errors и $values для вывода 
    // сообщений, полей с ранее заполненными данными и признаками ошибок.
    include('form.php');
}
// Иначе, если запрос был методом POST, т.е. нужно проверить данные и сохранить их в XML-файл.
else {
    // Проверяем ошибки.
    $errors = FALSE;
    if (empty(htmlentities($_POST['names']))) {
        setcookie('names_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        setcookie('names_value', $_POST['names'], time() + 12 * 30 * 24 * 60 * 60);
    }
    if (!preg_match('/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/', $_POST['phone'])) {
        setcookie('phone_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        setcookie('phone_value', $_POST['phone'], time() + 30 * 24 * 60 * 60);
    }
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        setcookie('email_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        setcookie('email_value', $_POST['email'], time() + 12 * 30 * 24 * 60 * 60);
    }
    if (empty($_POST['dates'])) {
        setcookie('data_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        setcookie('data_value', $_POST['dates'], time() + 12 * 30 * 24 * 60 * 60);
    }
    if (empty($_POST['gender'])) {
        setcookie('gender_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        setcookie('gender_value', $_POST['gender'], time() + 12 * 30 * 24 * 60 * 60);
    }
    if (empty($_POST['agree'])) {
        setcookie('agree_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        setcookie('agree_value', $_POST['agree'], time() + 12 * 30 * 24 * 60 * 60);
    }
    if (isset($_POST['language'])) {
        $language = $_POST['language'];
    } else {
        $language = array(); 
    }
    if (!empty($_POST['biography'])) {
        setcookie ('biography_value', $_POST['biography'], time() + 12 * 30 * 24 * 60 * 60);
    }
    if (!empty($_POST['language'])) {
        $json = json_encode($_POST['language']);
        setcookie ('language_value', $json, time() + 12 * 30 * 24 * 60 * 60);
    }

    if ($errors) {
        // При наличии ошибок перезагружаем страницу и завершаем работу скрипта.
        header('Location: index.php');
        exit();
    } else {
        // Удаляем Cookies с признаками ошибок.
        setcookie('names_error', '', 100000);
        setcookie('phone_error', '', 100000);
        setcookie('email_error', '', 100000);
        setcookie('data_error', '', 100000);
        setcookie('gender_error', '', 100000);
        setcookie('agree_error', '', 100000);
    }

    // Проверяем меняются ли ранее сохраненные данные или отправляются новые.
    if (!empty($_COOKIE[session_name()]) &&
        session_start() && !empty($_SESSION['login'])) {
        // Перезаписываем данные в БД новыми данными,
        // кроме логина и пароля.
        $db = new PDO('mysql:host=localhost;dbname=u67371', 'u67371', '3920651', array(PDO::ATTR_PERSISTENT => true));
        
        $stmt = $db->prepare("UPDATE application SET names = ?, phones = ?, email = ?, dates = ?, gender = ?, biography = ? WHERE id = ?");
        $stmt->execute([$_POST['names'], $_POST['phone'], $_POST['email'], $_POST['dates'], $_POST['gender'], $_POST['biography'], $_SESSION['uid']]);

        $stmt = $db->prepare("DELETE FROM languages WHERE id = ?");
        $stmt->execute([$_SESSION['uid']]);

        $ability = $_POST['language'];

        foreach ($language as $item) {
            $stmt = $db->prepare("INSERT INTO application_languages SET id = ?, name_of_language = ?");
            $stmt->execute([$_SESSION['uid'], $item]);
        }
    } else {
        // Генерируем уникальный логин и пароль.
        $chars="qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
        $max=rand(8,16);
        $size=StrLen($chars)-1;
        $pass=null;
        while($max--)
            $pass.=$chars[rand(0,$size)];
        $login = $chars[rand(0,25)] . strval(time());
        // Сохраняем в Cookies.
        setcookie('login', $login);
        setcookie('pass', $pass);

        // Сохранение данных формы, логина и хеш md5() пароля в базу данных.
        $db = new PDO('mysql:host=localhost;dbname=u67371', 'u67371', '3920651', array(PDO::ATTR_PERSISTENT => true));

        $stmt = $db->prepare("INSERT INTO application SET names = ?, phones = ?, email = ?, dates = ?, gender = ?, biography = ?");
        $stmt->execute([$_POST['names'], $_POST['phone'], $_POST['email'], $_POST['dates'], $_POST['gender'], $_POST['biography']]);
        
        $res = $db->query("SELECT max(id) FROM application");
        $row = $res->fetch();
        $count = (int) $row[0];

        $ability = $_POST['language'];

        foreach ($language as $item) {
            $stmt = $db->prepare("INSERT INTO application_languages SET id = ?, name_of_language = ?");
            $stmt->execute([$count, $item]);
        }

        // Запись в таблицу login_pass
        $stmt = $db->prepare("INSERT INTO login_pass SET id = ?, login = ?, pass = ?");
        $stmt->execute([$count, $login, md5($pass)]);
    }

    // Сохраняем куку с признаком успешного сохранения.
    setcookie('save', '1');

    // Делаем перенаправление.
    header('Location: ./');
}
?>
