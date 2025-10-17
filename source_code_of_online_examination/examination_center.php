<?php
// ===============================
// PHP: Exam Center Configuration
// ===============================
$examCenter = [
    "name" => "Kathmandu Examination Center",
    "lat" => 27.7172,  // fixed exam center latitude
    "lng" => 85.3240   // fixed exam center longitude
];

// Haversine Formula to calculate distance
function calculateDistance($lat1, $lon1, $lat2, $lon2, $unit = 'K') {
    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;

    if ($unit == "K") {
        return ($miles * 1.609344);
    } elseif ($unit == "N") {
        return ($miles * 0.8684);
    } else {
        return $miles;
    }
}

// If user coordinates are sent
if (isset($_GET['lat']) && isset($_GET['lng'])) {
    $user_lat = floatval($_GET['lat']);
    $user_lng = floatval($_GET['lng']);

    $distance = calculateDistance($examCenter['lat'], $examCenter['lng'], $user_lat, $user_lng, 'K');
    $speed = 40; // km/h assumed
    $time = $distance / $speed;
    $hours = floor($time);
    $minutes = round(($time - $hours) * 60);

    $response = [
        'distance' => round($distance, 2) . ' km',
        'time' => "{$hours} hr {$minutes} min"
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Examination Center</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f6f8fa;
            text-align: center;
            padding: 20px;
        }
        #map {
            height: 400px;
            width: 80%;
            margin: 20px auto;
            border-radius: 10px;
            border: 2px solid #ccc;
        }
        #info {
            background: white;
            padding: 15px;
            border-radius: 8px;
            display: inline-block;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        button {
            padding: 10px 15px;
            font-size: 16px;
            background: #0078d7;
            border: none;
            border-radius: 6px;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background: #005fa3;
        }
    </style>
</head>
<body>

    <h2>🏫 Visit the Examination Center</h2>
    <div id="map"></div>

    <button onclick="getUserLocation()">📍 Calculate Distance to Exam Center</button>
    <div id="info" style="margin-top:20px;">
        <p id="status">Click the button to find your distance.</p>
        <p id="distance"></p>
        <p id="time"></p>
    </div>

    <script>
        let map;
        const examCenter = { lat: <?= $examCenter['lat'] ?>, lng: <?= $examCenter['lng'] ?> };

        // Initialize Google Map
        function initMap() {
            map = new google.maps.Map(document.getElementById("map"), {
                center: examCenter,
                zoom: 14,
            });
            new google.maps.Marker({
                position: examCenter,
                map,
                title: "<?= $examCenter['name'] ?>",
            });
        }

        // Get user's current location and calculate distance
        function getUserLocation() {
            document.getElementById('status').textContent = "Getting your location...";
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((position) => {
                    const userLat = position.coords.latitude;
                    const userLng = position.coords.longitude;

                    fetch(`exam_center_page.php?lat=${userLat}&lng=${userLng}`)
                        .then(res => res.json())
                        .then(data => {
                            document.getElementById('status').textContent = "✅ Distance calculated successfully!";
                            document.getElementById('distance').textContent = "Distance: " + data.distance;
                            document.getElementById('time').textContent = "Estimated Travel Time: " + data.time;

                            // Show user marker
                            new google.maps.Marker({
                                position: { lat: userLat, lng: userLng },
                                map,
                                title: "Your Location",
                                icon: {
                                    url: "https://maps.google.com/mapfiles/ms/icons/blue-dot.png"
                                }
                            });
                        });
                }, () => {
                    document.getElementById('status').textContent = "⚠️ Location permission denied.";
                });
            } else {
                document.getElementById('status').textContent = "Geolocation not supported by your browser.";
            }
        }
    </script>

    <!-- Load Google Maps JS (no API key required just for map display) -->
    <script async defer src="https://maps.googleapis.com/maps/api/js?callback=initMap"></script>

</body>
</html>
