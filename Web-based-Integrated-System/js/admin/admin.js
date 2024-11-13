const toggleButton = document.getElementById("toggle-btn");
const sidebar = document.getElementById("sidebar");
const profile = document.querySelector(".admin-profile");

function toggleSidebar() {
  sidebar.classList.toggle("close");
  toggleButton.classList.toggle("rotate");
  profile.classList.toggle("collapsed");
  closeAllSubMenus();
}

function toggleSubMenu(button) {
  if (!button.nextElementSibling.classList.contains("show")) {
    closeAllSubMenus();
  }

  button.nextElementSibling.classList.toggle("show");
  button.classList.toggle("rotate");

  if (sidebar.classList.contains("close")) {
    sidebar.classList.toggle("close");
    toggleButton.classList.toggle("rotate");
  }
}

function closeAllSubMenus() {
  Array.from(sidebar.getElementsByClassName("show")).forEach((ul) => {
    ul.classList.remove("show");
    ul.previousElementSibling.classList.remove("rotate");
  });
}

function togglePasswordVisibility(fieldId, iconElement) {
  const passwordField = document.getElementById(fieldId);
  const icon = iconElement.querySelector("img");

  if (passwordField.type === "password") {
    passwordField.type = "text";
    icon.src = "/img/icon&logo/eye.svg";
    icon.alt = "Hide Password";
  } else {
    passwordField.type = "password";
    icon.src = "/img/icon&logo/eye-slash.svg";
    icon.alt = "Show Password";
  }
}

$("[data-confirm]").on("click", (e) => {
  e.preventDefault();

  const button = $(e.target).closest("[data-confirm]");
  const text = button.data("confirm") || "Are you sure?";

  if (!confirm(text)) {
    e.stopImmediatePropagation();
  } else {
    button.closest("form").submit();
  }
});

// Initiate GET request
$("[data-get]").on("click", (e) => {
  e.preventDefault();

  const url = $(e.target).closest("[data-get]").data("get");

  if (url) {
    location.href = url;
  }
});

// Initiate POST request
$("[data-post]").on("click", (e) => {
  e.preventDefault();

  const url = $(e.target).closest("[data-post]").data("post");

  if (url) {
    const f = $("<form>").appendTo(document.body)[0];
    f.method = "POST";
    f.action = url;
    f.submit();
  }
});

function toggleActionMenu(button) {
  const actionMenu = button.parentNode.querySelector(".product-action-menu");

  if (actionMenu) {
    const isDisplayed = window.getComputedStyle(actionMenu).display === "block";

    const allMenus = document.querySelectorAll(".product-action-menu");
    allMenus.forEach((menu) => {
      menu.style.display = "none";
    });

    if (isDisplayed) {
      actionMenu.style.display = "none";
    } else {
      actionMenu.style.display = "block";
    }
  }
}

function removeProfilePicture() {
  if (confirm("Are you sure you want to remove your profile picture?")) {
    const form = document.createElement("form");
    form.method = "POST";
    form.action = ""; // Same page

    const input = document.createElement("input");
    input.type = "hidden";
    input.name = "remove_picture";
    input.value = "1";

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
  }
}

// Close the action menu if the user clicks outside of it
window.onclick = function (event) {
  const isClickInside = event.target.closest(".product-action-buttons");
  if (!isClickInside) {
    const allMenus = document.querySelectorAll(".product-action-menu");
    allMenus.forEach((menu) => {
      menu.style.display = "none";
    });
  }
};

function updateDays() {
  const daySelect = document.getElementById("day");
  const monthSelect = document.getElementById("month");
  const yearSelect = document.getElementById("year");

  const selectedMonth = parseInt(monthSelect.value);
  const selectedYear = parseInt(yearSelect.value);

  if (!selectedMonth || !selectedYear) {
    return;
  }

  const currentDay = parseInt(daySelect.value);

  const daysInMonth = new Date(selectedYear, selectedMonth, 0).getDate();

  daySelect.innerHTML = '<option value="">- Select Day -</option>';

  for (let i = 1; i <= daysInMonth; i++) {
    let selected = i === currentDay ? "selected" : "";
    daySelect.innerHTML += `<option value="${i}" ${selected}>${i}</option>`;
  }
}

