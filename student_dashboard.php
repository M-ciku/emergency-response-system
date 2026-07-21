<?php
// student_dashboard.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'db.php';

// Check if request is an AJAX/Fetch call
$rawInput = file_get_contents('php://input');
$jsonInput = json_decode($rawInput, true);
$isAjax = (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) 
          || ($jsonInput && is_array($jsonInput));

// Safe authentication check
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'student') {
    if ($isAjax) {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(401);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Session expired. Please re-login in another tab.'
        ]);
        exit();
    } else {
        header("Location: login.php");
        exit();
    }
}
// ---------------------------------------------------------
// 1. Check and handle incoming JSON / AJAX fetch requests
// ---------------------------------------------------------
$rawInput = file_get_contents('php://input');
$jsonInput = json_decode($rawInput, true);

$isAjax = (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) 
          || ($jsonInput && is_array($jsonInput));

if ($isAjax) {
    // Clear output buffer to remove any unintended PHP warnings/notices/whitespace
    if (ob_get_length()) {
        ob_clean();
    }
    
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
    header('Content-Type: application/json; charset=utf-8');

    $name             = !empty($jsonInput['name']) ? $jsonInput['name'] : ($_SESSION['name'] ?? 'Student');
    $phone            = !empty($jsonInput['phone']) ? $jsonInput['phone'] : ($_SESSION['phone'] ?? 'N/A');
    $lat              = $jsonInput['latitude'] ?? '0';
    $lng              = $jsonInput['longitude'] ?? '0';
    $emergency_type   = $jsonInput['emergency_type'] ?? '';
    $custom_emergency = $jsonInput['custom_emergency'] ?? '';

    $type = ($emergency_type === 'Other' && !empty($custom_emergency)) ? trim($custom_emergency) : $emergency_type;
    if (empty($type)) {
        $type = "CRITICAL SOS ALERT";
    }

    $medical_types = ['Medical', 'Panic Attack', 'Asthma Attack'];
    $target_dashboard = in_array($type, $medical_types) ? 'both' : 'admin';

    $stmt = $conn->prepare("INSERT INTO incidents (student_name, phone, emergency_type, latitude, longitude, target_dashboard) VALUES (?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'SQL Prepare failed: ' . $conn->error]);
        exit();
    }

    $stmt->bind_param("ssssss", $name, $phone, $type, $lat, $lng, $target_dashboard);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Emergency dispatched securely.']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database storage failure: ' . $stmt->error]);
    }
    
    // Terminate immediately so no HTML document gets appended
    exit(); 
}

