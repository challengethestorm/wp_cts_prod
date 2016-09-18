var xtd;
if (!xtd) xtd = {};

xtd.fleximenus_position_fix = function() {
	var divs = document.getElementsByTagName('DIV');
	var layout_container, menu_container, abs_div, layout_width;
	for (var i=0;i<divs.length;i++) {
		if (divs[i].className.indexOf('FM2_') != -1 && divs[i].className.indexOf('_container') != -1) {
			menu_container = divs[i];
			abs_div = menu_container.offsetParent;
			layout_container = abs_div.parentElement;
			if (!layout_container) layout_container = abs_div.parentNode;
			if (layout_container) {
				layout_width = layout_container.offsetWidth;
				abs_div.style.width = layout_width + "px";
			}
		}
	}
}

xtd.addLoadEvent = function(str) {
  var oldonload = window.onload;
  if (typeof window.onload != 'function') {
    window.onload = function() {
		eval(str);
	}
  } else {
    window.onload = function() {
      if (oldonload) {
        oldonload();
      }
      eval(str);
    }
  }
}

xtd.addResizeEvent = function(str) {
  var oldonload = window.onresize;
  if (typeof window.onresize != 'function') {
    window.onresize = function() {
		eval(str);
	}
  } else {
    window.onresize = function() {
      if (oldonload) {
        oldonload();
      }
      eval(str);
    }
  }
}

var str = "xtd.fleximenus_position_fix();";
xtd.addLoadEvent(str);
xtd.addResizeEvent(str);