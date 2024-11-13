<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webcam Integration with Photo Save</title>

</head>
<body>
    <div class="container mt-5">
        <div class="card text-center">
            <div class="card-body">
                <div class="video-card">
                    <video id="video" autoplay></video>
                </div>
                <br>
                <a href="#" class="btn btn-primary" onClick="startCam()">Start Cam</a>
                <a href="#" class="btn btn-danger" onClick="stopCam()">Stop Cam</a>
                <a href="#" class="btn btn-success" onClick="takePhoto()">Take Photo</a>

                <div class="mt-3">
                    <canvas id="canvas" style="display: none;"></canvas>
                    <img id="photo" style="display: none;" />
                </div>
            </div>
        </div>
    </div>

    <script>
        // Start Cam function
        const startCam = () => {
            const video = document.getElementById("video");
            if (navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices
                    .getUserMedia({ video: true })
                    .then((stream) => {
                        video.srcObject = stream;
                    })
                    .catch(function (error) {
                        console.log("Something went wrong!");
                    });
            }
        };

        // Stop Cam function
        const stopCam = () => {
            const video = document.getElementById("video");
            let stream = video.srcObject;
            let tracks = stream.getTracks();

            tracks.forEach((track) => track.stop());
            video.srcObject = null;
        };

        // Take Photo function and send to server
        const takePhoto = () => {
            const video = document.getElementById("video");
            const canvas = document.getElementById("canvas");
            const photo = document.getElementById("photo");

            // Set canvas dimensions to match video stream
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            // Draw the video frame on the canvas
            const context = canvas.getContext("2d");
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Convert canvas to base64 image
            const dataUrl = canvas.toDataURL("image/png");

            // Show captured photo (optional)
            photo.src = dataUrl;
            photo.style.display = "block";

            // Send the image to the server for saving
            savePhotoToServer(dataUrl);
        };

        // Function to send the captured photo to the server
        const savePhotoToServer = (dataUrl) => {
            // Convert base64 image to blob
            const blob = dataURItoBlob(dataUrl);

            // Use FormData to send the image to the server
            const formData = new FormData();
            formData.append("image", blob, "webcam_photo.png");

            // Send an AJAX request to the PHP script
            fetch('save_image.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Photo saved successfully!");
                } else {
                    alert("Photo saving failed!");
                }
            })
            .catch((error) => {
                console.error('Error:', error);
            });
        };

        // Utility function to convert base64 to Blob
        function dataURItoBlob(dataURI) {
            const byteString = atob(dataURI.split(',')[1]);
            const mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];
            const ab = new ArrayBuffer(byteString.length);
            const ia = new Uint8Array(ab);
            for (let i = 0; i < byteString.length; i++) {
                ia[i] = byteString.charCodeAt(i);
            }
            return new Blob([ab], { type: mimeString });
        }
    </script>
</body>
</html>
