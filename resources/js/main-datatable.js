'use strict';

// Datatable (jquery)
$(function () {
  $('#datatable').DataTable({
    order: [[0, 'desc']]
  });

  $('.datatable').DataTable({
    order: [[0, 'desc']]
  });
});
