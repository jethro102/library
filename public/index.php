<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require '../src/vendor/autoload.php';
$app = new \Slim\App;

$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "library";

// Generate Token
function generateToken($userid) {
    $key = 'server_hack';
    $iat = time();
    $exp = $iat + 3600; // Token expires in 1 hour (3600 seconds)
    $payload = [
        'iss' => 'http://library.org',
        'aud' => 'http://library.com',
        'iat' => $iat,
        "data" => array("userid" => $userid)
    ];

    return JWT::encode($payload, $key, 'HS256');
}

$app->post('/user', function (Request $request, Response $response) use ($servername, $dbusername, $dbpassword, $dbname) {
    $data = json_decode($request->getBody());
    $usr = $data->username;
    $pass = $data->password;

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "INSERT INTO users (username, password) VALUES (:usr, :pass)";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['usr' => $usr, 'pass' => password_hash($pass, PASSWORD_DEFAULT)]);
        
        $response->getBody()->write(json_encode(["status" => "registration success, proceed to login"]));
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["status" => "fail", "error" => $e->getMessage()]));
    }
    $conn = null;

    return $response;
});

$app->put('/userupdate/{id}', function (Request $request, Response $response, array $args) use ($servername, $dbusername, $dbpassword, $dbname) {
    $userId = $args['id'];
    $data = json_decode($request->getBody());
    
    // Check if data contains fields to update
    if (!isset($data->username) && !isset($data->password)) {
        return $response
            ->withStatus(400)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode(["status" => "fail", "error" => "No valid fields to update"]));
    }

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Build the SQL query dynamically based on the fields provided
        $sql = "UPDATE users SET ";
        $params = [];

        if (isset($data->username)) {
            $sql .= "username = :username, ";
            $params['username'] = $data->username;
        }

        if (isset($data->password)) {
            $sql .= "password = :password, ";
            $params['password'] = password_hash($data->password, PASSWORD_DEFAULT);
        }

        // Remove trailing comma and space
        $sql = rtrim($sql, ', ');
        $sql .= " WHERE userid = :id";
        $params['id'] = $userId;

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        // Check if any rows were updated
        if ($stmt->rowCount() > 0) {
            $response->getBody()->write(json_encode(["status" => "success", "message" => "User updated successfully"]));
        } else {
            $response->getBody()->write(json_encode(["status" => "fail", "error" => "No changes made or user not found"]));
        }
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["status" => "fail", "error" => $e->getMessage()]));
    }

    $conn = null;
    return $response;
});

$app->delete('/userdelete/{id}', function (Request $request, Response $response, array $args) use ($servername, $dbusername, $dbpassword, $dbname) {
    $userId = $args['id']; // Get the user ID from the route parameter

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if the user exists
        $checkSql = "SELECT * FROM users WHERE userid = :id";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->execute(['id' => $userId]);

        if ($checkStmt->rowCount() > 0) {
            // Delete the user if they exist
            $sql = "DELETE FROM users WHERE userid = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['id' => $userId]);

            $response->getBody()->write(json_encode(["status" => "success", "message" => "User deleted successfully."]));
        } else {
            $response->getBody()->write(json_encode(["status" => "fail", "message" => "User not found."]));
        }
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["status" => "fail", "error" => $e->getMessage()]));
    }

    $conn = null;
    return $response;
});

$app->get('/displayalluser', function (Request $request, Response $response) use ($servername, $dbusername, $dbpassword, $dbname) {
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Query to fetch all users
        $sql = "SELECT userid, username FROM users"; // Select only necessary fields (avoid exposing passwords)
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        // Fetch all users as an associative array
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the user data as JSON
        $response->getBody()->write(json_encode(["status" => "success", "data" => $users]));
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["status" => "fail", "error" => $e->getMessage()]));
    }

    $conn = null;
    return $response->withHeader('Content-Type', 'application/json');
});


$app->post('/login', function (Request $request, Response $response) use ($servername, $dbusername, $dbpassword, $dbname) {
    $data = json_decode($request->getBody(), true);

    // Check if required fields are present
    if (!isset($data['username']) || !isset($data['password'])) {
        return $response->withStatus(400)->withJson(["status" => "fail", "error" => "Missing username or password"]);
    }

    $usr = $data['username'];
    $pass = $data['password'];

    try {
        // Establish a database connection
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Prepare and execute the SQL query to fetch user
        $sql = "SELECT * FROM users WHERE username = :usr";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['usr' => $usr]);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $user = $stmt->fetch();

        // Validate user and password
        if ($user && password_verify($pass, $user['password'])) {
            // Generate JWT (assuming generateToken function exists)
            $jwt = generateToken($user['userid']);

            // Store token in user_tokens table
            $sqlToken = "INSERT INTO user_tokens (userid, token) VALUES (:userid, :token)";
            $stmtToken = $conn->prepare($sqlToken);
            $stmtToken->execute(['userid' => $user['userid'], 'token' => $jwt]);

            // Respond with the token
            return $response->withJson(["status" => "Login Success", "token" => $jwt]);
        } else {
            return $response->withStatus(401)->withJson(["status" => "fail", "error" => "Invalid username or password"]);
        }
    } catch (PDOException $e) {
        // Handle database connection errors
        return $response->withStatus(500)->withJson(["status" => "fail", "error" => "Database error: " . $e->getMessage()]);
    } catch (Exception $e) {
        // Handle unexpected errors
        return $response->withStatus(500)->withJson(["status" => "fail", "error" => "Unexpected error: " . $e->getMessage()]);
    }
});

