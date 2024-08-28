<!DOCTYPE html>
<html>

<head>
    <title>Geolocation Tracking</title>
    <link href="https://unpkg.com/material-components-web@6.0.0/dist/material-components-web.css" rel="stylesheet" />
    <script src="https://unpkg.com/material-components-web@6.0.0/dist/material-components-web.min.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons" />
    <style>
        #map {
            height: 500px;
            width: 100%;
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- <script  src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCjdh9j3kw9soFqzCEwljxL1nKKuffbbDg"></script> --}}
    {{-- <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCjdh9j3kw9soFqzCEwljxL1nKKuffbbDg&callback=initMap" async defer></script> --}}
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCjdh9j3kw9soFqzCEwljxL1nKKuffbbDg&callback=initMap&v=weekly&libraries=marker" defer></script>

</head>

<body>
    <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
        <h1>Geolocation Tracking</h1>
        <p>Please allow the browser to access your current location.</p>
        <button onclick="getCurrentLocation()">Get Current Location</button>
        <div id="currentLocation">
            <p id="currentLocationText">
                Current Location : <span id="currentLocationValue"></span>
            </p>
        </div>
        <div id="map"
            class="absolute -bottom-16 -left-16 h-40 w-[calc(100%+8rem)] bg-gradient-to-b from-transparent via-white to-white">
        </div>
    </div>
    <script>
        let startMarker, endMarker;

        function initMap() {
            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 15,
                center: {
                    lat: -34.397,
                    lng: 150.644
                }
            });

            const infoWindow = new google.maps.InfoWindow();

            if (navigator.geolocation) {
                navigator.geolocation.watchPosition(
                    (position) => {
                        const pos = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude,
                        };

                        if (!startMarker) {
                            // Set start marker (user's current location)
                            startMarker = new google.maps.Marker({
                                position: pos,
                                map: map,
                                title: "Start Location",
                                draggable: true,
                                animation: google.maps.Animation.DROP,
                            });

                            // Add an event listener to update position on drag end
                            startMarker.addListener('dragend', () => {
                                sendLocationToServer();
                            });
                        } else {
                            // Update start marker position
                            startMarker.setPosition(pos);
                        }

                        if (!endMarker) {
                            // Set end marker (allow user to drag it to the destination)
                            endMarker = new google.maps.Marker({
                                position: pos,
                                map: map,
                                title: "End Location",
                                draggable: true,
                                animation: google.maps.Animation.DROP
                            });

                            // Add an event listener to update position on drag end
                            endMarker.addListener('dragend', () => {
                                sendLocationToServer();
                            });
                        }

                        map.setCenter(pos);
                        sendLocationToServer();
                    },
                    () => {
                        handleLocationError(true, infoWindow, map.getCenter());
                    }, {
                        enableHighAccuracy: true
                    }
                );
            } else {
                handleLocationError(false, infoWindow, map.getCenter());
            }
        }

        function handleLocationError(browserHasGeolocation, infoWindow, pos) {
            infoWindow.setPosition(pos);
            infoWindow.setContent(
                browserHasGeolocation ?
                "Error: The Geolocation service failed." :
                "Error: Your browser doesn't support geolocation."
            );
            infoWindow.open(map);
        }

        function sendLocationToServer() {
            if (startMarker && endMarker) {
                const data = {
                    start: {
                        lat: startMarker.getPosition().lat(),
                        lng: startMarker.getPosition().lng()
                    },
                    end: {
                        lat: endMarker.getPosition().lat(),
                        lng: endMarker.getPosition().lng()
                    }
                };

                fetch('{{ route('geolocation') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(data),
                });
            }
        }
    </script>
    {{-- getCurrentLocation --}}
    <script>
        function getCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((position) => {
                    const pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    };
                    document.getElementById("currentLocationValue").textContent = JSON.stringify(pos);
                });
            }
        }
        getCurrentLocation();
    </script>
</body>

</html>
