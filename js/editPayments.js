var paymentInputs = document.getElementsByClassName('paymentInput');

var paymentInput;
for(var i = 0; i < paymentInputs.length; i++) {
  paymentInput = paymentInputs[i];
  updateMax(paymentInput);
  paymentInputs[i].addEventListener("change", checkMax, false);
}

function checkMax(e) {
  var element = e.target;
  if(parseFloat(element.value) > parseFloat(element.getAttribute('max'))){
    alert("Ese monto es demasiado");
    element.value = element.getAttribute('max');
  }
  updateMax(element);
}
function updateMax(element) {
  var participantId = element.getAttribute('data-participantId');
  var quotaId = element.getAttribute('data-quotaId');
  var maxAmount = element.getAttribute('data-maxAmount');
  var participantQuotaPayments = Array.prototype.slice.call(document.getElementsByClassName('participantQuota-' + participantId + '-' + quotaId));
  var otherParticipantQuotaPayments;
  var max;
  participantQuotaPayments.forEach(function(elem) {
    otherParticipantQuotaPayments = participantQuotaPayments.filter(x => x !== elem);
    max = maxAmount - otherParticipantQuotaPayments.reduce((acc, curr) => acc + parseFloat(curr.value, 0), 0);
    elem.setAttribute('max', max);
  });
}
