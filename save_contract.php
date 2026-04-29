<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

if (!isset($_FILES['contract']) || $_FILES['contract']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'error' => 'No contract PDF uploaded',
        'uploadError' => $_FILES['contract']['error'] ?? null
    ]);
    exit;
}

$maxSize = 20 * 1024 * 1024; // 20MB
if ($_FILES['contract']['size'] > $maxSize) {
    http_response_code(413);
    echo json_encode(['ok' => false, 'error' => 'PDF file is too large']);
    exit;
}

$targetDir = __DIR__ . DIRECTORY_SEPARATOR . 'signed-contracts';

if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Failed to create storage directory']);
    exit;
}

$clientName = trim((string)($_POST['clientName'] ?? ''));
$clientPhone = trim((string)($_POST['clientPhone'] ?? ''));
$selectedPlan = trim((string)($_POST['selectedPlan'] ?? ''));
$firstPayment = trim((string)($_POST['firstPayment'] ?? ''));
$persianDate = trim((string)($_POST['persianDate'] ?? ''));

$safeClient = preg_replace('/[^a-zA-Z0-9_-]+/', '-', $clientName);
$safeClient = trim($safeClient, '-');
if ($safeClient === '') {
    $safeClient = 'client';
}

$timestamp = gmdate('Ymd-His');
$random = bin2hex(random_bytes(4));
$baseName = sprintf('contract-%s-%s-%s', $safeClient, $timestamp, $random);

$pdfFilename = $baseName . '.pdf';
$jsonFilename = $baseName . '.json';

$pdfPath = $targetDir . DIRECTORY_SEPARATOR . $pdfFilename;
$metaPath = $targetDir . DIRECTORY_SEPARATOR . $jsonFilename;

if (!move_uploaded_file($_FILES['contract']['tmp_name'], $pdfPath)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Unable to save uploaded PDF']);
    exit;
}

$metadata = [
    'clientName' => $clientName,
    'clientPhone' => $clientPhone,
    'selectedPlan' => $selectedPlan,
    'firstPayment' => $firstPayment,
    'persianDate' => $persianDate,
    'savedAtUtc' => gmdate(DATE_ATOM),
    'pdfFile' => $pdfFilename,
];

if (file_put_contents($metaPath, json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Unable to save metadata']);
    exit;
}

echo json_encode([
    'ok' => true,
    'pdf' => 'signed-contracts/' . $pdfFilename,
    'metadata' => 'signed-contracts/' . $jsonFilename,
]);
