<?php
$db = new PDO('mysql:host=localhost;dbname=u67371', 'u67371', '3920651', array(PDO::ATTR_PERSISTENT => true));
$stmt = $db->prepare("SELECT * FROM admin WHERE id = ?");
$stmt -> execute([1]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (empty($_SERVER['PHP_AUTH_USER']) ||
    empty($_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] != 'login' ||
    $_SERVER['PHP_AUTH_PW']) != 'pass') {
  header('HTTP/1.1 401 Unanthorized');
  header('WWW-Authenticate: Basic realm="My site"');
  print('<h1>401 Требуется авторизация</h1>');
  exit();
}

// успешно авторизовались и видим защищенные паролем данные
$stmt = $db->prepare("SELECT * FROM application_languages WHERE id_lang = ?");
$stmt -> execute([1]);
$count1 = $stmt->rowCount();
$stmt = $db->prepare("SELECT * FROM application_languages WHERE id_lang = ?");
$stmt -> execute([2]);
$count2 = $stmt->rowCount();
$stmt = $db->prepare("SELECT * FROM application_languages WHERE id_lang = ?");
$stmt -> execute([3]);
$count3 = $stmt->rowCount();
$stmt = $db->prepare("SELECT * FROM application_languages WHERE id_lang = ?");
$stmt -> execute([4]);
$count4 = $stmt->rowCount();
$stmt = $db->prepare("SELECT * FROM application_languages WHERE id_lang = ?");
$stmt -> execute([5]);
$count5 = $stmt->rowCount();
$stmt = $db->prepare("SELECT * FROM application_languages WHERE id_lang = ?");
$stmt -> execute([6]);
$count6 = $stmt->rowCount();
$stmt = $db->prepare("SELECT * FROM application_languages WHERE id_lang = ?");
$stmt -> execute([7]);
$count7 = $stmt->rowCount();
$stmt = $db->prepare("SELECT * FROM application_languages WHERE id_lang = ?");
$stmt -> execute([8]);
$count8 = $stmt->rowCount();
$stmt = $db->prepare("SELECT * FROM application_languages WHERE id_lang = ?");
$stmt -> execute([9]);
$count9 = $stmt->rowCount();
$stmt = $db->prepare("SELECT * FROM application_languages WHERE id_lang = ?");
$stmt -> execute([10]);
$count10 = $stmt->rowCount();
$stmt = $db->prepare("SELECT * FROM application_languages WHERE id_lang = ?");
$stmt -> execute([11]);
$count11 = $stmt->rowCount();

