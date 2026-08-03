<?php
require_once __DIR__ . '/../models/Goal.php';
require_once __DIR__ . '/../config/csrf.php';

class GoalController
{
    private function json($data, $status = 200)
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    public function index()
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $goalModel = new Goal($GLOBALS['pdo']);

        $goals = $goalModel->getByUser($userId);

        include __DIR__ . '/../views/goals/index.php';
    }

    public function listAjax()
    {
        if (empty($_SESSION['user_id'])) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $userId = $_SESSION['user_id'];
        $goalModel = new Goal($GLOBALS['pdo']);
        $goals = $goalModel->getByUser($userId);
        $this->json(['success' => true, 'data' => $goals]);
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

        $name = trim($input['name'] ?? '');
        $targetAmount = $input['target_amount'] ?? 0;
        $savedAmount = $input['saved_amount'] ?? 0;
        $deadline = $input['deadline'] ?? null;

        $errors = [];
        if (!$name) $errors['name'] = 'Le nom est requis.';
        if (!is_numeric($targetAmount) || $targetAmount <= 0) $errors['target_amount'] = 'Le montant cible doit être positif.';
        if (!is_numeric($savedAmount) || $savedAmount < 0) $errors['saved_amount'] = 'Le montant épargné ne peut pas être négatif.';

        if (!empty($errors)) $this->json(['success' => false, 'errors' => $errors], 400);

        $model = new Goal($GLOBALS['pdo']);
        $userId = $_SESSION['user_id'];
        $ok = $model->create($userId, $name, $targetAmount, $savedAmount, $deadline);
        if ($ok) {
            $this->json(['success' => true], 201);
        }

        $this->json(['success' => false, 'message' => 'Impossible de créer l\'objectif'], 500);
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
        $name = trim($input['name'] ?? '');
        $targetAmount = $input['target_amount'] ?? null;
        $savedAmount = $input['saved_amount'] ?? null;
        $deadline = $input['deadline'] ?? null;

        if (!$id) $this->json(['success' => false, 'message' => 'ID requis'], 400);

        $errors = [];
        if (!$name) $errors['name'] = 'Le nom est requis.';
        if (!is_numeric($targetAmount) || $targetAmount <= 0) $errors['target_amount'] = 'Le montant cible doit être positif.';
        if (!is_numeric($savedAmount) || $savedAmount < 0) $errors['saved_amount'] = 'Le montant épargné ne peut pas être négatif.';

        if (!empty($errors)) $this->json(['success' => false, 'errors' => $errors], 400);

        $model = new Goal($GLOBALS['pdo']);
        $existing = $model->findById($id);
        if (!$existing) $this->json(['success' => false, 'message' => 'Objectif introuvable'], 404);

        // Security: verify ownership
        if ($existing['user_id'] != $_SESSION['user_id']) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $ok = $model->update($id, $name, $targetAmount, $savedAmount, $deadline);
        if ($ok) {
            $this->json(['success' => true]);
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

        $model = new Goal($GLOBALS['pdo']);
        $existing = $model->findById($id);
        if (!$existing) $this->json(['success' => false, 'message' => 'Objectif introuvable'], 404);

        // Security: verify ownership
        if ($existing['user_id'] != $_SESSION['user_id']) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $ok = $model->delete($id);
        if ($ok) $this->json(['success' => true]);

        $this->json(['success' => false, 'message' => 'Impossible de supprimer'], 500);
    }

    public function addSavings()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        if (empty($_SESSION['user_id'])) $this->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true) ?? [];
        $csrf = $input['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!\Csrf::validateToken($csrf)) {
            $this->json(['success' => false, 'errors' => ['csrf' => 'Token CSRF invalide']], 403);
        }

        $goalId = $input['goal_id'] ?? null;
        $amount = $input['amount'] ?? 0;

        if (!$goalId) $this->json(['success' => false, 'message' => 'ID objectif requis'], 400);
        if (!is_numeric($amount) || $amount <= 0) $this->json(['success' => false, 'message' => 'Montant invalide'], 400);

        $model = new Goal($GLOBALS['pdo']);
        $goal = $model->findById($goalId);
        if (!$goal) $this->json(['success' => false, 'message' => 'Objectif introuvable'], 404);

        // Security: verify ownership
        if ($goal['user_id'] != $_SESSION['user_id']) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $newSaved = $goal['saved_amount'] + $amount;
        $ok = $model->update($goalId, $goal['name'], $goal['target_amount'], $newSaved, $goal['deadline']);
        if ($ok) {
            $this->json(['success' => true]);
        }

        $this->json(['success' => false, 'message' => 'Impossible d\'ajouter l\'épargne'], 500);
    }
}
