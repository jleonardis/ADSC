var startDateElem = document.getElementById('startDate');
var endDateElem = document.getElementById('endDate');

startDateElem.addEventListener("change", function() {
  endDateElem.setAttribute("min", startDateElem.value);
}, false);

endDateElem.addEventListener("change", function() {
  startDateElem.setAttribute("max", endDateElem.value);
}, false);
