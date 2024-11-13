window.addEventListener("scroll", function () {
  var burger = this.document.querySelector(".burger");
  var headerli1 = this.document.querySelector(".header-li1");
  var headerli2 = this.document.querySelector(".header-li2");
  var search = this.document.querySelector(".search-section");
  var searchWidth = this.document.querySelector(".search");
  burger.classList.toggle("burger-active", window.scrollY > 20);
  headerli1.classList.toggle("header-li1-active", window.scrollY > 20);
  headerli2.classList.toggle("header-li2-active", window.scrollY > 20);
  search.classList.toggle("search-section-active", window.scrollY > 20);
  searchWidth.classList.toggle("search-active", window.scrollY > 20);
  searchWidth.classList.remove("search", window.scrollY > 20);
  searchWidth.classList.add("search", window.scrollY <= 20);
})

function openNav() {
  var nav = document.getElementById("myNav");
  var style = document.createElement("style");
  var styleElement = document.querySelector("style");

  if (nav.style.height == "130vh") {
    nav.style.height = "0";
    styleElement.remove();
    document.body.classList.remove('no-scroll');
  } else {
    nav.style.height = "130vh";
    style.innerHTML = `
            *::-webkit-scrollbar {
                display: none;
            }
        `;
    document.head.appendChild(style);
    document.body.classList.add("no-scroll");
  }

}

function myFunction() {
  document.getElementById("myDropdown").classList.toggle("show");
}

// Close the dropdown if the user clicks outside of it
window.onclick = function (event) {
  if (!event.target.matches('.dropbtn')) {
    var dropdowns = document.getElementsByClassName("dropdown-content");
    var i;
    for (i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
      if (openDropdown.classList.contains('show')) {
        openDropdown.classList.remove('show');
      }
    }
  }
}

// Add an event listener for when the checkbox is checked/unchecked
window.addEventListener("change", function () {
  var checkbox = document.getElementById("checkbox");

  var newPassword_checkbox = document.getElementById("newPassoword-chekcbox");
  var passwordInput = document.getElementById("password");
  var newPasswordInput = document.getElementById("newPassowrd");

  if (checkbox.checked) {
    // Change the type of the password input to text to show the password
    passwordInput.type = "text";
  } else {
    // Change it back to password to hide the password
    passwordInput.type = "password";
  }


  var currentPasswordCheckbox = document.getElementById("currentPassCheckbox");
  var currentPasswordInput = document.getElementById("currentPassword");

  if (currentPasswordCheckbox.checked) {
    // Change the type of the password input to text to show the password
    currentPasswordInput.type = "text";
  } else {
    // Change it back to password to hide the password
    currentPasswordInput.type = "password";
  }

  if (newPassword_checkbox.checked) {
    // Change the type of the password input to text to show the password
    newPasswordInput.type = "text";
  } else {
    // Change it back to password to hide the password
    newPasswordInput.type = "password";
  }
});

window.addEventListener("change", function () {
  var currentPasswordCheckbox = document.getElementById("currentPassCheckbox");
  var currentPasswordInput = document.getElementById("currentPassword");

  if (currentPasswordCheckbox.checked) {
    // Change the type of the password input to text to show the password
    currentPasswordInput.type = "text";
  } else {
    // Change it back to password to hide the password
    currentPasswordInput.type = "password";
  }
});

window.addEventListener("change", function () {
  var newPasswordCheckbox = document.getElementById("newPassCheckbox");
  var newPasswordInput = document.getElementById("newPassword");


  if (newPasswordCheckbox.checked) {
    // Change the type of the password input to text to show the password
    newPasswordInput.type = "text";
  } else {
    // Change it back to password to hide the password
    newPasswordInput.type = "password";
  }
});

window.addEventListener("change", function () {
  var confirmPasswordCheckbox = document.getElementById("confirmPassCheckbox");
  var confirmPasswordInput = document.getElementById("confirmPassword");


  if (confirmPasswordCheckbox.checked) {
    // Change the type of the password input to text to show the password
    confirmPasswordInput.type = "text";
  } else {
    // Change it back to password to hide the password
    confirmPasswordInput.type = "password";
  }
});

function ShowHideDiv(checkbox) {
  switch (checkbox.id) {
    case "genre":
      var genre_list = document.getElementById("genre-list");
      genre_list.style.display = genre.checked ? "block" : "none";
      break;
    case "feature":
      var features_list = document.getElementById("features-list");
      features_list.style.display = feature.checked ? "block" : "none";
      break;
    default:
      //What ever you want do to
      break;
  }
}

$(() => {

  // Autofocus
  $('form :input:not(button):first').focus();
  $('.err:first').prev().focus();
  $('.err:first').prev().find(':input:first').focus();

  // Confirmation message
  $('[data-confirm]').on('click', e => {
    const text = e.target.dataset.confirm || 'Are you sure?';
    if (!confirm(text)) {
      e.preventDefault();
      e.stopImmediatePropagation();
    }
  });

  // Initiate GET request
  $('[data-get]').on('click', e => {
    e.preventDefault();
    const url = e.target.dataset.get;
    location = url || location;
  });

  // Initiate POST request
  $('[data-post]').on('click', e => {
    e.preventDefault();
    const url = e.target.dataset.post;
    const f = $('<form>').appendTo(document.body)[0];
    f.method = 'POST';
    f.action = url || location;
    f.submit();
  });

  // Reset form
  $('[type=reset]').on('click', e => {
    e.preventDefault();
    location = location;
  });

  // Auto uppercase
  $('[data-upper]').on('input', e => {
    const a = e.target.selectionStart;
    const b = e.target.selectionEnd;
    e.target.value = e.target.value.toUpperCase();
    e.target.setSelectionRange(a, b);
  });

  // Photo preview
  $('label.upload input[type=file]').on('change', e => {
    const f = e.target.files[0];
    const img = $(e.target).siblings('img')[0];

    if (!img) return;

    img.dataset.src ??= img.src;

    if (f?.type.startsWith('image/')) {
      img.src = URL.createObjectURL(f);
    }
    else {
      img.src = img.dataset.src;
      e.target.value = '';
    }
  });

});


