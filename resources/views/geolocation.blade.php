<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Geolocation Tracking') }}
        </h2>

        <style>
            #map {
                height: 500px;
                width: 100%;
            }
        </style>
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <script async src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCjdh9j3kw9soFqzCEwljxL1nKKuffbbDg&libraries=geometry,marker&v=weekly&loading=async&callback=initMap"></script>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="row d-flex">
                        <div class="p-1">
                            <b>Distance : -</b>
                            <span id="distanceValue">0.00</span> km
                        </div>
                        <div class="p-1">
                            <b>Start Location : -</b><span id="startLocationValue"></span>
                            <button type="button" id="startTrackingButton" class="btn btn-success btn-sm" onclick="startTracking()">Start Tracking</button>
                            <button type="button" id="stopTrackingButton" class="btn btn-success btn-sm" onclick="stopTracking()">Stop Tracking</button>
                        </div>
                        <div class="p-1">
                            <b>End Location : -</b><span id="endLocationValue"></span>
                        </div>
                        <div class="p-1">
                            <b>Speed : -</b><span id="speedValue">0.00</span> km/h
                        </div>
                        <div class="p-1">
                            <b>Time : -</b><span id="timeValue"></span>
                        </div>
                    </div>
                    <br>
                    <div id="map">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let startMarker, endMarker, polyline, map;
        let isStartMarkerSet = false;
        let trackingActive = true;  // Tracking state

        function initMap() {
            // Initialize the map
            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 15,
                center: { lat: -34.397, lng: 150.644 },
                mapId: "DEMO_MAP_ID", // Add your custom map ID here if using advanced markers.

            });

            const infoWindow = new google.maps.InfoWindow();

            if (navigator.geolocation) {
                navigator.geolocation.watchPosition(
                    (position) => {
                        const pos = { lat: position.coords.latitude, lng: position.coords.longitude };

                        if (!isStartMarkerSet) {
                            startMarker = new google.maps.Marker({
                                position: pos,
                                map: map,
                                icon: 'https://maps.google.com/mapfiles/kml/paddle/blu-circle.png',
                                title: "Start Location",
                                draggable: true,
                                animation: google.maps.Animation.DROP,
                                optimized: true,
                            });
                            isStartMarkerSet = true;
                        }

                        map.setCenter(pos);
                        sendLocationToServer();
                    },
                    () => { handleLocationError(true, infoWindow, map.getCenter()); },
                    { enableHighAccuracy: true }
                );
            } else {
                handleLocationError(false, infoWindow, map.getCenter());
            }

            map.addListener("click", (event) => {
                const pos = { lat: event.latLng.lat(), lng: event.latLng.lng() };

                if (!endMarker) {
                    endMarker = new google.maps.Marker({
                        position: pos,
                        map: map,
                        title: "End Location",
                        icon: 'https://maps.google.com/mapfiles/kml/paddle/orange-circle.png',
                        shape: 'CIRCLE',
                        size: 'SMALL',
                        zIndex: 1,
                        optimized: true,
                        draggable: true,
                        animation: google.maps.Animation.DROP,
                    });

                    endMarker.addListener('dragend', () => {
                        updatePolyline();
                        sendLocationToServer();
                    });
                } else {
                    endMarker.setPosition(pos);
                }

                updatePolyline();
                sendLocationToServer();
            });

            // Add the advanced marker for Uluru
            const uluruPosition = { lat: -25.344, lng: 131.031 };
            const advancedMarker = new google.maps.marker.AdvancedMarkerElement({
                map: map,
                position: uluruPosition,
                icon: 'https://developers.google.com/maps/documentation/javascript/examples/full/images/beachflag.png',
                shape: 'CIRCLE',
                size: 'SMALL',
                title: 'Uluru',
                zIndex: 1
            });
        }

        function handleLocationError(browserHasGeolocation, infoWindow, pos) {
            infoWindow.setPosition(pos);
            infoWindow.setContent(browserHasGeolocation ?
                "Error: The Geolocation service failed." :
                "Error: Your browser doesn't support geolocation."
            );
            infoWindow.open(map);
        }

        function sendLocationToServer() {
            if (startMarker && endMarker) {
                const data = {
                    start: { lat: startMarker.getPosition().lat(), lng: startMarker.getPosition().lng() },
                    end: { lat: endMarker.getPosition().lat(), lng: endMarker.getPosition().lng() }
                };

                fetch('{{ route('location') }}', {
                    method: 'post',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(data),
                });
            }
        }

        function updatePolyline() {
            if (startMarker && endMarker) {
                const path = [startMarker.getPosition(), endMarker.getPosition()];

                if (polyline) {
                    polyline.setPath(path);
                } else {
                    polyline = new google.maps.Polyline({
                        path: path,
                        geodesic: true,
                        strokeColor: "#FF0000",
                        strokeOpacity: 1.0,
                        strokeWeight: 2,
                        map: map
                    });
                }

                displayDistance();
            }
        }

        function displayDistance() {
            if (startMarker && endMarker && trackingActive) {
                const start = startMarker.getPosition();
                const end = endMarker.getPosition();
                const distance = google.maps.geometry.spherical.computeDistanceBetween(start, end);
                const distanceInKm = (distance / 1000).toFixed(2);

                if (!window.distanceInfoWindow) {
                    window.distanceInfoWindow = new google.maps.InfoWindow();
                    window.distanceInfoWindow.open(map, startMarker);
                    window.distanceInfoWindow.setContent(`Distance: ${distanceInKm} km`);

                }

                window.distanceInfoWindow.setContent(`Distance: ${distanceInKm} km`);
                window.distanceInfoWindow.setPosition(end);
                window.distanceInfoWindow.open(map);

                updateRoute();

                document.getElementById("distanceValue").textContent = distanceInKm;
                document.getElementById("startLocationValue").textContent = JSON.stringify(start);
                document.getElementById("endLocationValue").textContent = JSON.stringify(end);

                if (distance <= 1) {
                    deleteLocationRecord();
                }
            }
        }

        function deleteLocationRecord() {
            fetch('{{ route('location.delete') }}', {
                method: 'post',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ start: startMarker.getPosition(), end: endMarker.getPosition() }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    console.log('Location record deleted successfully');
                    document.getElementById('stopTrackingButton').style.display = 'block';
                } else {
                    console.error('Failed to delete location record');
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function stopTracking() {
            trackingActive = false;
            console.log('Tracking stopped');
            document.getElementById('stopTrackingButton').style.display = 'none';
            resetMap();
            window.distanceInfoWindow.close();
            window.distanceInfoWindow = null;
            document.getElementById("distanceValue").textContent = '';
            document.getElementById("startLocationValue").textContent = '';

            sendLocationToServer();

        }

        function resetMap() {
            // Logic to reset the map
            console.log('Map reset');

            if (startMarker) {
                startMarker.setMap(null);
                startMarker = null;
            }

            if (endMarker) {
                endMarker.setMap(null);
                endMarker = null;
            }
        }

        function updateRoute() {
            console.log('updateRoute function called');

            // Logic to update the route
            console.log('Route updated');

            // Update the route on the map
            if (polyline) {
                polyline.setMap(map);
            }

            // Update the route information
            displayDistance();
        }
    </script>
</x-app-layout>
