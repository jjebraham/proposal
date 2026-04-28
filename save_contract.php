<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$rawBody = file_get_contents('php://input');
$data = json_decode($rawBody ?: '', true);

if (!is_array($data) || empty($data['pdfBase64'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing pdfBase64 payload']);
    exit;
}

$targetDir = __DIR__ . DIRECTORY_SEPARATOR . 'signed-contracts';
if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Failed to create storage directory']);
    exit;
}

$pdfBase64 = preg_replace('/^data:application\/pdf;base64,/', '', (string) $data['pdfBase64']);
$pdfBytes = base64_decode($pdfBase64, true);

if ($pdfBytes === false) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid base64 PDF']);
    exit;
}

$timestamp = gmdate('Ymd-His');
$random = bin2hex(random_bytes(4));
$baseName = sprintf('contract-%s-%s', $timestamp, $random);

$pdfPath = $targetDir . DIRECTORY_SEPARATOR . $baseName . '.pdf';
$metaPath = $targetDir . DIRECTORY_SEPARATOR . $baseName . '.json';

$metadata = [
    'clientName' => (string) ($data['clientName'] ?? ''),
    'clientPhone' => (string) ($data['clientPhone'] ?? ''),
    'selectedPlan' => (string) ($data['selectedPlan'] ?? ''),
    'firstPayment' => (string) ($data['firstPayment'] ?? ''),
    'persianDate' => (string) ($data['persianDate'] ?? ''),
    'savedAtUtc' => gmdate(DATE_ATOM),
    'pdfFile' => $baseName . '.pdf',
];

if (file_put_contents($pdfPath, $pdfBytes) === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Unable to save PDF']);
    exit;
}

if (file_put_contents($metaPath, json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Unable to save metadata']);
    exit;
}

echo json_encode([
    'ok' => true,
    'pdf' => 'signed-contracts/' . $baseName . '.pdf',
    'metadata' => 'signed-contracts/' . $baseName . '.json',
]);
