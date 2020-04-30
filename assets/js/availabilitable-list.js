const $ = require('jquery');

$(document).ready(function () {
  $('form[name="organization_selector"] select').on('change', function () {
    let $selectedOption = $('option:selected', this);
    window.location = $selectedOption.data('url');
  });
});
