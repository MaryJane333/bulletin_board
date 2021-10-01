<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
    </head>
    <body>
        
        <?php
        // DB接続
        $dsn = 'データベース名';
        $user = 'ユーザー名';
        $password = 'パスワード';
        $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
        
        // DB作成
        $sql = "CREATE TABLE IF NOT EXISTS tbartist"
        ." ("
        . "id INT AUTO_INCREMENT PRIMARY KEY,"
        . "name char(32),"
        . "artist TEXT,"
        . "password TEXT"
        .");";
        $stmt = $pdo->query($sql);
        
        // データ編集or新規投稿
        if (isset($_POST["submit"]) &&
            !empty($_POST["artist"]) &&
            !empty($_POST["name"])  &&
            !empty($_POST["password"]) &&
            $_SERVER["REQUEST_METHOD"] == "POST") {
            $name = $_POST["name"];
            $artist = $_POST["artist"];
            $password = $_POST["password"];
            // データ編集
            if (!empty($_POST["edit-num-hidden"])) {
                $id = $_POST["edit-num-hidden"];
                $sql = 'UPDATE tbartist SET name=:name,artist=:artist,password=:password WHERE id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':artist', $artist, PDO::PARAM_STR);
                $stmt->bindParam(':password', $password, PDO::PARAM_STR);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
            } else { // 新規投稿
                $sql = $pdo -> prepare("INSERT INTO tbartist (name, artist, password) VALUES (:name, :artist, :password)");
                $sql -> bindParam(':name', $name, PDO::PARAM_STR);
                $sql -> bindParam(':artist', $artist, PDO::PARAM_STR);
                $sql -> bindParam(':password', $password, PDO::PARAM_STR);
                $sql -> execute();
            }
        header("Location: " . $_SERVER["SCRIPT_NAME"]);
        exit;
        }
        
        // データ削除
        if (isset($_POST["delete-num"]) && !empty($_POST["delete-num"])) {
            $id = $_POST["delete-num"];
            $delete_password = $_POST["delete-password"];
            // パスワード取得
            $sql = 'select * from tbartist where id=:id';
            $stmt = $pdo->prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備し、
            $stmt->bindParam(':id', $id, PDO::PARAM_INT); // ←その差し替えるパラメータの値を指定してから、
            $stmt->execute();                             // ←SQLを実行する。
            $results = $stmt->fetchAll();
            foreach ($results as $row) {
                $password_check = $row['password'];
            }
            
            if ($password_check == $delete_password) {
                $sql = 'delete from tbartist where id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
            }
        }
        
        // データ編集(元の投稿を表示）
        $edit_name = "";
        $edit_artist = "";
        $edit_num = "";
        if (isset($_POST["edit-num"]) && !empty($_POST["edit-num"])) {
            $edit_num = $_POST["edit-num"];
            $edit_password = $_POST["edit-password"];
            
            $sql = 'select * from tbartist where id=:id';
            $stmt = $pdo->prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備し、
            $stmt->bindParam(':id', $edit_num, PDO::PARAM_INT); // ←その差し替えるパラメータの値を指定してから、
            $stmt->execute();                             // ←SQLを実行する。
            $results = $stmt->fetchAll();
            foreach ($results as $row) {
                $password_check = $row['password'];
            }
            if ($edit_password == $password_check) {
                foreach ($results as $row) {
                    $edit_name = $row['name'];
                    $edit_artist = $row['artist'];
                }
            } else {
                $edit_num = ""; // パスワードが違ったらedit-num-hiddenを空にする
            }
        }
        ?>
        
        <h1>好きなアーティストをコメントしてください！</h1>
        <form action="" method="post">
            <input type="text" name="name" placeholder="名前" value="<?php echo $edit_name; ?>">
            <input type="text" name="artist" placeholder="アーティスト名" value="<?php echo $edit_artist; ?>">
            <input type="text" name="password" placeholder="パスワード">
            <button type="submit" name="submit">登録</button><br><br>
            
            <input type="text" name="delete-num" placeholder="削除対象番号">
            <input type="text" name="delete-password" placeholder="パスワード">
            <button type="submit" name="delete">削除</button><br><br>
            
            <input type="text" name="edit-num" placeholder="編集対象番号">
            <input type="text" name="edit-password" placeholder="パスワード">
            <button type="submit" name="edit">編集</button><br><br>
            
            <input type="hidden" name="edit-num-hidden" value="<?php echo $edit_num; ?>">
        </form>
        
        <?php
        // データ表示
        $sql = 'SELECT * FROM tbartist';
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        foreach ($results as $row) {
            echo $row['id'].'. ';
            echo $row['name'].' は ';
            echo $row['artist'].'が好きです！';
        echo "<hr>";
        }
        ?>
    </body>
</html>