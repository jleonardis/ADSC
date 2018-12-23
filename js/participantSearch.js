(function($) {


  async function searchNames() {
    var results = false;
    //change if we move away from spreading names across two td's
    var search = $('#searchBox').val().replace(/ /g, '').toLowerCase();
    $('.table-row').each(function() {
      var text = $(this).text().replace(/ /g, '').toLowerCase();
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
      $('.table-head').hide();
      alert("tu busqueda no encontró nada")
    }
  }

  //fire search on click or enter but only if the user has submitted more than 3 leters (so the search doesn't  take forever)
  $('#search').click(function() {
    if($('#searchBox').val().length > 3){
      searchNames();
    }
    else {
      if(confirm("Quieres hacer buscar con tan pocas letras?")) {
        searchNames();
      }
    }
  });
  $(document).keypress(function(e) {
    if(e.which === 13 && $('#searchBox').val().length > 3){
      searchNames();
    }
  });

  $('.participant-row').click(function() {
    window.location = $(this).data("href");
  });

})(jQuery)
