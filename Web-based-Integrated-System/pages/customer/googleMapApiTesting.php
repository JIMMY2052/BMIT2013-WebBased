<!DOCTYPE html>
<html>
  <head>
    <title>Custom Marker Icon</title>
    <script>
      let map;
      const chicago = { lat: 41.85, lng: -87.65 };

      function createCenterControl(map) {
        const controlButton = document.createElement("button");
        controlButton.classList.add('buttonStyle');
        controlButton.textContent = "Center Map";
        controlButton.title = "Click to recenter the map";
        controlButton.type = "button";
        controlButton.addEventListener("click", () => {
          map.setCenter(chicago);
        });
        return controlButton;
      }

      function initMap() {
        map = new google.maps.Map(document.getElementById("map"), {
          zoom: 16,
          center: { lat: 3.2152552, lng: 101.7265571 },
        });
//3.2152552,101.7265571
        // Add a marker at the center of the map with a custom icon
        const marker = new google.maps.Marker({
          position: { lat: 3.2152552, lng: 101.7265571 },
          map: map,
          title: "Center of the Map",
          icon: {
            url: "http://localhost:8000/img/icon&logo/Map_pin_icon.svg.png", // Replace with your custom icon URL
            scaledSize: new google.maps.Size(30, 40), // Resize the icon
          }
        });
      }

      window.initMap = initMap;
    </script>
    <style>
      #map {
        height: 350px;
        width: 350px;
      }
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
    </style>
  </head>
  <body>
    <div id="map"></div>
    <script
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAAEFH_zXpMYNcQJFscENyIKxtLGLTcnQ8&callback=initMap&v=weekly"
      defer
    ></script>
  </body>
</html>