document.getElementById("month").addEventListener("change", updateDays);
document.getElementById("year").addEventListener("change", updateDays);

window.addEventListener("load", updateDays);


const startCam = () => {
  const video = document.getElementById("video");
  const profilePic = document.getElementById("profile-pic");

  // Hide profile picture and show video
  profilePic.style.display = "none";
  video.style.display = "block";

  if (navigator.mediaDevices.getUserMedia) {
    navigator.mediaDevices
      .getUserMedia({
        video: true,
      })
      .then((stream) => {
        video.srcObject = stream;
      })
      .catch(function (error) {
        console.log("Something went wrong!", error);
      });
  }
};

const stopCam = () => {
  const video = document.getElementById("video");
  const profilePic = document.getElementById("profile-pic");

  // Show profile picture and hide video
  profilePic.style.display = "block";
  video.style.display = "none";

  let stream = video.srcObject;
  let tracks = stream.getTracks();
  tracks.forEach((track) => track.stop());
  video.srcObject = null;
};

const takePhoto = () => {
  const video = document.getElementById("video");
  const canvas = document.getElementById("canvas");
  const profilePic = document.getElementById("profile-pic");
  const webcamPhotoInput = document.getElementById("webcam_photo");

  // Set canvas dimensions to match video stream
  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;

  // Draw the video frame on the canvas
  const context = canvas.getContext("2d");
  context.drawImage(video, 0, 0, canvas.width, canvas.height);

  // Convert canvas to base64 image
  const dataUrl = canvas.toDataURL("image/png");

  // Update profile-pic img tag with the captured photo
  profilePic.src = dataUrl;

  // Show the captured photo
  profilePic.style.display = "block";
  video.style.display = "none";

  // Store base64 image in hidden input for form submission
  webcamPhotoInput.value = dataUrl;
};

$(() => {
  $("form :input:not(button):first").focus();
  $(".err:first").prev().focus();
  $(".err:first").prev().find(":input:first").focus();

  $("label.upload input[type=file]").on("change", (e) => {
    const f = e.target.files[0];
    const img = $(e.target).closest(".avatar-preview").find("img")[0];

    if (!img) return;

    img.dataset.src ??= img.src;

    if (f?.type.startsWith("image/")) {
      img.src = URL.createObjectURL(f);
    } else {
      img.src = img.dataset.src;
      e.target.value = "";
    }
  });

  // Reset form
  // TODO
  $("[type=reset]").on("click", (e) => {
    e.preventDefault();
    location = location;
  });

  // Auto uppercase
  $("[data-upper]").on("input", (e) => {
    const a = e.target.selectionStart;
    const b = e.target.selectionEnd;
    e.target.value = e.target.value.toUpperCase();
    e.target.setSelectionRange(a, b);
  });

  $("#stop-cam").hide();
  $("#take-photo").hide();

  // Show stop cam and take photo buttons when start cam is clicked
  $("#start-cam").click(function () {
    $("#stop-cam").show();
    $("#take-photo").show();
  });

  // Hide the start cam button after starting the cam
  $("#start-cam").click(function () {
    $(this).hide();
  });

  // Function to start webcam
  $("#start-cam").click(function () {
    startCam();
  });

  // Function to stop the webcam
  $("#stop-cam").click(function () {
    stopCam();
    $("#start-cam").show();
    $("#stop-cam").hide();
    $("#take-photo").hide();
  });

  // Function to take photo
  $("#take-photo").click(function () {
    takePhoto();
    $("#start-cam").show();
    $("#stop-cam").hide();
    $("#take-photo").hide();
  });

  $(".err").each(function () {
    const field = $(this).data("field");
    const errorMessage = $(this).data("error");

    const inputField = $('[name="' + field + '"]');

    if (inputField.length) {
      const errorDiv = $('<div class="error-message"></div>').text(
        errorMessage
      );

      inputField.after(errorDiv);

      inputField.addClass("input-error");
    }
  });

  $(".delete-photo-btn").on("click", function (event) {
    let confirmed = confirm(
      "Are you sure you want to delete your profile picture?"
    );

    if (!confirmed) {
      event.preventDefault();
    }
  });


});

