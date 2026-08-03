<?php
require_once __DIR__ . '/../models/UserProfile.php';
require_once __DIR__ . '/../models/FixedExpense.php';
require_once __DIR__ . '/../config/csrf.php';

class ProfileController
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
        $userProfile = new UserProfile($GLOBALS['pdo']);
        $fixedExpense = new FixedExpense($GLOBALS['pdo']);

        $profile = $userProfile->findByUserId($userId);
        $fixedExpenses = $fixedExpense->findByUserId($userId);

        include __DIR__ . '/../views/profile/index.php';
    }

    public function saveProfile()
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

        $monthlySalary = $input['monthly_salary'] ?? 0;
        $additionalIncome = $input['additional_income'] ?? 0;

        $errors = [];
        if (!is_numeric($monthlySalary) || $monthlySalary < 0) $errors['monthly_salary'] = 'Salaire invalide.';
        if (!is_numeric($additionalIncome) || $additionalIncome < 0) $errors['additional_income'] = 'Revenus supplémentaires invalides.';

        if (!empty($errors)) $this->json(['success' => false, 'errors' => $errors], 400);

        $userId = $_SESSION['user_id'];
        $userProfile = new UserProfile($GLOBALS['pdo']);
        $existing = $userProfile->findByUserId($userId);

        if ($existing) {
            $ok = $userProfile->update($userId, $monthlySalary, $additionalIncome);
        } else {
            $ok = $userProfile->create($userId, $monthlySalary, $additionalIncome);
        }

        if ($ok) {
            $this->json(['success' => true]);
        }

        $this->json(['success' => false, 'message' => 'Impossible de sauvegarder le profil'], 500);
    }

    public function addFixedExpense()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        if (empty($_SESSION['user_id'])) $this->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true) ?? [];
        $csrf = $input['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!\Csrf::validateToken($csrf)) {
            $this->json(['success' => false, 'errors' => ['csrf' => 'Token CSRF invalide']], 403);
        }

        $name = trim($input['name'] ?? '');
        $amount = $input['amount'] ?? 0;
        $category = $input['category'] ?? null;

        $errors = [];
        if (!$name) $errors['name'] = 'Le nom est requis.';
        if (!is_numeric($amount) || $amount <= 0) $errors['amount'] = 'Le montant doit être positif.';

        if (!empty($errors)) $this->json(['success' => false, 'errors' => $errors], 400);

        $userId = $_SESSION['user_id'];
        $fixedExpense = new FixedExpense($GLOBALS['pdo']);
        $ok = $fixedExpense->create($userId, $name, $amount, $category);

        if ($ok) {
            $this->json(['success' => true], 201);
        }

        $this->json(['success' => false, 'message' => 'Impossible d\'ajouter la charge fixe'], 500);
    }

    public function updateFixedExpense()
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
        $amount = $input['amount'] ?? 0;
        $category = $input['category'] ?? null;

        if (!$id) $this->json(['success' => false, 'message' => 'ID requis'], 400);

        $errors = [];
        if (!$name) $errors['name'] = 'Le nom est requis.';
        if (!is_numeric($amount) || $amount <= 0) $errors['amount'] = 'Le montant doit être positif.';

        if (!empty($errors)) $this->json(['success' => false, 'errors' => $errors], 400);

        $fixedExpense = new FixedExpense($GLOBALS['pdo']);
        $existing = $fixedExpense->findById($id);
        if (!$existing) $this->json(['success' => false, 'message' => 'Charge introuvable'], 404);

        // Security: verify ownership
        if ($existing['user_id'] != $_SESSION['user_id']) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $ok = $fixedExpense->update($id, $name, $amount, $category);
        if ($ok) {
            $this->json(['success' => true]);
        }

        $this->json(['success' => false, 'message' => 'Impossible de modifier'], 500);
    }

    public function deleteFixedExpense()
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

        $fixedExpense = new FixedExpense($GLOBALS['pdo']);
        $existing = $fixedExpense->findById($id);
        if (!$existing) $this->json(['success' => false, 'message' => 'Charge introuvable'], 404);

        // Security: verify ownership
        if ($existing['user_id'] != $_SESSION['user_id']) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $ok = $fixedExpense->delete($id);
        if ($ok) $this->json(['success' => true]);

        $this->json(['success' => false, 'message' => 'Impossible de supprimer'], 500);
    }
}
