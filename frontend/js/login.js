'use strict';

(function ($) {
  function showAlert(message, type) {
    const el = $('#loginAlert');
    el.removeClass('d-none alert-success alert-danger alert-warning').addClass('alert-' + type).text(message);
  }

  $('#loginForm').on('submit', function (e) {
    e.preventDefault();
    const username = $('#username').val().trim();
    const password = $('#password').val();

    if (!username || !password) {
      showAlert('Please enter both username and password.', 'warning');
      return;
    }

    $.ajax({
      url: '/guvi-internship/backend/login.php',
      method: 'POST',
      dataType: 'json',
      data: { username, password },
      success: function (res) {
        if (res && res.success && res.token) {
          // Store token in localStorage (no PHP sessions)
          localStorage.setItem('sessionToken', res.token);
          if (res.user) {
            localStorage.setItem('userCore', JSON.stringify(res.user));
          }
          window.location.href = 'profile.html';
        } else {
          showAlert(res && res.error ? res.error : 'Login failed.', 'danger');
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
