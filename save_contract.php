<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$targetDir = __DIR__ . DIRECTORY_SEPARATOR . 'signed-contracts';
if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Failed to create storage directory']);
    exit;
}

if (!isset($_FILES['contract']) || !is_uploaded_file($_FILES['contract']['tmp_name'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing contract file']);
    exit;
}

$uploadedPath = (string) $_FILES['contract']['tmp_name'];
$originalName = (string) ($_FILES['contract']['name'] ?? 'contract.pdf');
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

if ($extension !== 'pdf') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Only PDF files are accepted']);
    exit;
}

$timestamp = gmdate('Ymd-His');
$random = bin2hex(random_bytes(4));
$filename = sprintf('contract-%s-%s.pdf', $timestamp, $random);
$destination = $targetDir . DIRECTORY_SEPARATOR . $filename;

if (!move_uploaded_file($uploadedPath, $destination)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Unable to store contract']);
    exit;
}

echo json_encode(['ok' => true, 'file' => 'signed-contracts/' . $filename]);
