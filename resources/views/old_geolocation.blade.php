<!DOCTYPE html>
<html>
<head>
    <title>Geolocation Tracking</title>
    <style>
        #map {
            height: 500px;
            width: 100%;
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script  src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCjdh9j3kw9soFqzCEwljxL1nKKuffbbDg"></script>
    {{-- <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCjdh9j3kw9soFqzCEwljxL1nKKuffbbDg&libraries=marker"></script> --}}

    <script>
        function initMap() {
            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 15,
                center: { lat: -34.397, lng: 150.644 }
            });

            const infoWindow = new google.maps.InfoWindow();

            if (navigator.geolocation) {
                navigator.geolocation.watchPosition(
                    (position) => {
                        // Get user's current location
                        const pos = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude,
                        };

                        const marker = new google.maps.Marker({
                            position: pos,
                            map: map,
                            title: "You are here",
                            draggable: true
                        });

                        infoWindow.setPosition(pos);
                        infoWindow.setContent("Your Current location.");
                        infoWindow.open(map);
                        map.setCenter(pos);

                        // Send the user's current location to the server
                        fetch('{{ route('geolocation') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'X-Requested-With': 'XMLHttpRequest',  // For CSRF protection
                            },
                            body: JSON.stringify(pos),
                        });
                    },
                    () => {
                        // Handle location error
                        handleLocationError(true, infoWindow, map.getCenter());
                    },
                    { enableHighAccuracy: true }
                );
            } else {
                handleLocationError(false, infoWindow, map.getCenter());
            }
        }

        function handleLocationError(browserHasGeolocation, infoWindow, pos) {
            infoWindow.setPosition(pos);
            infoWindow.setContent(
                browserHasGeolocation
                    ? "Error: The Geolocation service failed."
                    : "Error: Your browser doesn't support geolocation."
            );
            infoWindow.open(map);
        }
    </script>
</head>
<body onload="initMap()">
    <div id="map"></div>
</body>
</html>
