(function ($) {
  $(document).ready(function() {
    var elements = document.querySelectorAll(".views-field-field-apr-teamlead-1 .form-select  > *:not(:nth-child(3))");
    var elements2 = document.querySelectorAll(".views-field-field-apr-teamlead .form-select  > *:not(:nth-child(4))");
    var elements3 = document.querySelectorAll(".views-field-field-apr-teamlead-1 .form-submit");
    var elements4 = document.querySelectorAll(".views-field-field-apr-teamlead .form-submit");

    elements.forEach(function(element) {
        element.parentNode.removeChild(element);
    });
    elements2.forEach(function(element) {
        element.parentNode.removeChild(element);
    });
    elements3.forEach(function(element) {
        element.setAttribute("value", "Approve");
    });
    elements4.forEach(function(element) {
        element.setAttribute("value", "Cancel");
    });
  });
})(jQuery);
