import { initDatesRange } from './_helpers';

const $ = require('jquery');

$(document).ready(function () {
  initDatesRange($('#availableRange'), $('#availableFrom'), $('#availableTo'), true);
});
