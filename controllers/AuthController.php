<?php
require_once __DIR__ . '/../models/User.php';

class AuthController
{
    private function isAjax()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $isAjax = $this->isAjax();

            // Get data from JSON body for AJAX or from POST for normal form
            $csrfToken = null;
            if ($isAjax) {
                // parse body once
                $raw = file_get_contents('php://input');
                $input = json_decode($raw, true) ?? [];
                $name = trim($input['name'] ?? '');
                $email = trim($input['email'] ?? '');
                $password = $input['password'] ?? '';
                $csrfToken = $input['csrf_token'] ?? null;
                // fallback to header extraction
                if (empty($csrfToken)) {
                    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
                }
            } else {
                $name = trim($_POST['name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                $csrfToken = $_POST['csrf_token'] ?? null;
            }

            // Validate CSRF token
            if (!\Csrf::validateToken($csrfToken)) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    http_response_code(403);
                    echo json_encode(['success' => false, 'errors' => ['general' => 'Token CSRF invalide']]);
                    return;
                }
                $error = 'Token CSRF invalide.';
                include __DIR__ . '/../views/auth/register.php';
                return;
            }

            $errors = [];
            if (!$name) {
                $errors['name'] = 'Le nom est requis.';
            }
            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Email invalide.';
            }
            // Password strength: min 8 chars, at least one letter and one number
            if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
                $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères, une lettre et un chiffre.';
            }

            $userModel = new User($GLOBALS['pdo']);
            if (empty($errors) && $userModel->findByEmail($email)) {
                $errors['email'] = 'Un utilisateur existe déjà avec cet email.';
            }

            if (!empty($errors)) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    http_response_code(400);
                    echo json_encode(['success' => false, 'errors' => $errors]);
                    return;
                }
                $error = implode(' ', $errors);
                include __DIR__ . '/../views/auth/register.php';
                return;
            }

            $userId = $userModel->create($name, $email, $password);
            if ($userId) {
                // Log the user in
                session_regenerate_id(true);
                $_SESSION['user_id'] = $userId;

                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'redirect' => 'index.php?route=dashboard']);
                    return;
                }

                header('Location: index.php?route=dashboard');
                exit;
            }

            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode(['success' => false, 'errors' => ['general' => 'Impossible de créer le compte.']]);
                return;
            }

            $error = 'Impossible de créer le compte. Réessayez.';
        }

        include __DIR__ . '/../views/auth/register.php';
    }

    public function checkEmail()
    {
        // Allow GET or POST - respond JSON
        $email = trim($_GET['email'] ?? $_POST['email'] ?? '');
        header('Content-Type: application/json');

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'valid' => false, 'message' => 'Format email invalide']);
            return;
        }

        $userModel = new User($GLOBALS['pdo']);
        $exists = (bool)$userModel->findByEmail($email);
        echo json_encode(['success' => true, 'valid' => true, 'exists' => $exists]);
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $csrfToken = $_POST['csrf_token'] ?? null;

            if (!\Csrf::validateToken($csrfToken)) {
                $error = 'Token CSRF invalide.';
                include __DIR__ . '/../views/auth/login.php';
                return;
            }

            $userModel = new User($GLOBALS['pdo']);
            $user = $userModel->findByEmail($email);
            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                header('Location: index.php?route=dashboard');
                exit;
            }

            $error = 'Email ou mot de passe invalide.';
        }

        include __DIR__ . '/../views/auth/login.php';
    }

    public function logout()
    {
        session_unset();
        session_destroy();
        header('Location: index.php');
        exit;
    }
}
