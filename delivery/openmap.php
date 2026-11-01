<?php
ob_start();
include("connect.php");
if (!isset($_SESSION['name']) || $_SESSION['name'] == '') {
    header("Location: deliverylogin.php");
    exit();
}
$name = $_SESSION['name'];
$city = $_SESSION['city']; // Get distributor's city
$id = $_SESSION['Did'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Location Map - Feed Forward</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css"
    integrity="sha256-kLaT2GOSpHechhsozzB+flnD+zUyjE2LlfWPgU04xyI="
    crossorigin=""/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,200;0,300;0,500;0,700;0,800;1,400;1,600&display=swap');

        :root {
            --brand: #003d3a;
            --brand-2: #0ea5a4;
            --ink: #14212b;
            --muted-ink: #41515e;
            --surface: #ffffff;
            --border: #e6efee;
            --shadow: 0 10px 30px rgba(0, 61, 58, 0.12);
            --shadow-sm: 0 6px 18px rgba(0, 61, 58, 0.12);
            --success: #10b981;
            --error: #ef4444;
            --warning: #f59e0b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(180deg, #f6fbfb, #eef6f5);
            color: var(--ink);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: 800;
            color: var(--ink);
            text-decoration: none;
        }
        .brand-name {
            color: var(--brand);
            background: linear-gradient(90deg, var(--brand), var(--brand-2));
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
        }

        .nav-link {
            color: var(--ink);
            text-decoration: none;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .nav-link:hover {
            color: var(--brand);
            background: rgba(14, 165, 164, 0.1);
            transform: translateY(-2px);
        }
        .nav-link.active {
            color: var(--brand);
            background: rgba(14, 165, 164, 0.15);
            font-weight: 600;
        }

        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
            gap: 4px;
        }
        .hamburger span {
            width: 25px;
            height: 3px;
            background: var(--ink);
            transition: all 0.3s ease;
        }

        /* Welcome Section */
        .welcome-section {
            text-align: center;
            padding: 3rem 2rem;
            background: linear-gradient(135deg, var(--brand) 0%, var(--brand-2) 100%);
            color: white;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        .welcome-section::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(800px 400px at 0% -10%, rgba(255, 255, 255, 0.1), transparent 60%),
                radial-gradient(600px 300px at 100% 110%, rgba(255, 255, 255, 0.1), transparent 60%);
            z-index: 0;
        }

        .welcome-content {
            position: relative;
            z-index: 2;
        }

        .welcome-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .welcome-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            font-weight: 300;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Map Section */
        .map-section {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto 3rem;
        }

        .section-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--brand);
            margin-bottom: 0.5rem;
        }

        .section-subtitle {
            color: var(--muted-ink);
            font-size: 1rem;
        }

        .map-container {
            background: var(--surface);
            border-radius: 25px;
            box-shadow: var(--shadow);
            overflow: hidden;
            border: 1px solid var(--border);
            margin-bottom: 2rem;
        }

        .map-wrapper {
            position: relative;
        }

        #map-container {
            width: 100%;
            height: 500px;
            z-index: 2;
        }

        .map-overlay {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            z-index: 1000;
            max-width: 300px;
        }

        .overlay-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--brand);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .overlay-content {
            font-size: 0.85rem;
            color: var(--muted-ink);
            line-height: 1.4;
        }

        /* Location Info Cards */
        .location-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-card {
            background: var(--surface);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            transition: all 0.3s ease;
            text-align: center;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
        }

        .info-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: white;
        }

        .info-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--brand);
            margin-bottom: 0.5rem;
        }

        .info-content {
            color: var(--muted-ink);
            font-size: 0.9rem;
            line-height: 1.5;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .action-btn {
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
            background: linear-gradient(135deg, var(--brand-2), var(--brand));
        }

        .action-btn.secondary {
            background: linear-gradient(135deg, var(--muted-ink), #64748b);
        }

        .action-btn.secondary:hover {
            background: linear-gradient(135deg, #64748b, var(--muted-ink));
        }

        /* Loading State */
        .loading {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            color: var(--muted-ink);
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid var(--border);
            border-top: 4px solid var(--brand);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 1rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Error State */
        .error-state {
            text-align: center;
            padding: 3rem;
            color: var(--error);
        }

        .error-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.7;
        }

        .error-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .error-description {
            font-size: 1rem;
            opacity: 0.8;
            margin-bottom: 1rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hamburger {
                display: flex;
            }

            .nav-menu {
                position: fixed;
                top: 80px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 80px);
                background: var(--surface);
                flex-direction: column;
                justify-content: flex-start;
                align-items: center;
                padding-top: 2rem;
                transition: left 0.3s ease;
                box-shadow: var(--shadow);
            }

            .nav-menu.active {
                left: 0;
            }

            .welcome-title {
                font-size: 2rem;
            }

            .map-section {
                padding: 1rem;
            }

            #map-container {
                height: 400px;
            }

            .map-overlay {
                position: relative;
                top: auto;
                right: auto;
                margin: 1rem;
                max-width: none;
            }

            .location-info {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .action-btn {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .welcome-section {
                padding: 2rem 1rem;
            }

            .welcome-title {
                font-size: 1.8rem;
            }

            #map-container {
                height: 300px;
            }

            .info-card {
                padding: 1.5rem;
            }
        }

        /* Enhanced focus states for accessibility */
        .action-btn:focus,
        .nav-link:focus {
            outline: 3px solid rgba(0, 61, 58, 0.3);
            outline-offset: 2px;
        }

        /* Custom Leaflet Styles */
        .leaflet-popup-content-wrapper {
            border-radius: 15px;
            box-shadow: var(--shadow);
        }

        .leaflet-popup-content {
            margin: 1rem;
            font-family: 'Poppins', sans-serif;
            color: var(--ink);
        }

        .leaflet-popup-tip {
            background: var(--surface);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <a href="delivery.php" class="logo">Feed <span class="brand-name">Forward</span></a>
            
            <ul class="nav-menu">
                <li><a href="delivery.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="#" class="nav-link active"><i class="fas fa-map-marked-alt"></i> Map</a></li>
                <li><a href="deliverymyord.php" class="nav-link"><i class="fas fa-list-alt"></i> My Orders</a></li>
                <li><a href="../logout.php" class="nav-link" style="color: #ef4444;"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
            
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </header>

    <!-- Welcome Section -->
    <section class="welcome-section">
        <div class="welcome-content">
            <h1 class="welcome-title">
                <i class="fas fa-map-marked-alt"></i> Location Services
            </h1>
            <p class="welcome-subtitle">
                View your current location and service area. This helps us connect you with nearby delivery opportunities.
            </p>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <div class="section-header">
            <h2 class="section-title">Your Current Location</h2>
            <p class="section-subtitle">Real-time location tracking for optimal delivery routing</p>
        </div>

        <div class="map-container">
            <div class="map-wrapper">
                <div id="map-container"></div>
                <div class="map-overlay">
                    <div class="overlay-title">
                        <i class="fas fa-info-circle"></i> Location Info
                    </div>
                    <div class="overlay-content">
                        <div style="margin-bottom: 10px;">
                            <input type="text" id="location-search" placeholder="Search address or location..." 
                                   style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.85rem;">
                            <button onclick="searchLocation()" style="width: 100%; margin-top: 5px; padding: 8px; background: #003d3a; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 0.85rem;">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                        <div id="city-name">Detecting location...</div>
                        <div id="coordinates" style="margin-top: 0.5rem; font-size: 0.8rem; opacity: 0.7;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Information Cards -->
        <div class="location-info">
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="info-title">Service Area</div>
                <div class="info-content" id="service-area">Detecting...</div>
            </div>

            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="info-title">Last Updated</div>
                <div class="info-content" id="last-updated">Just now</div>
            </div>

            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-signal"></i>
                </div>
                <div class="info-title">GPS Accuracy</div>
                <div class="info-content" id="gps-accuracy">High</div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="delivery.php" class="action-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <button class="action-btn secondary" onclick="refreshLocation()">
                <i class="fas fa-sync-alt"></i> Refresh Location
            </button>
            <a href="deliverymyord.php" class="action-btn">
                <i class="fas fa-list-alt"></i> View My Orders
            </a>
        </div>
    </section>

    <script>
        // Mobile Navigation Toggle
        const hamburger = document.querySelector('.hamburger');
        const navMenu = document.querySelector('.nav-menu');

        hamburger.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });

        // Close mobile menu when clicking on a link
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
            });
        });

        // Global variables
        let map, marker, userLocation, accuracyCircle;
        const distributorCity = '<?php echo addslashes($city); ?>';

        // City to coordinates mapping
        const cityCoordinates = {
            'chikkaballapur': { lat: 13.4319, lng: 77.7275 },
            'mysuru': { lat: 12.2958, lng: 76.6394 },
            'bengaluru': { lat: 12.9716, lng: 77.5946 },
            'bangalore': { lat: 12.9716, lng: 77.5946 },
            'ballari': { lat: 15.1500, lng: 76.9333 },
            'belagavi': { lat: 15.8497, lng: 74.4977 },
            'hubli': { lat: 15.3647, lng: 75.1240 },
            'mangalore': { lat: 12.9141, lng: 74.8560 },
            'dharwad': { lat: 15.4589, lng: 75.0078 },
            'gulbarga': { lat: 17.3297, lng: 76.8343 },
            'raichur': { lat: 16.2076, lng: 77.3463 },
            'tumakuru': { lat: 13.3409, lng: 77.1010 },
            'hassan': { lat: 13.0031, lng: 76.1028 },
            'mandya': { lat: 12.5221, lng: 76.8970 },
            'udupi': { lat: 13.3409, lng: 74.7421 }
        };

        // Get city coordinates
        function getCityCoordinates(cityName) {
            const cityLower = cityName.toLowerCase().trim();
            return cityCoordinates[cityLower] || { lat: 13.4319, lng: 77.7275 };
        }

        // Simple, working map initialization
        function initMap() {
            const mapContainer = document.getElementById("map-container");
            if (!mapContainer) {
                console.error('Map container not found');
                return;
            }

            const defaultLocation = getCityCoordinates(distributorCity);
            
            // Create map immediately
            map = L.map(mapContainer).setView(defaultLocation, 15);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

            // Add marker
            const userIcon = L.divIcon({
                className: 'user-location-marker',
                html: '<div style="background-color: #0ea5a4; width: 28px; height: 28px; border-radius: 50%; border: 4px solid white; box-shadow: 0 2px 10px rgba(14, 165, 164, 0.4);"></div>',
                iconSize: [28, 28],
                iconAnchor: [14, 14]
            });
            
            userLocation = defaultLocation;
            marker = L.marker(defaultLocation, {icon: userIcon, draggable: true}).addTo(map);
            
            // Update location display
            updateLocationInfoManual(defaultLocation);
            if (document.getElementById('lat-input') && document.getElementById('lng-input')) {
                document.getElementById('lat-input').value = defaultLocation.lat.toFixed(6);
                document.getElementById('lng-input').value = defaultLocation.lng.toFixed(6);
            }
            
            // Setup controls
            setupFineTuneControls();
            
            // Marker drag
            marker.on('dragend', function(e) {
                const pos = marker.getLatLng();
                userLocation = { lat: pos.lat, lng: pos.lng };
                if (document.getElementById('lat-input') && document.getElementById('lng-input')) {
                    document.getElementById('lat-input').value = pos.lat.toFixed(6);
                    document.getElementById('lng-input').value = pos.lng.toFixed(6);
                }
                reverseGeocode(pos.lat, pos.lng);
                updateLocationDisplay(pos, false);
            });
            
            // Map click
            map.on('click', function(e) {
                const newLoc = { lat: e.latlng.lat, lng: e.latlng.lng };
                marker.setLatLng(newLoc);
                userLocation = newLoc;
                if (document.getElementById('lat-input') && document.getElementById('lng-input')) {
                    document.getElementById('lat-input').value = newLoc.lat.toFixed(6);
                    document.getElementById('lng-input').value = newLoc.lng.toFixed(6);
                }
                reverseGeocode(newLoc.lat, newLoc.lng);
                updateLocationDisplay(newLoc, false);
                marker.setPopupContent("<b>Location Set</b><br>Lat: " + newLoc.lat.toFixed(6) + "<br>Lng: " + newLoc.lng.toFixed(6)).openPopup();
            });
            
            marker.bindPopup("<b>Your Location</b><br>Click map or drag marker").openPopup();
            
            // Try GPS (simple, no complex checks)
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        if (marker && map) {
                            const loc = {
                                lat: position.coords.latitude,
                                lng: position.coords.longitude
                            };
                            marker.setLatLng(loc);
                            map.setView(loc, 15);
                            userLocation = loc;
                            if (document.getElementById('lat-input') && document.getElementById('lng-input')) {
                                document.getElementById('lat-input').value = loc.lat.toFixed(6);
                                document.getElementById('lng-input').value = loc.lng.toFixed(6);
                            }
                            updateLocationInfo(position);
                            reverseGeocode(loc.lat, loc.lng);
                            
                            if (position.coords.accuracy) {
                                accuracyCircle = L.circle(loc, {
                                    radius: position.coords.accuracy,
                                    color: '#0ea5a4',
                                    fillColor: '#0ea5a4',
                                    fillOpacity: 0.15,
                                    weight: 2
                                }).addTo(map);
                            }
                            
                            marker.setPopupContent("<b>Current Location</b><br>Accuracy: ±" + Math.round(position.coords.accuracy) + "m").openPopup();
                        }
                    },
                    function(error) {
                        console.log('GPS not available');
                    },
                    {
                        enableHighAccuracy: false,
                        timeout: 10000,
                        maximumAge: 300000
                    }
                );
            }
        }

        // Update location display
        function updateLocationDisplay(location, isAccurate) {
            const coordsEl = document.getElementById("coordinates");
            const updatedEl = document.getElementById("last-updated");
            const accuracyEl = document.getElementById("gps-accuracy");
            
            if (coordsEl) coordsEl.textContent = location.lat.toFixed(6) + ", " + location.lng.toFixed(6);
            if (updatedEl) updatedEl.textContent = new Date().toLocaleTimeString();
            if (accuracyEl) accuracyEl.textContent = isAccurate ? "GPS Location" : "Manual Location";
        }

        // Update location info manual
        function updateLocationInfoManual(location) {
            updateLocationDisplay(location, false);
            reverseGeocode(location.lat, location.lng);
        }

        // Update location info
        function updateLocationInfo(position) {
            const coordsEl = document.getElementById("coordinates");
            const updatedEl = document.getElementById("last-updated");
            const accuracyEl = document.getElementById("gps-accuracy");
            
            if (coordsEl) coordsEl.textContent = position.coords.latitude.toFixed(6) + ", " + position.coords.longitude.toFixed(6);
            if (updatedEl) updatedEl.textContent = new Date().toLocaleTimeString();
            
            const acc = position.coords.accuracy;
            if (accuracyEl) {
                if (acc <= 10) accuracyEl.textContent = "Very High (±" + Math.round(acc) + "m)";
                else if (acc <= 50) accuracyEl.textContent = "High (±" + Math.round(acc) + "m)";
                else if (acc <= 100) accuracyEl.textContent = "Medium (±" + Math.round(acc) + "m)";
                else accuracyEl.textContent = "Low (±" + Math.round(acc) + "m)";
            }
            
            reverseGeocode(position.coords.latitude, position.coords.longitude);
        }

        // Reverse geocode
        function reverseGeocode(lat, lng) {
            const cityEl = document.getElementById("city-name");
            const areaEl = document.getElementById("service-area");
            
            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`, {
                headers: { 'User-Agent': 'FeedForward/1.0' }
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.address) {
                    const city = data.address.city || data.address.town || data.address.village || distributorCity;
                    if (cityEl) cityEl.textContent = "You are in " + city;
                    if (areaEl) {
                        const state = data.address.state || "Karnataka";
                        areaEl.textContent = city + ", " + state + ", India";
                    }
                }
            })
            .catch(error => {
                if (cityEl) cityEl.textContent = "Location: " + lat.toFixed(4) + ", " + lng.toFixed(4);
                if (areaEl) areaEl.textContent = distributorCity + ", Karnataka, India";
            });
        }

        // Fine-tune location
        function fineTuneLocation(direction) {
            if (!marker || !userLocation) return;
            const step = 0.000005;
            let lat = userLocation.lat;
            let lng = userLocation.lng;
            
            if (direction === 'n') lat += step;
            else if (direction === 's') lat -= step;
            else if (direction === 'e') lng += step;
            else if (direction === 'w') lng -= step;
            else if (direction === 'ne') { lat += step; lng += step; }
            else if (direction === 'nw') { lat += step; lng -= step; }
            else if (direction === 'se') { lat -= step; lng += step; }
            else if (direction === 'sw') { lat -= step; lng -= step; }
            else if (direction === 'center') { map.setView(userLocation, 19); return; }
            
            const newLoc = { lat: lat, lng: lng };
            marker.setLatLng(newLoc);
            if (accuracyCircle) accuracyCircle.setLatLng(newLoc);
            userLocation = newLoc;
            if (document.getElementById('lat-input') && document.getElementById('lng-input')) {
                document.getElementById('lat-input').value = lat.toFixed(6);
                document.getElementById('lng-input').value = lng.toFixed(6);
            }
            reverseGeocode(lat, lng);
            updateLocationDisplay(newLoc, false);
        }

        // Set exact coordinates
        function setExactCoordinates() {
            const lat = parseFloat(document.getElementById('lat-input').value);
            const lng = parseFloat(document.getElementById('lng-input').value);
            if (isNaN(lat) || isNaN(lng) || lat < -90 || lat > 90 || lng < -180 || lng > 180) {
                alert('Invalid coordinates');
                return;
            }
            const newLoc = { lat: lat, lng: lng };
            marker.setLatLng(newLoc);
            if (accuracyCircle) accuracyCircle.setLatLng(newLoc);
            map.setView(newLoc, 19);
            userLocation = newLoc;
            reverseGeocode(lat, lng);
            updateLocationDisplay(newLoc, false);
            marker.setPopupContent("<b>Exact Location</b><br>Lat: " + lat.toFixed(6) + "<br>Lng: " + lng.toFixed(6)).openPopup();
        }

        // Search location
        function searchLocation() {
            const query = document.getElementById('location-search').value.trim();
            if (!query) { alert('Enter address'); return; }
            
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query + ', ' + distributorCity)}&limit=1`, {
                headers: { 'User-Agent': 'FeedForward/1.0' }
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    const loc = { lat: parseFloat(data[0].lat), lng: parseFloat(data[0].lon) };
                    marker.setLatLng(loc);
                    if (accuracyCircle) accuracyCircle.setLatLng(loc);
                    map.setView(loc, 18);
                    userLocation = loc;
                    if (document.getElementById('lat-input') && document.getElementById('lng-input')) {
                        document.getElementById('lat-input').value = loc.lat.toFixed(6);
                        document.getElementById('lng-input').value = loc.lng.toFixed(6);
                    }
                    reverseGeocode(loc.lat, loc.lng);
                    updateLocationDisplay(loc, false);
                    marker.setPopupContent("<b>Location Found</b><br>" + data[0].display_name).openPopup();
                } else {
                    alert('Location not found');
                }
            })
            .catch(error => alert('Search error'));
        }

        // Refresh location
        function refreshLocation() {
            if (map) map.remove();
            initMap();
        }

        // Initialize when page loads - SIMPLE VERSION
        window.addEventListener('load', function() {
            if (typeof L !== 'undefined') {
                initMap();
            } else {
                setTimeout(function() {
                    if (typeof L !== 'undefined') {
                        initMap();
                    }
                }, 500);
            }
        });

        // Enhanced fine-tune controls with better precision
        function setupFineTuneControls() {
            const fineTuneContainer = document.createElement('div');
            fineTuneContainer.id = 'fine-tune-controls';
            fineTuneContainer.style.cssText = 'position: absolute; bottom: 20px; left: 20px; z-index: 1000; background: white; padding: 15px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); max-width: 300px;';
            fineTuneContainer.innerHTML = `
                <div style="font-size: 0.85rem; font-weight: 600; margin-bottom: 12px; color: #003d3a; border-bottom: 2px solid #0ea5a4; padding-bottom: 8px;">
                    <i class="fas fa-crosshairs"></i> Precise Location Control
                </div>
                <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 10px; background: #f0f9ff; padding: 8px; border-radius: 6px;">
                    <strong>For Maximum Accuracy:</strong><br>
                    1. Search for your address<br>
                    2. Click on the exact spot on map<br>
                    3. Use fine-tune buttons for micro-adjustments<br>
                    4. Or enter exact coordinates
                </div>
                <div style="display: grid; grid-template-columns: repeat(3, 40px); gap: 6px; margin-bottom: 12px; justify-content: center;">
                    <button onclick="fineTuneLocation('nw')" style="grid-column: 1; padding: 10px; border: 2px solid #0ea5a4; background: white; cursor: pointer; border-radius: 6px; transition: all 0.2s; font-size: 16px; font-weight: bold;" onmouseover="this.style.background='#0ea5a4'; this.style.color='white';" onmouseout="this.style.background='white'; this.style.color='inherit';">↖</button>
                    <button onclick="fineTuneLocation('n')" style="grid-column: 2; padding: 10px; border: 2px solid #0ea5a4; background: white; cursor: pointer; border-radius: 6px; transition: all 0.2s; font-size: 16px; font-weight: bold;" onmouseover="this.style.background='#0ea5a4'; this.style.color='white';" onmouseout="this.style.background='white'; this.style.color='inherit';">↑</button>
                    <button onclick="fineTuneLocation('ne')" style="grid-column: 3; padding: 10px; border: 2px solid #0ea5a4; background: white; cursor: pointer; border-radius: 6px; transition: all 0.2s; font-size: 16px; font-weight: bold;" onmouseover="this.style.background='#0ea5a4'; this.style.color='white';" onmouseout="this.style.background='white'; this.style.color='inherit';">↗</button>
                    <button onclick="fineTuneLocation('w')" style="grid-column: 1; padding: 10px; border: 2px solid #0ea5a4; background: white; cursor: pointer; border-radius: 6px; transition: all 0.2s; font-size: 16px; font-weight: bold;" onmouseover="this.style.background='#0ea5a4'; this.style.color='white';" onmouseout="this.style.background='white'; this.style.color='inherit';">←</button>
                    <button onclick="fineTuneLocation('center')" style="grid-column: 2; padding: 10px; border: 2px solid #0ea5a4; background: #0ea5a4; color: white; cursor: pointer; border-radius: 6px; transition: all 0.2s; font-size: 16px; font-weight: bold;" onmouseover="this.style.background='#003d3a';" onmouseout="this.style.background='#0ea5a4';">•</button>
                    <button onclick="fineTuneLocation('e')" style="grid-column: 3; padding: 10px; border: 2px solid #0ea5a4; background: white; cursor: pointer; border-radius: 6px; transition: all 0.2s; font-size: 16px; font-weight: bold;" onmouseover="this.style.background='#0ea5a4'; this.style.color='white';" onmouseout="this.style.background='white'; this.style.color='inherit';">→</button>
                    <button onclick="fineTuneLocation('sw')" style="grid-column: 1; padding: 10px; border: 2px solid #0ea5a4; background: white; cursor: pointer; border-radius: 6px; transition: all 0.2s; font-size: 16px; font-weight: bold;" onmouseover="this.style.background='#0ea5a4'; this.style.color='white';" onmouseout="this.style.background='white'; this.style.color='inherit';">↙</button>
                    <button onclick="fineTuneLocation('s')" style="grid-column: 2; padding: 10px; border: 2px solid #0ea5a4; background: white; cursor: pointer; border-radius: 6px; transition: all 0.2s; font-size: 16px; font-weight: bold;" onmouseover="this.style.background='#0ea5a4'; this.style.color='white';" onmouseout="this.style.background='white'; this.style.color='inherit';">↓</button>
                    <button onclick="fineTuneLocation('se')" style="grid-column: 3; padding: 10px; border: 2px solid #0ea5a4; background: white; cursor: pointer; border-radius: 6px; transition: all 0.2s; font-size: 16px; font-weight: bold;" onmouseover="this.style.background='#0ea5a4'; this.style.color='white';" onmouseout="this.style.background='white'; this.style.color='inherit';">↘</button>
                </div>
                <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e2e8f0;">
                    <div style="font-size: 0.75rem; color: #003d3a; margin-bottom: 8px; font-weight: 600;">
                        <i class="fas fa-map-pin"></i> Exact Coordinates:
                    </div>
                    <div style="display: flex; gap: 5px; margin-bottom: 5px;">
                        <input type="number" id="lat-input" placeholder="Latitude" step="0.000001" style="flex: 1; padding: 8px; border: 2px solid #0ea5a4; border-radius: 6px; font-size: 0.8rem;">
                        <input type="number" id="lng-input" placeholder="Longitude" step="0.000001" style="flex: 1; padding: 8px; border: 2px solid #0ea5a4; border-radius: 6px; font-size: 0.8rem;">
                    </div>
                    <button onclick="setExactCoordinates()" style="width: 100%; padding: 8px; background: linear-gradient(135deg, #003d3a, #0ea5a4); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.8rem; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.transform='scale(1.02)';" onmouseout="this.style.transform='scale(1)';">
                        <i class="fas fa-check"></i> Set Exact Location
                    </button>
                </div>
                <div style="margin-top: 10px; font-size: 0.7rem; color: #64748b; text-align: center;">
                    Fine-tune: ~1 meter per click | Zoom: 19x for precision
                </div>
            `;
            document.querySelector('.map-wrapper').appendChild(fineTuneContainer);
        }

        // Start periodic location updates
        function startLocationUpdates() {
            if (locationUpdateInterval) {
                clearInterval(locationUpdateInterval);
            }
            
            locationUpdateInterval = setInterval(() => {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        if (userLocation && map && marker) {
                            const newLocation = {
                                lat: position.coords.latitude,
                                lng: position.coords.longitude
                            };
                            
                            const distance = calculateDistance(userLocation, newLocation);
                            if (distance > 10) {
                                marker.setLatLng(newLocation);
                                if (accuracyCircle) {
                                    accuracyCircle.setLatLng(newLocation);
                                    accuracyCircle.setRadius(position.coords.accuracy);
                                }
                                map.setView(newLocation, 18);
                                userLocation = newLocation;
                                updateLocationInfo(position);
                            }
                        }
                    },
                    function(error) {
                        console.log('Location update failed:', error);
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 30000
                    }
                );
            }, 30000);
        }

        // Calculate distance between two coordinates (in meters)
        function calculateDistance(coord1, coord2) {
            const R = 6371e3;
            const φ1 = coord1.lat * Math.PI/180;
            const φ2 = coord2.lat * Math.PI/180;
            const Δφ = (coord2.lat - coord1.lat) * Math.PI/180;
            const Δλ = (coord2.lng - coord1.lng) * Math.PI/180;

            const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                      Math.cos(φ1) * Math.cos(φ2) *
                      Math.sin(Δλ/2) * Math.sin(Δλ/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

            return R * c;
        }

        // Clean up interval when page unloads
        window.onbeforeunload = function() {
            if (locationUpdateInterval) {
                clearInterval(locationUpdateInterval);
            }
        };

        // Add smooth animations (non-blocking)
        document.addEventListener('DOMContentLoaded', () => {
            const elements = document.querySelectorAll('.info-card, .map-container');
            elements.forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    element.style.transition = 'all 0.6s ease-out';
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, 300 + (index * 100));
            });
        });
    </script>
</body>
</html> 