<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    getStatistics($jwtHandler);
} else {
    sendJsonResponse(['error' => 'Método não permitido. Use GET.'], 405);
}