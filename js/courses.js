(function($) {


  $("#programSelect").change(function() {
    var programId = $(this).val();
    $(".table-cell").hide();
    $(".table-head").show();
    $(".table-" + programId).show();
  });

  $('.course-row').click(function() {
    window.location = $(this).data("href");
  });
  
})(jQuery);
