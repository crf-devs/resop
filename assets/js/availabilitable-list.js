const $ = require('jquery');

$(document).ready(function () {
  $('form[name="organization_selector"] select').on('change', function () {
    window.location = window.location.pathname + '?organizationId=' + $(this).val();
  });
});
