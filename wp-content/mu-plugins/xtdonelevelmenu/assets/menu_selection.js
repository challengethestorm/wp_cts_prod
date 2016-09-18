function getBrowser() {
    // jquery code//
    var ua = navigator.userAgent.toLowerCase();
    var match = /(chrome)[ \/]([\w.]+)/.exec(ua) ||
        /(webkit)[ \/]([\w.]+)/.exec(ua) ||
        /(opera)(?:.*version|)[ \/]([\w.]+)/.exec(ua) ||
        /(msie) ([\w.]+)/.exec(ua) ||
        ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec(ua) ||
        [];

    var ver = match[2] || "0";
    var fi = ver.indexOf('.');
    var fi2 = ver.indexOf('.', fi);
    if (fi2 != -1) {
        ver = parseFloat(ver.substring(0, fi2));
    }
    return {
        name: match[1] || "",
        version: ver
    };
}

function updateSelectedMenu() {
    var loc = document.location.protocol + '//' +document.location.host + document.location.pathname;
    var scripts = document.getElementsByTagName('script');
    var parentDiv = scripts[scripts.length - 1].parentNode;
    var parentNode;
    var selectTag;
    var sibling;
    for (var i = 0; i < parentDiv.childNodes.length; i++) {
        sibling = parentDiv.childNodes[i];

        if (sibling.tagName == 'UL') {
            parentNode = sibling;
        }
        if (sibling.tagName == 'SELECT') {
            selectTag = sibling;
        }
    }

    var ul = parentNode;
    var href;
    for (var i = 0; i < ul.childNodes.length; i++) {
        if (ul.childNodes[i].tagName == "LI") {
            href = ul.childNodes[i].childNodes[0].href;
            if (ul.childNodes[i].childNodes[0].className == 'selected') {
                ul.childNodes[i].childNodes[0].className = '';
            }

            if (loc == href) {
                ul.childNodes[i].childNodes[0].className = 'selected';
                break;
            }
        }
    }

    if (selectTag) {
        var useInner = true;
        var opt;
        var browser = getBrowser();
        if (browser.name == "msie" && browser.version < 9) {
            useInner = false;
        }

        if (useInner) {

            var optionsHtml = "<option value=''>MENU</option>";
            optionsHtml += "<option value=''>----------------------------------</option>";
        } else {
            selectTag.options.remove(0);

            opt = document.createElement('OPTION');
            opt.text = 'MENU';
            opt.value = '';
            selectTag.options.add(opt);

            opt = document.createElement('OPTION');
            opt.text = '----------------------------------';
            opt.value = '';
            selectTag.options.add(opt);
        }


        var href, label, notSelected = true;

        for (var i = 0; i < parentNode.childNodes.length; i++) {
            if (parentNode.childNodes[i].tagName == "LI") {
                href = parentNode.childNodes[i].childNodes[0].href;
                label = parentNode.childNodes[i].childNodes[0].innerText || parentNode.childNodes[i].childNodes[0].textContent;
                if (loc == href && notSelected) {
                    if (useInner) {
                        optionsHtml += "<option value='" + href + "' selected>" + label + "</option>";
                        notSelected = false;
                    } else {
                        opt = document.createElement('OPTION');
                        opt.text = label;
                        opt.value = href;
                        opt.setAttribute('selected', 'selected');
                        selectTag.options.add(opt);
                    }
                } else {
                    if (useInner) {
                        optionsHtml += "<option value='" + href + "'>" + label + "</option>";
                    } else {
                        opt = document.createElement('OPTION');
                        opt.text = label;
                        opt.value = href;
                        selectTag.options.add(opt);
                    }
                }
            }
        }

        if (useInner) {
            selectTag.innerHTML = optionsHtml;
        }
        mobileMenuChange(selectTag);
    }

}

function mobileMenuChange(selectTag) {
    selectTag.onchange = function() {
        if (selectTag.selectedIndex < 2) {
            return;
        }
        location.assign(selectTag.options[selectTag.selectedIndex].value);
    }
}

updateSelectedMenu();