$app->post('/add', function (Request $request, Response $response) use ($servername, $dbusername, $dbpassword, $dbname) {
    $data = json_decode($request->getBody());
    $book = $data->book;
    $author = $data->author;
    $token = $request->getHeader('Authorization')[0] ?? '';
    $token = str_replace('Bearer ', '', $token);

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //Validate token and get user ID
        $sqlCheckToken = "SELECT userid FROM user_tokens WHERE token = :token";
        $stmtCheckToken = $conn->prepare($sqlCheckToken);
        $stmtCheckToken->execute(['token' => $token]);

        if ($stmtCheckToken->rowCount() === 0) {
            return $response->withStatus(401)
                ->write(json_encode(["status" => "fail", "data" => ["title" => "Token is used."]]));
        }

        $decoded = JWT::decode($token, new Key('server_hack', 'HS256'));
        $userid = $decoded->data->userid;

        //Insert book and author
        $sqlBook = "INSERT INTO books (title) VALUES (:title)";
        $stmtBook = $conn->prepare($sqlBook);
        $stmtBook->execute(['title' => $book]);

        $sqlAuthor = "INSERT INTO authors (name, book_id) VALUES (:name, LAST_INSERT_ID())";
        $stmtAuthor = $conn->prepare($sqlAuthor);
        $stmtAuthor->execute(['name' => $author]);

        //Invalidate the used token
        $sqlDeleteToken = "DELETE FROM user_tokens WHERE token = :token";
        $stmtDeleteToken = $conn->prepare($sqlDeleteToken);
        $stmtDeleteToken->execute(['token' => $token]);

        //Generate a new token
        $newToken = generateToken($userid);
        $sqlInsertNewToken = "INSERT INTO user_tokens (userid, token) VALUES (:userid, :token)";
        $stmtInsertNewToken = $conn->prepare($sqlInsertNewToken);
        $stmtInsertNewToken->execute(['userid' => $userid, 'token' => $newToken]);

        $response->getBody()->write(json_encode(["status" => "Book added", "Here's your token" => $newToken]));
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(["status" => "fail", "data" => ["title" => $e->getMessage()]]));
    }

    return $response;
});

$app->get('/get', function (Request $request, Response $response) use ($servername, $dbusername, $dbpassword, $dbname) {
    $token = $request->getHeader('Authorization')[0] ?? '';
    $token = str_replace('Bearer ', '', $token);

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sqlCheckToken = "SELECT userid FROM user_tokens WHERE token = :token";
        $stmtCheckToken = $conn->prepare($sqlCheckToken);
        $stmtCheckToken->execute(['token' => $token]);

        if ($stmtCheckToken->rowCount() === 0) {
            return $response->withStatus(401)
                ->write(json_encode(["status" => "fail", "data" => ["title" => "Token is used."]]));
        }

        $decoded = JWT::decode($token, new Key('server_hack', 'HS256'));
        $userid = $decoded->data->userid;

        $sql = "SELECT books.id AS book_id, books.title, authors.name AS author_name 
                FROM books 
                JOIN authors ON books.id = authors.book_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //Invalidate the used token and create a new one
        $sqlDeleteToken = "DELETE FROM user_tokens WHERE token = :token";
        $stmtDeleteToken = $conn->prepare($sqlDeleteToken);
        $stmtDeleteToken->execute(['token' => $token]);

        $newToken = generateToken($userid);
        $sqlInsertNewToken = "INSERT INTO user_tokens (userid, token) VALUES (:userid, :token)";
        $stmtInsertNewToken = $conn->prepare($sqlInsertNewToken);
        $stmtInsertNewToken->execute(['userid' => $userid, 'token' => $newToken]);

        $response->getBody()->write(json_encode(["status" => "success", "data" => $books, "Here's your token" => $newToken]));
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(["status" => "fail", "data" => ["title" => $e->getMessage()]]));
    }

    return $response;
});

