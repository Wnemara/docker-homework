<?php
$mysql_host = 'db';
$mysql_user = 'user';
$mysql_pass = 'test';
$mysql_db = 'myDb';

$pg_host = 'postgres';
$pg_user = 'pguser';
$pg_pass = 'pgpassword';
$pg_db = 'my_pg_db';

function check_mysql_connection($host, $user, $pass, $db) {
    $conn = @new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        return [
            'status' => false,
            'error' => "MySQL Error: " . $conn->connect_error
        ];
    }
    return [
        'status' => true,
        'connection' => $conn
    ];
}

function check_pg_connection($host, $user, $pass, $db) {
    $conn_string = "host=$host dbname=$db user=$user password=$pass";
    $conn = @pg_connect($conn_string);
    if (!$conn) {
        return [
            'status' => false,
            'error' => "PostgreSQL Error: " . pg_last_error()
        ];
    }
    return [
        'status' => true,
        'connection' => $conn
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Docker DB Connections</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .db-panel { 
            border: 1px solid #ddd; 
            padding: 15px; 
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .success { background-color: #e8f5e9; }
        .error { background-color: #ffebee; }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Database Connections Status</h1>

    <div class="db-panel <?php echo $mysql['status'] ? 'success' : 'error'; ?>">
        <h2>MySQL Connection</h2>
        <?php
        $mysql = check_mysql_connection($mysql_host, $mysql_user, $mysql_pass, $mysql_db);
        if ($mysql['status']) {
            echo "<p>✅ Successfully connected to MySQL!</p>";

            $tables = $mysql['connection']->query("SHOW TABLES");
            echo "<h3>Tables in myDb:</h3>";
            
            if ($tables->num_rows > 0) {
                echo "<table><tr><th>Table Name</th></tr>";
                while ($row = $tables->fetch_array()) {
                    echo "<tr><td>" . $row[0] . "</td></tr>";
                }
                echo "</table>";
                if ($result = $mysql['connection']->query("SELECT * FROM users LIMIT 5")) {
                    echo "<h3>Sample data from users:</h3>";
                    echo "<table><tr><th>ID</th><th>Username</th><th>Email</th></tr>";
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['id'] ?? '') . "</td>";
                        echo "<td>" . htmlspecialchars($row['username'] ?? '') . "</td>";
                        echo "<td>" . htmlspecialchars($row['email'] ?? '') . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }
            } else {
                echo "<p>No tables found in the database.</p>";
            }
            
            $mysql['connection']->close();
        } else {
            echo "<p>❌ " . $mysql['error'] . "</p>";
            echo "<p><strong>Troubleshooting:</strong></p>";
            echo "<ul>";
            echo "<li>Check if MySQL container is running</li>";
            echo "<li>Verify credentials in docker-compose.yml</li>";
            echo "<li>Try connecting manually: <code>docker-compose exec db mysql -uuser -ptest myDb</code></li>";
            echo "</ul>";
        }
        ?>
    </div>

    <div class="db-panel <?php echo $pg['status'] ? 'success' : 'error'; ?>">
        <h2>PostgreSQL Connection</h2>
        <?php
        $pg = check_pg_connection($pg_host, $pg_user, $pg_pass, $pg_db);
        if ($pg['status']) {
            echo "<p>✅ Successfully connected to PostgreSQL!</p>";

            $tables = pg_query($pg['connection'], 
                "SELECT table_name FROM information_schema.tables WHERE table_schema='public'");
            $num_tables = pg_num_rows($tables);
            echo "<h3>Tables in my_pg_db:</h3>";
            
            if ($num_tables > 0) {
                echo "<table><tr><th>Table Name</th></tr>";
                while ($row = pg_fetch_array($tables)) {
                    echo "<tr><td>" . $row['table_name'] . "</td></tr>";
                }
                echo "</table>";

                if ($result = pg_query($pg['connection'], "SELECT * FROM customers LIMIT 5")) {
                    echo "<h3>Sample data from customers:</h3>";
                    echo "<table><tr><th>ID</th><th>Name</th><th>Email</th></tr>";
                    while ($row = pg_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['id'] ?? '') . "</td>";
                        echo "<td>" . htmlspecialchars($row['name'] ?? '') . "</td>";
                        echo "<td>" . htmlspecialchars($row['email'] ?? '') . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }
            } else {
                echo "<p>No tables found in the database.</p>";
            }
            
            pg_close($pg['connection']);
        } else {
            echo "<p>❌ " . $pg['error'] . "</p>";
            echo "<p><strong>Troubleshooting:</strong></p>";
            echo "<ul>";
            echo "<li>Check if PostgreSQL container is running</li>";
            echo "<li>Verify credentials in docker-compose.yml</li>";
            echo "<li>Try connecting manually: <code>docker-compose exec postgres psql -U pguser -d my_pg_db</code></li>";
            echo "</ul>";
        }
        ?>
    </div>

    <div class="db-panel">
        <h2>Admin Links</h2>
        <ul>
            <li><a href="http://localhost:8000" target="_blank">phpMyAdmin (MySQL)</a></li>
            <li><a href="http://localhost:8002" target="_blank">pgAdmin (PostgreSQL)</a></li>
        </ul>
    </div>
</body>
</html>