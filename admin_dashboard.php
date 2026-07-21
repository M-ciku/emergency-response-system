<?php
// admin_dashboard.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'db.php';

// Authentication verification
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Command Station - Incidents Overview</title>
    <link rel="stylesheet" href="style.css">
    <style>
    body {
        background: #f8fafc;
        color: #0f172a;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        margin: 0;
        padding: 0;
    }

    .header {
        background: #0f172a;
        color: #ffffff;
        padding: 16px 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .header h2 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .logout-link {
        color: #ef4444;
        font-weight: 600;
        text-decoration: none;
        font-size: 0.875rem;
        padding: 6px 12px;
        border: 1px solid rgba(239, 68, 68, 0.3);
        border-radius: 6px;
        transition: background 0.2s;
    }

    .logout-link:hover {
        background: rgba(239, 68, 68, 0.1);
    }

    .container-main {
        max-width: 1600px;
        margin: 28px auto;
        padding: 0 24px;
    }

    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 28px;
    }

    .metric-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
    }

    .metric-card .label {
        font-size: 0.8rem;
        text-transform: uppercase;
        font-weight: 700;
        color: #64748b;
        letter-spacing: 0.05em;
    }

    .metric-card .value {
        font-size: 2.25rem;
        font-weight: 800;
        color: #0f172a;
        margin-top: 8px;
    }

    .toolbar-panel {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px 10px 0 0;
        padding: 16px 20px;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        border-bottom: none;
    }

    .filter-group {
        display: flex;
        gap: 8px;
    }

    .filter-btn {
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        color: #475569;
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 0.813rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .filter-btn:hover,
    .filter-btn.active {
        background: #0f172a;
        color: #ffffff;
        border-color: #0f172a;
    }

    .search-box {
        padding: 7px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 0.875rem;
        width: 250px;
        outline: none;
    }

    .search-box:focus {
        border-color: #0f172a;
    }

    .export-btn {
        background: #0284c7;
        color: #ffffff;
        border: none;
        padding: 8px 14px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.813rem;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .export-btn:hover {
        background: #0369a1;
    }

    .table-responsive {
        overflow-x: auto;
        background: #ffffff;
        border-radius: 0 0 10px 10px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
    }

    .incident-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 0.875rem;
    }

    .incident-table th {
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        color: #64748b;
        font-size: 0.75rem;
        text-transform: uppercase;
        padding: 14px 16px;
        font-weight: 700;
    }

    .incident-table td {
        padding: 16px;
        border-bottom: 1px solid #e2e8f0;
        color: #334155;
    }

    .incident-table tr:hover td {
        background: #f8fafc;
    }

    .admin-btn {
        background: #ffffff;
        color: #0f172a;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        font-size: 0.813rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .admin-btn.btn-resolve {
        background: #10b981;
        color: #ffffff;
        border-color: #10b981;
    }

    .admin-btn.btn-resolve:hover {
        background: #059669;
    }

    .admin-btn.btn-delete {
        background: #ef4444;
        color: #ffffff;
        border-color: #dc2626;
    }

    .admin-btn.btn-delete:hover {
        background: #dc2626;
    }
    </style>
</head>

<body>
    <div class="header">
        <h2>🛠️ Administrator Dashboard</h2>
        <div class="header-actions">
            <span style="color: #94a3b8; font-size: 0.875rem;">Administrator Active</span>
            <a href="logout.php" class="logout-link">Secure Logout</a>
        </div>
    </div>

    <main class="container-main">
        <!-- Incidents Metrics Overview -->
        <div class="metrics-grid">
            <div class="metrics-grid">
                <div class="metric-card">
                    <span class="label">Total Incidents</span>
                    <span id="metric-total" class="value">0</span>
                </div>
                <div class="metric-card" style="border-left: 4px solid #f59e0b;">
                    <span class="label">Pending Incidents</span>
                    <span id="metric-waiting" class="value" style="color: #d97706;">0</span>
                </div>
                <div class="metric-card" style="border-left: 4px solid #10b981;">
                    <span class="label">Resolved Incidents</span>
                    <span id="metric-resolved" class="value" style="color: #059669;">0</span>
                </div>
            </div>
        </div>

        <!-- System Controls -->
        <div class="toolbar-panel">
            <div class="filter-group">
                <button class="filter-btn active" onclick="setAdminFilter('all', this)">All Records</button>
                <button class="filter-btn" onclick="setAdminFilter('waiting', this)">Pending</button>
                <button class="filter-btn" onclick="setAdminFilter('resolved', this)">Resolved Archive</button>
            </div>

            <div style="display: flex; gap: 12px; align-items: center;">
                <input type="text" id="searchInput" class="search-box"
                    placeholder="Search callers, refs, or categories..." onkeyup="filterTableSearch()">
                <button onclick="exportTableToCSV('incidents_report.csv')" class="export-btn">📊 Generate Report
                    (CSV)</button>
            </div>
        </div>

        <!-- Incidents Data Table -->
        <div class="table-responsive">
            <table class="incident-table" id="auditTable">
                <thead>
                    <tr>
                        <th>Incident Ref</th>
                        <th>Student / Caller</th>
                        <th>Contact Line</th>
                        <th>Category</th>
                        <th>Coordinates</th>
                        <th>Status</th>
                        <th>Logged Time</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody id="admin_table_body">
                    <!-- Incidents table data renders here dynamically -->
                </tbody>
            </table>
        </div>
    </main>

    <script>
    let currentAdminFilter = 'all';

    function setAdminFilter(filterType, btn) {
        currentAdminFilter = filterType;
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        loadAdminIncidents();
    }

    function loadAdminIncidents() {
        fetch(`fetch_incidents.php?role=admin&filter=${currentAdminFilter}`)
            .then(res => res.text())
            .then(data => {
                const body = document.getElementById("admin_table_body");
                if (body) {
                    body.innerHTML = data;
                }
                updateAdminMetrics();
                filterTableSearch();
            })
            .catch(err => console.error("Error fetching incidents:", err));
    }

    function updateAdminMetrics() {
        fetch('fetch_incidents.php?role=admin&filter=all')
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const rows = Array.from(doc.querySelectorAll('tr'));

                const validRows = rows.filter(row => !row.innerText.includes("No incidents recorded"));

                let total = validRows.length;
                let resolved = 0;
                let pending = 0;

                validRows.forEach(row => {
                    const rowText = row.innerText.toLowerCase();
                    if (rowText.includes('resolved') || rowText.includes('closed') || rowText.includes(
                            'completed')) {
                        resolved++;
                    } else {
                        pending++;
                    }
                });

                document.getElementById("metric-total").textContent = total;
                document.getElementById("metric-waiting").textContent = pending;
                document.getElementById("metric-resolved").textContent = resolved;
            })
            .catch(err => console.error("Metrics update error:", err));
    }

    function viewLocation(id, lat, lng) {
        if (!lat || !lng || lat == 0) {
            alert("No position lock available for this incident.");
            return;
        }
        window.open(`https://www.google.com/maps/search/?api=1&query=${lat},${lng}`, '_blank');
    }

    function resolveIncident(id) {
        if (confirm("Are you sure you want to mark this incident as resolved?")) {
            fetch("resolve_incident.php?id=" + encodeURIComponent(id))
                .then(res => {
                    if (res.ok) loadAdminIncidents();
                })
                .catch(err => console.error("Error resolving incident:", err));
        }
    }

    function deleteIncident(id) {
        if (confirm("⚠️ PERMANENT ACTION: Are you sure you want to delete this incident from records?")) {
            fetch("delete_incident.php?id=" + encodeURIComponent(id), {
                    method: 'POST'
                })
                .then(res => res.json())
                .then(response => {
                    if (response.status === 'success') {
                        loadAdminIncidents();
                    } else {
                        alert(response.message || "Failed to delete incident.");
                    }
                })
                .catch(err => console.error("Error deleting incident:", err));
        }
    }

    function filterTableSearch() {
        const query = document.getElementById("searchInput").value.toLowerCase();
        const rows = document.querySelectorAll("#admin_table_body tr");

        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(query) ? "" : "none";
        });
    }

    function updateAdminMetrics() {
        fetch('fetch_metrics.php')
            .then(res => res.json())
            .then(data => {
                document.getElementById("metric-total").textContent = data.total;
                document.getElementById("metric-waiting").textContent = data.pending;
                document.getElementById("metric-resolved").textContent = data.resolved;
            })
            .catch(err => console.error("Metrics update error:", err));
    }

    function exportTableToCSV(filename) {
        const rows = document.querySelectorAll("table tr");
        let csv = [];

        for (let i = 0; i < rows.length; i++) {
            let row = [],
                cols = rows[i].querySelectorAll("td, th");
            for (let j = 0; j < cols.length - 1; j++) {
                let cleanData = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, " ").replace(/"/g, '""');
                row.push('"' + cleanData + '"');
            }
            csv.push(row.join(","));
        }

        const csvFile = new Blob([csv.join("\n")], {
            type: "text/csv"
        });
        const downloadLink = document.createElement("a");
        downloadLink.download = filename;
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = "none";
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }

    setInterval(loadAdminIncidents, 3000);
    window.onload = loadAdminIncidents;
    </script>
</body>

</html>