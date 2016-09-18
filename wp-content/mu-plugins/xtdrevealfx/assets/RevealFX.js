(function(e) {
    window.Modernizr = function(e, t, n) {
        function r(e) {
            v.cssText = e
        }

        function i(e, t) {
            return r(prefixes.join(e + ";") + (t || ""))
        }

        function s(e, t) {
            return typeof e === t
        }

        function o(e, t) {
            return !!~("" + e).indexOf(t)
        }

        function u(e, t) {
            for (var r in e) {
                var i = e[r];
                if (!o(i, "-") && v[i] !== n) return t == "pfx" ? i : !0
            }
            return !1
        }

        function a(e, t, r) {
            for (var i in e) {
                var o = t[e[i]];
                if (o !== n) return r === !1 ? e[i] : s(o, "function") ? o.bind(r || t) : o
            }
            return !1
        }

        function f(e, t, n) {
            var r = e.charAt(0).toUpperCase() + e.slice(1),
                i = (e + " " + b.join(r + " ") + r).split(" ");
            return s(t, "string") || s(t, "undefined") ? u(i, t) : (i = (e + " " + w.join(r + " ") + r).split(" "), a(i, t, n))
        }
        var l = "2.7.1",
            c = {},
            h = t.documentElement,
            p = "modernizr",
            d = t.createElement(p),
            v = d.style,
            m, g = {}.toString,
            y = "Webkit Moz O ms",
            b = y.split(" "),
            w = y.toLowerCase().split(" "),
            E = {},
            S = {},
            x = {},
            T = [],
            N = T.slice,
            C, k = {}.hasOwnProperty,
            L;
        !s(k, "undefined") && !s(k.call, "undefined") ? L = function(e, t) {
            return k.call(e, t)
        } : L = function(e, t) {
            return t in e && s(e.constructor.prototype[t], "undefined")
        }, Function.prototype.bind || (Function.prototype.bind = function(e) {
            var t = this;
            if (typeof t != "function") throw new TypeError;
            var n = N.call(arguments, 1),
                r = function() {
                    if (this instanceof r) {
                        var i = function() {};
                        i.prototype = t.prototype;
                        var s = new i,
                            o = t.apply(s, n.concat(N.call(arguments)));
                        return Object(o) === o ? o : s
                    }
                    return t.apply(e, n.concat(N.call(arguments)))
                };
            return r
        }), E.csstransitions = function() {
            return f("transition")
        };
        for (var A in E) L(E, A) && (C = A.toLowerCase(), c[C] = E[A](), T.push((c[C] ? "" : "no-") + C));
        return c.addTest = function(e, t) {
            if (typeof e == "object")
                for (var r in e) L(e, r) && c.addTest(r, e[r]);
            else {
                e = e.toLowerCase();
                if (c[e] !== n) return c;
                t = typeof t == "function" ? t() : t, typeof enableClasses != "undefined" && enableClasses && (h.className += " " + (t ? "" : "no-") + e), c[e] = t
            }
            return c
        }, r(""), d = m = null, c._version = l, c._domPrefixes = w, c._cssomPrefixes = b, c.testProp = function(e) {
            return u([e])
        }, c.testAllProps = f, c
    }(this, this.document),
    function(e, t, n) {
        function r(e) {
            return "[object Function]" == d.call(e)
        }

        function i(e) {
            return "string" == typeof e
        }

        function s() {}

        function o(e) {
            return !e || "loaded" == e || "complete" == e || "uninitialized" == e
        }

        function u() {
            var e = v.shift();
            m = 1, e ? e.t ? h(function() {
                ("c" == e.t ? k.injectCss : k.injectJs)(e.s, 0, e.a, e.x, e.e, 1)
            }, 0) : (e(), u()) : m = 0
        }

        function a(e, n, r, i, s, a, f) {
            function l(t) {
                if (!d && o(c.readyState) && (w.r = d = 1, !m && u(), c.onload = c.onreadystatechange = null, t)) {
                    "img" != e && h(function() {
                        b.removeChild(c)
                    }, 50);
                    for (var r in T[n]) T[n].hasOwnProperty(r) && T[n][r].onload()
                }
            }
            var f = f || k.errorTimeout,
                c = t.createElement(e),
                d = 0,
                g = 0,
                w = {
                    t: r,
                    s: n,
                    e: s,
                    a: a,
                    x: f
                };
            1 === T[n] && (g = 1, T[n] = []), "object" == e ? c.data = n : (c.src = n, c.type = e), c.width = c.height = "0", c.onerror = c.onload = c.onreadystatechange = function() {
                l.call(this, g)
            }, v.splice(i, 0, w), "img" != e && (g || 2 === T[n] ? (b.insertBefore(c, y ? null : p), h(l, f)) : T[n].push(c))
        }

        function f(e, t, n, r, s) {
            return m = 0, t = t || "j", i(e) ? a("c" == t ? E : w, e, t, this.i++, n, r, s) : (v.splice(this.i++, 0, e), 1 == v.length && u()), this
        }

        function l() {
            var e = k;
            return e.loader = {
                load: f,
                i: 0
            }, e
        }
        var c = t.documentElement,
            h = e.setTimeout,
            p = t.getElementsByTagName("script")[0],
            d = {}.toString,
            v = [],
            m = 0,
            g = "MozAppearance" in c.style,
            y = g && !!t.createRange().compareNode,
            b = y ? c : p.parentNode,
            c = e.opera && "[object Opera]" == d.call(e.opera),
            c = !!t.attachEvent && !c,
            w = g ? "object" : c ? "script" : "img",
            E = c ? "script" : w,
            S = Array.isArray || function(e) {
                return "[object Array]" == d.call(e)
            },
            x = [],
            T = {},
            N = {
                timeout: function(e, t) {
                    return t.length && (e.timeout = t[0]), e
                }
            },
            C, k;
        k = function(e) {
            function t(e) {
                var e = e.split("!"),
                    t = x.length,
                    n = e.pop(),
                    r = e.length,
                    n = {
                        url: n,
                        origUrl: n,
                        prefixes: e
                    },
                    i, s, o;
                for (s = 0; s < r; s++) o = e[s].split("="), (i = N[o.shift()]) && (n = i(n, o));
                for (s = 0; s < t; s++) n = x[s](n);
                return n
            }

            function o(e, i, s, o, u) {
                var a = t(e),
                    f = a.autoCallback;
                a.url.split(".").pop().split("?").shift(), a.bypass || (i && (i = r(i) ? i : i[e] || i[o] || i[e.split("/").pop().split("?")[0]]), a.instead ? a.instead(e, i, s, o, u) : (T[a.url] ? a.noexec = !0 : T[a.url] = 1, s.load(a.url, a.forceCSS || !a.forceJS && "css" == a.url.split(".").pop().split("?").shift() ? "c" : n, a.noexec, a.attrs, a.timeout), (r(i) || r(f)) && s.load(function() {
                    l(), i && i(a.origUrl, u, o), f && f(a.origUrl, u, o), T[a.url] = 2
                })))
            }

            function u(e, t) {
                function n(e, n) {
                    if (e) {
                        if (i(e)) n || (f = function() {
                            var e = [].slice.call(arguments);
                            l.apply(this, e), c()
                        }), o(e, f, t, 0, u);
                        else if (Object(e) === e)
                            for (p in h = function() {
                                var t = 0,
                                    n;
                                for (n in e) e.hasOwnProperty(n) && t++;
                                return t
                            }(), e) e.hasOwnProperty(p) && (!n && !--h && (r(f) ? f = function() {
                                var e = [].slice.call(arguments);
                                l.apply(this, e), c()
                            } : f[p] = function(e) {
                                return function() {
                                    var t = [].slice.call(arguments);
                                    e && e.apply(this, t), c()
                                }
                            }(l[p])), o(e[p], f, t, p, u))
                    } else !n && c()
                }
                var u = !!e.test,
                    a = e.load || e.both,
                    f = e.callback || s,
                    l = f,
                    c = e.complete || s,
                    h, p;
                n(u ? e.yep : e.nope, !!a), a && n(a)
            }
            var a, f, c = this.yepnope.loader;
            if (i(e)) o(e, 0, c, 0);
            else if (S(e))
                for (a = 0; a < e.length; a++) f = e[a], i(f) ? o(f, 0, c, 0) : S(f) ? k(f) : Object(f) === f && u(f, c);
            else Object(e) === e && u(e, c)
        }, k.addPrefix = function(e, t) {
            N[e] = t
        }, k.addFilter = function(e) {
            x.push(e)
        }, k.errorTimeout = 1e4, null == t.readyState && t.addEventListener && (t.readyState = "loading", t.addEventListener("DOMContentLoaded", C = function() {
            t.removeEventListener("DOMContentLoaded", C, 0), t.readyState = "complete"
        }, 0)), e.yepnope = l(), e.yepnope.executeStack = u, e.yepnope.injectJs = function(e, n, r, i, a, f) {
            var l = t.createElement("script"),
                c, d, i = i || k.errorTimeout;
            l.src = e;
            for (d in r) l.setAttribute(d, r[d]);
            n = f ? u : n || s, l.onreadystatechange = l.onload = function() {
                !c && o(l.readyState) && (c = 1, n(), l.onload = l.onreadystatechange = null)
            }, h(function() {
                c || (c = 1, n(1))
            }, i), a ? l.onload() : p.parentNode.insertBefore(l, p)
        }, e.yepnope.injectCss = function(e, n, r, i, o, a) {
            var i = t.createElement("link"),
                f, n = a ? u : n || s;
            i.href = e, i.rel = "stylesheet", i.type = "text/css";
            for (f in r) i.setAttribute(f, r[f]);
            o || (p.parentNode.insertBefore(i, p), h(n, 0))
        }
    }(this, document), Modernizr.load = function() {
        yepnope.apply(window, [].slice.call(arguments, 0))
    };
    var t = Modernizr.csstransitions;
    window.scrollReveal = function(n) {
        function r(e) {
            this.docElem = n.document.documentElement;
            this.options = this.extend(this.defaults, e);
            if (this.options.init == true) this.init()
        }
        r.prototype = {
            defaults: {
                effect: "slide",
                after: "0s",
                enter: "bottom",
                move: "24px",
                over: "0.66s",
                easing: "ease-in-out",
                viewportFactor: .33,
                reset: false,
                init: true
            },
            instanceOptions: {
                effect: "slide",
                after: "0s",
                enter: "bottom",
                move: "24px",
                over: "0.66s",
                easing: "ease-in-out",
                viewportFactor: .33
            },
            init: function() {
                this.scrolled = false;
                var r = this;
                if (t) {
                    this.elems = Array.prototype.slice.call(this.docElem.querySelectorAll("[data-scrollReveal]"))
                } else {
                    this.elems = e(document).find("[data-scrollReveal]")
                }
                e.each(this.elems, function(t, n) {
                    e(n).show();
                    if (e(n).attr("style")) {
                        e(n).attr("scrollReveal-style", e(n).attr("style"))
                    }
                    r.update(n)
                });
                var i = function() {
                    if (!r.scrolled) {
                        r.scrolled = true;
                        setTimeout(function() {
                            r._scrollPage()
                        }, 60)
                    }
                };
                var s = function() {
                    function e() {
                        r._scrollPage();
                        r.resizeTimeout = null
                    }
                    if (r.resizeTimeout) {
                        clearTimeout(r.resizeTimeout)
                    }
                    r.resizeTimeout = setTimeout(e, 200)
                };
                if (t) {
                    n.addEventListener("scroll", i, false);
                    n.addEventListener("resize", s, false)
                } else {
                    n.attachEvent("onscroll", i, false);
                    n.attachEvent("onresize", s, false)
                }
            },
            _scrollPage: function() {
                var t = this;
                e.each(this.elems, function(e, n) {
                    t.update(n)
                });
                this.scrolled = false
            },
            parseLanguage: function(t) {
                var r = t.getAttribute("data-scrollReveal");
                var i = n.scrollEffects[r];
                var s = {};
                e.each(i, function(t, n) {
                    if (typeof n === "object") {
                        e.each(n, function(e, t) {
                            switch (e) {
                                case "from":
                                    s.enter = t;
                                    break;
                                case "distance":
                                    s.move = t;
                                    s.distance = t;
                                    break;
                                default:
                                    s[e] = t;
                                    break
                            }
                        })
                    } else {
                        s[t] = n
                    }
                });
                var o = i.defaultDelay;
                if (o) {
                    s["after"] = o
                }
                return s
            },
            update: function(n) {
                var r = this.genCSS(n);
                if (!n.getAttribute("data-scrollReveal-initialized")) {
                    if (t) {
                        n.setAttribute("style", r.initial)
                    }
                    n.setAttribute("data-scrollReveal-initialized", true);
                    jQuery(n).find('*').trigger('cp-revealfx-initialized');
                }
                if (!this.isElementInViewport(n, this.instanceOptions.viewportFactor)) {
                    if (!t) {
                        if (this.instanceOptions.reset) {
                            e(n).stop().fadeTo(10, 0, function() {
                                n.removeAttribute("data-scrollReveal-complete")
                            })
                        }
                    } else {
                        if (this.instanceOptions.reset) {
                            n.setAttribute("style", r.initial + r.reset);
                            n.removeAttribute("data-scrollReveal-complete")
                        }
                    }
                    return
                }
                if (n.getAttribute("data-scrollReveal-complete")) return;
                if (this.isElementInViewport(n, this.instanceOptions.viewportFactor)) {
                    if (!t) {
                        if (!n.getAttribute("data-scrollReveal-ie-initialized")) {
                            e(n).stop().fadeTo(1, 0)
                        }
                        this.executeIEEffect(n, this.instanceOptions)
                    } else {
                        n.setAttribute("style", r.target + r.transition);
                        var i = this;
                        setTimeout(function() {
                            n.removeAttribute("style");
                            n.setAttribute("style", r.original);
                            if (!i.instanceOptions.reset) {
                                n.setAttribute("data-scrollReveal-complete", true);
                                jQuery(n).find('*').trigger('cp-revealfx-complete');
                            }
                        }, r.totalDuration)
                    }
                    return
                }
            },
            genCSS: function(t) {
                var n = this.parseLanguage(t),
                    r, i;
                this.instanceOptions = e.extend(this.options, n);
                this.instanceOptions.reset = false;
                var s = "";
                if (e(t).attr("scrollReveal-style")) {
                    s = e(t).attr("scrollReveal-style")
                }
                if (n.enter) {
                    if (n.enter == "top" || n.enter == "bottom") {
                        r = n.enter;
                        i = "y"
                    }
                    if (n.enter == "left" || n.enter == "right") {
                        r = n.enter;
                        i = "x"
                    }
                } else {
                    if (this.options.enter == "top" || this.options.enter == "bottom") {
                        r = this.options.enter;
                        i = "y"
                    }
                    if (this.options.enter == "left" || this.options.enter == "right") {
                        r = this.options.enter;
                        i = "x"
                    }
                } if (r == "top" || r == "left") {
                    if (n.move === "undefined") {
                        n.move = "-" + this.options.move
                    } else {
                        n.move = "-" + n.move
                    }
                }
                var o = n.move || this.options.move,
                    u = n.over || this.options.over,
                    a = n.after || this.options.after,
                    f = n.easing || this.options.easing;
                var l = "";
                var c = this.customEasing(n.easing);
                var h = "-webkit-transition: all " + u + " " + l + " " + a + ";" + "-moz-transition: all " + u + " " + l + " " + a + ";" + "-o-transition: all " + u + " " + l + " " + a + ";" + "transition: all " + u + " " + l + " " + a + ";" + "-webkit-perspective: 1000;" + "-webkit-backface-visibility: hidden;";
                h += c;
                var p = "-webkit-transition: all " + u + " " + l + " 0s;" + "-moz-transition: all " + u + " " + l + " 0s;" + "-o-transition: all " + u + " " + l + " 0s;" + "transition: all " + u + " " + l + " 0s;" + "-webkit-perspective: 1000;" + "-webkit-backface-visibility: hidden;";
                var d, v;
                switch (n.effect) {
                    case "slide":
                        d = "-webkit-transform: translate" + i + "(" + o + ");" + "-moz-transform: translate" + i + "(" + o + ");" + "transform: translate" + i + "(" + o + ");" + "opacity: " + n.opacity + ";";
                        v = "-webkit-transform: translate" + i + "(0);" + "-moz-transform: translate" + i + "(0);" + "transform: translate" + i + "(0);" + "opacity: 1;";
                        break;
                    case "zoom":
                        var m = n.start;
                        var g = 1;
                        var y = n.opacity;
                        var b = 1;
                        d = "-webkit-transform: scale(" + m + ");" + "-moz-transform: scale(" + m + ");" + "transform: scale(" + m + ");" + "opacity:" + y + ";";
                        v = "-webkit-transform: scale(" + g + ");" + "-moz-transform: scale(" + g + ");" + "transform: scale(" + g + ");" + "opacity:" + b + ";";
                        break;
                    case "fade":
                        d = "opacity: 0;";
                        v = "opacity: 1;";
                        break
                }
                if (d != v && d != undefined) {
                    d += "-webkit-transition: none;" + "-moz-transition: none;" + "-o-transition: none;" + "transition: none;";
                }
                if (d == undefined) {
                    d = ""
                }
                if (d == v) {
                    v = ""
                }
                return {
                    transition: h,
                    original: s,
                    initial: s + d,
                    target: s + v,
                    reset: p,
                    totalDuration: parseFloat(u) + parseFloat(a)
                }
            },
            getViewportH: function() {
                var t = this.docElem["clientHeight"],
                    r = n["innerHeight"] ? n["innerHeight"] : e(n).height();
                return t < r ? r : t
            },
            getOffset: function(e) {
                var t = 0,
                    n = 0;
                do {
                    if (!isNaN(e.offsetTop)) {
                        t += e.offsetTop
                    }
                    if (!isNaN(e.offsetLeft)) {
                        n += e.offsetLeft
                    }
                } while (e = e.offsetParent);
                return {
                    top: t,
                    left: n
                }
            },
            isElementInViewport: function(e, r) {
                var i = n.pageYOffset,
                    s = i + this.getViewportH(),
                    o = e.offsetHeight,
                    u = this.getOffset(e).top,
                    a = u + o,
                    r = r || 0;
                if (!t) {
                    i = document.body.scrollTop || document.documentElement.scrollTop;
                    s = i + this.getViewportH()
                }
                return u + o * r <= s && a >= i
            },
            customEasing: function(e) {
                var t;
                var n = "";
                var r = false;
                switch (e) {
                    case "quick-in":
                        t = "cubic-bezier(0.315, -0.185, 0.000, 1.270)";
                        r = "cubic-bezier(0.315, 0, 0.000, 1)";
                        break;
                    case "accelerate":
                        t = "cubic-bezier(0.885, 0.420, 0.000, 1.270)";
                        r = "cubic-bezier(0.885, 0.420, 0.000, 1)";
                        break;
                    case "super-fast":
                        t = "cubic-bezier(0.000, 0.870, 0.000, 0.890)";
                        break;
                    case "ease-in-out-back":
                        t = "cubic-bezier(0.680, -0.550, 0.265, 1.550)";
                        r = "cubic-bezier(0.680, 0, 0.265, 1)";
                        break;
                    case "ease-in-out-expo":
                        t = "cubic-bezier(1.000, 0.000, 0.000, 1.000)";
                        break;
                    case "ease-out-back":
                        t = "cubic-bezier(0.175, 0.885, 0.320, 1.275)";
                        r = "cubic-bezier(0.175, 0.885, 0.320, 1)";
                        break;
                    case "ease-out-circ":
                        t = "cubic-bezier(0.075, 0.820, 0.165, 1.000)";
                        break;
                    case "ease-in-back":
                        t = "cubic-bezier(0.600, -0.280, 0.735, 0.045)";
                        r = "cubic-bezier(0.600, 0, 0.735, 0.045)";
                        break;
                    case "ease-in-circ":
                        t = "cubic-bezier(0.600, 0.040, 0.980, 0.335)";
                        break;
                    case "ease":
                        t = "ease";
                        break;
                    case "ease-in":
                        t = "ease-in";
                        break;
                    case "ease-in-out":
                        t = "ease-in-out";
                        break;
                    case "ease-out":
                        t = "ease-out";
                        break
                }
                if (r) {
                    n += "-webkit-transition-timing-function:" + r + ";"
                }
                var i = ["-webkit-", "-moz-", "-o-", ""];
                for (var s = 0; s < i.length; s++) {
                    n += i[s] + "transition-timing-function:" + t + ";"
                }
                return n
            },
            executeIEEffect: function(t, n) {
                t.setAttribute("data-scrollReveal-ie-initialized", true);
                var r = n.move || this.options.move,
                    i = n.over || this.options.over,
                    s = n.after || this.options.after,
                    o = n.easing || this.options.easing;
                var u = n.enter;
                var a = new RegExp("\\d+");
                i = i.match(a);
                i = i[0];
                s = s.match(a);
                s = s[0];
                r = r.match(a);
                r = r[0];
                var f, l;
                e(t).stop(true, true).fadeTo(parseInt(i) + parseInt(s), 1, function() {
                    t.setAttribute("data-scrollReveal-complete", true)
                })
            },
            extend: function(e, t) {
                for (var n in t) {
                    if (t.hasOwnProperty(n)) {
                        e[n] = t[n]
                    }
                }
                return e
            }
        };
        return r
    }(window);
    var n = {
        after: "0s",
        enter: "bottom",
        over: "0.66s",
        easing: "ease-in-out",
        viewportFactor: .33,
        reset: true,
        init: false
    };
    window.extendScrollReveal = new scrollReveal(n);
    var r;
    if (!r) {
        r = new Object
    }
    r.UserAgent = navigator.userAgent.toLowerCase();
    r.isMobile = function(e, t) {
        var n = /iphone|ipod|android|blackberry|mini|windows\sce|windows\sphone|iemobile|palm|webos|series60|symbianos|meego/i.test(r.UserAgent);
        var i;
        if (window.matchMedia) {
            i = window.matchMedia("(max-width:" + t + "px)").matches
        } else {
            i = e < t
        }
        return n || i
    };
    r.isTablet = function(e, t, n) {
        var i = r.UserAgent;
        var s = "ontouchstart" in document.documentElement;
        var o = /ipad|Win64|tablet/i.test(i);
        var u;
        if (window.matchMedia) {
            u = window.matchMedia("(max-width:" + n + "px) and (min-width:" + (t + 1) + "px)").matches
        } else {
            u = e < n && e >= t
        }
        return s && (o || u)
    };
    var i = function(e, t) {
        var n = e["matchMedia"] || e["msMatchMedia"],
            r = t["clientWidth"],
            i = e["innerWidth"];
        return n && r < i && true === n("(min-width:" + i + "px)")["matches"] ? function() {
            return e["innerWidth"]
        } : function() {
            return t["clientWidth"]
        }
    }(window, document.documentElement);
    window.viewportWidth = i();
    window.mobileCheck = r.isMobile(viewportWidth, 480);
    window.isMobileDevice = mobileCheck;
    window.tabletCheck = r.isTablet(viewportWidth, 480, 750);
    window.isTabletDevice = tabletCheck || mobileCheck
})(menus_jQuery)