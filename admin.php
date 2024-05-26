

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
    // <link rel="stylesheet" href="style.css">
</head>
<body>
<?php

// Проверка HTTP-авторизации
if (empty($_SERVER['PHP_AUTH_USER']) ||
    empty($_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] != 'admin' ||
    $_SERVER['PHP_AUTH_PW'] != 'pass') {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="My site"');
    print('<h1>401 Требуется авторизация</h1>');
    exit();
}

// Подключение к базе данных и выполнение запросов
$db = new PDO('mysql:host=localhost;dbname=u67371', 'u67371', '3920651', array(PDO::ATTR_PERSISTENT => true));

// Извлечение данных пользователей
$stmt = $db->query("SELECT * FROM application");
$usersData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Вывод данных в виде таблицы
echo '<table border="1">';
echo '<tr><th>Имя</th><th>Телефон</th><th>Email</th><th>Год рождения</th><th>Пол</th><th>Биография</th><th>Языки программирования</th><th>Действия</th></tr>';
foreach ($usersData as $userData) {
    // Вывод данных пользователя в ячейки таблицы
    echo '<tr>';
    echo '<td>' . $userData['names'] . '</td>';
    echo '<td>' . $userData['phones'] . '</td>';
    echo '<td>' . $userData['email'] . '</td>';
    echo '<td>' . $userData['dates'] . '</td>';
    echo '<td>' . $userData['gender'] . '</td>';
    echo '<td>' . $userData['biography'] . '</td>';

    // Извлечение языков программирования для данного пользователя
    $stmt = $db->prepare("SELECT id_lang FROM application_languages WHERE id = ?");
    $stmt->execute([$userData['id']]);
    $userLanguages = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo '<td>' . implode(', ', $userLanguages) . '</td>';

    // Действия: редактирование и удаление
    echo '<td><a href="edit_user.php?id=' . $userData['id'] . '">Редактировать</a> | <form action="delete_user.php" method="post"><input type="hidden" name="id" value="' . $userData['id'] . '"><input type="submit" value="Удалить"></form></td>';
    echo '</tr>';
}
echo '</table>';

// Вывод статистики по языкам программирования
$stmt = $db->query("SELECT id_lang, COUNT(*) AS count FROM application_languages GROUP BY id_lang");
$languagesStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo '<h2>Статистика по языкам программирования</h2>';
echo '<ul>';
foreach ($languagesStats as $languageStat) {
    echo '<li>' . $languageStat['id_lang'] . ': ' . $languageStat['count'] . ' пользователей</li>';
}
echo '</ul>';

?>
