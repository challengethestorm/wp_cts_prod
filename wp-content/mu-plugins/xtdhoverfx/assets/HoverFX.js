(function($) {

    /* Modernizr 2.7.2 (Custom Build) | MIT & BSD
     * Build: http://modernizr.com/download/#-rgba-csstransitions-touch-teststyles-testprop-testallprops-prefixes-domprefixes
     */
    ;
    var Modernizr = function(a, b, c) {
        function y(a) {
            i.cssText = a
        }

        function z(a, b) {
            return y(l.join(a + ";") + (b || ""))
        }

        function A(a, b) {
            return typeof a === b
        }

        function B(a, b) {
            return !!~("" + a).indexOf(b)
        }

        function C(a, b) {
            for (var d in a) {
                var e = a[d];
                if (!B(e, "-") && i[e] !== c) return b == "pfx" ? e : !0
            }
            return !1
        }

        function D(a, b, d) {
            for (var e in a) {
                var f = b[a[e]];
                if (f !== c) return d === !1 ? a[e] : A(f, "function") ? f.bind(d || b) : f
            }
            return !1
        }

        function E(a, b, c) {
            var d = a.charAt(0).toUpperCase() + a.slice(1),
                e = (a + " " + n.join(d + " ") + d).split(" ");
            return A(b, "string") || A(b, "undefined") ? C(e, b) : (e = (a + " " + o.join(d + " ") + d).split(" "), D(e, b, c))
        }
        var d = "2.7.2",
            e = {},
            f = b.documentElement,
            g = "modernizr",
            h = b.createElement(g),
            i = h.style,
            j, k = {}.toString,
            l = " -webkit- -moz- -o- -ms- ".split(" "),
            m = "Webkit Moz O ms",
            n = m.split(" "),
            o = m.toLowerCase().split(" "),
            p = {},
            q = {},
            r = {},
            s = [],
            t = s.slice,
            u, v = function(a, c, d, e) {
                var h, i, j, k, l = b.createElement("div"),
                    m = b.body,
                    n = m || b.createElement("body");
                if (parseInt(d, 10))
                    while (d--) j = b.createElement("div"), j.id = e ? e[d] : g + (d + 1), l.appendChild(j);
                return h = ["&#173;", '<style id="s', g, '">', a, "</style>"].join(""), l.id = g, (m ? l : n).innerHTML += h, n.appendChild(l), m || (n.style.background = "", n.style.overflow = "hidden", k = f.style.overflow, f.style.overflow = "hidden", f.appendChild(n)), i = c(l, a), m ? l.parentNode.removeChild(l) : (n.parentNode.removeChild(n), f.style.overflow = k), !!i
            },
            w = {}.hasOwnProperty,
            x;
        !A(w, "undefined") && !A(w.call, "undefined") ? x = function(a, b) {
            return w.call(a, b)
        } : x = function(a, b) {
            return b in a && A(a.constructor.prototype[b], "undefined")
        }, Function.prototype.bind || (Function.prototype.bind = function(b) {
            var c = this;
            if (typeof c != "function") throw new TypeError;
            var d = t.call(arguments, 1),
                e = function() {
                    if (this instanceof e) {
                        var a = function() {};
                        a.prototype = c.prototype;
                        var f = new a,
                            g = c.apply(f, d.concat(t.call(arguments)));
                        return Object(g) === g ? g : f
                    }
                    return c.apply(b, d.concat(t.call(arguments)))
                };
            return e
        }), p.touch = function() {
            var c;
            return "ontouchstart" in a || a.DocumentTouch && b instanceof DocumentTouch ? c = !0 : v(["@media (", l.join("touch-enabled),("), g, ")", "{#modernizr{top:9px;position:absolute}}"].join(""), function(a) {
                c = a.offsetTop === 9
            }), c
        }, p.rgba = function() {
            return y("background-color:rgba(150,255,150,.5)"), B(i.backgroundColor, "rgba")
        }, p.csstransitions = function() {
            return E("transition")
        };
        for (var F in p) x(p, F) && (u = F.toLowerCase(), e[u] = p[F](), s.push((e[u] ? "" : "no-") + u));
        return e.addTest = function(a, b) {
            if (typeof a == "object")
                for (var d in a) x(a, d) && e.addTest(d, a[d]);
            else {
                a = a.toLowerCase();
                if (e[a] !== c) return e;
                b = typeof b == "function" ? b() : b, typeof enableClasses != "undefined" && enableClasses && (f.className += " " + (b ? "" : "no-") + a), e[a] = b
            }
            return e
        }, y(""), h = j = null, e._version = d, e._prefixes = l, e._domPrefixes = o, e._cssomPrefixes = n, e.testProp = function(a) {
            return C([a])
        }, e.testAllProps = E, e.testStyles = v, e
    }(this, this.document);

    /*! Hammer.JS - v1.1.2 - 2014-04-25
     * http://eightmedia.github.io/hammer.js
     *
     * Copyright (c) 2014 Jorik Tangelder <j.tangelder@gmail.com>;
     * Licensed under the MIT license */


    ! function(a, b) {
        "use strict";

        function c() {
            d.READY || (s.determineEventTypes(), r.each(d.gestures, function(a) {
                u.register(a)
            }), s.onTouch(d.DOCUMENT, n, u.detect), s.onTouch(d.DOCUMENT, o, u.detect), d.READY = !0)
        }
        var d = function v(a, b) {
            return new v.Instance(a, b || {})
        };
        d.VERSION = "1.1.2", d.defaults = {
            behavior: {
                userSelect: "none",
                touchAction: "none",
                touchCallout: "none",
                contentZooming: "none",
                userDrag: "none",
                tapHighlightColor: "rgba(0,0,0,0)"
            }
        }, d.DOCUMENT = document, d.HAS_POINTEREVENTS = navigator.pointerEnabled || navigator.msPointerEnabled, d.HAS_TOUCHEVENTS = "ontouchstart" in a, d.IS_MOBILE = /mobile|tablet|ip(ad|hone|od)|android|silk/i.test(navigator.userAgent), d.NO_MOUSEEVENTS = d.HAS_TOUCHEVENTS && d.IS_MOBILE || d.HAS_POINTEREVENTS, d.CALCULATE_INTERVAL = 25;
        var e = {},
            f = d.DIRECTION_DOWN = "down",
            g = d.DIRECTION_LEFT = "left",
            h = d.DIRECTION_UP = "up",
            i = d.DIRECTION_RIGHT = "right",
            j = d.POINTER_MOUSE = "mouse",
            k = d.POINTER_TOUCH = "touch",
            l = d.POINTER_PEN = "pen",
            m = d.EVENT_START = "start",
            n = d.EVENT_MOVE = "move",
            o = d.EVENT_END = "end",
            p = d.EVENT_RELEASE = "release",
            q = d.EVENT_TOUCH = "touch";
        d.READY = !1, d.plugins = d.plugins || {}, d.gestures = d.gestures || {};
        var r = d.utils = {
                extend: function(a, c, d) {
                    for (var e in c)!c.hasOwnProperty(e) || a[e] !== b && d || (a[e] = c[e]);
                    return a
                },
                on: function(a, b, c) {
                    a.addEventListener(b, c, !1)
                },
                off: function(a, b, c) {
                    a.removeEventListener(b, c, !1)
                },
                each: function(a, c, d) {
                    var e, f;
                    if ("forEach" in a) a.forEach(c, d);
                    else if (a.length !== b) {
                        for (e = 0, f = a.length; f > e; e++)
                            if (c.call(d, a[e], e, a) === !1) return
                    } else
                        for (e in a)
                            if (a.hasOwnProperty(e) && c.call(d, a[e], e, a) === !1) return
                },
                inStr: function(a, b) {
                    return a.indexOf(b) > -1
                },
                inArray: function(a, b) {
                    if (a.indexOf) {
                        var c = a.indexOf(b);
                        return -1 === c ? !1 : c
                    }
                    for (var d = 0, e = a.length; e > d; d++)
                        if (a[d] === b) return d;
                    return !1
                },
                toArray: function(a) {
                    return Array.prototype.slice.call(a, 0)
                },
                hasParent: function(a, b) {
                    for (; a;) {
                        if (a == b) return !0;
                        a = a.parentNode
                    }
                    return !1
                },
                getCenter: function(a) {
                    var b = [],
                        c = [],
                        d = [],
                        e = [],
                        f = Math.min,
                        g = Math.max;
                    return 1 === a.length ? {
                        pageX: a[0].pageX,
                        pageY: a[0].pageY,
                        clientX: a[0].clientX,
                        clientY: a[0].clientY
                    } : (r.each(a, function(a) {
                        b.push(a.pageX), c.push(a.pageY), d.push(a.clientX), e.push(a.clientY)
                    }), {
                        pageX: (f.apply(Math, b) + g.apply(Math, b)) / 2,
                        pageY: (f.apply(Math, c) + g.apply(Math, c)) / 2,
                        clientX: (f.apply(Math, d) + g.apply(Math, d)) / 2,
                        clientY: (f.apply(Math, e) + g.apply(Math, e)) / 2
                    })
                },
                getVelocity: function(a, b, c) {
                    return {
                        x: Math.abs(b / a) || 0,
                        y: Math.abs(c / a) || 0
                    }
                },
                getAngle: function(a, b) {
                    var c = b.clientX - a.clientX,
                        d = b.clientY - a.clientY;
                    return 180 * Math.atan2(d, c) / Math.PI
                },
                getDirection: function(a, b) {
                    var c = Math.abs(a.clientX - b.clientX),
                        d = Math.abs(a.clientY - b.clientY);
                    return c >= d ? a.clientX - b.clientX > 0 ? g : i : a.clientY - b.clientY > 0 ? h : f
                },
                getDistance: function(a, b) {
                    var c = b.clientX - a.clientX,
                        d = b.clientY - a.clientY;
                    return Math.sqrt(c * c + d * d)
                },
                getScale: function(a, b) {
                    return a.length >= 2 && b.length >= 2 ? this.getDistance(b[0], b[1]) / this.getDistance(a[0], a[1]) : 1
                },
                getRotation: function(a, b) {
                    return a.length >= 2 && b.length >= 2 ? this.getAngle(b[1], b[0]) - this.getAngle(a[1], a[0]) : 0
                },
                isVertical: function(a) {
                    return a == h || a == f
                },
                setPrefixedCss: function(a, b, c, d) {
                    var e = ["", "Webkit", "Moz", "O", "ms"];
                    b = r.toCamelCase(b);
                    for (var f = 0; f < e.length; f++) {
                        var g = b;
                        if (e[f] && (g = e[f] + g.slice(0, 1).toUpperCase() + g.slice(1)), g in a.style) {
                            a.style[g] = (null == d || d) && c || "";
                            break
                        }
                    }
                },
                toggleBehavior: function(a, b, c) {
                    if (b && a && a.style) {
                        r.each(b, function(b, d) {
                            r.setPrefixedCss(a, d, b, c)
                        });
                        var d = c && function() {
                            return !1
                        };
                        "none" == b.userSelect && (a.onselectstart = d), "none" == b.userDrag && (a.ondragstart = d)
                    }
                },
                toCamelCase: function(a) {
                    return a.replace(/[_-]([a-z])/g, function(a) {
                        return a[1].toUpperCase()
                    })
                }
            },
            s = d.event = {
                preventMouseEvents: !1,
                started: !1,
                shouldDetect: !1,
                on: function(a, b, c, d) {
                    var e = b.split(" ");
                    r.each(e, function(b) {
                        r.on(a, b, c), d && d(b)
                    })
                },
                off: function(a, b, c, d) {
                    var e = b.split(" ");
                    r.each(e, function(b) {
                        r.off(a, b, c), d && d(b)
                    })
                },
                onTouch: function(a, b, c) {
                    var f = this,
                        g = function(e) {
                            var g, h = e.type.toLowerCase(),
                                i = d.HAS_POINTEREVENTS,
                                j = r.inStr(h, "mouse");
                            j && f.preventMouseEvents || (j && b == m && 0 === e.button ? (f.preventMouseEvents = !1, f.shouldDetect = !0) : i && b == m ? f.shouldDetect = 1 === e.buttons : j || b != m || (f.preventMouseEvents = !0, f.shouldDetect = !0), i && b != o && t.updatePointer(b, e), f.shouldDetect && (g = f.doDetect.call(f, e, b, a, c)), g == o && (f.preventMouseEvents = !1, f.shouldDetect = !1, t.reset()), i && b == o && t.updatePointer(b, e))
                        };
                    return this.on(a, e[b], g), g
                },
                doDetect: function(a, b, c, d) {
                    var e = this.getTouchList(a, b),
                        f = e.length,
                        g = b,
                        h = e.trigger,
                        i = f;
                    b == m ? h = q : b == o && (h = p, i = e.length - (a.changedTouches ? a.changedTouches.length : 1)), i > 0 && this.started && (g = n), this.started = !0;
                    var j = this.collectEventData(c, g, e, a);
                    return b != o && d.call(u, j), h && (j.changedLength = i, j.eventType = h, d.call(u, j), j.eventType = g, delete j.changedLength), g == o && (d.call(u, j), this.started = !1), g
                },
                determineEventTypes: function() {
                    var b;
                    return b = d.HAS_POINTEREVENTS ? a.PointerEvent ? ["pointerdown", "pointermove", "pointerup pointercancel lostpointercapture"] : ["MSPointerDown", "MSPointerMove", "MSPointerUp MSPointerCancel MSLostPointerCapture"] : d.NO_MOUSEEVENTS ? ["touchstart", "touchmove", "touchend touchcancel"] : ["touchstart mousedown", "touchmove mousemove", "touchend touchcancel mouseup"], e[m] = b[0], e[n] = b[1], e[o] = b[2], e
                },
                getTouchList: function(a, b) {
                    if (d.HAS_POINTEREVENTS) return t.getTouchList();
                    if (a.touches) {
                        if (b == n) return a.touches;
                        var c = [],
                            e = [].concat(r.toArray(a.touches), r.toArray(a.changedTouches)),
                            f = [];
                        return r.each(e, function(a) {
                            r.inArray(c, a.identifier) === !1 && f.push(a), c.push(a.identifier)
                        }), f
                    }
                    return a.identifier = 1, [a]
                },
                collectEventData: function(a, b, c, d) {
                    var e = k;
                    return r.inStr(d.type, "mouse") || t.matchType(j, d) ? e = j : t.matchType(l, d) && (e = l), {
                        center: r.getCenter(c),
                        timeStamp: Date.now(),
                        target: d.target,
                        touches: c,
                        eventType: b,
                        pointerType: e,
                        srcEvent: d,
                        preventDefault: function() {
                            var a = this.srcEvent;
                            a.preventManipulation && a.preventManipulation(), a.preventDefault && a.preventDefault()
                        },
                        stopPropagation: function() {
                            this.srcEvent.stopPropagation()
                        },
                        stopDetect: function() {
                            return u.stopDetect()
                        }
                    }
                }
            },
            t = d.PointerEvent = {
                pointers: {},
                getTouchList: function() {
                    var a = [];
                    return r.each(this.pointers, function(b) {
                        a.push(b)
                    }), a
                },
                updatePointer: function(a, b) {
                    a == o || a != o && 1 !== b.buttons ? delete this.pointers[b.pointerId] : (b.identifier = b.pointerId, this.pointers[b.pointerId] = b)
                },
                matchType: function(a, b) {
                    if (!b.pointerType) return !1;
                    var c = b.pointerType,
                        d = {};
                    return d[j] = c === (b.MSPOINTER_TYPE_MOUSE || j), d[k] = c === (b.MSPOINTER_TYPE_TOUCH || k), d[l] = c === (b.MSPOINTER_TYPE_PEN || l), d[a]
                },
                reset: function() {
                    this.pointers = {}
                }
            },
            u = d.detection = {
                gestures: [],
                current: null,
                previous: null,
                stopped: !1,
                startDetect: function(a, b) {
                    this.current || (this.stopped = !1, this.current = {
                        inst: a,
                        startEvent: r.extend({}, b),
                        lastEvent: !1,
                        lastCalcEvent: !1,
                        futureCalcEvent: !1,
                        lastCalcData: {},
                        name: ""
                    }, this.detect(b))
                },
                detect: function(a) {
                    if (this.current && !this.stopped) {
                        a = this.extendEventData(a);
                        var b = this.current.inst,
                            c = b.options;
                        return r.each(this.gestures, function(d) {
                            return !this.stopped && b.enabled && c[d.name] && d.handler.call(d, a, b) === !1 ? (this.stopDetect(), !1) : void 0
                        }, this), this.current && (this.current.lastEvent = a), a.eventType == o && this.stopDetect(), a
                    }
                },
                stopDetect: function() {
                    this.previous = r.extend({}, this.current), this.current = null, this.stopped = !0
                },
                getCalculatedData: function(a, b, c, e, f) {
                    var g = this.current,
                        h = !1,
                        i = g.lastCalcEvent,
                        j = g.lastCalcData;
                    i && a.timeStamp - i.timeStamp > d.CALCULATE_INTERVAL && (b = i.center, c = a.timeStamp - i.timeStamp, e = a.center.clientX - i.center.clientX, f = a.center.clientY - i.center.clientY, h = !0), (a.eventType == q || a.eventType == p) && (g.futureCalcEvent = a), (!g.lastCalcEvent || h) && (j.velocity = r.getVelocity(c, e, f), j.angle = r.getAngle(b, a.center), j.direction = r.getDirection(b, a.center), g.lastCalcEvent = g.futureCalcEvent || a, g.futureCalcEvent = a), a.velocityX = j.velocity.x, a.velocityY = j.velocity.y, a.interimAngle = j.angle, a.interimDirection = j.direction
                },
                extendEventData: function(a) {
                    var b = this.current,
                        c = b.startEvent,
                        d = b.lastEvent || c;
                    (a.eventType == q || a.eventType == p) && (c.touches = [], r.each(a.touches, function(a) {
                        c.touches.push({
                            clientX: a.clientX,
                            clientY: a.clientY
                        })
                    }));
                    var e = a.timeStamp - c.timeStamp,
                        f = a.center.clientX - c.center.clientX,
                        g = a.center.clientY - c.center.clientY;
                    return this.getCalculatedData(a, d.center, e, f, g), r.extend(a, {
                        startEvent: c,
                        deltaTime: e,
                        deltaX: f,
                        deltaY: g,
                        distance: r.getDistance(c.center, a.center),
                        angle: r.getAngle(c.center, a.center),
                        direction: r.getDirection(c.center, a.center),
                        scale: r.getScale(c.touches, a.touches),
                        rotation: r.getRotation(c.touches, a.touches)
                    }), a
                },
                register: function(a) {
                    var c = a.defaults || {};
                    return c[a.name] === b && (c[a.name] = !0), r.extend(d.defaults, c, !0), a.index = a.index || 1e3, this.gestures.push(a), this.gestures.sort(function(a, b) {
                        return a.index < b.index ? -1 : a.index > b.index ? 1 : 0
                    }), this.gestures
                }
            };
        d.Instance = function(a, b) {
            var e = this;
            c(), this.element = a, this.enabled = !0, r.each(b, function(a, c) {
                delete b[c], b[r.toCamelCase(c)] = a
            }), this.options = r.extend(r.extend({}, d.defaults), b || {}), this.options.behavior && r.toggleBehavior(this.element, this.options.behavior, !0), this.eventStartHandler = s.onTouch(a, m, function(a) {
                e.enabled && a.eventType == m ? u.startDetect(e, a) : a.eventType == q && u.detect(a)
            }), this.eventHandlers = []
        }, d.Instance.prototype = {
            on: function(a, b) {
                var c = this;
                return s.on(c.element, a, b, function(a) {
                    c.eventHandlers.push({
                        gesture: a,
                        handler: b
                    })
                }), c
            },
            off: function(a, b) {
                var c = this;
                return s.off(c.element, a, b, function(a) {
                    var d = r.inArray({
                        gesture: a,
                        handler: b
                    });
                    d !== !1 && c.eventHandlers.splice(d, 1)
                }), c
            },
            trigger: function(a, b) {
                b || (b = {});
                var c = d.DOCUMENT.createEvent("Event");
                c.initEvent(a, !0, !0), c.gesture = b;
                var e = this.element;
                return r.hasParent(b.target, e) && (e = b.target), e.dispatchEvent(c), this
            },
            enable: function(a) {
                return this.enabled = a, this
            },
            dispose: function() {
                var a, b;
                for (r.toggleBehavior(this.element, this.options.behavior, !1), a = -1; b = this.eventHandlers[++a];) r.off(this.element, b.gesture, b.handler);
                return this.eventHandlers = [], s.off(this.element, e[m], this.eventStartHandler), null
            }
        },
        function(a) {
            function b(b, d) {
                var e = u.current;
                if (!(d.options.dragMaxTouches > 0 && b.touches.length > d.options.dragMaxTouches)) switch (b.eventType) {
                    case m:
                        c = !1;
                        break;
                    case n:
                        if (b.distance < d.options.dragMinDistance && e.name != a) return;
                        var j = e.startEvent.center;
                        if (e.name != a && (e.name = a, d.options.dragDistanceCorrection && b.distance > 0)) {
                            var k = Math.abs(d.options.dragMinDistance / b.distance);
                            j.pageX += b.deltaX * k, j.pageY += b.deltaY * k, j.clientX += b.deltaX * k, j.clientY += b.deltaY * k, b = u.extendEventData(b)
                        }(e.lastEvent.dragLockToAxis || d.options.dragLockToAxis && d.options.dragLockMinDistance <= b.distance) && (b.dragLockToAxis = !0);
                        var l = e.lastEvent.direction;
                        b.dragLockToAxis && l !== b.direction && (b.direction = r.isVertical(l) ? b.deltaY < 0 ? h : f : b.deltaX < 0 ? g : i), c || (d.trigger(a + "start", b), c = !0), d.trigger(a, b), d.trigger(a + b.direction, b);
                        var q = r.isVertical(b.direction);
                        (d.options.dragBlockVertical && q || d.options.dragBlockHorizontal && !q) && b.preventDefault();
                        break;
                    case p:
                        c && b.changedLength <= d.options.dragMaxTouches && (d.trigger(a + "end", b), c = !1);
                        break;
                    case o:
                        c = !1
                }
            }
            var c = !1;
            d.gestures.Drag = {
                name: a,
                index: 50,
                handler: b,
                defaults: {
                    dragMinDistance: 10,
                    dragDistanceCorrection: !0,
                    dragMaxTouches: 1,
                    dragBlockHorizontal: !1,
                    dragBlockVertical: !1,
                    dragLockToAxis: !1,
                    dragLockMinDistance: 25
                }
            }
        }("drag"), d.gestures.Gesture = {
            name: "gesture",
            index: 1337,
            handler: function(a, b) {
                b.trigger(this.name, a)
            }
        },
        function(a) {
            function b(b, d) {
                var e = d.options,
                    f = u.current;
                switch (b.eventType) {
                    case m:
                        clearTimeout(c), f.name = a, c = setTimeout(function() {
                            f && f.name == a && d.trigger(a, b)
                        }, e.holdTimeout);
                        break;
                    case n:
                        b.distance > e.holdThreshold && clearTimeout(c);
                        break;
                    case p:
                        clearTimeout(c)
                }
            }
            var c;
            d.gestures.Hold = {
                name: a,
                index: 10,
                defaults: {
                    holdTimeout: 500,
                    holdThreshold: 2
                },
                handler: b
            }
        }("hold"), d.gestures.Release = {
            name: "release",
            index: 1 / 0,
            handler: function(a, b) {
                a.eventType == p && b.trigger(this.name, a)
            }
        }, d.gestures.Swipe = {
            name: "swipe",
            index: 40,
            defaults: {
                swipeMinTouches: 1,
                swipeMaxTouches: 1,
                swipeVelocityX: .6,
                swipeVelocityY: .6
            },
            handler: function(a, b) {
                if (a.eventType == p) {
                    var c = a.touches.length,
                        d = b.options;
                    if (c < d.swipeMinTouches || c > d.swipeMaxTouches) return;
                    (a.velocityX > d.swipeVelocityX || a.velocityY > d.swipeVelocityY) && (b.trigger(this.name, a), b.trigger(this.name + a.direction, a))
                }
            }
        },
        function(a) {
            function b(b, d) {
                var e, f, g = d.options,
                    h = u.current,
                    i = u.previous;
                switch (b.eventType) {
                    case m:
                        c = !1;
                        break;
                    case n:
                        c = c || b.distance > g.tapMaxDistance;
                        break;
                    case o:
                        "touchcancel" != b.srcEvent.type && b.deltaTime < g.tapMaxTime && !c && (e = i && i.lastEvent && b.timeStamp - i.lastEvent.timeStamp, f = !1, i && i.name == a && e && e < g.doubleTapInterval && b.distance < g.doubleTapDistance && (d.trigger("doubletap", b), f = !0), (!f || g.tapAlways) && (h.name = a, d.trigger(h.name, b)))
                }
            }
            var c = !1;
            d.gestures.Tap = {
                name: a,
                index: 100,
                handler: b,
                defaults: {
                    tapMaxTime: 250,
                    tapMaxDistance: 10,
                    tapAlways: !0,
                    doubleTapDistance: 20,
                    doubleTapInterval: 300
                }
            }
        }("tap"), d.gestures.Touch = {
            name: "touch",
            index: -1 / 0,
            defaults: {
                preventDefault: !1,
                preventMouse: !1
            },
            handler: function(a, b) {
                return b.options.preventMouse && a.pointerType == j ? void a.stopDetect() : (b.options.preventDefault && a.preventDefault(), void(a.eventType == q && b.trigger("touch", a)))
            }
        },
        function(a) {
            function b(b, d) {
                switch (b.eventType) {
                    case m:
                        c = !1;
                        break;
                    case n:
                        if (b.touches.length < 2) return;
                        var e = Math.abs(1 - b.scale),
                            f = Math.abs(b.rotation);
                        if (e < d.options.transformMinScale && f < d.options.transformMinRotation) return;
                        u.current.name = a, c || (d.trigger(a + "start", b), c = !0), d.trigger(a, b), f > d.options.transformMinRotation && d.trigger("rotate", b), e > d.options.transformMinScale && (d.trigger("pinch", b), d.trigger("pinch" + (b.scale < 1 ? "in" : "out"), b));
                        break;
                    case p:
                        c && b.changedLength < 2 && (d.trigger(a + "end", b), c = !1)
                }
            }
            var c = !1;
            d.gestures.Transform = {
                name: a,
                index: 45,
                defaults: {
                    transformMinScale: .01,
                    transformMinRotation: 1
                },
                handler: b
            }
        }("transform"), "function" == typeof define && define.amd ? define(function() {
            return d
        }) : "undefined" != typeof module && module.exports ? module.exports = d : a.Hammer = d
    }(window);


    window.initHoverFX = function(options, isUI, resizeEvent) {
        var defaults, item, opts, params, instanceName, className, classElements, doesCSS3, supportRGBA;
        item = $('.contentswap-effect');
        supportRGBA = Modernizr.rgba;
        doesCSS3 = Modernizr.csstransitions;

        opts = options;

        function setParams(elem) {

            className = $(elem).attr('class');
            classElements = className.split(" ");
            for (var i = 0; i <= item.length; i++) {
                for (var prop in opts) {
                    if (classElements[i] === prop) {
                        instanceName = prop;
                    }
                }
            }

            return opts ? params = opts[instanceName] : params = defaults[instanceName];
        }

        function getInner(elem, type) {

            var inner;
            switch (type) {
                case "overlay":
                    inner = $(elem).find('.swap-inner');
                    break;
                case "imageSwap":
                    inner = $(elem).find('.second-image');
                    break;
                case "caption":
                    inner = $(elem).find('.swap-inner');

                    break;
            }
            return inner;
        }

        function getDimms(elem) {
            var dims = {
                width: Math.abs(parseFloat($(elem).outerWidth(false))),
                height: Math.abs(parseFloat($(elem).outerHeight(false)))
            };
            return dims
        }

        function moveCSSProps(source, target) {
            var ml = source.css('margin-left');
            var mr = source.css('margin-right');
            var mt = source.css('margin-top');
            var mb = source.css('margin-bottom');
            var floatVal = source.css('float');
            source.css({
                'float': 'none',
                'margin-left': '0px',
                'margin-right': '0px',
                'margin-top': '0px',
                'margin-bottom': '0px'
            })
            target.css({
                'float': floatVal,
                'margin-left': ml,
                'margin-right': mr,
                'margin-top': mt,
                'margin-bottom': mb
            })

        }

        function setCaptionHeight(height, elem) {
            if (height != 'auto' || height != 'undefined') elem.css('height', parseInt(height) + 'px');
        }

        function genRGBA(hex, alpha, elem) {
            //          with both longhand and shorthand support (accepts both #fff or #fffff)
            if(hex.indexOf('#') == -1 || hex.indexOf('rgb(') > -1 || hex.indexOf('rgba') > -1) {
                return {
                    rgba : hex
                };
            }
            var opacity;
            alpha ? opacity = (alpha / 100).toFixed(2) : opacity = 1;

            function convertRGBDecimalToHex(rgb) {

                // check for RGB
                var regexRGB = /rgb *\( *([0-9]{1,3}) *, *([0-9]{1,3}) *, *([0-9]{1,3}) *\)/;
                var values = regexRGB.exec(rgb);

                // check for RGBA
                if (!values) {
                    var regexRGBA = /rgba *\( *([0-9]{1,3}) *, *([0-9]{1,3}) *, *([0-9]{1,3}) *, *(0.+[0-9]) *\)/;
                    values = regexRGBA.exec(rgb);
                }

                if (!values) return rgb;
                if (values.length != 5) {
                    return rgb; // fall back to what was given.              
                }
                var r = Math.round(parseFloat(values[1]));
                var g = Math.round(parseFloat(values[2]));
                var b = Math.round(parseFloat(values[3]));
                if (values[4]) {
                    opacity = values[4];
                }
                return "#" + (r + 0x10000).toString(16).substring(3).toUpperCase() + (g + 0x10000).toString(16).substring(3).toUpperCase() + (b + 0x10000).toString(16).substring(3).toUpperCase();
            }

            hex = convertRGBDecimalToHex(hex);

            var shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
            hex = hex.replace(shorthandRegex, function(m, r, g, b) {
                return r + r + g + g + b + b;
            });

            var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);

            function alhpaToHex(val) {
                var num = val * 255;
                return Math.floor(num).toString(16);
            }

            if(typeof(elem) !== 'undefined' && result) {
                switch (supportRGBA) {
                    case true:
                        elem.css('background-color', 'rgba(' + [parseInt(result[1], 16), parseInt(result[2], 16), parseInt(result[3], 16)].join(",") + ',' + opacity + ')');
                        break;
                    case false:
                        elem.css({
                            'background': 'transparent',
                            'filter': 'progid:DXImageTransform.Microsoft.gradient(startColorstr=#' + alhpaToHex(opacity) + result[0].replace('#', '') + ',endColorstr=#' + alhpaToHex(opacity) + result[0].replace('#', '') + ');',
                            'zoom': 1
                        });
                        break;
                }
            }

            return result ? {
                rgba: 'rgba(' + [parseInt(result[1], 16), parseInt(result[2], 16), parseInt(result[3], 16)].join(",") + ',' + opacity + ')',
                fallback: alhpaToHex(opacity) + result[0].replace('#', '')
            } : null;
        }

        function getEaseType(ease) {
            //    Add more ease types - work in progress
            var easeCSS = "";
            var easeType;
            var oldWebkit = false;

            switch (ease.toLowerCase()) {
                case 'easeinoutback':
                    easeType = 'cubic-bezier(0.680, -0.550, 0.265, 1.550)';
                    oldWebkit = 'cubic-bezier(0.680, 0, 0.265, 1)';
                    break;
                case 'quick-in':
                    easeType = "cubic-bezier(0.315, -0.185, 0.000, 1.270)";
                    oldWebkit = "cubic-bezier(0.315, 0, 0.000, 1)";
                    break;
                case 'accelerate':
                    easeType = "cubic-bezier(0.885, 0.420, 0.000, 1.270)";
                    oldWebkit = "cubic-bezier(0.885, 0.420, 0.000, 1)";
                    break;
                case 'super-fast':
                    easeType = "cubic-bezier(0.000, 0.870, 0.000, 0.890)";
                    break;
                case 'ease-in-out-back':
                    easeType = "cubic-bezier(0.680, -0.550, 0.265, 1.550)";
                    oldWebkit = "cubic-bezier(0.680, 0, 0.265, 1)";
                    break;
                case 'ease-in-out-expo':
                    easeType = "cubic-bezier(1.000, 0.000, 0.000, 1.000)";
                    break;
                case 'ease-out-back':
                    easeType = "cubic-bezier(0.175, 0.885, 0.320, 1.275)";
                    oldWebkit = "cubic-bezier(0.175, 0.885, 0.320, 1)";
                    break;
                case 'ease-out-circ':
                    easeType = "cubic-bezier(0.075, 0.820, 0.165, 1.000)";
                    break;
                case 'ease-in-back':
                    easeType = "cubic-bezier(0.600, -0.280, 0.735, 0.045)";
                    oldWebkit = "cubic-bezier(0.600, 0, 0.735, 0.045)";
                    break;
                case 'ease-in-circ':
                    easeType = "cubic-bezier(0.600, 0.040, 0.980, 0.335)";
                    oldWebkit = "cubic-bezier(0.600, 0.040, 0.980, 0.335)";
                    break;
                case 'ease':
                    easeType = "ease";
                    break;
                case "ease-in":
                    easeType = "ease-in";
                    break;
                case "ease-in-out":
                    easeType = "ease-in-out";
                    break;
                case "ease-out":
                    easeType = "ease-out";
                    break;
            }

            if (oldWebkit) {
                easeType = oldWebkit;
                // easeCSS += "-webkit-transition-timing-function:" + oldWebkit + ";";
            }
            var prefixes = ["-webkit-", "-moz-", "-o-", ""];
            for (var i = 0; i < prefixes.length; i++) {
                easeCSS += prefixes[i] + "transition-timing-function:" + easeType + ";"
            }

            return easeCSS;


        };

        options.resizeEvent = resizeEvent ? true : false;
        if (!resizeEvent) {
            /* xtd code */
        }
        //initLegacy(options);
        function centerIcon(icon) {
            var elem = $(icon);
            elem
                .parent()
                .css({
                    'width': elem.width(),
                    'height': elem.height()
                })
        }
     //   function startHover() {
            if (doesCSS3 == true && supportRGBA == true) {
                initModern(options);
            } else {
                if (!resizeEvent) {
                    initLegacy(options);
                }
            }        
       // }
      
        //    Legacy Browsers - draft code / cleanup required
        function initLegacy(options) {
            item.each(function() {



                var self, params, inner, initial, dims, type, overlay, opacity, elem, icon;
                self = $(this);
                self.unbind('.hoverfx');
                //self[0].setAttribute('aria-haspopup', true);
                self.css({
                    'zoom': 1
                });
				self.css({
                    "width" : "",
                    "height" : ""
                })
                params = setParams(self);
                inner = getInner(self, params.contentType);
				params.overlayColor = inner.css('background-color');
                params.innerColor = inner.css('background-color');
                initial = self.find('.initial-image');
                dims = getDimms(self);

                type = params.contentType;

                moveCSSProps(initial, self);

                overlay = self.find('.overlay');
				
				inner.removeAttr('style');
				overlay.removeAttr('style');
				initial.removeAttr('style');
				
                //inner.css('background-color', 'transparent');

                opacity = genRGBA(params.overlayColor, params.overlayAlpha).rgba;
                // elem = initial;
                if (params.captionType == "over") {
                    elem = inner;
                } else {
                    elem = initial;
                }
                //params.captionType == 'over' ? elem = inner : elem = initial;
                icon = inner.find('.swap-icons img');
                centerIcon(icon);
                genRGBA(params.overlayColor, params.overlayAlpha, overlay);
                

                if (isUI != true) {
                    if (params.link) {
                        self.unbind('click.hoverfx').bind('click.hoverfx', function() {
                            if (params.openPage === 'same') {
                                window.location.assign(params['link']);
                            } else {
                                window.open(params['link'], '_blank');
                            }
                        });
                    }
                }

                //          Hide overlay and inner content

                initial.css('float', 'none');

                switch (supportRGBA == true) {
                    case true:
                        self.find('.swap-title').css({
                            'color': genRGBA(params.innerColor).rgba
                        });
                        self.find('.swap-subtitle').css({
                            'color': genRGBA(params.innerColor, 50).rgba
                        });
                        break;
                    case false:
                        overlay.css({
                            'display': "none"
                        });
                        inner.css({
                            'color': params.innerColor
                        });
                        break;
                }

                if (type == 'caption') {
                    genRGBA(params.overlayColor, params.overlayAlpha, inner);
                    if (params.captionHeight > inner.outerHeight()) {
                        setCaptionHeight(params.captionHeight, inner);
                    }

                }
                /*self.css({
                    'width': '100%',
                    'height': 'auto'
                });*/

                if (initial.height() > 0) {
                    var initialDims = getDimms(initial);
                    self.css({
                        // 'width': initialDims.width,
                        'height': initialDims.height
                    });
                }



                initial.load(function() {
                    var dims = getDimms($(this));
                    self.css({
                        // 'width': dims.width,
                        'height': dims.height
                    });
                    inner.css({
                        'display': 'none',
                        'margin-top': Math.abs(Math.round(parseFloat(dims.height - inner.outerHeight()) / 2))
                    })
                });


                if (type == 'overlay') {
                    $(inner, overlay).css('display', 'none');



                    if (initial.height() > 0) {
                        var initialDims = getDimms(initial);
                        inner.css({
                            'margin-top': Math.abs(initialDims.height - inner.outerHeight()) / 2,
                            'margin-left': Math.abs(initialDims.width - inner.outerWidth()) / 2
                        });
                    }
                    var backgroundFallback = genRGBA(params.overlayColor, params.overlayAlpha).fallback;
                    var opacity = genRGBA(params.overlayColor, params.overlayAlpha).rgba;
                    self.hover(function() {

                        var self = $(this);
                        setParams(self);
						
                        var inner = getInner(self, params.contentType);
                        var overlay = self.find('.overlay');
                        params.overlayColor = inner.css('background-color');
                        inner.css('background-color', params.overlayColor);
                        var type = params.contentType;
						
                        if (type == 'imageSwap' || type == 'overlay') {

                            overlay
                                .stop(true, true)
                                .fadeTo(parseInt(params.effectDelay), params.overlayAlpha * 0.01);
                            inner
                                .stop(true, true)
                                .fadeIn(parseInt(params.effectDelay));
                        }
                    }, function() {
                        var self = $(this);
                        setParams(self);
                        var type = params.contentType;
                        var inner = getInner(self, params.contentType);
                        if (type == 'imageSwap' || type == 'overlay') {
                            $(this)
                                .find('.overlay')
                                .stop(true, true)
                                .fadeOut(parseInt(params.effectDelay));
                            inner
                                .stop(true, true)
                                .fadeOut(parseInt(params.effectDelay));
                        }
                    });
                }
                if (type == 'caption') {
                    self.addClass('caption');
                    $(inner, overlay).css('display', 'block');
                    $(inner).css('opacity', 1);
                    // inner.css('display', 'block');
                    if (params.captionType == "over") {
                        inner.css({
                            'width': '100%',
                            'position': 'absolute',
                            'top': 'auto',
                            'bottom': -inner.outerHeight()
                        })
                    }
                    self.hover(function() {


                        if (params.captionType == "over") {

                            inner.stop(true, true).animate({
                                "bottom": '0px'
                            }, parseInt(params.effectDelay));

                        } else {
                            elem.stop(true, true).animate({
                                "margin-top": -inner.outerHeight()
                            }, parseInt(params.effectDelay));

                        }

                    }, function() {
                        if (params.captionType == "over") {

                            inner.stop(true, true).animate({
                                "bottom": -inner.outerHeight()
                            }, parseInt(params.effectDelay));

                        } else {
                            elem.stop(true, true).animate({
                                "margin-top": 0
                            }, parseInt(params.effectDelay));

                        }
                    });
                }
            });


        }
        // might be needed at some point
        function setVars(elem) {
            var params, elemVars, inner, initial, overlay, easeType, type, dims;
            params = setParams(elem);
            elemVars = {
                params: setParams(elem),
                inner: getInner(elem, params.contentType),
                initial: elem.find('.initial-image'),
                overlay: elem.find('.overlay'),
                easeType: getEaseType(params.effectEasing),
                type: params.contentType,
                dims: getDimms(elem)
            };
            return elemVars;
        }

        //    Modern browsers
        function initModern(options) {
            var addEffects = $();

            item.each(function() {

                var self, params, inner, overlay, initial, dims, type, transitionProp, easeType, icon;
                self = $(this);
                self.unbind('.hoverfx');
                self.css({
                    "width" : "",
                    "height" : ""
                })



                //             var _v= setVars(self);
                params = setParams(self);
                inner = getInner(self, params.contentType);
                initial = self.find('.initial-image');
                overlay = self.find('.overlay');
                params.overlayColor = inner.css('background-color');
                params.innerColor = inner.css('background-color');
				inner.removeAttr('style');
				overlay.removeAttr('style');
				initial.removeAttr('style');

                moveCSSProps(initial, self);

                dims = getDimms(self);
                console.log(dims);
                easeType = getEaseType(params.effectEasing);
                type = params.contentType;

                icon = inner.find('.swap-icons img');
                centerIcon(icon);
                if (type == 'overlay') {
                    transitionProp = 'all';
                } else {
                    transitionProp = 'margin';
                }
                initial.css('float', 'none');

                if (type == "caption") {
                    if (self.find('.caption-wrap').length == 0) {
                        var captionWrap = $('<div />').addClass('caption-wrap overlay');
                        inner.wrap(captionWrap);
                    }
                    overlay = self.find('.caption-wrap');
                } else {
                    overlay = self.find('.overlay');
                }
                overlay.add(inner).css('display', 'block');



                function applyTransition(elem, prop, easeCSS, duration, includeTiming) {
                    var property = prop ? prop : "all";
                    var customEase = "";
                    var dur = params.effectDelay + "ms";
                    var delay = "";



                    var transitionValue = property + " " + dur;
                    elem.css({
                        '-webkit-transition': transitionValue,
                        '-moz-transition': transitionValue,
                        '-o-transition': transitionValue,
                        'transition': transitionValue
                    })
                    if (includeTiming) {
                        applyTiming(elem, easeCSS);
                    }
                }

                function applyTiming(elem, easeCSS) {
                    elem.attr('style', elem.attr('style') + easeCSS);
                }

                self.addClass(params.effectType);

                // self.css({
                //     // 'width': '100%',
                //     'height': 'auto'
                // });

                var loaded = false;

                if (initial.height() > 0) {

                    loaded = true;
                    var initialDims = getDimms(initial);
                    self.css({
                        // 'width': initialDims.width,
                        'height': initialDims.height
                    });

                    if (type == "overlay") {
                        applyTransition(inner, transitionProp, easeType, params.effectDelay, true);
                    }
                }

                initial.load(function() {
                    var imageDims = getDimms($(this));

                    self.css({
                        // 'width': imageDims.width,
                        'height': imageDims.height
                    });
                    // console.log(imageDims.width + " .|. " + inner.outerWidth() + " .. " + inner[0].outerHTML);
                    if (type == 'overlay') {
                        inner.css({
                            // 'margin-top': Math.abs(imageDims.height - inner.outerHeight()) / 2,
                            'margin-top': 0 / 2,
                            'margin-left': Math.abs(imageDims.width - inner.outerWidth()) / 2
                        });
                    }
                    if (type == "overlay") {
                        applyTransition(inner, transitionProp, easeType, params.effectDelay);
                    }

                });

              

                /*overlay.css({
                    'background-color': genRGBA(params.overlayColor, params.overlayAlpha).rgba
                });*/

                inner.css({
                    'background-color': genRGBA(params.overlayColor, params.overlayAlpha).rgba
                });

                if (type == "overlay") {
                    applyTransition(overlay, 'opacity', easeType, params.effectDelay, true);
                }



                // inner
                //     .css({
                //         'display': 'block',
                //         'color': genRGBA(params.innerColor, 100).rgba
                //     })
                //     .find('.swap-subtitle')
                //     .css('color', genRGBA(params.innerColor, 50).rgba);

                // var myHammertime;
                var showCaption, hideCaption;
                // if (Modernizr.touch == true) {
                //     self.unbind('.hoverfx')
                //     myHammertime = new Hammer(self[0]);

                // }

                var isMobile = {
                    Android: function() {
                        return navigator.userAgent.match(/Android/i);
                    },
                    BlackBerry: function() {
                        return navigator.userAgent.match(/BlackBerry/i);
                    },
                    iOS: function() {
                        return navigator.userAgent.match(/iPhone|iPad|iPod/i);
                    },
                    Opera: function() {
                        return navigator.userAgent.match(/Opera Mini/i);
                    },
                    Windows: function() {
                        return navigator.userAgent.match(/IEMobile/i);
                    },
                    any: function() {
                        return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
                    }
                };

                Modernizr.touch = Modernizr.touch && isMobile.any();

                if (isUI != true) {
                    if (params.link) {
                        if (Modernizr.touch == true) {

                        } else {
                            self.bind('click.hoverfx', function() {
                                if (params.openPage === 'same') {
                                    window.location.assign(params['link']);
                                } else {
                                    window.open(params['link'], '_blank');
                                }
                            });
                        }
                    }
                }

                if (type == 'overlay') {
                    self.unbind('.caption');
                    self.addClass('contentswap-overlay');
                    var initialDims = getDimms(initial);
                    //console.log(dims.width + " .. " +  inner.outerWidth());
                    if (initial.height() > 0) {
                        inner.css({
                            // 'margin-top': Math.abs(initialDims.height - inner.outerHeight()) / 2,
                            'margin-top': 0 / 2,
                            'margin-left': Math.abs(initialDims.width - inner.outerWidth()) / 2
                        });
                    }

                }

                if (type == 'caption') {
                    self.addClass('caption');

                    if (params.direction == 'top') {
                        if (!loaded) {
                            setTimeout(function() {
                                overlay.css({
                                    'margin-top': (-inner.outerHeight() - 1) + 'px',
                                    'opacity': 1
                                });
                            }, 10);

                        } else {
                            overlay.css({
                                'margin-top': (-inner.outerHeight() - 1) + 'px',
                                'opacity': 1
                            });
                        }

                        setTimeout(function() {
                            overlay.insertBefore(initial);

                        }, 10);
                    } else {

                        setTimeout(function() {
                            overlay.css({
                                'opacity': 1
                            });
                        }, 10);
                    }
                    //else applyTransition(initial,transitionProp);
                    var showCaption, hideCaption;


                    //work in progress
                    if (params.captionType == "over") {

                        if (params.direction == 'top') {
                            overlay.css({
                                'position': 'absolute',
                                'z-index': 9991

                            });
                        } else {
                            overlay.css({
                                'margin-top': '1px'
                            });
                        }
                        applyTransition(overlay, 'margin', easeType, params.effectDelay, true);

                        showCaption = function() {

                            overlay.css({
                                'margin-top': (-inner.outerHeight()) + 'px'
                            });
                            if (params.direction == 'top') overlay.css('margin-top', 0);
                        };
                        hideCaption = function() {
                            overlay.css({
                                'margin-top': "0px"
                            });
                            if (params.direction == 'top') overlay.css('margin-top', (-inner.outerHeight()) + 'px');
                        }
                    } else {
                        applyTransition(overlay, "margin", easeType, params.effectDelay, true);
                        applyTransition(initial, "margin", easeType, params.effectDelay, true);


                        showCaption = function() {

                            if (params.captionHeight <= inner.outerHeight()) {
                                setCaptionHeight(params.captionHeight, inner);
                            }

                            if (params.direction == 'top' && params.captionType != 'over') {

                                overlay.css({
                                    'margin-top': '0px'
                                });
                            } else {
                                overlay.css({
                                    'margin-top': -inner.outerHeight()
                                })
                                initial.css({
                                    'margin-top': -inner.outerHeight(),
                                    'margin-bottom': inner.outerHeight()
                                })

                            }
                        }

                        hideCaption = function() {
                            if (params.direction == 'top') {
                                overlay.css({
                                    'margin-top': (-inner.outerHeight() - 1) + 'px'
                                })
                            } else {
                                overlay.css({
                                    'margin-top': '1px'
                                })
                                initial.css({
                                    'margin-top': '0px',
                                    'margin-bottom': '0px'
                                })
                            }
                        }
                    }


                }

                if (Modernizr.touch == true) {
                      self.addClass('hover');
                        if (type == "caption") {
                            showCaption();
                        } else {

                        }
                    // myHammertime.on("tap", function(event) {

                    //     if (self.hasClass('hover')) {
                    //         self.removeClass('hover');
                    //         if (isUI != true) {
                    //             if (params.link) {
                    //                 if (params.openPage === 'same') {
                    //                     window.location.assign(params['link']);
                    //                 } else {
                    //                     window.open(params['link'], '_blank');
                    //                 }

                    //             }
                    //         }
                    //         if (type == "caption") {
                    //             hideCaption();
                    //         } else {

                    //         }

                    //     } else {
                    //         self.addClass('hover');
                    //         if (type == "caption") {
                    //             showCaption();
                    //         } else {

                    //         }
                    //     }


                    //     //self.toggleClass('hover');

                    // });
                } else {
                    if (type == "caption") {
                        self.bind('mouseenter.hoverfx', showCaption)
                            .bind('mouseleave.hoverfx', hideCaption);
                    }

                }




            });
        }

    }
})(menus_jQuery);