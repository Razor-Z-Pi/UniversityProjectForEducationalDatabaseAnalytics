<?php
header('Content-Type: application/json');
require_once '../models/Statistic.php';

$type = isset($_GET['type']) ? $_GET['type'] : 'monthly';

$statistic = new Statistic();
$data = $statistic->getChartData($type);

echo json_encode($data);
?>