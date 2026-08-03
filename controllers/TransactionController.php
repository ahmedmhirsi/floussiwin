<?php
require_once __DIR__ . '/../models/Transaction.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../services/FinancialEngine.php';

class TransactionController
{
    private $allowedCategories = [
        'Nourriture / Makla','Café','Transport','Loyer','Factures STEG / SONEDE','Internet / Téléphone','Courses','Loisirs','Études','Santé','Autres'
    ];

    public function index()
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $model = new Transaction($GLOBALS['pdo']);
        // Provide categories summary to the view (MVC separation)
        $categories = $model->getCategorySumsThisMonth($userId);
        // Optionally pass initial transactions (most recent 50)
        $initialTransactions = $model->getByUser($userId, null, null, null, null, 50);

        include __DIR__ . '/../views/transactions/index.php';
    }

    private function json($data, $status = 200)
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    public function listAjax()
    {
        if (empty($_SESSION['user_id'])) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $userId = $_SESSION['user_id'];
        $start = $_GET['start'] ?? null;
        $end = $_GET['end'] ?? null;
        $category = $_GET['category'] ?? null;
        $q = $_GET['q'] ?? null;
        $limit = $_GET['limit'] ?? 50;

        $model = new Transaction($GLOBALS['pdo']);
        $items = $model->getByUser($userId, $start, $end, $category, $q, $limit);
        $this->json(['success' => true, 'data' => $items]);
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        if (empty($_SESSION['user_id'])) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true) ?? [];
        $csrf = $input['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!\Csrf::validateToken($csrf)) {
            $this->json(['success' => false, 'errors' => ['csrf' => 'Token CSRF invalide']], 403);
        }

        $type = $input['type'] ?? 'expense';
        $amount = $input['amount'] ?? 0;
        $category = $input['category'] ?? null;
        $description = $input['description'] ?? null;
        $date = $input['date'] ?? null;

        $errors = [];
        if (!in_array($type, ['income', 'expense'])) $errors['type'] = 'Type invalide.';
        if (!is_numeric($amount) || $amount <= 0) $errors['amount'] = 'Montant invalide.';
        if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $errors['date'] = 'Date invalide.';
        if ($category && !in_array($category, $this->allowedCategories)) $errors['category'] = 'Catégorie invalide.';

        if (!empty($errors)) $this->json(['success' => false, 'errors' => $errors], 400);

        $model = new Transaction($GLOBALS['pdo']);
        $userId = $_SESSION['user_id'];
        $newId = $model->create($userId, $type, $amount, $category, $description, $date);
        if ($newId) {
            $tx = $model->findById($newId);
            // Recalculate financial insights after transaction
            $financialEngine = new FinancialEngine($GLOBALS['pdo']);
            $financialEngine->recalculateAfterTransaction($userId);
            $this->json(['success' => true, 'data' => $tx], 201);
        }

        $this->json(['success' => false, 'message' => 'Impossible de créer la transaction'], 500);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        if (empty($_SESSION['user_id'])) $this->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true) ?? [];
        $csrf = $input['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!\Csrf::validateToken($csrf)) {
            $this->json(['success' => false, 'errors' => ['csrf' => 'Token CSRF invalide']], 403);
        }

        $id = $input['id'] ?? null;
        $type = $input['type'] ?? null;
        $amount = $input['amount'] ?? null;
        $category = $input['category'] ?? null;
        $description = $input['description'] ?? null;
        $date = $input['date'] ?? null;

        if (!$id) $this->json(['success' => false, 'message' => 'ID requis'], 400);

        $errors = [];
        if (!in_array($type, ['income', 'expense'])) $errors['type'] = 'Type invalide.';
        if (!is_numeric($amount) || $amount <= 0) $errors['amount'] = 'Montant invalide.';
        if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $errors['date'] = 'Date invalide.';
        if ($category && !in_array($category, $this->allowedCategories)) $errors['category'] = 'Catégorie invalide.';

        if (!empty($errors)) $this->json(['success' => false, 'errors' => $errors], 400);

        $model = new Transaction($GLOBALS['pdo']);
        $existing = $model->findById($id);
        if (!$existing) $this->json(['success' => false, 'message' => 'Transaction introuvable'], 404);

        // Security: verify ownership
        if ($existing['user_id'] != $_SESSION['user_id']) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $ok = $model->update($id, $type, $amount, $category, $description, $date);
        if ($ok) {
            $tx = $model->findById($id);
            // Recalculate financial insights after transaction update
            $financialEngine = new FinancialEngine($GLOBALS['pdo']);
            $financialEngine->recalculateAfterTransaction($_SESSION['user_id']);
            $this->json(['success' => true, 'data' => $tx]);
        }

        $this->json(['success' => false, 'message' => 'Impossible de mettre à jour'], 500);
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        if (empty($_SESSION['user_id'])) $this->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true) ?? [];
        $csrf = $input['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!\Csrf::validateToken($csrf)) {
            $this->json(['success' => false, 'errors' => ['csrf' => 'Token CSRF invalide']], 403);
        }

        $id = $input['id'] ?? null;
        if (!$id) $this->json(['success' => false, 'message' => 'ID requis'], 400);

        $model = new Transaction($GLOBALS['pdo']);
        $existing = $model->findById($id);
        if (!$existing) $this->json(['success' => false, 'message' => 'Transaction introuvable'], 404);

        // Security: verify ownership
        if ($existing['user_id'] != $_SESSION['user_id']) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $ok = $model->delete($id);
        if ($ok) {
            // Recalculate financial insights after transaction deletion
            $financialEngine = new FinancialEngine($GLOBALS['pdo']);
            $financialEngine->recalculateAfterTransaction($_SESSION['user_id']);
            $this->json(['success' => true]);
        }

        $this->json(['success' => false, 'message' => 'Impossible de supprimer'], 500);
    }

    public function summary()
    {
        if (empty($_SESSION['user_id'])) $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
        $userId = $_SESSION['user_id'];
        $model = new Transaction($GLOBALS['pdo']);
        $income = $model->getMonthlySumByType($userId, 'income');
        $expense = $model->getMonthlySumByType($userId, 'expense');
        $categories = $model->getCategorySumsThisMonth($userId);
        $this->json(['success' => true, 'data' => ['income' => $income, 'expense' => $expense, 'categories' => $categories]]);
    }
}
