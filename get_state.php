<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
// get_state.php
header('Content-Type: application/json; charset=utf-8');
// adjust file path if needed
$stateFile = __DIR__ . '/state.json';

// safe default state (keeps the same shape)
$default = array(
  "dishesPeople" => array("Alice","Bob","Charlie"),
  "tablePeople" => array("Dana","Evan","Fran"),
  "vacations" => array(),
  "taskOffs" => array(),
  "overrides" => new stdClass(),
  "startShift" => array("dishes" => 0, "table" => 0)
);

if (file_exists($stateFile)) {
    $contents = file_get_contents($stateFile);
    if ($contents === false) {
        echo json_encode($default);
        exit;
    }
    // validate JSON
    $data = json_decode($contents, true);
    if ($data === null) {
        // corrupted file -> return default
        echo json_encode($default);
        exit;
    }
    echo json_encode($data);
    exit;
} else {
    // no file -> return default
    echo json_encode($default);
    exit;
}
