<?php
// config/csrf.php
// Simple CSRF helper storing a token in session

class Csrf
{
    const TOKEN_KEY = 'csrf_token';
    const TOKEN_TIME_KEY = 'csrf_token_time';
    const TTL = 7200; // 2 hours

    public static function ensureToken(): string
    {
        if (empty($_SESSION[self::TOKEN_KEY]) || empty($_SESSION[self::TOKEN_TIME_KEY]) || time() - $_SESSION[self::TOKEN_TIME_KEY] > self::TTL) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
            $_SESSION[self::TOKEN_TIME_KEY] = time();
        }
        return $_SESSION[self::TOKEN_KEY];
    }

    public static function getToken(): ?string
    {
        return $_SESSION[self::TOKEN_KEY] ?? null;
    }

    public static function validateToken(?string $token): bool
    {
        if (empty($token)) return false;
        if (empty($_SESSION[self::TOKEN_KEY]) || empty($_SESSION[self::TOKEN_TIME_KEY])) return false;
        if (time() - $_SESSION[self::TOKEN_TIME_KEY] > self::TTL) return false;
        // Use hash_equals to mitigate timing attacks
        return hash_equals($_SESSION[self::TOKEN_KEY], (string)$token);
    }

    // Convenience: extract token from various sources (headers, post, json)
    // Note: This should NOT be used after php://input has been read elsewhere
    public static function extractTokenFromRequest(): ?string
    {
        // Check header 'X-CSRF-Token' (often used by AJAX)
        $headers = self::getAllHeadersLower();
        if (!empty($headers['x-csrf-token'])) return $headers['x-csrf-token'];

        // Check POST field (for form submissions)
        if (!empty($_POST['csrf_token'])) return $_POST['csrf_token'];

        // For JSON body - WARNING: php://input can only be read once
        // This method should be called before any other code reads php://input
        $raw = file_get_contents('php://input');
        if ($raw) {
            $data = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && !empty($data['csrf_token'])) return $data['csrf_token'];
        }

        return null;
    }

    private static function getAllHeadersLower()
    {
        $result = [];
        if (function_exists('getallheaders')) {
            foreach (getallheaders() as $k => $v) {
                $result[strtolower($k)] = $v;
            }
            return $result;
        }
        // Fallback for environments without getallheaders()
        foreach ($_SERVER as $k => $v) {
            if (strpos($k, 'HTTP_') === 0) {
                $header = str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($k, 5))));
                $result[$header] = $v;
            }
        }
        return $result;
    }
}
