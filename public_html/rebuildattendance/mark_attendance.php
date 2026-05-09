<?php
session_start();
require_once __DIR__ . '/config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$userId = $_SESSION['user_id'];

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);
$latitude  = floatval($input['latitude'] ?? 0);
$longitude = floatval($input['longitude'] ?? 0);

// --- DUPLICATE CHECK ---
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT id FROM attendance WHERE user_id = ? AND date = ?");
$stmt->execute([$userId, $today]);
if ($stmt->fetch()) {
    echo json_encode([
        'success' => false,
        'message' => 'You have already marked attendance today.'
    ]);
    exit;
}

// Check for Admin Override (Global Setting)
$overrideStmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'location_override'");
$overrideStmt->execute();
$isOverride = ($overrideStmt->fetchColumn() === 'on');

if (!$isOverride) {
    // --- TIME WINDOW CHECK (Only if override is OFF) ---
    $currentHour = (int)date('H');
    $currentMinute = (int)date('i');
    $timeInMinutes = $currentHour * 60 + $currentMinute;

    // 4:00 AM = 240 minutes, 8:00 AM = 480 minutes
    if ($timeInMinutes < 240 || $timeInMinutes >= 480) {
        echo json_encode([
            'success' => false,
            'message' => 'Attendance is closed. Please mark attendance between 4:00 AM and 8:00 AM.'
        ]);
        exit;
    }
}

// --- LOCATION CHECK (Haversine formula) ---
$centerLat = 20.24532;
$centerLng = 85.81090;
$maxRadius = 1.0; // km (1000 meters)

if (!$isOverride) {
    function haversineDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    $distance = haversineDistance($centerLat, $centerLng, $latitude, $longitude);

    if ($distance > $maxRadius) {
        echo json_encode([
            'success'  => false,
            'message'  => 'You must be inside the fitness location to mark attendance.',
            'distance' => round($distance, 2)
        ]);
        exit;
    }
}

// --- MARK ATTENDANCE ---
$time = date('H:i:s');
$stmt = $pdo->prepare("INSERT INTO attendance (user_id, date, time, latitude, longitude) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$userId, $today, $time, $latitude, $longitude]);

// Calculate new streak
$streakQuery = $pdo->prepare("SELECT date FROM attendance WHERE user_id = ? ORDER BY date DESC");
$streakQuery->execute([$userId]);
$dates = $streakQuery->fetchAll(PDO::FETCH_COLUMN);

$streak = 0;
$checkDate = new DateTime();
foreach ($dates as $d) {
    if ($d === $checkDate->format('Y-m-d')) {
        $streak++;
        $checkDate->modify('-1 day');
    } else {
        break;
    }
}

echo json_encode([
    'success'   => true,
    'message'   => 'Attendance marked successfully for today!',
    'streak'    => $streak,
    'time'      => date('h:i A'),
    'latitude'  => $latitude,
    'longitude' => $longitude
]);
