<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateToken($jwtHandler);
} else {
    sendJsonResponse(['error' => 'Método não permitido. Use POST.'], 405);
}