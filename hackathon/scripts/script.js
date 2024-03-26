(function ($) {
  $(document).ready(function () {
    $("#cssmenu").prepend(
      '<div id="menu-button"><i class="fa fa-bars" aria-hidden="true" class="menu-topo-bar"></i></div>'
    );
    $("#cssmenu #menu-button").on("click", function () {
      var menu = $(this).next("ul");
      if (menu.hasClass("open")) {
        menu.removeClass("open");
      } else {
        menu.addClass("open");
      }
    });
  });
})(jQuery);

var theToggle = document.getElementById("toggle");

// based on Todd Motto functions
// http://toddmotto.com/labs/reusable-js/

// hasClass
function hasClass(elem, className) {
  return new RegExp(" " + className + " ").test(" " + elem.className + " ");
}
// addClass
function addClass(elem, className) {
  if (!hasClass(elem, className)) {
    elem.className += " " + className;
  }
}
// removeClass
function removeClass(elem, className) {
  var newClass = " " + elem.className.replace(/[\t\r\n]/g, " ") + " ";
  if (hasClass(elem, className)) {
    while (newClass.indexOf(" " + className + " ") >= 0) {
      newClass = newClass.replace(" " + className + " ", " ");
    }
    elem.className = newClass.replace(/^\s+|\s+$/g, "");
  }
}
// toggleClass
function toggleClass(elem, className) {
  var newClass = " " + elem.className.replace(/[\t\r\n]/g, " ") + " ";
  if (hasClass(elem, className)) {
    while (newClass.indexOf(" " + className + " ") >= 0) {
      newClass = newClass.replace(" " + className + " ", " ");
    }
    elem.className = newClass.replace(/^\s+|\s+$/g, "");
  } else {
    elem.className += " " + className;
  }

  theToggle.onclick = function () {
    toggleClass(this, "on");
    return false;
  };
}

// …
