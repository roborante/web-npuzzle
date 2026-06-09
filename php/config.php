<?php

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) 
{
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) 
    {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) 
        {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

define('DB_HOST'        ,getenv('DB_HOST'        ) ?: 'localhost'    );
define('DB_USER'        ,getenv('DB_USER'        ) ?: 'root'         );
define('DB_PASS'        ,getenv('DB_PASS'        ) ?: 'abcdef_123456'); // fake password
define('DB_LOG_NAME'    ,getenv('DB_LOG_NAME'    ) ?: 'nPuzzle_logs' );
define('DB_PUZZLE_NAME' ,getenv('DB_PUZZLE_NAME' ) ?: 'slidingPuzzle');
define('UPLOAD_DIR'     ,getenv('UPLOAD_DIR'     ) ?: __DIR__ . '/../pictures/');
define('UPLOAD_MAX_SIZE',getenv('UPLOAD_MAX_SIZE') ?: 3000000);

function updateLoginData( ) 
{
    if     (isset($_SERVER['HTTP_CLIENT_IP'      ])) $ip = $_SERVER['HTTP_CLIENT_IP'      ];
	else if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	else if(isset($_SERVER['HTTP_X_FORWARDED'    ])) $ip = $_SERVER['HTTP_X_FORWARDED'    ];
	else if(isset($_SERVER['HTTP_FORWARDED'      ])) $ip = $_SERVER['HTTP_FORWARDED'      ];
	else                                             $ip = $_SERVER['REMOTE_ADDR'         ];    
	// Validate IP format
	if (!filter_var($ip, FILTER_VALIDATE_IP)) {
		$ip = '0.0.0.0';
	}

	static $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    if ($conn->connect_error) 
    {
        die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
    }
    $conn->set_charset("utf8mb4");
    $conn->query("CREATE DATABASE IF NOT EXISTS `" . DB_LOG_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->select_db(DB_LOG_NAME);

    $conn->query("CREATE TABLE IF NOT EXISTS `addresses` (
        `ip`      VARCHAR(45)  NOT NULL,
        `counter` INT UNSIGNED NOT NULL DEFAULT 1,
        PRIMARY KEY (`ip`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->query("CREATE TABLE IF NOT EXISTS `logs` (
        `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `datetime`   DATETIME        NOT NULL,
        `ip`         VARCHAR(45)     NOT NULL,
        `user_agent` VARCHAR(255)    NOT NULL DEFAULT '',
        PRIMARY KEY (`id`),
        KEY `idx_ip` (`ip`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
	// Check if IP exists
	$stmt = $conn->prepare("SELECT counter FROM addresses WHERE ip = ?");
	$stmt->bind_param('s', $ip);
	$stmt->execute();
	$result = $stmt->get_result();
	$query = $result->fetch_array(MYSQLI_ASSOC);
	$stmt->close();
	
	if(!$query['counter']) {
		$stmt = $conn->prepare("INSERT INTO addresses (ip, counter) VALUES (?, 1)");
		$stmt->bind_param('s', $ip);
        $stmt->execute();
        $stmt->close();
	} else {
		$counter = $query['counter'] + 1;
		$stmt = $conn->prepare("UPDATE addresses SET counter = ? WHERE ip = ?");
		$stmt->bind_param('is', $counter, $ip);
        $stmt->execute();
        $stmt->close();
	}

	$datetime = date('Y-m-d H:i:s');
	$agent = substr($_SERVER['HTTP_USER_AGENT'], 0, 255);
	$stmt = $conn->prepare("INSERT INTO logs (datetime, ip, user_agent) VALUES (?, ?, ?)");
	$stmt->bind_param('sss', $datetime, $ip, $agent);
	$stmt->execute();
	$stmt->close();
	
	setcookie(md5($ip),'nPuzzle',0,'/');
}

function getPuzzleDatabase( ) 
{
    static $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    if ($conn->connect_error) {
        die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
    }
    $conn->set_charset("utf8mb4");
    $conn->query("CREATE DATABASE IF NOT EXISTS `" . DB_PUZZLE_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->select_db(DB_PUZZLE_NAME);

    $conn->query("CREATE TABLE IF NOT EXISTS `8puzzle` (
        `node`          VARCHAR(150) NOT NULL,
        `solution`      TEXT         DEFAULT NULL,
        `moves_to_goal` SMALLINT     DEFAULT NULL,
        `counter`       INT UNSIGNED NOT NULL DEFAULT 1,
        PRIMARY KEY (`node`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    $conn->query("CREATE TABLE IF NOT EXISTS `15puzzle` (
        `node`          VARCHAR(600) NOT NULL,
        `solution`      TEXT         DEFAULT NULL,
        `moves_to_goal` SMALLINT     DEFAULT NULL,
        `counter`       INT UNSIGNED NOT NULL DEFAULT 1,
        PRIMARY KEY (`node`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    return $conn;
}

@mkdir(UPLOAD_DIR . 'imgPool'  , 0755, true);
@mkdir(UPLOAD_DIR . 'temp'     , 0755, true);
@mkdir(UPLOAD_DIR . 'tempTiles', 0755, true);

define('BLANK',0);
set_time_limit(0);
ini_set('memory_limit','256M');
