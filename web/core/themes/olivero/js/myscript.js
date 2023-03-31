(function ($) {
  $(document).ready(function() {
    var elements = document.querySelectorAll(".views-field-field-apr-teamlead-1 .form-select  > *:not(:nth-child(3))");
    var elements2 = document.querySelectorAll(".views-field-field-apr-teamlead .form-select  > *:not(:nth-child(4))");
    var elements3 = document.querySelectorAll(".views-field-field-apr-teamlead-1 .form-submit");
    var elements4 = document.querySelectorAll(".views-field-field-apr-teamlead .form-submit");
    var elements6 = document.querySelectorAll(".editablefields-form");
    var elements7 = document.querySelectorAll(".views-field-field-paid");

    $('.form-submit').off('click');
    $('.form-submit').off('keypress');
    $('.form-submit').off('mousedown');
    elements.forEach(function(element) {
        element.parentNode.removeChild(element);
    });
    elements2.forEach(function(element) {
        element.parentNode.removeChild(element);
    });
    elements3.forEach(function(element) {
        element.setAttribute("value", "Approve");
	element.addEventListener("click", function(event){
        event.preventDefault();
	var table = element.parentElement.parentElement.parentElement.parentElement.getElementsByClassName("views-field views-field-name");
        var name = table[0].firstChild.innerHTML;
        $('body').append('<div class="my-confirm-background"><div class="my-confirm">Are you sure want to Approve the vacation for ' + name + '?<div class="my-confirm-buttons"><button class="my-confirm-ok">Yes</button><button class="my-confirm-cancel" onclick="cancel();">No</button></div></div></div>');
        var elements8 = document.querySelectorAll(".my-confirm-ok");
        elements8.forEach(function(elemente) {
        elemente.addEventListener("click", function(event){
        element.parentElement.submit();
        elemente.parentElement.removeChild(elemente.parentElement);
        });
        });
        });
    });
    elements4.forEach(function(element) {
        element.setAttribute("value", "Cancel");
	element.addEventListener("click", function(event){
        event.preventDefault();
	var table = element.parentElement.parentElement.parentElement.parentElement.getElementsByClassName("views-field views-field-name");
	var name = table[0].firstChild.innerHTML;
        $('body').append('<div class="my-confirm-background"><div class="my-confirm">Are you sure want to Cancel the vacation for ' + name + '?<div class="my-confirm-buttons"><button class="my-confirm-ok">Yes</button><button class="my-confirm-cancel" onclick="cancel();">No</button></div></div></div>');
        var elements8 = document.querySelectorAll(".my-confirm-ok");
        elements8.forEach(function(elemente) {
        elemente.addEventListener("click", function(event){
        element.parentElement.submit();
        elemente.parentElement.removeChild(elemente.parentElement);
        });
        });
        });

    });
  });
})(jQuery);
function cancel(){
var elements8 = document.querySelectorAll(".my-confirm-background");
elements8.forEach(function(element) {
        element.parentNode.removeChild(element);
    });
};
