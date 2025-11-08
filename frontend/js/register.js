'use strict';

(function ($) {
  function showAlert(message, type) {
    const el = $('#registerAlert');
    el.removeClass('d-none alert-success alert-danger alert-warning').addClass('alert-' + type).text(message);
  }

  $('#registerForm').on('submit', function (e) {
    e.preventDefault();

    const username = $('#username').val().trim();
    const email = $('#email').val().trim();
    const password = $('#password').val();

    if (!username || !email || !password) {
      showAlert('Please fill in all required fields.', 'warning');
      return;
    }

    if (password.length < 8) {
      showAlert('Password must be at least 8 characters.', 'warning');
      return;
    }

    $.ajax({
      url: '/guvi-internship/backend/register.php',
      method: 'POST',
      dataType: 'json',
      data: { username, email, password },
      success: function (res) {
        if (res && res.success) {
          showAlert('Registration successful. Redirecting to login…', 'success');
          setTimeout(function () { window.location.href = 'login.html'; }, 1000);
        } else {
          showAlert(res && res.error ? res.error : 'Registration failed.', 'danger');
        }
      },
      error: function (xhr) {
        let msg = 'An error occurred.';
        try { msg = (xhr.responseJSON && xhr.responseJSON.error) || msg; } catch (_) {}
        showAlert(msg, 'danger');
      }
    });
  });
})(jQuery);