$app->get('/booksauthors', function (Request $request, Response $response) use ($servername, $dbusername, $dbpassword, $dbname) {
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Query to fetch all books and their authors
        $sql = "SELECT books.id AS book_id, books.title AS book_title, authors.name AS author_name 
                FROM books 
                LEFT JOIN authors ON books.id = authors.book_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        // Fetch all results
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the books and authors as JSON
        $response->getBody()->write(json_encode(["status" => "success", "data" => $books]));
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["status" => "fail", "error" => $e->getMessage()]));
    }

    $conn = null;
    return $response->withHeader('Content-Type', 'application/json');
});


$app->put('/update/{id}', function (Request $request, Response $response, $args) use ($servername, $dbusername, $dbpassword, $dbname) {
    $data = json_decode($request->getBody());
    $book = $data->book;
    $author = $data->author;
    $bookId = $args['id'];
    $token = $request->getHeader('Authorization')[0] ?? '';
    $token = str_replace('Bearer ', '', $token);

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //Validate token
        $sqlCheckToken = "SELECT userid FROM user_tokens WHERE token = :token";
        $stmtCheckToken = $conn->prepare($sqlCheckToken);
        $stmtCheckToken->execute(['token' => $token]);

        if ($stmtCheckToken->rowCount() === 0) {
            return $response->withStatus(401)
                ->write(json_encode(["status" => "fail", "data" => ["title" => "Token is used."]]));
        }

        $decoded = JWT::decode($token, new Key('server_hack', 'HS256'));
        $userid = $decoded->data->userid;

        //Update book and author
        $sqlUpdateBook = "UPDATE books SET title = :title WHERE id = :id";
        $stmtUpdateBook = $conn->prepare($sqlUpdateBook);
        $stmtUpdateBook->execute(['title' => $book, 'id' => $bookId]);

        $sqlUpdateAuthor = "UPDATE authors SET name = :name WHERE book_id = :id";
        $stmtUpdateAuthor = $conn->prepare($sqlUpdateAuthor);
        $stmtUpdateAuthor->execute(['name' => $author, 'id' => $bookId]);

        //Invalidate token and create a new one
        $sqlDeleteToken = "DELETE FROM user_tokens WHERE token = :token";
        $stmtDeleteToken = $conn->prepare($sqlDeleteToken);
        $stmtDeleteToken->execute(['token' => $token]);

        $newToken = generateToken($userid);
        $sqlInsertNewToken = "INSERT INTO user_tokens (userid, token) VALUES (:userid, :token)";
        $stmtInsertNewToken = $conn->prepare($sqlInsertNewToken);
        $stmtInsertNewToken->execute(['userid' => $userid, 'token' => $newToken]);

        $response->getBody()->write(json_encode([
            "status" => "Update Success",
            "Here's your token" => $newToken
        ], JSON_PRETTY_PRINT));
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(["status" => "fail", "data" => ["title" => $e->getMessage()]]));
    }

    return $response;
});

$app->delete('/delete/{id}', function (Request $request, Response $response, $args) use ($servername, $dbusername, $dbpassword, $dbname) {
    $bookId = $args['id'];
    $token = $request->getHeader('Authorization')[0] ?? '';
    $token = str_replace('Bearer ', '', $token);

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //Validate token
        $sqlCheckToken = "SELECT userid FROM user_tokens WHERE token = :token";
        $stmtCheckToken = $conn->prepare($sqlCheckToken);
        $stmtCheckToken->execute(['token' => $token]);

        if ($stmtCheckToken->rowCount() === 0) {
            return $response->withStatus(401)
                ->write(json_encode(["status" => "fail", "data" => ["title" => "Token is used."]]));
        }

        $decoded = JWT::decode($token, new Key('server_hack', 'HS256'));
        $userid = $decoded->data->userid;

        //Delete book and author
        $sqlDeleteAuthor = "DELETE FROM authors WHERE book_id = :id";
        $stmtDeleteAuthor = $conn->prepare($sqlDeleteAuthor);
        $stmtDeleteAuthor->execute(['id' => $bookId]);

        $sqlDeleteBook = "DELETE FROM books WHERE id = :id";
        $stmtDeleteBook = $conn->prepare($sqlDeleteBook);
        $stmtDeleteBook->execute(['id' => $bookId]);

        //Invalidate token and create a new one
        $sqlDeleteToken = "DELETE FROM user_tokens WHERE token = :token";
        $stmtDeleteToken = $conn->prepare($sqlDeleteToken);
        $stmtDeleteToken->execute(['token' => $token]);

        $newToken = generateToken($userid);
        $sqlInsertNewToken = "INSERT INTO user_tokens (userid, token) VALUES (:userid, :token)";
        $stmtInsertNewToken = $conn->prepare($sqlInsertNewToken);
        $stmtInsertNewToken->execute(['userid' => $userid, 'token' => $newToken]);

        $response->getBody()->write(json_encode([
            "status" => "Delete success",
            "Here's your token" => $newToken
        ], JSON_PRETTY_PRINT));
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(["status" => "fail", "data" => ["title" => $e->getMessage()]]));
    }

    return $response;
});

$app->run();
