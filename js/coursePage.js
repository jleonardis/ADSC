(function($) {

  $('#search').click(function() {
    var results = false;
    var search = $('#searchBox').val().replace(/ /g, '').replace(/\n/g,'').toLowerCase();
    $('.table-row').each(function() {
      var text = $(this).text().replace(/ /g, '').replace(/\n/g,'').toLowerCase();
      if (text.indexOf(search) !== -1) {
        $(this).show();
        results=true;
      }
      else {
        $(this).hide();
      }
    });
    if(results){
      $('.table-head').show();
      $('#submit').show();
    }
    else {
      alert("tu busqueda no encontró nada")
    }
  });

})(jQuery)
