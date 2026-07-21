/* eslint-env browser */
/* global alert, confirm */

// Modernized async Fetch API logic
function loadIncidents() {
    fetch("fetch_incidents.php")
        .then(response => {
            if (!response.ok) throw new Error("Network response error");
            return response.text();
        })
        .then(data => {
            const container = document.getElementById("incident_table_body");
            if (container) {
                container.innerHTML = data;
            }
        })
        .catch(error => console.error("Error loading incidents:", error));
}

function viewLocation(id, lat, lng) {
    if (!lat || !lng || lat === 0 || lat === "0") {
        alert("No position lock available.");
        return;
    }
    const mapUrl = "https://www.google.com/maps/search/?api=1&query=" + encodeURIComponent(lat) + "," + encodeURIComponent(lng);
    window.open(mapUrl, "_blank");
}

function resolveIncident(id) {
    fetch("resolve_incident.php?id=" + encodeURIComponent(id))
        .then(response => {
            if (response.ok) loadIncidents();
        })
        .catch(error => console.error("Error resolving incident:", error));
}

function deleteIncident(id) {
    if (confirm("Are you sure you want to permanently delete this incident record?")) {
        fetch("delete_incident.php?id=" + encodeURIComponent(id))
            .then(response => {
                if (response.ok) loadIncidents();
            })
            .catch(error => console.error("Error deleting incident:", error));
    }
}

// Polling interval engine
setInterval(loadIncidents, 3000);
window.onload = loadIncidents;