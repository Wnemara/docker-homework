<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Docker Compose Training</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .database-container { display: flex; gap: 20px; }
        .database { flex: 1; border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Данные из контейнеров MySQL и PostgreSQL</h1>
    
    <div class="database-container">
        <div class="database">
            <h2>MySQL Database</h2>
            <?php
            $mysql_conn = new mysqli('db', 'user', 'test', 'myDb');
            
            if ($mysql_conn->connect_error) {
                echo '<p class="error">MySQL Connection Error: ' . $mysql_conn->connect_error . '</p>';
                echo '<p>Проверьте:';
                echo '<ul>';
                echo '<li>Запущен ли контейнер MySQL</li>';
                echo '<li>Правильность логина/пароля</li>';
                echo '<li>docker-compose logs db</li>';
                echo '</ul></p>';
            } else {
                $result = $mysql_conn->query("SHOW TABLES");
                echo '<p>Доступные таблицы: ' . $result->num_rows . '</p>';
                
                if ($result->num_rows > 0) {
                    echo '<table>';
                    echo '<tr><th>Таблица</th></tr>';
                    while ($row = $result->fetch_array()) {
                        echo '<tr><td>' . $row[0] . '</td></tr>';
                    }
                    echo '</table>';

                    if ($mysql_conn->query("SELECT 1 FROM users LIMIT 1")) {
                        $users = $mysql_conn->query("SELECT * FROM users");
                        echo '<h3>Данные из таблицы users:</h3>';
                        echo '<table>';
                        echo '<tr><th>ID</th><th>Имя</th><th>Email</th></tr>';
                        while ($user = $users->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($user['id']) . '</td>';
                            echo '<td>' . htmlspecialchars($user['username'] ?? '') . '</td>';
                            echo '<td>' . htmlspecialchars($user['email'] ?? '') . '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                    }
                }
                $mysql_conn->close();
            }
            ?>
        </div>

        <div class="database">
            <h2>PostgreSQL Database</h2>
            <?php
            $pg_conn = pg_connect("host=postgres dbname=my_pg_db user=pguser password=pgpassword");
            
            if (!$pg_conn) {
                echo '<p class="error">PostgreSQL Connection Error: ' . pg_last_error() . '</p>';
                echo '<p>Проверьте:';
                echo '<ul>';
                echo '<li>Запущен ли контейнер PostgreSQL</li>';
                echo '<li>Правильность логина/пароля</li>';
                echo '<li>docker-compose logs postgres</li>';
                echo '</ul></p>';
            } else {
                $result = pg_query($pg_conn, "SELECT table_name FROM information_schema.tables WHERE table_schema='public'");
                $num_tables = pg_num_rows($result);
                echo '<p>Доступные таблицы: ' . $num_tables . '</p>';
                
                if ($num_tables > 0) {
                    echo '<table>';
                    echo '<tr><th>Таблица</th></tr>';
                    while ($row = pg_fetch_array($result)) {
                        echo '<tr><td>' . htmlspecialchars($row['table_name']) . '</td></tr>';
                    }
                    echo '</table>';
                    
                    if (pg_query($pg_conn, "SELECT 1 FROM customers LIMIT 1")) {
                        $customers = pg_query($pg_conn, "SELECT * FROM customers");
                        echo '<h3>Данные из таблицы customers:</h3>';
                        echo '<table>';
                        echo '<tr><th>ID</th><th>Имя</th><th>Email</th></tr>';
                        while ($customer = pg_fetch_assoc($customers)) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($customer['id']) . '</td>';
                            echo '<td>' . htmlspecialchars($customer['name'] ?? '') . '</td>';
                            echo '<td>' . htmlspecialchars($customer['email'] ?? '') . '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                    }
                }
                pg_close($pg_conn);
            }
            ?>
        </div>
    </div>

    <div style="margin-top: 30px;">
        <h3>Доступ к админ-панелям:</h3>
        <ul>
            <li><a href="http://localhost:8000" target="_blank">phpMyAdmin (MySQL)</a></li>
            <li><a href="http://localhost:8002" target="_blank">pgAdmin (PostgreSQL)</a></li>
        </ul>
    </div>
</body>
</html> 