// ---------------------------------------------------------
// 2. Standard HTML Form POST Fallback
// ---------------------------------------------------------
if (isset($_POST['report_emergency'])) {
    $name             = $_POST['name'] ?? ($_SESSION['name'] ?? 'Student');
    $phone            = $_POST['phone'] ?? ($_SESSION['phone'] ?? 'N/A');
    $lat              = $_POST['latitude'] ?? '0';
    $lng              = $_POST['longitude'] ?? '0';
    $emergency_type   = $_POST['emergency_type'] ?? '';
    $custom_emergency = $_POST['custom_emergency'] ?? '';

    $type = ($emergency_type === 'Other' && !empty($custom_emergency)) ? trim($custom_emergency) : $emergency_type;
    if (empty($type)) {
        $type = "CRITICAL SOS ALERT";
    }

    $medical_types = ['Medical', 'Panic Attack', 'Asthma Attack'];
    $target_dashboard = in_array($type, $medical_types) ? 'both' : 'admin';

    $stmt = $conn->prepare("INSERT INTO incidents (student_name, phone, emergency_type, latitude, longitude, target_dashboard) VALUES (?, ?, ?, ?, ?, ?)");
    
    if ($stmt) {
        $stmt->bind_param("ssssss", $name, $phone, $type, $lat, $lng, $target_dashboard);
        $stmt->execute();
    }
    
    header("Location: student_dashboard.php?success=1");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Emergency Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="header">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? 'Student'); ?></h2>
        <a href="logout.php">Logout</a>
    </div>

    <main class="dashboard-layout">
        <section class="container-main">

            <div id="dynamic-alert-banner"></div>

            <?php if(isset($_GET['success'])): ?>
            <div class="msg-banner success-banner"
                style="background-color: #d1e7dd; color: #0f5132; padding: 15px; border-radius: 6px; margin-bottom: 20px; font-weight: bold; border: 1px solid #badbcc;">
                ✨ Your message has been sent and dispatch streams are active! Help is on the way.
            </div>
            <?php endif; ?>

            <div class="first-aid-panel">
                <h4>⏱️ Immediate Safety &amp; First Aid Actions</h4>
                <ol id="firstAidSteps" class="first-aid-steps"></ol>
            </div>

            <form id="emergencyForm" method="POST">
                <input type="hidden" id="student_name_cache" name="name"
                    value="<?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?>">
                <input type="hidden" id="student_phone_cache" name="phone"
                    value="<?php echo htmlspecialchars($_SESSION['phone'] ?? ''); ?>">
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">

                <div class="form-group">
                    <h3>Select Type of Incident</h3>
                    <select name="emergency_type" id="emergency_type" class="input-select"
                        onchange="handleEmergencyChange()">
                        <option value="">-- General / Unspecified Critical Alert --</option>
                        <option value="Medical">General Medical Emergency</option>
                        <option value="Panic Attack">Panic Attack</option>
                        <option value="Asthma Attack">Asthma Attack</option>
                        <option value="Fire">Fire Outbreak</option>
                        <option value="Assault/Security">Security Threat / Assault</option>
                        <option value="Other">Other (Type custom emergency...)</option>
                    </select>

                    <div id="customInputContainer" class="custom-input-container" style="display:none;">
                        <input type="text" id="custom_emergency" name="custom_emergency" class="input-text"
                            placeholder="Please specify your emergency context here...">
                    </div>
                </div>

                <div id="gpsStatus" class="gps-status"
                    style="margin-bottom: 15px; font-weight: bold; font-size: 0.9rem;">
                    <span id="locationStatus">🔒 Securing connection...</span>
                </div>

                <div class="critical-box">
                    <h3 style="color: red; margin-bottom: 4px;">TRIGGER ALARM</h3>
                    <p style="font-size: 0.8rem; margin-bottom: 20px;">
                        Clicking below will instantly dispatch your profile and live location to both Admin and Medic
                        emergency terminals.
                    </p>
                    <div class="pulse-wrapper">
                        <button type="submit" id="sos-btn" name="report_emergency" class="action-sos"
                            style="cursor:pointer;">SOS</button>
                    </div>
                </div>
            </form>
        </section>
    </main>

    <script>
    const firstAidInstructions = {
        "": [
            "Stay calm and assess your immediate surroundings for hazards.",
            "Find a safe, well-lit location if possible.",
            "Keep your phone near you and stay on this page to let GPS lock your signal."
        ],
        "Medical": [
            "Check if the victim is conscious and breathing normally.",
            "If bleeding heavily, apply firm, direct pressure to the wound with a clean cloth.",
            "Do not move an injured person unless they are in immediate danger."
        ],
        "Panic Attack": [
            "Inhale slowly through your nose for 4 seconds, hold for 4, then exhale for 4.",
            "Look around and name 5 things you can see, 4 you can touch, and 3 you can hear.",
            "Remind yourself that this feeling is temporary and you are safe."
        ],
        "Asthma Attack": [
            "Sit up completely straight—do not lie down.",
            "Take slow, steady deep breaths.",
            "Use your rescue inhaler immediately (usually blue) up to 10 puffs if needed."
        ],
        "Fire": [
            "If there is heavy smoke, drop to your knees and crawl beneath it to escape.",
            "Before opening doors, check them with the back of your hand; do not open hot doors.",
            "Once outside, stay out. Never re-enter the building."
        ],
        "Assault/Security": [
            "Run to a crowded or secure place immediately.",
            "Call for help loudly if it is safe.",
            "Lock yourself in a safe room if necessary."
        ],
        "Other": [
            "Stay calm and remove yourself from danger.",
            "Keep your phone on.",
            "Wait for emergency responders."
        ]
    };

    function getLocation(callback = null) {
        const status = document.getElementById("locationStatus");
        if (!navigator.geolocation) {
            if (status) status.innerHTML = "❌ Location services not supported";
            return;
        }
        const geoOptions = {
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 10000
        };

        navigator.geolocation.getCurrentPosition(
            function(position) {
                document.getElementById("latitude").value = position.coords.latitude.toFixed(6);
                document.getElementById("longitude").value = position.coords.longitude.toFixed(6);
                if (status) status.innerHTML = "🛡️ Emergency Dispatch Ready";
                if (callback) callback();
            },
            function(error) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        document.getElementById("latitude").value = position.coords.latitude.toFixed(6);
                        document.getElementById("longitude").value = position.coords.longitude.toFixed(6);
                        if (status) status.innerHTML = "🛡️ Emergency Dispatch Ready (Low Accuracy)";
                        if (callback) callback();
                    },
                    function() {
                        if (status) status.innerHTML = "⚠️ Location Locked. Submit anyway.";
                        if (callback) callback();
                    }, {
                        enableHighAccuracy: false,
                        timeout: 5000,
                        maximumAge: 60000
                    }
                );
            },
            geoOptions
        );
    }

    function handleEmergencyChange() {
        const select = document.getElementById("emergency_type");
        const customDiv = document.getElementById("customInputContainer");
        const customInput = document.getElementById("custom_emergency");
        const list = document.getElementById("firstAidSteps");

        if (select.value === "Other") {
            customDiv.style.display = "block";
            customInput.required = true;
        } else {
            customDiv.style.display = "none";
            customInput.required = false;
        }

        list.innerHTML = "";
        const steps = firstAidInstructions[select.value] || firstAidInstructions["Other"];
        steps.forEach(step => {
            const li = document.createElement("li");
            li.textContent = step;
            list.appendChild(li);
        });
    }

    function executeAjaxSosSubmit() {
        const sosButton = document.getElementById("sos-btn");
        sosButton.disabled = true;
        sosButton.innerText = "SENDING...";

        const payload = {
            name: document.getElementById("student_name_cache").value,
            phone: document.getElementById("student_phone_cache").value,
            latitude: document.getElementById("latitude").value,
            longitude: document.getElementById("longitude").value,
            emergency_type: document.getElementById("emergency_type").value,
            custom_emergency: document.getElementById("custom_emergency").value
        };

        fetch('student_dashboard.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(async response => {
                const text = await response.text();

                // Handle Session Expiration cleanly
                if (response.status === 401) {
                    alert("🔒 Your session has expired. Redirecting to login...");
                    window.location.href = "login.php";
                    return;
                }

                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error("Server Returned Non-JSON Output:", text);
                    alert("Server Error Diagnostic:\n" + text.substring(0, 300));
                    throw new Error("Server returned HTML instead of JSON.");
                }
            })
            .then(data => {
                if (!data) return;

                if (data.status === 'success') {
                    verifyBroadcastStreamReceipt();
                } else {
                    alert("Dispatch Notice: " + (data.message || "Unknown error."));
                    sosButton.disabled = false;
                    sosButton.innerText = "SOS";
                }
            })
            .catch(err => {
                console.error("AJAX Error Details:", err);
                sosButton.disabled = false;
                sosButton.innerText = "SOS";
            });
    }

    function verifyBroadcastStreamReceipt() {
        fetch("fetch_incidents.php?role=student")
            .then(response => response.text())
            .then(htmlStream => {
                const studentName = document.getElementById("student_name_cache").value;
                const bannerContainer = document.getElementById("dynamic-alert-banner");
                const sosButton = document.getElementById("sos-btn");

                if (htmlStream.includes(studentName)) {
                    bannerContainer.innerHTML = `
                    <div style="background-color: #059669; color: white; padding: 18px; border-radius: 8px; margin-bottom: 25px; font-weight: bold; border: 2px solid #047857; box-shadow: 0 4px 12px rgba(5,150,105,0.3);">
                        🚨 CONFIRMED: Your emergency broadcast request has successfully registered on the active dispatch streams! Responders are tracking your coordinates now.
                    </div>`;
                } else {
                    bannerContainer.innerHTML = `
                    <div style="background-color: #2563eb; color: white; padding: 18px; border-radius: 8px; margin-bottom: 25px; font-weight: bold; border: 2px solid #1d4ed8;">
                        ✅ DISPATCH SENT: SOS transmission reached the system server. Emergency crews are deploying.
                    </div>`;
                }

                sosButton.disabled = false;
                sosButton.innerText = "SOS";
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            })
            .catch(err => {
                console.error("Stream audit validation error: ", err);
            });
    }

    window.onload = function() {
        handleEmergencyChange();
        getLocation();

        const form = document.getElementById("emergencyForm");
        form.addEventListener("submit", function(e) {
            e.preventDefault();

            if (document.getElementById("latitude").value && document.getElementById("longitude").value) {
                executeAjaxSosSubmit();
            } else {
                document.getElementById("locationStatus").innerHTML = "⚡ Forcing Instant Location Sync...";
                getLocation(function() {
                    executeAjaxSosSubmit();
                });
            }
        });
    };
    </script>
</body>

</html>