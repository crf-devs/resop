import { initDatesRange } from './_helpers';

const $ = require('jquery');

$(document).ready(function () {
  initDatesRange($('#fromToRange'), $('#mission_startTime'), $('#mission_endTime'), true);
});
