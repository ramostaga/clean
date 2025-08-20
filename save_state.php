<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header('Content-Type: application/json; charset=utf-8');

// adjust file path if needed
$stateFile = __DIR__ . '/state.json';

// set admin password here (change to your chosen password)
$ADMIN_PASSWORD = 'admin';

// read input
$raw = file_get_contents('php://input');
if (!$raw) { http_response_code(400); echo json_encode(["ok" => false, "error" => "No body"]); exit; }

$payload = json_decode($raw, true);
if ($payload === null) { http_response_code(400); echo json_encode(["ok" => false, "error" => "Invalid JSON"]); exit; }

if (!isset($payload['password']) || !is_string($payload['password'])) { http_response_code(401); echo json_encode(["ok" => false, "error" => "Missing password"]); exit; }

$pw = $payload['password'];

// dry-run: only verify password, do NOT write state
if (isset($payload['dry_run']) && $payload['dry_run']) {
    if ($pw === $ADMIN_PASSWORD) {
        echo json_encode(["ok" => true, "auth" => true]);
        exit;
    } else {
        http_response_code(403);
        echo json_encode(["ok" => false, "auth" => false, "error" => "Wrong password"]);
        exit;
    }
}

// normal save flow follows
if ($pw !== $ADMIN_PASSWORD) { http_response_code(403); echo json_encode(["ok" => false, "error" => "Wrong password"]); exit; }
if (!isset($payload['state'])) { http_response_code(400); echo json_encode(["ok" => false, "error" => "Missing state"]); exit; }

$state = $payload['state'];

// basic shape validation
if (!isset($state['dishesPeople']) || !is_array($state['dishesPeople']) || !isset($state['tablePeople']) || !is_array($state['tablePeople'])) {
    http_response_code(400); echo json_encode(["ok" => false, "error" => "Invalid state shape"]); exit;
}

// write atomically
$tmp = $stateFile . '.tmp';
if (file_put_contents($tmp, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
    http_response_code(500); echo json_encode(["ok" => false, "error" => "Could not write temp file"]); exit;
}
if (!rename($tmp, $stateFile)) {
    @unlink($tmp);
    http_response_code(500); echo json_encode(["ok" => false, "error" => "Could not move temp file"]); exit;
}

echo json_encode(["ok" => true, "saved" => $state]);
exit;
