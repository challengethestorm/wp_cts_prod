(function($) {
    runTest = {
        phone: function(val) {
            //return /^\(?(\d{3})\)?[\- ]?\d{3}[\- ]?\d{4}$/.test(val);
            return (/^[0-9\.\+\-\(\)\ ]*$/.test(val) && (this.minLength(val)));
            //find better regex for phone
        },
        minLength: function(val) {
            minLength = 3;
            return val.length > minLength;
        },
        email: function(val) {
            return ((/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(val)) && (this.minLength(val)));
        },
        website: function(val) {
            return (/(http(s)?:\/\/)?([\w-]+\.)+[\w-]+(\/[\w- ;,.\/?%&=#]*)?/).test(val);
        },
        numberbox: function(val) {
            return (/[0-9]/.test(val));
        },
        code: function(val) {
            return (/[0-9]/.test(val));
        },
        captcha: function(val) {
            //alert('hidden---' + runTest.captcha);
            if (runTest.hidden == undefined) return false;
            if (val != runTest.hidden) {
                return false;
            } else {
                return true;
            }
        },
        dropdown: function(val) {
            if (val == 'None') {
                return false;
            } else {
                return true;
            }
        },
        required: function(val) {
            //alert('val' + val);
            if (val == '') {
                return false;
            } else {
                return true;
            }
        }
    };




    var overlay;

    function activityShow() {
        overlay = indicator({
            text: "Sending message"
        });
    }

    function activityHide() {
        overlay.hide();
    }

    function activityUpdate(status, message, callback) {
        var errorClass = 'xtd-' + status + '-message';

        window.setTimeout(function() {
            overlay.update({
                errorClass: errorClass,
                text: message
            });
        }, 1000);
        window.setTimeout(function() {
            activityHide();
            if (callback) {
                callback();
            }
        }, 5000);


    }

    function getBrowser() {
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

    var result = getBrowser();
    var browser = result.name;
    var v = result.version;
    var fallbackPlaceholders;

    if (browser === "msie" && v <= 9) {
        fallbackPlaceholders = true;
    } else {
        fallbackPlaceholders = false;
    }

    function detectLocal() {
        return window.location.protocol.indexOf("file") > -1;
    }

    function detectLocalhost() {
        return window.location.host.indexOf('localhost') > -1;
    }

    function xtdSecQ() {
        function random(min, max) {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }

        var number1 = random(1, 10);
        var number2 = random(1, number1);
        var op = random(1, 2);

        var question;
        var questionLabel = 'Anti-spam: ';
        var result = 0;
        if (op == 1) {
            result = number1 + number2;
            question = number1 + ' + ' + number2 + ' = ?';
        } else {
            result = number1 - number2;
            question = number1 + ' - ' + number2 + ' = ?';
        }

        var hidden = '<input type="hidden" name="result" value="' + result + '">';

        return {
            q: question,
            h: hidden,
            r: result
        };
    }



    function validControlValue(messages, controlValue, controlType, required) {

        //alert('in function have' + controlType + ' and ' + controlValue + 'and class' + required);

        var xtdContactFormsMessages = messages;

        if (!xtdContactFormsMessages) {
            xtdContactFormsMessages = xtdDefaultContactFormsMessages;
        }

        var controlValue;
        if (typeof controlValue === 'string') {
            controlValue = $.trim(controlValue);
        } else {
            controlValue = "";
        }

        if (controlType == 'dropdown') {
            if (required && !runTest.dropdown(controlValue)) {
                return {
                    valid: false,
                    message: xtdContactFormsMessages.required
                };
            }
        }


        if (required && !runTest.required(controlValue)) {
            // value is empty, throw error

            var message = xtdDefaultContactFormsMessages.required;
            if (xtdContactFormsMessages.hasOwnProperty(controlType + '_required') && xtdContactFormsMessages[controlType + '_required'].length>0){
                message = xtdContactFormsMessages[controlType + '_required'];
            } 

            return {
                valid: false,
                message: message
            };
        }

        //if required is not true
        if (!required) {
            return {
                valid: true,
                message: null
            };
        }

        // check if we have a validate function for this type of control+
        //alert(controlType)
        if (runTest[controlType]) {
            // we do, run the test            
            if (!runTest[controlType](controlValue)) {
                // test has failed, send result and error
                var errorMessage = xtdDefaultContactFormsMessages[controlType + '_invalid'];
                if (xtdContactFormsMessages.hasOwnProperty(controlType + '_invalid') && xtdContactFormsMessages[controlType + '_invalid'].length>0)
                    errorMessage = xtdContactFormsMessages[controlType + '_invalid'];
                return {
                    valid: false,
                    message: errorMessage
                };
            }
        } else {
            //if we have other erros
        }

        // no test failed! congrats, value is valid
        return {
            valid: true,
            message: null
        };
    }



    $.fn.xtdValidateFormGroup = function(messages) {
        var formGroup = $(this);

        var control = formGroup.find('input,textarea,select,submit,button');
        var required;
        var buttons = [];
        var ok = false;

        if ($(this).hasClass('required')) {
            required = true;
        } else {
            required = false;
        }

        var holders = $(this).find('.control-holder');
        var controlValue = null;
        var controlType;
        var self = this;

        holders.each(function() {
            var controlHolder = $(this);

            if (controlHolder.hasClass('checkbox-holder')) {
                var controls = controlHolder.find('input[type=checkbox]:checked');
                controlValue = "";
                controls.each(function() {
                    controlValue = $(this).val() + ",";
                });
                controlType = "checkbox";
                //alert('we have' + controlType + ' and ' + controlValue + 'and class' + required);

            } else if (controlHolder.hasClass('radio-holder')) {
                var control = controlHolder.find('input[type=radio]:checked');
                controlValue = control.val();
                controlType = "radio";
            } else {
                var control = controlHolder.find('input.form-control,textarea,select,submit,button');
                var regexpID = new RegExp('[^0-9\\_]+', 'gi');

                var _controlID = "";

                if ($(this).closest('form').attr('id')) {
                    _controlID = control[0].id.replace($(this).closest('form').attr('id').replace('_form', ''), '');
                }

                var id = (_controlID).match(regexpID);

                if (id[0] == "address") {
                    controlType = id[1];
                } else {
                    controlType = id[0];
                }

                controlValue = control.val();
            }
        });

        var result = validControlValue(messages, controlValue, controlType, required);

        if (result.valid) {
            var notValids = formGroup.find('input, textarea,select,radio,checkbox');
            notValids.removeClass('invalid');


            if (controlType != undefined) {
                if (!(notValids.hasClass('invalid'))) {
                    notValids.tooltip('destroy');
                    //alert('destroy');
                }
            }

            var obj = {};
            obj[controlType] = controlValue;

            return obj;


        } else {
            var notValids = formGroup.find('input, textarea,select,radio,checkbox').not('input[type="hidden"]');
            notValids.addClass('invalid')
                .focus(function() {
                    $(this).removeClass('invalid').tooltip('destroy');
                })
                .tooltip('destroy')
                .tooltip({
                    placement: tooltipPlacement,
                    trigger: 'manual',
                    title: result.message
                })
                .tooltip('show');

        }


    };




    $.fn.xtdValidateForm = function(messages, okFunction, failFunction) {
        // run requirements validation (check all form-group with ‘required’ class if they have values)
        var data = {};
        var ok = true;



        $(this).find('.form-group').each(function() {


            if (!$(this).xtdValidateFormGroup(messages)) {
                ok = false;
            } else {
                var obj = $(this).xtdValidateFormGroup(messages);

                if (obj.undefined !== null) {
                    //data.push(($(this).xtdValidateFormGroup()));
                    data = $.extend(data, obj);
                }

            }
        });



        if (ok) {

            data['id'] = $(this).parent().attr('id');

            okFunction(data);

        } else {
            if (failFunction) failFunction();
        }
        // run input type based validations (email, phone.. )
    }

    var tooltipPlacement = "right";


    $.fn.xtdContactForm = function(options) {
        return this.each(function() {
            var form = $(this);

            // var instanceName = form.attr('class').match(/ContactForms[0-9]*/)[0];

            var timeout = '<input type="hidden" name="timeout" value="0">';
            $(this).find('input[type=text]').first().before(timeout);

            setTimeout(function() {
                form.find('input[name="timeout"]').val('1');
                //alert('end ' + $('input[name="timeout"]').val());
            }, 1000);


            obj = xtdSecQ();

            $(this).find('input[name="captcha"]').each(function() {
                var input = $(this);
                var targetLabel = input.parent().parent().find('label');
                if (targetLabel.length > 0) {
                    targetLabel.text(obj.q);
                } else if (browser === "msie" && v <= 10) {

                    input.attr('placeholder', obj.q);
                    $(input).val(input.attr('placeholder'));

                    $(input).focus(function() {
                        if (input.val() == input.attr('placeholder')) {
                            input.val('');
                        }
                    });

                    $(input).blur(function() {
                        if (input.val() == '' || input.val() == input.attr('placeholder')) {
                            input.val(input.attr('placeholder'));
                        }
                    });
                } else {
                    input.attr('placeholder', obj.q);
                }
                input.before(obj.h);
                runTest.hidden = obj.r;
            });


            var invalidContent = form.find('input.invalid, textarea.invalid');

            function clearInvalidHighlight(element) {
                var check = function() {
                    if (element.hasClass('invalid')) {
                        return true
                    } else return false
                };
                if (check = true) element.removeClass('invalid');
            }

            invalidContent.focus(function() {

                clearInvalidHighlight($(this));
            });



            function submitAndStuff() {


                form.xtdValidateForm(options.messages, function(formdata) {
                    options.formdata = formdata;
                    options.emailSentMessage = decodeURIComponent(options.emailSentMessage);
                    // options.contactForm = instanceName;

                    if (form.find('input[name="timeout"]').val() == 0) {
                        return;
                    }

                    if (!options.messages) {
                        xtdContactFormsMessages = xtdDefaultContactFormsMessages;
                    } else {
                        xtdContactFormsMessages = options.messages;
                    }

                    // submit form to php
                    activityShow();
                    if (detectLocal()) {
                        activityUpdate('notice', "You are running this page locally. Because of this, the email sending functionality will not work. You must run the page on a web server for this functionality to work");
                    } else {
                        $.ajax({
                            type: 'POST',
                            url: form.attr('data-location'),
                            data: options,
                            success: function(data) {
                                if (detectLocalhost()) {
                                    activityUpdate('ok', 'While testing on localhost it is possible that your emails will not reach their destination, depending of the configuration of the server.');
                                    return;
                                }

                                if (data == "1") {
                                    if (options.emailSent == 'firstOption') {
                                        activityUpdate('ok', options.emailSentMessage);
                                        clearForm();

                                    } else {
                                        window.location = options.relPath;
                                    }
                                } else {
                                    var phperror = "";
                                    activityUpdate('error', xtdContactFormsMessages.hasOwnProperty("phperror") ? xtdContactFormsMessages.phperror : xtdDefaultContactFormsMessages.phperror);

                                }
                            },
                            error: function(req, status, error) {
                                activityUpdate('error', xtdContactFormsMessages.hasOwnProperty("phperror") ? xtdContactFormsMessages.phperror : xtdDefaultContactFormsMessages.phperror);
                            }

                        });
                    }
                });
            }

            function clearForm() {
                var formInputs = form.find('input, textarea').not('input[type="hidden"]');
                formInputs.val('');
            }

            $(this).find('button[type=submit]').click(function(event) {
                event.preventDefault();
                event.stopPropagation();
                submitAndStuff();
            });

            function calculateTooltipPlacement() {
                // calculate tooltip offset
                var offsetObj = form.offset();
                var rightOffset = offsetObj.left + form.width();

                var windowWidth = $(window).width();
                if (windowWidth - rightOffset < 200) {
                    if (offsetObj.left < 200) {
                        tooltipPlacement = "top";
                    } else {
                        tooltipPlacement = "left";
                    }
                } else {
                    tooltipPlacement = "right";
                }
            };

            calculateTooltipPlacement();

            function updateOnResize() {
                form.find('.invalid').tooltip('show');
                calculateTooltipPlacement();
            }

            var timeoutId;

            $(window).resize(function() {
                if (timeoutId) {
                    clearTimeout(timeoutId);
                }
                timeoutId = setTimeout(updateOnResize, 100);
            });

            //if (fallbackPlaceholders == true) form.find('input, textarea').placeholder();



        });
    };


    var xtdDefaultContactFormsMessages = {
        "email_invalid": "Email value is not correct.",
        "website_invalid": "Website value is not correct.",
        "phone_invalid": "Phone value is not correct.",
        "code_invalid": "Postal code must be just numbers.",
        "numberbox": "Number Box must be just numbers.",
        "captcha": "Captcha value is not correct.",
        "phperror": "There was a problem sending your message. Please try again.",
        "required": "This value is required"
    };


    $(document).ready(function() {

        // if (!window.xtdContactFormsMessages) {
        //     xtdContactFormsMessages = xtdDefaultContactFormsMessages;
        // }

    });

    + function(a) {
        "use strict";
        var b = function(a, b) {
            this.type = this.options = this.enabled = this.timeout = this.hoverState = this.$element = null, this.init("tooltip", a, b)
        };
        b.DEFAULTS = {
            animation: !0,
            placement: "top",
            selector: !1,
            template: '<div class="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
            trigger: "hover focus",
            title: "",
            delay: 0,
            html: !1,
            container: !1
        }, b.prototype.init = function(b, c, d) {
            this.enabled = !0, this.type = b, this.$element = a(c), this.options = this.getOptions(d);
            var e = this.options.trigger.split(" ");
            for (var f = e.length; f--;) {
                var g = e[f];
                if (g == "click") this.$element.on("click." + this.type, this.options.selector, a.proxy(this.toggle, this));
                else if (g != "manual") {
                    var h = g == "hover" ? "mouseenter" : "focus",
                        i = g == "hover" ? "mouseleave" : "blur";
                    this.$element.on(h + "." + this.type, this.options.selector, a.proxy(this.enter, this)), this.$element.on(i + "." + this.type, this.options.selector, a.proxy(this.leave, this))
                }
            }
            this.options.selector ? this._options = a.extend({}, this.options, {
                trigger: "manual",
                selector: ""
            }) : this.fixTitle()
        }, b.prototype.getDefaults = function() {
            return b.DEFAULTS
        }, b.prototype.getOptions = function(b) {
            return b = a.extend({}, this.getDefaults(), this.$element.data(), b), b.delay && typeof b.delay == "number" && (b.delay = {
                show: b.delay,
                hide: b.delay
            }), b
        }, b.prototype.getDelegateOptions = function() {
            var b = {},
                c = this.getDefaults();
            return this._options && a.each(this._options, function(a, d) {
                c[a] != d && (b[a] = d)
            }), b
        }, b.prototype.enter = function(b) {
            var c = b instanceof this.constructor ? b : a(b.currentTarget)[this.type](this.getDelegateOptions()).data("bs." + this.type);
            clearTimeout(c.timeout), c.hoverState = "in";
            if (!c.options.delay || !c.options.delay.show) return c.show();
            c.timeout = setTimeout(function() {
                c.hoverState == "in" && c.show()
            }, c.options.delay.show)
        }, b.prototype.leave = function(b) {
            var c = b instanceof this.constructor ? b : a(b.currentTarget)[this.type](this.getDelegateOptions()).data("bs." + this.type);
            clearTimeout(c.timeout), c.hoverState = "out";
            if (!c.options.delay || !c.options.delay.hide) return c.hide();
            c.timeout = setTimeout(function() {
                c.hoverState == "out" && c.hide()
            }, c.options.delay.hide)
        }, b.prototype.show = function() {
            var b = a.Event("show.bs." + this.type);
            if (this.hasContent() && this.enabled) {
                this.$element.trigger(b);
                if (b.isDefaultPrevented()) return;
                var c = this.tip();
                this.setContent(), this.options.animation && c.addClass("fade");
                var d = typeof this.options.placement == "function" ? this.options.placement.call(this, c[0], this.$element[0]) : this.options.placement,
                    e = /\s?auto?\s?/i,
                    f = e.test(d);
                f && (d = d.replace(e, "") || "top"), c.detach().css({
                    top: 0,
                    left: 0,
                    display: "block"
                }).addClass(d), this.options.container ? c.appendTo(this.options.container) : c.insertAfter(this.$element);
                var g = this.getPosition(),
                    h = c[0].offsetWidth,
                    i = c[0].offsetHeight;
                if (f) {
                    var j = this.$element.parent(),
                        k = d,
                        l = document.documentElement.scrollTop || document.body.scrollTop,
                        m = this.options.container == "body" ? window.innerWidth : j.outerWidth(),
                        n = this.options.container == "body" ? window.innerHeight : j.outerHeight(),
                        o = this.options.container == "body" ? 0 : j.offset().left;
                    d = d == "bottom" && g.top + g.height + i - l > n ? "top" : d == "top" && g.top - l - i < 0 ? "bottom" : d == "right" && g.right + h > m ? "left" : d == "left" && g.left - h < o ? "right" : d, c.removeClass(k).addClass(d)
                }
                var p = this.getCalculatedOffset(d, g, h, i);
                this.applyPlacement(p, d), this.$element.trigger("shown.bs." + this.type)
            }
        }, b.prototype.applyPlacement = function(a, b) {
            var c, d = this.tip(),
                e = d[0].offsetWidth,
                f = d[0].offsetHeight,
                g = parseInt(d.css("margin-top"), 10),
                h = parseInt(d.css("margin-left"), 10);
            isNaN(g) && (g = 0), isNaN(h) && (h = 0), a.top = a.top + g, a.left = a.left + h, d.offset(a).addClass("in");
            var i = d[0].offsetWidth,
                j = d[0].offsetHeight;
            b == "top" && j != f && (c = !0, a.top = a.top + f - j);
            if (/bottom|top/.test(b)) {
                var k = 0;
                a.left < 0 && (k = a.left * -2, a.left = 0, d.offset(a), i = d[0].offsetWidth, j = d[0].offsetHeight), this.replaceArrow(k - e + i, i, "left")
            } else this.replaceArrow(j - f, j, "top");
            c && d.offset(a)
        }, b.prototype.replaceArrow = function(a, b, c) {
            this.arrow().css(c, a ? 50 * (1 - a / b) + "%" : "")
        }, b.prototype.setContent = function() {
            var a = this.tip(),
                b = this.getTitle();
            a.find(".tooltip-inner")[this.options.html ? "html" : "text"](b), a.removeClass("fade in top bottom left right")
        }, b.prototype.hide = function() {
            function e() {
                b.hoverState != "in" && c.detach()
            }
            var b = this,
                c = this.tip(),
                d = a.Event("hide.bs." + this.type);
            this.$element.trigger(d);
            if (d.isDefaultPrevented()) return;
            return c.removeClass("in"), a.support.transition && this.$tip.hasClass("fade") ? c.one(a.support.transition.end, e).emulateTransitionEnd(150) : e(), this.$element.trigger("hidden.bs." + this.type), this
        }, b.prototype.fixTitle = function() {
            var a = this.$element;
            (a.attr("title") || typeof a.attr("data-original-title") != "string") && a.attr("data-original-title", a.attr("title") || "").attr("title", "")
        }, b.prototype.hasContent = function() {
            return this.getTitle()
        }, b.prototype.getPosition = function() {
            var b = this.$element[0];
            return a.extend({}, typeof b.getBoundingClientRect == "function" ? b.getBoundingClientRect() : {
                width: b.offsetWidth,
                height: b.offsetHeight
            }, this.$element.offset())
        }, b.prototype.getCalculatedOffset = function(a, b, c, d) {
            return a == "bottom" ? {
                top: b.top + b.height,
                left: b.left + b.width / 2 - c / 2
            } : a == "top" ? {
                top: b.top - d,
                left: b.left + b.width / 2 - c / 2
            } : a == "left" ? {
                top: b.top + b.height / 2 - d / 2,
                left: b.left - c
            } : {
                top: b.top + b.height / 2 - d / 2,
                left: b.left + b.width
            }
        }, b.prototype.getTitle = function() {
            var a, b = this.$element,
                c = this.options;
            return a = b.attr("data-original-title") || (typeof c.title == "function" ? c.title.call(b[0]) : c.title), a
        }, b.prototype.tip = function() {
            return this.$tip = this.$tip || a(this.options.template)
        }, b.prototype.arrow = function() {
            return this.$arrow = this.$arrow || this.tip().find(".tooltip-arrow")
        }, b.prototype.validate = function() {
            this.$element[0].parentNode || (this.hide(), this.$element = null, this.options = null)
        }, b.prototype.enable = function() {
            this.enabled = !0
        }, b.prototype.disable = function() {
            this.enabled = !1
        }, b.prototype.toggleEnabled = function() {
            this.enabled = !this.enabled
        }, b.prototype.toggle = function(b) {
            var c = b ? a(b.currentTarget)[this.type](this.getDelegateOptions()).data("bs." + this.type) : this;
            c.tip().hasClass("in") ? c.leave(c) : c.enter(c)
        }, b.prototype.destroy = function() {
            this.hide().$element.off("." + this.type).removeData("bs." + this.type)
        };
        var c = a.fn.tooltip;
        a.fn.tooltip = function(c) {
            return this.each(function() {
                var d = a(this),
                    e = d.data("bs.tooltip"),
                    f = typeof c == "object" && c;
                e || d.data("bs.tooltip", e = new b(this, f)), typeof c == "string" && e[c]()
            })
        }, a.fn.tooltip.Constructor = b, a.fn.tooltip.noConflict = function() {
            return a.fn.tooltip = c, this
        }
    }($)


    /*! http://mths.be/placeholder v2.0.7 by @mathias */
    ;
    (function(window, document, $) {

        // Opera Mini v7 doesn’t support placeholder although its DOM seems to indicate so
        var isOperaMini = Object.prototype.toString.call(window.operamini) == '[object OperaMini]';
        var isInputSupported = 'placeholder' in document.createElement('input') && !isOperaMini;
        var isTextareaSupported = 'placeholder' in document.createElement('textarea') && !isOperaMini;
        var prototype = $.fn;
        var valHooks = $.valHooks;
        var propHooks = $.propHooks;
        var hooks;
        var placeholder;

        if (isInputSupported && isTextareaSupported) {

            placeholder = prototype.placeholder = function() {
                return this;
            };

            placeholder.input = placeholder.textarea = true;

        } else {

            placeholder = prototype.placeholder = function() {
                var $this = this;
                $this
                    .filter((isInputSupported ? 'textarea' : ':input') + '[placeholder]')
                    .not('.placeholder')
                    .bind({
                        'focus.placeholder': clearPlaceholder,
                        'blur.placeholder': setPlaceholder
                    })
                    .data('placeholder-enabled', true)
                    .trigger('blur.placeholder');
                return $this;
            };

            placeholder.input = isInputSupported;
            placeholder.textarea = isTextareaSupported;

            hooks = {
                'get': function(element) {
                    var $element = $(element);

                    var $passwordInput = $element.data('placeholder-password');
                    if ($passwordInput) {
                        return $passwordInput[0].value;
                    }

                    return $element.data('placeholder-enabled') && $element.hasClass('placeholder') ? '' : element.value;
                },
                'set': function(element, value) {
                    var $element = $(element);

                    var $passwordInput = $element.data('placeholder-password');
                    if ($passwordInput) {
                        return $passwordInput[0].value = value;
                    }

                    if (!$element.data('placeholder-enabled')) {
                        return element.value = value;
                    }
                    if (value == '') {
                        element.value = value;
                        // Issue #56: Setting the placeholder causes problems if the element continues to have focus.
                        if (element != safeActiveElement()) {
                            // We can't use `triggerHandler` here because of dummy text/password inputs :(
                            setPlaceholder.call(element);
                        }
                    } else if ($element.hasClass('placeholder')) {
                        clearPlaceholder.call(element, true, value) || (element.value = value);
                    } else {
                        element.value = value;
                    }
                    // `set` can not return `undefined`; see http://jsapi.info/jquery/1.7.1/val#L2363
                    return $element;
                }
            };

            if (!isInputSupported) {
                valHooks.input = hooks;
                propHooks.value = hooks;
            }
            if (!isTextareaSupported) {
                valHooks.textarea = hooks;
                propHooks.value = hooks;
            }

            $(function() {
                // Look for forms
                $(document).delegate('form.contactForm-base', 'submit.placeholder', function() {
                    // Clear the placeholder values so they don't get submitted
                    var $inputs = $('.contactForm-base .placeholder', this).each(clearPlaceholder);
                    setTimeout(function() {
                        $inputs.each(setPlaceholder);
                    }, 10);
                });
            });

            // Clear placeholder values upon page reload
            $(window).bind('beforeunload.placeholder', function() {
                $('.contactForm-base .placeholder').each(function() {
                    this.value = '';
                });
            });

        }

        function args(elem) {
            // Return an object of element attributes
            var newAttrs = {};
            var rinlinejQuery = /^jQuery\d+$/;
            $.each(elem.attributes, function(i, attr) {
                if (attr.specified && !rinlinejQuery.test(attr.name)) {
                    newAttrs[attr.name] = attr.value;
                }
            });
            return newAttrs;
        }

        function clearPlaceholder(event, value) {
            var input = this;
            var $input = $(input);
            if (input.value == $input.attr('placeholder') && $input.hasClass('placeholder')) {
                if ($input.data('placeholder-password')) {
                    $input = $input.hide().next().show().attr('id', $input.removeAttr('id').data('placeholder-id'));
                    // If `clearPlaceholder` was called from `$.valHooks.input.set`
                    if (event === true) {
                        return $input[0].value = value;
                    }
                    $input.focus();
                } else {
                    input.value = '';
                    $input.removeClass('placeholder');
                    input == safeActiveElement() && input.select();
                }
            }
        }

        function setPlaceholder() {
            var $replacement;
            var input = this;
            var $input = $(input);
            var id = this.id;
            if (input.value == '') {
                if (input.type == 'password') {
                    if (!$input.data('placeholder-textinput')) {
                        try {
                            $replacement = $input.clone().attr({
                                'type': 'text'
                            });
                        } catch (e) {
                            $replacement = $('<input>').attr($.extend(args(this), {
                                'type': 'text'
                            }));
                        }
                        $replacement
                            .removeAttr('name')
                            .data({
                                'placeholder-password': $input,
                                'placeholder-id': id
                            })
                            .bind('focus.placeholder', clearPlaceholder);
                        $input
                            .data({
                                'placeholder-textinput': $replacement,
                                'placeholder-id': id
                            })
                            .before($replacement);
                    }
                    $input = $input.removeAttr('id').hide().prev().attr('id', id).show();
                    // Note: `$input[0] != input` now!
                }
                $input.addClass('placeholder');
                $input[0].value = $input.attr('placeholder');
            } else {
                $input.removeClass('placeholder');
            }
        }

        function safeActiveElement() {
            // Avoid IE9 `document.activeElement` of death
            // https://github.com/mathiasbynens/jquery-placeholder/pull/99
            try {
                return document.activeElement;
            } catch (err) {}
        }

    }(this, document, menus_jQuery));


    //    https://github.com/taitems/iOS-Overlay
    var indicator = function(params) {

        "use strict";

        var overlayDOM;
        var noop = function() {};
        var defaults = {
            onbeforeshow: noop,
            onshow: noop,
            onbeforehide: noop,
            onhide: noop,
            text: "",
            icon: null,
            spinner: null,
            duration: null,
            id: null,
            parentEl: null,
            errorClass: null
        };

        // helper - merge two objects together, without using $.extend
        var merge = function(obj1, obj2) {
            var obj3 = {};
            for (var attrOne in obj1) {
                obj3[attrOne] = obj1[attrOne];
            }
            for (var attrTwo in obj2) {
                obj3[attrTwo] = obj2[attrTwo];
            }
            return obj3;
        };

        // helper - does it support CSS3 transitions/animation
        var doesTransitions = (function() {
            var b = document.body || document.documentElement;
            var s = b.style;
            var p = 'transition';
            if (typeof s[p] === 'string') {
                return true;
            }

            // Tests for vendor specific prop
            var v = ['Moz', 'Webkit', 'Khtml', 'O', 'ms'];
            p = p.charAt(0).toUpperCase() + p.substr(1);
            for (var i = 0; i < v.length; i++) {
                if (typeof s[v[i] + p] === 'string') {
                    return true;
                }
            }
            return false;
        }());

        // setup overlay settings
        var settings = merge(defaults, params);

        //
        var handleAnim = function(anim) {
            if (anim.animationName === "ios-overlay-show") {
                settings.onshow();
            }
            if (anim.animationName === "ios-overlay-hide") {
                destroy();
                settings.onhide();
            }
        };

        // IIFE
        var create = (function() {

            // initial DOM creation and event binding
            overlayDOM = document.createElement("div");
            overlayDOM.className = "ui-ios-overlay ";

            overlayDOM.innerHTML += '<span class="title">' + settings.text + '</span';
            if (params.icon) {
                overlayDOM.innerHTML += '<img src="' + params.icon + '">';
            } else if (params.spinner) {
                overlayDOM.appendChild(params.spinner.el);
            }
            if (doesTransitions) {
                overlayDOM.addEventListener("webkitAnimationEnd", handleAnim, false);
                overlayDOM.addEventListener("msAnimationEnd", handleAnim, false);
                overlayDOM.addEventListener("oAnimationEnd", handleAnim, false);
                overlayDOM.addEventListener("animationend", handleAnim, false);
            }


            settings.onbeforeshow();
            // begin fade in
            if (doesTransitions) {
                overlayDOM.className += " ios-overlay-show";
            } else if (typeof $ === "function") {
                $(overlayDOM).fadeIn({
                    duration: 200
                }, function() {
                    settings.onshow();
                });
            }

            if (settings.duration) {
                window.setTimeout(function() {
                    hide();
                }, settings.duration);
            }

            if (params.parentEl) {
                document.getElementById(params.parentEl).appendChild(overlayDOM);
            } else {
                document.body.appendChild(overlayDOM);
            }

        }());

        var hide = function() {
            // pre-callback
            settings.onbeforehide();
            // fade out
            if (doesTransitions) {
                // CSS animation bound to classes
                overlayDOM.className = overlayDOM.className.replace("show", "hide");
            } else if (typeof $ === "function") {
                // polyfill requires jQuery
                $(overlayDOM).fadeOut({
                    duration: 200
                }, function() {
                    destroy();
                    settings.onhide();
                });
            }
        };

        var destroy = function() {
            if (params.parentEl) {
                document.getElementById(params.parentEl).removeChild(overlayDOM);
            } else {
                document.body.removeChild(overlayDOM);
            }
        };

        var update = function(params) {

            //          safe class adding method provided by jQuery
            $(overlayDOM).addClass(params.errorClass);

            //            triggers exception on IE's lower than 10
            //            overlayDOM.classList.add(params.errorClass) ;
            if (params.text) {
                overlayDOM.getElementsByTagName("span")[0].innerHTML = params.text;
            }
            if (params.icon) {
                if (settings.spinner) {
                    settings.spinner.el.parentNode.removeChild(settings.spinner.el);
                }
                overlayDOM.innerHTML += '<img src="' + params.icon + '">';
            }
        };

        return {
            hide: hide,
            destroy: destroy,
            update: update
        };

    };


}(jQuery));