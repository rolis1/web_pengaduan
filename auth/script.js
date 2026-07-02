//--- LOGIN JS ---//

// Animasi fade-in saat halaman dimuat
document.addEventListener("DOMContentLoaded", function () {
  const container = document.querySelector(".container");
  setTimeout(() => {
    container.classList.add("loaded");
  });

  // Tampilkan popup error jika ada
  if (window.loginError) {
    Swal.fire({
      width: "400px",
      icon: "error",
      title: "Login Gagal",
      text: "Email atau Password salah.",
      confirmButtonText: "Coba Lagi",
      backdrop: "rgba(0,0,0,0.4)", // Semi-transparent black background
      allowOutsideClick: false, // Prevent closing by clicking outside
    });
  }

  // Tampilkan popup success jika ada
  if (window.loginSuccess) {
    const email =
      window.userEmail || window.loginSuccess.split("! ")[1] || "Pengguna";

    Swal.fire({
      width: "400px",
      icon: "success",
      title: "Login Berhasil",
      text: ` ${email}`,
      confirmButtonText: "Lanjutkan",
      backdrop: "rgba(0,0,0,0.4)", // Semi-transparent black background
      allowOutsideClick: false, // Prevent closing by clicking outside
    }).then((result) => {
      if (result.isConfirmed && window.loginRedirect) {
        window.location.href = window.loginRedirect;
      }
    });
  }

  // Navigasi ke halaman daftar dengan animasi
  const toRegisterLink = document.getElementById("to-register");
  if (toRegisterLink) {
    toRegisterLink.addEventListener("click", function (e) {
      e.preventDefault();
      const formContainer = document.getElementById("form-container");
      if (formContainer) {
        formContainer.classList.add("slide-right");
      }
      setTimeout(() => {
        window.location.href = "register.php";
      });
    });
  }
});

// --- TOGGLE PASSWORD (Lihat Password) --- //
function togglePassword(inputId, icon) {
  const input = document.getElementById(inputId);
  const isVisible = input.type === "text";

  input.type = isVisible ? "password" : "text";

  //ganti icon
  icon.classList.toggle("fa-eye-slash", !isVisible);
  icon.classList.toggle("fa-eye", isVisible);
}

//--- REGISTER JS ---//

// Validasi password real-time
document.getElementById("password").addEventListener("input", function (e) {
  const password = e.target.value;
  const lengthValid = password.length >= 8;
  const hasNumber = /\d/.test(password);
  const hasLetter = /[a-zA-Z]/.test(password);

  // Update tampilan persyaratan
  document.getElementById("req-length").style.color = lengthValid
    ? "green"
    : "red";
  document.getElementById("req-number").style.color = hasNumber
    ? "green"
    : "red";
  document.getElementById("req-letter").style.color = hasLetter
    ? "green"
    : "red";
});

// Handle form submission dengan AJAX
document
  .getElementById("registerForm")
  .addEventListener("submit", function (e) {
    e.preventDefault();
    const password = document.getElementById("password").value;

    // Validasi client-side tambahan
    if (password.length < 8) {
      Swal.fire({
        width: "400px",
        icon: "error",
        title: "Password terlalu pendek",
        text: "Password harus minimal 8 karakter",
        confirmButtonText: "OK",
      });
      return;
    }

    if (!/\d/.test(password)) {
      Swal.fire({
        width: "400px",
        icon: "error",
        title: "Password tidak valid",
        text: "Password harus mengandung angka",
        confirmButtonText: "OK",
      });
      return;
    }

    if (!/[a-zA-Z]/.test(password)) {
      Swal.fire({
        width: "400px",
        icon: "error",
        title: "Password tidak valid",
        text: "Password harus mengandung huruf",
        confirmButtonText: "OK",
      });
      return;
    }

    const formData = new FormData(this);

    fetch("register.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          Swal.fire({
            width: "400px",
            icon: "success",
            title: "Registrasi Berhasil!",
            text: `Selamat akun ${data.email} berhasil dibuat`,
            confirmButtonText: "OK",
          }).then(() => {
            window.location.href = "login.php";
          });
        } else {
          Swal.fire({
            width: "400px",
            icon: "error",
            title: "Gagal!",
            text: data.message,
            confirmButtonText: "Coba Lagi",
          });
        }
      })
      .catch((error) => {
        Swal.fire({
          width: "400px",
          icon: "error",
          title: "Terjadi Kesalahan!",
          text: "Tidak dapat melakukan registrasi. Silakan coba lagi.",
          confirmButtonText: "Coba Lagi",
        });
      });
  });