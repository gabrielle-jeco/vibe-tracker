<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class AuthController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function register()
    {
        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->username) || !isset($data->password)) {
            http_response_code(400);
            echo json_encode(['message' => 'Username and password are required']);
            return;
        }

        // Check if user exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute(['username' => $data->username]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['message' => 'Username already exists']);
            return;
        }

        $hashed_password = password_hash($data->password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");

        if ($stmt->execute(['username' => $data->username, 'password' => $hashed_password])) {
            http_response_code(201);
            echo json_encode(['message' => 'User registered successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Registration failed']);
        }
    }

    public function login()
    {
        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->username) || !isset($data->password)) {
            http_response_code(400);
            echo json_encode(['message' => 'Username and password are required']);
            return;
        }

        $stmt = $this->db->prepare("SELECT id, username, password FROM users WHERE username = :username");
        $stmt->execute(['username' => $data->username]);
        $user = $stmt->fetch();

        if ($user && password_verify($data->password, $user['password'])) {
            // In a real app, use JWT. For this simple vibe coding, we'll return a fake token (or just the user ID as token base64 encoded for simplicity functionality)
            // But let's be slightly better, just base64 encode user_id:random_hash
            $token = base64_encode($user['id'] . ':' . bin2hex(random_bytes(16)));

            echo json_encode([
                'message' => 'Login successful',
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username']
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['message' => 'Invalid credentials']);
        }
    }
}
