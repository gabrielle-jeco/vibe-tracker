<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class TransactionController
{
    private $db;
    private $userId;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        // Simple auth check simulation. In real app, validate JWT from header.
        // For vibe coding, we'll assume the client sends 'Authorization: Bearer <token>'
        // and we parse the fake token we made: base64(user_id:random)
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
            $decoded = base64_decode($token);
            $parts = explode(':', $decoded);
            $this->userId = $parts[0] ?? null;
        }
    }

    private function isAuthenticated()
    {
        if (!$this->userId) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized']);
            return false;
        }
        return true;
    }

    public function index()
    {
        if (!$this->isAuthenticated())
            return;

        $type = $_GET['type'] ?? null;
        $category = $_GET['category'] ?? null;
        // Basic pagination?

        $sql = "SELECT * FROM transactions WHERE user_id = :user_id";
        $params = ['user_id' => $this->userId];

        if ($type) {
            $sql .= " AND type = :type";
            $params['type'] = $type;
        }
        if ($category) {
            $sql .= " AND category = :category";
            $params['category'] = $category;
        }

        $sql .= " ORDER BY transaction_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $transactions = $stmt->fetchAll();

        echo json_encode($transactions);
    }

    public function store()
    {
        if (!$this->isAuthenticated())
            return;

        $data = json_decode(file_get_contents("php://input"));

        // Validation simple
        if (!isset($data->amount) || !isset($data->type)) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid data']);
            return;
        }

        $stmt = $this->db->prepare("INSERT INTO transactions (user_id, type, amount, category, description, transaction_date) VALUES (:uid, :type, :amt, :cat, :desc, :date)");
        $stmt->execute([
            'uid' => $this->userId,
            'type' => $data->type,
            'amt' => $data->amount,
            'cat' => $data->category ?? 'General',
            'desc' => $data->description ?? '',
            'date' => $data->transaction_date ?? date('Y-m-d')
        ]);

        http_response_code(201);
        echo json_encode(['id' => $this->db->lastInsertId(), 'message' => 'Transaction created']);
    }

    public function update($id)
    {
        if (!$this->isAuthenticated())
            return;
        $data = json_decode(file_get_contents("php://input"));

        $stmt = $this->db->prepare("UPDATE transactions SET type=:type, amount=:amt, category=:cat, description=:desc, transaction_date=:date WHERE id=:id AND user_id=:uid");
        $success = $stmt->execute([
            'type' => $data->type,
            'amt' => $data->amount,
            'cat' => $data->category,
            'desc' => $data->description,
            'date' => $data->transaction_date,
            'id' => $id,
            'uid' => $this->userId
        ]);

        if ($success) {
            echo json_encode(['message' => 'Transaction updated']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Update failed']);
        }
    }

    public function destroy($id)
    {
        if (!$this->isAuthenticated())
            return;

        $stmt = $this->db->prepare("DELETE FROM transactions WHERE id=:id AND user_id=:uid");
        $stmt->execute(['id' => $id, 'uid' => $this->userId]);
        echo json_encode(['message' => 'Transaction deleted']);
    }

    public function summary()
    {
        if (!$this->isAuthenticated())
            return;

        $stmt = $this->db->prepare("
            SELECT 
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
            FROM transactions 
            WHERE user_id = :uid
        ");
        $stmt->execute(['uid' => $this->userId]);
        $result = $stmt->fetch();

        $balance = $result['total_income'] - $result['total_expense'];

        echo json_encode([
            'income' => $result['total_income'] ?? 0,
            'expense' => $result['total_expense'] ?? 0,
            'balance' => $balance
        ]);
    }
}
