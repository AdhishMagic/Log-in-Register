'use strict';

(function ($) {
  const token = localStorage.getItem('sessionToken');
  if (!token) {
    window.location.replace('login.html');
  }

  function setUserCore(user) {
    if (!user) return;
    $('#d-username').text(user.username || '—');
    $('#d-email').text(user.email || '—');
  }

  function showAlert(message, type) {
    const el = $('#profileAlert');
    el.removeClass('d-none alert-success alert-danger alert-warning').addClass('alert-' + type).text(message);
  }

  // Prefill core user details from localStorage if available
  try {
    const cached = JSON.parse(localStorage.getItem('userCore') || 'null');
    if (cached) setUserCore(cached);
  } catch (_) {}

  // Fetch profile data from backend
  function fetchProfile() {
    $.ajax({
      url: '/php/profile.php',
      method: 'GET',
      dataType: 'json',
      headers: { 'X-Session-Token': token },
      success: function (res) {
        if (res && res.success) {
          if (res.user) {
            localStorage.setItem('userCore', JSON.stringify(res.user));
            setUserCore(res.user);
          }
          if (res.profile) {
            $('#age').val(res.profile.age || '');
            $('#dob').val(res.profile.dob || '');
            $('#contact').val(res.profile.contact || '');
            $('#address').val(res.profile.address || '');
          }
        } else {
          // Invalid token or other error – force logout
          doLogout();
        }
      },
      error: function () {
        // On error, don't reveal details – just require re-login
        doLogout();
      }
    });
  }

  function doLogout() {
    localStorage.removeItem('sessionToken');
    localStorage.removeItem('userCore');
    window.location.replace('login.html');
  }

  $('#logoutBtn').on('click', function () { doLogout(); });

  $('#profileForm').on('submit', function (e) {
    e.preventDefault();
    const payload = {
      age: $('#age').val() || null,
      dob: $('#dob').val() || null,
      contact: $('#contact').val().trim() || null,
      address: $('#address').val().trim() || null
    };

    $.ajax({
      url: '/php/profile.php',
      method: 'POST',
      dataType: 'json',
      headers: { 'X-Session-Token': token },
      data: JSON.stringify({ action: 'update', profile: payload }),
      processData: false,
      contentType: 'application/json; charset=UTF-8',
      success: function (res) {
        if (res && res.success) {
          showAlert('Profile updated successfully.', 'success');
        } else {
          showAlert(res && res.error ? res.error : 'Update failed.', 'danger');
        }
      },
      error: function (xhr) {
        let msg = 'An error occurred.';
        try { msg = (xhr.responseJSON && xhr.responseJSON.error) || msg; } catch (_) {}
        showAlert(msg, 'danger');
      }
    });
  });

  // Initial fetch
  fetchProfile();
})(jQuery);
