<?php
// medic_dashboard.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'db.php';

// Safe authentication check
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medic Dispatch Terminal</title>
    <link rel="stylesheet" href="style.css">
    <style>
    /* Clean, professional minimalist command theme */
    body {
        background: #f1f5f9;
        color: #1e293b;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        margin: 0;
        padding: 0;
    }

    .header {
        background: #ffffff;
        color: #0f0f0f;
        padding: 16px 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #cbd5e1;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .header h2 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 700;
        letter-spacing: -0.5px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .system-status {
        font-size: 0.75rem;
        background: #e2e8f0;
        color: #475569;
        padding: 4px 8px;
        border-radius: 4px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .status-pulse {
        width: 8px;
        height: 8px;
        background: #10b981;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        }

        70% {
            transform: scale(1);
            box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
        }

        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
        }
    }

    /* High-Visibility Logout Button */
    .logout-btn {
        background-color: #dc2626 !important;
        color: #ffffff !important;
        border: 1px solid #b91c1c !important;
        font-weight: 700 !important;
        text-decoration: none !important;
        font-size: 0.875rem !important;
        padding: 8px 18px !important;
        border-radius: 6px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2) !important;
        transition: all 0.2s ease-in-out !important;
        cursor: pointer !important;
    }

    .logout-btn:hover {
        background-color: #b91c1c !important;
        border-color: #991b1b !important;
        box-shadow: 0 4px 6px rgba(185, 28, 28, 0.3) !important;
    }

    /* Structural Grid Layout */
    .dashboard-grid {
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 24px;
        max-width: 1600px;
        margin: 32px auto;
        padding: 0 24px;
    }

    /* Responsive grid for tablet/mobile screens */
    @media (max-width: 1024px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Left Control Sidebar */
    .sidebar-panel {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 24px;
        height: fit-content;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .sidebar-title {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        margin-top: 0;
        margin-bottom: 16px;
        font-weight: 700;
    }

    .counter-card {
        background: #0f172a;
        border-radius: 8px;
        padding: 24px;
        text-align: center;
        margin-bottom: 24px;
    }

    .counter-value {
        font-size: 3rem;
        font-weight: 800;
        color: #ffffff;
        display: block;
        line-height: 1;
        margin-bottom: 6px;
    }

    .counter-label {
        font-size: 0.85rem;
        color: #94a3b8;
        font-weight: 500;
        text-transform: capitalize;
    }

    /* Audio Status Indicator (Automatic) */
    .audio-notice {
        background: #dcfce7;
        border: 1px solid #86efac;
        padding: 12px;
        border-radius: 6px;
        font-size: 0.8rem;
        color: #166534;
        margin-bottom: 20px;
        text-align: center;
        font-weight: 600;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
    }

    .filter-menu {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .filter-btn {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #334155;
        padding: 10px 14px;
        border-radius: 6px;
        text-align: left;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.2s;
    }

    .filter-btn:hover,
    .filter-btn.active {
        background: #e2e8f0;
        color: #0f172a;
        border-color: #cbd5e1;
    }

    /* Right Main Feed */
    .feed-panel {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        /* Prevents container blowout */
    }

    .feed-header-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .feed-title {
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0;
        color: #0f172a;
    }

    /* TABLE SCROLLING & RESPONSIVE COLUMN FIXES */
    .table-container {
        width: 100%;
        overflow-x: auto;
        background: #ffffff;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }

    .incident-table {
        width: 100%;
        min-width: 900px;
        /* Forces full width layout before scrolling horizontally */
        border-collapse: collapse;
        table-layout: auto;
        /* Dynamic sizing to prevent vertical letter crushing */
        text-align: left;
        font-size: 0.875rem;
    }

    .incident-table th,
    .incident-table td {
        padding: 14px 16px;
        vertical-align: middle;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
        /* Prevents linebreaks within headers */
    }

    .incident-table th {
        background: #f8fafc;
        color: #475569;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
    }

    .incident-table tr:hover td {
        background: #f8fafc;
    }

    .action-btn {
        background: #ffffff;
        color: #1e293b;
        border: 1px solid #cbd5e1;
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        font-size: 0.813rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s;
        white-space: nowrap;
    }

    .action-btn:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
    }

    .call-link:hover {
        background-color: #e0f2fe !important;
        border-color: #38bdf8 !important;
        text-decoration: underline !important;
    }
    </style>
</head>

<body>

    <header class="header">
        <h2>
            Medic Dispatch Terminal
            <span class="system-status"><span class="status-pulse"></span> Persistent Stream Active</span>
        </h2>
        <a href="logout.php" class="logout-btn">🔒 Logout</a>
    </header>

    <main class="dashboard-grid">
        <!-- Sidebar Navigation Controls -->
        <aside class="sidebar-panel">
            <h3 class="sidebar-title">Terminal State</h3>
            <div class="counter-card">
                <span id="active-counter" class="counter-value">0</span>
                <span id="counter-status-label" class="counter-label">Waiting Cases</span>
            </div>

            <!-- Automatic Live Dispatch Audio Indicator -->
            <div id="audioGate" class="audio-notice" onclick="toggleAudioState()">
                ✅ Live Dispatch Audio Alerts Active
            </div>

            <h3 class="sidebar-title" style="margin-top: 32px;">Triage Filter</h3>
            <nav class="filter-menu">
                <button class="filter-btn active" onclick="setFilter('waiting', this)">
                    <span>Waiting Actions</span>
                </button>

                <button class="filter-btn" onclick="setFilter('resolved', this)">
                    <span>Resolved Cases</span>
                </button>
            </nav>
        </aside>

        <!-- Dynamic Feed Block -->
        <section class="feed-panel">
            <div class="feed-header-wrapper">
                <h3 id="feed-header-title" class="feed-title">Live Dispatch Feed (Waiting)</h3>
            </div>

            <div class="table-container">
                <table class="incident-table">
                    <thead>
                        <tr>
                            <th>Ref #</th>
                            <th>Patient Name</th>
                            <th>Contact</th>
                            <th>Emergency Type</th>
                            <th>GPS Location</th>
                            <th>Status</th>
                            <th>Time</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="incident_table_body">
                        <!-- Dynamic rows output here -->
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
    let activeIncidentCount = 0;
    let initialLoad = true;
    let currentFilter = 'waiting';

    let audioUnlocked = false;
    let audioCtx = null;
    let sirenInterval = null;
    let sirenTimeout = null;
    let isSirenPlaying = false;

    // Automatic Unlocking for Web Audio API
    function unlockAudioStream() {
        if (!audioCtx) {
            audioCtx = new(window.AudioContext || window.webkitAudioContext)();
        }
        if (audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
        audioUnlocked = true;

        const gate = document.getElementById("audioGate");
        if (gate && !isSirenPlaying) {
            gate.style.background = "#dcfce7";
            gate.style.borderColor = "#86efac";
            gate.style.color = "#166534";
            gate.innerHTML = "✅ Live Dispatch Audio Alerts Active";
        }
    }

    function toggleAudioState() {
        if (isSirenPlaying) {
            stopEmergencySiren();
        } else {
            unlockAudioStream();
        }
    }

    // 30-Second Continuous Emergency Siren Generator
    function playEmergencySiren30Sec() {
        if (!audioUnlocked) unlockAudioStream();
        if (!audioCtx) return;

        stopEmergencySiren();

        isSirenPlaying = true;
        const gate = document.getElementById("audioGate");
        if (gate) {
            gate.style.background = "#fee2e2";
            gate.style.borderColor = "#fca5a5";
            gate.style.color = "#991b1b";
            gate.innerHTML = "🚨 EMERGENCY ALARM RINGING (Click to Silence)";
        }

        let toggleFreq = false;

        // Alternating dual-tone siren
        sirenInterval = setInterval(() => {
            try {
                if (audioCtx.state === 'suspended') audioCtx.resume();

                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();

                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(toggleFreq ? 960 : 770, audioCtx.currentTime);

                gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.35);

                osc.connect(gain);
                gain.connect(audioCtx.destination);

                osc.start();
                osc.stop(audioCtx.currentTime + 0.35);

                toggleFreq = !toggleFreq;
            } catch (e) {
                console.error("Siren loop error:", e);
            }
        }, 400);

        // Automatically stop siren after 30 seconds
        sirenTimeout = setTimeout(() => {
            stopEmergencySiren();
        }, 30000);
    }

    function stopEmergencySiren() {
        if (sirenInterval) {
            clearInterval(sirenInterval);
            sirenInterval = null;
        }
        if (sirenTimeout) {
            clearTimeout(sirenTimeout);
            sirenTimeout = null;
        }
        isSirenPlaying = false;

        const gate = document.getElementById("audioGate");
        if (gate) {
            gate.style.background = "#dcfce7";
            gate.style.borderColor = "#86efac";
            gate.style.color = "#166534";
            gate.innerHTML = "✅ Live Dispatch Audio Alerts Active";
        }
    }

    function setFilter(type, buttonElement) {
        currentFilter = type;
        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        buttonElement.classList.add('active');

        const statusLabel = document.getElementById("counter-status-label");
        const feedHeader = document.getElementById("feed-header-title");

        if (type === 'resolved') {
            if (statusLabel) statusLabel.textContent = "Resolved Cases";
            if (feedHeader) feedHeader.textContent = "Archived Dispatch Feed (Resolved)";
        } else if (type === 'critical') {
            if (statusLabel) statusLabel.textContent = "Critical Waiting Cases";
            if (feedHeader) feedHeader.textContent = "Live Dispatch Feed (High Priority)";
        } else {
            if (statusLabel) statusLabel.textContent = "Waiting Cases";
            if (feedHeader) feedHeader.textContent = "Live Dispatch Feed (Waiting)";
        }

        loadIncidents();
    }

    function loadIncidents() {
        fetch(`fetch_incidents.php?role=medic&filter=${currentFilter}`)
            .then(response => {
                if (!response.ok) throw new Error("Network response error");
                return response.text();
            })
            .then(data => {
                const targetContainer = document.getElementById("incident_table_body");
                if (targetContainer) {
                    targetContainer.innerHTML = data;
                }

                let currentLiveCount = 0;
                if (!data.includes("No incidents recorded")) {
                    currentLiveCount = (data.match(/<tr/g) || []).length;
                }

                // Trigger 30-Second Siren on new emergency detection
                if (!initialLoad && currentLiveCount > activeIncidentCount && currentFilter !== 'resolved') {
                    playEmergencySiren30Sec();
                }

                activeIncidentCount = currentLiveCount;
                initialLoad = false;

                const counterElement = document.getElementById("active-counter");
                if (counterElement) {
                    counterElement.textContent = activeIncidentCount;
                }
            })
            .catch(err => console.error("Async stream error:", err));
    }

    function viewLocation(id, lat, lng) {
        if (!lat || !lng || lat == 0) {
            alert("No GPS position lock available for this incident.");
            return;
        }
        var mapUrl = "https://www.google.com/maps/search/?api=1&query=" + encodeURIComponent(lat) + "," +
            encodeURIComponent(lng);
        window.open(mapUrl, '_blank');
    }

    function acknowledgeIncident(id) {
        if (!confirm("Are you sure you want to mark this incident as Resolved?")) {
            return;
        }

        fetch("acknowledge_medic.php?id=" + encodeURIComponent(id))
            .then(response => {
                if (response.ok) {
                    stopEmergencySiren(); // Stop alarm if playing
                    loadIncidents(); // Instantly refresh the table feed
                } else {
                    response.text().then(msg => alert("Failed to resolve incident: " + msg));
                }
            })
            .catch(err => {
                console.error("Acknowledgement network error:", err);
                alert("Network error occurred while updating status.");
            });
    }

    // Automatic silent background unlock on first micro-gesture
    const autoUnlockEvents = ['click', 'mousemove', 'keydown', 'touchstart', 'scroll'];

    function silentAutoActivate() {
        unlockAudioStream();
        autoUnlockEvents.forEach(evt => document.removeEventListener(evt, silentAutoActivate));
    }
    autoUnlockEvents.forEach(evt => document.addEventListener(evt, silentAutoActivate, {
        once: true
    }));

    // Poll live feed every 3 seconds (keeps session active via server contact)
    setInterval(loadIncidents, 3000);

    window.onload = function() {
        unlockAudioStream();
        loadIncidents();
    };
    </script>
</body>

</html>