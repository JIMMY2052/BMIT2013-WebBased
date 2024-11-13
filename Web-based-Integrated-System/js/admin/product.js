let photoSlideIndex = 1;
let videoSlideIndex = 1;

// Initial calls to display the first slide for both
showPhotoSlides(photoSlideIndex);
showVideoSlides(videoSlideIndex);

// Next/previous controls for photos
function plusPhotoSlides(n) {
  showPhotoSlides((photoSlideIndex += n));
}

// Next/previous controls for videos
function plusVideoSlides(n) {
  showVideoSlides((videoSlideIndex += n));
}

function showPhotoSlides(n) {
  let i;
  let photoSlides = document.getElementsByClassName("photoSlides");
  let totalPhotoSlides = photoSlides.length;

  if (n > totalPhotoSlides) {
    photoSlideIndex = 1;
  }
  if (n < 1) {
    photoSlideIndex = totalPhotoSlides;
  }

  for (i = 0; i < totalPhotoSlides; i++) {
    photoSlides[i].style.display = "none";
  }

  photoSlides[photoSlideIndex - 1].style.display = "block";
  document.getElementById("photo-slide-number").innerHTML = photoSlideIndex;
}

function showVideoSlides(n) {
  let i;
  let videoSlides = document.getElementsByClassName("videoSlides");
  let totalVideoSlides = videoSlides.length;

  if (n > totalVideoSlides) {
    videoSlideIndex = 1;
  }
  if (n < 1) {
    videoSlideIndex = totalVideoSlides;
  }

  for (i = 0; i < totalVideoSlides; i++) {
    videoSlides[i].style.display = "none";
  }

  videoSlides[videoSlideIndex - 1].style.display = "block";
  document.getElementById("video-slide-number").innerHTML = videoSlideIndex;
}