$stmt = $db->query("SELECT max(id) FROM application");
$row = $stmt->fetch();
$count = (int) $row[0]; //Берем максимальный айди среди пользователей для заполнения списка пользователей

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
  if ($_POST['select_user'] == 0) { // Обработчик того был ли выбран пользователь
      header('Location: admin.php');
  }
  
  $user_id = (int) $_POST['select_user'];

  // Удаление всех выбранных им ЯП из таблицы application_languages
  $stmt = $db->prepare("DELETE FROM application_languages WHERE id_app = ?");
  $stmt -> execute([$user_id]);

  // Удаление выбранного пользователя
  $stmt = $db->prepare("DELETE FROM login_pass WHERE id = ?");
  $stmt -> execute([$user_id]);
  $stmt = $db->prepare("DELETE FROM application WHERE id = ?");
  $stmt -> execute([$user_id]);
  
  header('Location: admin.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit'])) {
  // Перезаписываем данные в БД новыми данными,
  // кроме логина и пароля.

  $user_id = (int) $_COOKIE['user_id'];
  
  // Обновление данных
  $stmt = $db->prepare("INSERT INTO application SET names = ?, phones = ?, email = ?, dates = ?, gender = ?, biography = ?");
  $stmt->execute([$_POST['name'], $_POST['phone'], $_POST['email'], $_POST['year'], $_POST['gender'], $_POST['bio']]);

  // Обновление данных в таблице ЯП
  $stmt = $db->prepare("DELETE FROM languages WHERE id = ?");
  $stmt -> execute([$user_id]);

  $language = $_POST['language'];

  foreach ($language as $item) {
    $stmt = $db->prepare("INSERT INTO application_languages SET id = ?, title = ?");
    $stmt->execute([$count, $item]);
  }

  header('Location: admin.php');  
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="admin.css">
  <title>Админка</title>
</head>
<body>
<div class="container">
  <h2>Панель администратора</h2>

  <h3>Статистика по языкам программирования:</h3>
  <p>Pascal: <?php print $count1 ?></p> <br>
  <p>C: <?php print $count2 ?></p> <br>
  <p>C++: <?php print $count3 ?></p> <br>
  <p>JavaScript: <?php print $count4 ?></p> <br>
  <p>PHP: <?php print $count5 ?></p> <br>
  <p>Python: <?php print $count6 ?></p> <br>
  <p>Java: <?php print $count7 ?></p> <br>
  <p>Haskel: <?php print $count8 ?></p> <br>
  <p>Clojure: <?php print $count9 ?></p> <br>
  <p>Prolog: <?php print $count10 ?></p> <br>
  <p>Scala: <?php print $count10 ?></p> <br>

  <h3>Выбери пользователя:</h3>
  <form action="" method="POST">
    <select name="select_user" class ="group list" id="selector_user">
      <option selected disabled value ="0">Выбрать пользователя</option>
      <?php
      for($index = 1; $index <= $count; $index++){ //Заполнение списка пользователями
        $stmt = $db->prepare("SELECT * FROM application WHERE id = ?");
        $stmt -> execute([$index]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user['id'] == $index){ //Проверка на существование пользователя с айди index
            print("<option value =" . $index . ">" . "id: ". $user['id'] . ", Имя: " . $user['names'] . "</option>"); //Добавление в список пользователя с существующим айди
        }
      }
      ?>
    </select><br> 
    <input name="delete" type="submit" class="send" value="УДАЛИТЬ ПОЛЬЗОВАТЕЛЯ" />
    <input name="editing" type="submit" class="send" value="РЕДАКТИРОВАТЬ ПОЛЬЗОВАТЕЛЯ" />
  </form>

  <?php

  if (isset($_POST['editing']) && $_SERVER['REQUEST_METHOD'] == 'POST'){
    if ($_POST['select_user'] == 0) { //Обработчик того был ли выбран пользователь
      header('Location: admin.php');
    }
    $user_id = (int) $_POST['select_user']; // получение айди выбраного пользователя
    setcookie('user_id', $user_id);
    // получаем данные пользователя из бд
    $values = array();
    $stmt = $db->prepare("SELECT * FROM application WHERE id = ?");
    $stmt -> execute([$user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $values['name'] = strip_tags($row['name']);
    $values['phone'] = strip_tags($row['phone']);
    $values['email'] = strip_tags($row['email']);
    $values['year'] = $row['year'];
    $values['gender'] = $row['gender'];
    $values['bio'] = strip_tags($row['bio']);
    $values['checkbox'] = true; 

    $stmt = $db->prepare("SELECT * FROM languages WHERE id = ?");
    $stmt -> execute([$user_id]);
    $language = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        array_push($language, strip_tags($row['title']));
    }
    $values['language'] = $language;
  ?>
  <br>

  <h3>Режим редактирования:</h3>
  <form action="" method="POST">
    Имя:<br><input type="text" name="name" <?php if ($errors['name']) {print 'class="group error"';} else print 'class="group"'; ?> value="<?php print $values['name']; ?>">
      <br>
      Телефон:<br><input type="tel" name="phone" <?php if ($errors['phone']) {print 'class="group error"';} else print 'class="group"'; ?> value="<?php print $values['phone']; ?>">
      <br>
      E-mail:<br><input type="text" name="email" <?php if ($errors['email']) {print 'class="group error';} else print 'class="group"'; ?> value="<?php print $values['email']; ?>">
      <br>
      <div class="form-group">
        <legend for="year"class="group" style="color: white;">Дата рождения:</legend>
        <input type="date" id="year" size="3" name="year" <?php if ($errors['year']) {print 'class="group error"';} else print 'class="group"';?> value="<?php print $values['year']; ?>">
      </div>
      <div <?php if ($errors['gender']) {print 'class="error"';} ?>>
        Пол:<br>
        <input class="radio" type="radio" name="gender" value="M" <?php if ($values['gender'] == 'M') {print 'checked';} ?>> Мужской
        <input class="radio" type="radio" name="gender" value="W" <?php if ($values['gender'] == 'W') {print 'checked';} ?>> Женский
      </div>
      Любимый язык программирования:<br>
      <select class="group" name="languages[]" size="11" multiple>
        <option value="Pascal" <?php if (in_array("Pascal", $values['language'])) {print 'selected';} ?>>Pascal</option>
        <option value="C" <?php if (in_array("C", $values['language'])) {print 'selected';} ?>>C</option>
        <option value="C_plus_plus" <?php if (in_array("C++", $values['language'])) {print 'selected';} ?>>C++</option>
        <option value="JavaScript" <?php if (in_array("JavaScript", $values['language'])) {print 'selected';} ?>>JavaScript</option>
        <option value="PHP" <?php if (in_array("PHP", $values['language'])) {print 'selected';} ?>>PHP</option>
        <option value="Python" <?php if (in_array("Python", $values['language'])) {print 'selected';} ?>>Python</option>
        <option value="Java" <?php if (in_array("Java", $values['language'])) {print 'selected';} ?>>Java</option>
        <option value="Haskel" <?php if (in_array("Haskel", $values['language'])) {print 'selected';} ?>>Haskel</option>
        <option value="Clojure" <?php if (in_array("Clojure", $values['language'])) {print 'selected';} ?>>Clojure</option>
        <option value="Prolog" <?php if (in_array("Prolog", $values['language'])) {print 'selected';} ?>>Prolog</option>
        <option value="Scala" <?php if (in_array("Scala", $values['language'])) {print 'selected';} ?>>Scala</option>
      </select>
      <br>
      Биография:<br><textarea class="group" name="bio" rows="3" cols="30"><?php print $values['bio']; ?></textarea>
      <div  <?php if ($errors['checkbox']) {print 'class="error"';} ?>>
        <input type="checkbox" name="checkbox" <?php if ($values['checkbox']) {print 'checked';} ?>> С контрактом ознакомлен(a) 
      </div>
      <input type="submit" id="send" value="ОТПРАВИТЬ">
    <input name="edit" type="submit" class="send" value="СОХРАНИТЬ ИЗМЕНЕНИЯ">
  </form>

  <?php
  }
  ?>
</div>
</body>
</html>
