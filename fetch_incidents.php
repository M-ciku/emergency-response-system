<?php
// fetch_incidents.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'db.php';

$role = $_GET['role'] ?? 'admin';
$filter = $_GET['filter'] ?? 'all';

// Base SQL query
$sql = "SELECT * FROM incidents";

if ($filter === 'waiting' || $filter === 'pending') {
    $sql .= " WHERE status = 'pending'";
} elseif ($filter === 'critical') {
    $sql .= " WHERE status = 'pending' AND emergency_type LIKE '%Critical%'";
} elseif ($filter === 'resolved') {
    $sql .= " WHERE status = 'resolved'";
}

$sql .= " ORDER BY created_at DESC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $ref = 'INC-' . $id;
        $caller = htmlspecialchars($row['student_name'] ?? 'Unknown');
        
        // Clean phone number for tel: link & format display button
        $rawPhone = preg_replace('/[^0-9+]/', '', $row['phone'] ?? '');
        $phone = htmlspecialchars($row['phone'] ?? 'N/A');

        $phoneDisplay = (!empty($rawPhone)) 
            ? "<a href='tel:{$rawPhone}' class='call-link' style='color:#0284c7; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:4px; padding:4px 8px; background:#f0f9ff; border-radius:4px; border:1px solid #bae6fd;'>📞 {$phone}</a>" 
            : "<span style='color:#94a3b8;'>N/A</span>";

        $category = htmlspecialchars($row['emergency_type'] ?? 'General Incident');
        $lat = $row['latitude'] ?? 0;
        $lng = $row['longitude'] ?? 0;
        $status = strtolower($row['status'] ?? 'pending');
        $time = date("d M Y, H:i", strtotime($row['created_at']));

        // Format status badge
        $statusBadge = ($status === 'resolved') 
            ? '<span style="color:#059669; font-weight:700; background:#d1fae5; padding:4px 8px; border-radius:4px; display:inline-block;">Resolved</span>' 
            : '<span style="color:#d97706; font-weight:700; background:#fef3c7; padding:4px 8px; border-radius:4px; display:inline-block;">Pending</span>';

        // GPS Coordinates cell containing Map View button
        $coordsDisplay = ($lat != 0 && $lng != 0) 
            ? "<div style='font-size:0.8rem; color:#475569; margin-bottom:4px;'>{$lat}, {$lng}</div>"
              . "<button class='action-btn' onclick='viewLocation({$id}, {$lat}, {$lng})'>📍 Map View</button>" 
            : "<span style='color:#94a3b8; font-size:0.8rem;'>No Coords</span>";

       echo "<tr>";
echo "<td><strong>{$ref}</strong></td>";
echo "<td><strong>{$caller}</strong></td>";
echo "<td>{$phoneDisplay}</td>"; // <-- Updated here
echo "<td><span style='background:#f1f5f9; padding:4px 8px; border-radius:4px; font-weight:600;'>{$category}</span></td>";
echo "<td>{$coordsDisplay}</td>";
echo "<td>{$statusBadge}</td>";
echo "<td>{$time}</td>";
echo "<td style='text-align: right;'>";
if ($status !== 'resolved') {
    echo "<button class='action-btn' style='background:#10b981; color:#fff; border:none; padding:8px 16px; border-radius:6px; font-weight:700; cursor:pointer;' onclick='acknowledgeIncident({$id})'>✓ Resolve</button>";
} else {
    echo "<span style='color:#64748b; font-size:0.8rem; font-style:italic;'>Completed</span>";
}
echo "</td>";
echo "</tr>";
    }
} else {
    echo "<tr><td colspan='8' style='text-align:center; padding: 24px; color:#64748b;'>No incidents recorded in this category.</td></tr>";
}
?>