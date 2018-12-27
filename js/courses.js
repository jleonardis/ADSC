(function($) {

  displayCourses();

  $("#programSelect").change(displayCourses);

  function displayCourses() {
    var programId = $('#programSelect').val();
    if(programId) {
      $(".table-head").show();
    }
    else {
      $('.table-head').hide();
    }
    $(".course-row").hide();
    $(".course-row-" + programId).show();
  }

  $('.course-row').click(function() {
    window.location = $(this).data("href");
  });


  $('.select-teacher').click(function() {
    var id = $(this).attr('id');
  })

})(jQuery);
