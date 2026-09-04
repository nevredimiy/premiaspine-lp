(function polyfill() {
  const relList = document.createElement("link").relList;
  if (relList && relList.supports && relList.supports("modulepreload")) {
    return;
  }
  for (const link of document.querySelectorAll('link[rel="modulepreload"]')) {
    processPreload(link);
  }
  new MutationObserver((mutations) => {
    for (const mutation of mutations) {
      if (mutation.type !== "childList") {
        continue;
      }
      for (const node of mutation.addedNodes) {
        if (node.tagName === "LINK" && node.rel === "modulepreload")
          processPreload(node);
      }
    }
  }).observe(document, { childList: true, subtree: true });
  function getFetchOpts(link) {
    const fetchOpts = {};
    if (link.integrity) fetchOpts.integrity = link.integrity;
    if (link.referrerPolicy) fetchOpts.referrerPolicy = link.referrerPolicy;
    if (link.crossOrigin === "use-credentials")
      fetchOpts.credentials = "include";
    else if (link.crossOrigin === "anonymous") fetchOpts.credentials = "omit";
    else fetchOpts.credentials = "same-origin";
    return fetchOpts;
  }
  function processPreload(link) {
    if (link.ep) return;
    link.ep = true;
    const fetchOpts = getFetchOpts(link);
    fetch(link.href, fetchOpts);
  }
})();
let slideUp = (target, duration = 500, showmore = 0) => {
  if (!target.classList.contains("--slide")) {
    target.classList.add("--slide");
    target.style.transitionProperty = "height, margin, padding";
    target.style.transitionDuration = duration + "ms";
    target.style.height = `${target.offsetHeight}px`;
    target.offsetHeight;
    target.style.overflow = "hidden";
    target.style.height = showmore ? `${showmore}px` : `0px`;
    target.style.paddingTop = 0;
    target.style.paddingBottom = 0;
    target.style.marginTop = 0;
    target.style.marginBottom = 0;
    window.setTimeout(() => {
      target.hidden = !showmore ? true : false;
      !showmore ? target.style.removeProperty("height") : null;
      target.style.removeProperty("padding-top");
      target.style.removeProperty("padding-bottom");
      target.style.removeProperty("margin-top");
      target.style.removeProperty("margin-bottom");
      !showmore ? target.style.removeProperty("overflow") : null;
      target.style.removeProperty("transition-duration");
      target.style.removeProperty("transition-property");
      target.classList.remove("--slide");
      document.dispatchEvent(
        new CustomEvent("slideUpDone", {
          detail: {
            target,
          },
        }),
      );
    }, duration);
  }
};
let slideDown = (target, duration = 500, showmore = 0) => {
  if (!target.classList.contains("--slide")) {
    target.classList.add("--slide");
    target.hidden = target.hidden ? false : null;
    showmore ? target.style.removeProperty("height") : null;
    let height = target.offsetHeight;
    target.style.overflow = "hidden";
    target.style.height = showmore ? `${showmore}px` : `0px`;
    target.style.paddingTop = 0;
    target.style.paddingBottom = 0;
    target.style.marginTop = 0;
    target.style.marginBottom = 0;
    target.offsetHeight;
    target.style.transitionProperty = "height, margin, padding";
    target.style.transitionDuration = duration + "ms";
    target.style.height = height + "px";
    target.style.removeProperty("padding-top");
    target.style.removeProperty("padding-bottom");
    target.style.removeProperty("margin-top");
    target.style.removeProperty("margin-bottom");
    window.setTimeout(() => {
      target.style.removeProperty("height");
      target.style.removeProperty("overflow");
      target.style.removeProperty("transition-duration");
      target.style.removeProperty("transition-property");
      target.classList.remove("--slide");
      document.dispatchEvent(
        new CustomEvent("slideDownDone", {
          detail: {
            target,
          },
        }),
      );
    }, duration);
  }
};
let slideToggle = (target, duration = 500) => {
  if (target.hidden) {
    return slideDown(target, duration);
  } else {
    return slideUp(target, duration);
  }
};
let bodyLockStatus = true;
let bodyUnlock = (delay = 500) => {
  if (bodyLockStatus) {
    const lockPaddingElements = document.querySelectorAll("[data-fls-lp]");
    setTimeout(() => {
      lockPaddingElements.forEach((lockPaddingElement) => {
        lockPaddingElement.style.paddingRight = "";
      });
      document.body.style.paddingRight = "";
      document.documentElement.removeAttribute("data-fls-scrolllock");
    }, delay);
    bodyLockStatus = false;
    setTimeout(function () {
      bodyLockStatus = true;
    }, delay);
  }
};
let bodyLock = (delay = 500) => {
  if (bodyLockStatus) {
    const lockPaddingElements = document.querySelectorAll("[data-fls-lp]");
    const lockPaddingValue =
      window.innerWidth - document.body.offsetWidth + "px";
    lockPaddingElements.forEach((lockPaddingElement) => {
      lockPaddingElement.style.paddingRight = lockPaddingValue;
    });
    document.body.style.paddingRight = lockPaddingValue;
    document.documentElement.setAttribute("data-fls-scrolllock", "");
    bodyLockStatus = false;
    setTimeout(function () {
      bodyLockStatus = true;
    }, delay);
  }
};
function uniqArray(array) {
  return array.filter((item, index, self2) => self2.indexOf(item) === index);
}
function dataMediaQueries(array, dataSetValue) {
  const media = Array.from(array)
    .filter((item) => item.dataset[dataSetValue])
    .map((item) => {
      const [value, type = "max"] = item.dataset[dataSetValue].split(",");
      return { value, type, item };
    });
  if (media.length === 0) return [];
  const breakpointsArray = media.map(
    ({ value, type }) => `(${type}-width: ${value}px),${value},${type}`,
  );
  const uniqueQueries = [...new Set(breakpointsArray)];
  return uniqueQueries.map((query2) => {
    const [mediaQuery, mediaBreakpoint, mediaType] = query2.split(",");
    const matchMedia2 = window.matchMedia(mediaQuery);
    const itemsArray = media.filter(
      (item) => item.value === mediaBreakpoint && item.type === mediaType,
    );
    return { itemsArray, matchMedia: matchMedia2 };
  });
}
let formValidate = {
  getErrors(form) {
    let error = 0;
    let formRequiredItems = form.querySelectorAll("[required]");
    if (formRequiredItems.length) {
      formRequiredItems.forEach((formRequiredItem) => {
        if (
          (formRequiredItem.offsetParent !== null ||
            formRequiredItem.tagName === "SELECT") &&
          !formRequiredItem.disabled
        ) {
          error += this.validateInput(formRequiredItem);
        }
      });
    }
    return error;
  },
  validateInput(formRequiredItem) {
    let error = 0;
    if (formRequiredItem.type === "email") {
      formRequiredItem.value = formRequiredItem.value.replace(" ", "");
      if (this.emailTest(formRequiredItem)) {
        this.addError(formRequiredItem);
        this.removeSuccess(formRequiredItem);
        error++;
      } else {
        this.removeError(formRequiredItem);
        this.addSuccess(formRequiredItem);
      }
    } else if (
      formRequiredItem.type === "checkbox" &&
      !formRequiredItem.checked
    ) {
      this.addError(formRequiredItem);
      this.removeSuccess(formRequiredItem);
      error++;
    } else {
      if (!formRequiredItem.value.trim()) {
        this.addError(formRequiredItem);
        this.removeSuccess(formRequiredItem);
        error++;
      } else {
        this.removeError(formRequiredItem);
        this.addSuccess(formRequiredItem);
      }
    }
    return error;
  },
  addError(formRequiredItem) {
    formRequiredItem.classList.add("--form-error");
    formRequiredItem.parentElement.classList.add("--form-error");
    let inputError = formRequiredItem.parentElement.querySelector(
      "[data-fls-form-error]",
    );
    if (inputError) formRequiredItem.parentElement.removeChild(inputError);
    if (formRequiredItem.dataset.flsFormErrtext) {
      formRequiredItem.parentElement.insertAdjacentHTML(
        "beforeend",
        `<div data-fls-form-error>${formRequiredItem.dataset.flsFormErrtext}</div>`,
      );
    }
  },
  removeError(formRequiredItem) {
    formRequiredItem.classList.remove("--form-error");
    formRequiredItem.parentElement.classList.remove("--form-error");
    if (formRequiredItem.parentElement.querySelector("[data-fls-form-error]")) {
      formRequiredItem.parentElement.removeChild(
        formRequiredItem.parentElement.querySelector("[data-fls-form-error]"),
      );
    }
  },
  addSuccess(formRequiredItem) {
    formRequiredItem.classList.add("--form-success");
    formRequiredItem.parentElement.classList.add("--form-success");
  },
  removeSuccess(formRequiredItem) {
    formRequiredItem.classList.remove("--form-success");
    formRequiredItem.parentElement.classList.remove("--form-success");
  },
  formClean(form) {
    form.reset();
    setTimeout(() => {
      let inputs = form.querySelectorAll("input,textarea");
      for (let index = 0; index < inputs.length; index++) {
        const el = inputs[index];
        el.parentElement.classList.remove("--form-focus");
        el.classList.remove("--form-focus");
        formValidate.removeError(el);
      }
      let checkboxes = form.querySelectorAll('input[type="checkbox"]');
      if (checkboxes.length) {
        checkboxes.forEach((checkbox) => {
          checkbox.checked = false;
        });
      }
      if (window["flsSelect"]) {
        let selects = form.querySelectorAll(
          "select[data-fls-select], .fls-select",
        );

        if (selects.length) {
          selects.forEach((select) => {
            window["flsSelect"].selectBuild(select);
          });
        }
      }
    }, 0);
  },
  emailTest(formRequiredItem) {
    return !/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,8})+$/.test(
      formRequiredItem.value,
    );
  },
};
function isObject$1(value) {
  var type = typeof value;
  return value != null && (type == "object" || type == "function");
}
var freeGlobal =
  typeof global == "object" && global && global.Object === Object && global;
var freeSelf =
  typeof self == "object" && self && self.Object === Object && self;
var root = freeGlobal || freeSelf || Function("return this")();
var now = function () {
  return root.Date.now();
};
var reWhitespace = /\s/;
function trimmedEndIndex(string) {
  var index = string.length;
  while (index-- && reWhitespace.test(string.charAt(index))) {}
  return index;
}
var reTrimStart = /^\s+/;
function baseTrim(string) {
  return string
    ? string.slice(0, trimmedEndIndex(string) + 1).replace(reTrimStart, "")
    : string;
}
var Symbol$1 = root.Symbol;
var objectProto$1 = Object.prototype;
var hasOwnProperty = objectProto$1.hasOwnProperty;
var nativeObjectToString$1 = objectProto$1.toString;
var symToStringTag$1 = Symbol$1 ? Symbol$1.toStringTag : void 0;
function getRawTag(value) {
  var isOwn = hasOwnProperty.call(value, symToStringTag$1),
    tag = value[symToStringTag$1];
  try {
    value[symToStringTag$1] = void 0;
    var unmasked = true;
  } catch (e) {}
  var result = nativeObjectToString$1.call(value);
  if (unmasked) {
    if (isOwn) {
      value[symToStringTag$1] = tag;
    } else {
      delete value[symToStringTag$1];
    }
  }
  return result;
}
var objectProto = Object.prototype;
var nativeObjectToString = objectProto.toString;
function objectToString(value) {
  return nativeObjectToString.call(value);
}
var nullTag = "[object Null]",
  undefinedTag = "[object Undefined]";
var symToStringTag = Symbol$1 ? Symbol$1.toStringTag : void 0;
function baseGetTag(value) {
  if (value == null) {
    return value === void 0 ? undefinedTag : nullTag;
  }
  return symToStringTag && symToStringTag in Object(value)
    ? getRawTag(value)
    : objectToString(value);
}
function isObjectLike(value) {
  return value != null && typeof value == "object";
}
var symbolTag = "[object Symbol]";
function isSymbol(value) {
  return (
    typeof value == "symbol" ||
    (isObjectLike(value) && baseGetTag(value) == symbolTag)
  );
}
var NAN = 0 / 0;
var reIsBadHex = /^[-+]0x[0-9a-f]+$/i;
var reIsBinary = /^0b[01]+$/i;
var reIsOctal = /^0o[0-7]+$/i;
var freeParseInt = parseInt;
function toNumber(value) {
  if (typeof value == "number") {
    return value;
  }
  if (isSymbol(value)) {
    return NAN;
  }
  if (isObject$1(value)) {
    var other = typeof value.valueOf == "function" ? value.valueOf() : value;
    value = isObject$1(other) ? other + "" : other;
  }
  if (typeof value != "string") {
    return value === 0 ? value : +value;
  }
  value = baseTrim(value);
  var isBinary = reIsBinary.test(value);
  return isBinary || reIsOctal.test(value)
    ? freeParseInt(value.slice(2), isBinary ? 2 : 8)
    : reIsBadHex.test(value)
      ? NAN
      : +value;
}
var FUNC_ERROR_TEXT$1 = "Expected a function";
var nativeMax = Math.max,
  nativeMin = Math.min;
function debounce(func, wait, options) {
  var lastArgs,
    lastThis,
    maxWait,
    result,
    timerId,
    lastCallTime,
    lastInvokeTime = 0,
    leading = false,
    maxing = false,
    trailing = true;
  if (typeof func != "function") {
    throw new TypeError(FUNC_ERROR_TEXT$1);
  }
  wait = toNumber(wait) || 0;
  if (isObject$1(options)) {
    leading = !!options.leading;
    maxing = "maxWait" in options;
    maxWait = maxing
      ? nativeMax(toNumber(options.maxWait) || 0, wait)
      : maxWait;
    trailing = "trailing" in options ? !!options.trailing : trailing;
  }
  function invokeFunc(time) {
    var args = lastArgs,
      thisArg = lastThis;
    lastArgs = lastThis = void 0;
    lastInvokeTime = time;
    result = func.apply(thisArg, args);
    return result;
  }
  function leadingEdge(time) {
    lastInvokeTime = time;
    timerId = setTimeout(timerExpired, wait);
    return leading ? invokeFunc(time) : result;
  }
  function remainingWait(time) {
    var timeSinceLastCall = time - lastCallTime,
      timeSinceLastInvoke = time - lastInvokeTime,
      timeWaiting = wait - timeSinceLastCall;
    return maxing
      ? nativeMin(timeWaiting, maxWait - timeSinceLastInvoke)
      : timeWaiting;
  }
  function shouldInvoke(time) {
    var timeSinceLastCall = time - lastCallTime,
      timeSinceLastInvoke = time - lastInvokeTime;
    return (
      lastCallTime === void 0 ||
      timeSinceLastCall >= wait ||
      timeSinceLastCall < 0 ||
      (maxing && timeSinceLastInvoke >= maxWait)
    );
  }
  function timerExpired() {
    var time = now();
    if (shouldInvoke(time)) {
      return trailingEdge(time);
    }
    timerId = setTimeout(timerExpired, remainingWait(time));
  }
  function trailingEdge(time) {
    timerId = void 0;
    if (trailing && lastArgs) {
      return invokeFunc(time);
    }
    lastArgs = lastThis = void 0;
    return result;
  }
  function cancel() {
    if (timerId !== void 0) {
      clearTimeout(timerId);
    }
    lastInvokeTime = 0;
    lastArgs = lastCallTime = lastThis = timerId = void 0;
  }
  function flush() {
    return timerId === void 0 ? result : trailingEdge(now());
  }
  function debounced() {
    var time = now(),
      isInvoking = shouldInvoke(time);
    lastArgs = arguments;
    lastThis = this;
    lastCallTime = time;
    if (isInvoking) {
      if (timerId === void 0) {
        return leadingEdge(lastCallTime);
      }
      if (maxing) {
        clearTimeout(timerId);
        timerId = setTimeout(timerExpired, wait);
        return invokeFunc(lastCallTime);
      }
    }
    if (timerId === void 0) {
      timerId = setTimeout(timerExpired, wait);
    }
    return result;
  }
  debounced.cancel = cancel;
  debounced.flush = flush;
  return debounced;
}
var FUNC_ERROR_TEXT = "Expected a function";
function throttle(func, wait, options) {
  var leading = true,
    trailing = true;
  if (typeof func != "function") {
    throw new TypeError(FUNC_ERROR_TEXT);
  }
  if (isObject$1(options)) {
    leading = "leading" in options ? !!options.leading : leading;
    trailing = "trailing" in options ? !!options.trailing : trailing;
  }
  return debounce(func, wait, {
    leading: leading,
    maxWait: wait,
    trailing: trailing,
  });
}
var __assign = function () {
  __assign =
    Object.assign ||
    function __assign2(t) {
      for (var s, i = 1, n = arguments.length; i < n; i++) {
        s = arguments[i];
        for (var p in s)
          if (Object.prototype.hasOwnProperty.call(s, p)) t[p] = s[p];
      }
      return t;
    };
  return __assign.apply(this, arguments);
};
function getElementWindow$1(element) {
  if (
    !element ||
    !element.ownerDocument ||
    !element.ownerDocument.defaultView
  ) {
    return window;
  }
  return element.ownerDocument.defaultView;
}
function getElementDocument$1(element) {
  if (!element || !element.ownerDocument) {
    return document;
  }
  return element.ownerDocument;
}
var getOptions$1 = function (obj) {
  var initialObj = {};
  var options = Array.prototype.reduce.call(
    obj,
    function (acc, attribute) {
      var option = attribute.name.match(/data-simplebar-(.+)/);
      if (option) {
        var key = option[1].replace(/\W+(.)/g, function (_, chr) {
          return chr.toUpperCase();
        });
        switch (attribute.value) {
          case "true":
            acc[key] = true;
            break;
          case "false":
            acc[key] = false;
            break;
          case void 0:
            acc[key] = true;
            break;
          default:
            acc[key] = attribute.value;
        }
      }
      return acc;
    },
    initialObj,
  );
  return options;
};
function addClasses$1(el, classes) {
  var _a2;
  if (!el) return;
  (_a2 = el.classList).add.apply(_a2, classes.split(" "));
}
function removeClasses$1(el, classes) {
  if (!el) return;
  classes.split(" ").forEach(function (className) {
    el.classList.remove(className);
  });
}
function classNamesToQuery$1(classNames) {
  return ".".concat(classNames.split(" ").join("."));
}
var canUseDOM$1 = !!(
  typeof window !== "undefined" &&
  window.document &&
  window.document.createElement
);
var helpers = /* @__PURE__ */ Object.freeze({
  __proto__: null,
  addClasses: addClasses$1,
  canUseDOM: canUseDOM$1,
  classNamesToQuery: classNamesToQuery$1,
  getElementDocument: getElementDocument$1,
  getElementWindow: getElementWindow$1,
  getOptions: getOptions$1,
  removeClasses: removeClasses$1,
});
var cachedScrollbarWidth = null;
var cachedDevicePixelRatio = null;
if (canUseDOM$1) {
  window.addEventListener("resize", function () {
    if (cachedDevicePixelRatio !== window.devicePixelRatio) {
      cachedDevicePixelRatio = window.devicePixelRatio;
      cachedScrollbarWidth = null;
    }
  });
}
function scrollbarWidth() {
  if (cachedScrollbarWidth === null) {
    if (typeof document === "undefined") {
      cachedScrollbarWidth = 0;
      return cachedScrollbarWidth;
    }
    var body = document.body;
    var box = document.createElement("div");
    box.classList.add("simplebar-hide-scrollbar");
    body.appendChild(box);
    var width = box.getBoundingClientRect().right;
    body.removeChild(box);
    cachedScrollbarWidth = width;
  }
  return cachedScrollbarWidth;
}
var getElementWindow = getElementWindow$1,
  getElementDocument = getElementDocument$1,
  getOptions$2 = getOptions$1,
  addClasses$2 = addClasses$1,
  removeClasses = removeClasses$1,
  classNamesToQuery = classNamesToQuery$1;
var SimpleBarCore =
  /** @class */
  (function () {
    function SimpleBarCore2(element, options) {
      if (options === void 0) {
        options = {};
      }
      var _this = this;
      this.removePreventClickId = null;
      this.minScrollbarWidth = 20;
      this.stopScrollDelay = 175;
      this.isScrolling = false;
      this.isMouseEntering = false;
      this.isDragging = false;
      this.scrollXTicking = false;
      this.scrollYTicking = false;
      this.wrapperEl = null;
      this.contentWrapperEl = null;
      this.contentEl = null;
      this.offsetEl = null;
      this.maskEl = null;
      this.placeholderEl = null;
      this.heightAutoObserverWrapperEl = null;
      this.heightAutoObserverEl = null;
      this.rtlHelpers = null;
      this.scrollbarWidth = 0;
      this.resizeObserver = null;
      this.mutationObserver = null;
      this.elStyles = null;
      this.isRtl = null;
      this.mouseX = 0;
      this.mouseY = 0;
      this.onMouseMove = function () {};
      this.onWindowResize = function () {};
      this.onStopScrolling = function () {};
      this.onMouseEntered = function () {};
      this.onScroll = function () {
        var elWindow = getElementWindow(_this.el);
        if (!_this.scrollXTicking) {
          elWindow.requestAnimationFrame(_this.scrollX);
          _this.scrollXTicking = true;
        }
        if (!_this.scrollYTicking) {
          elWindow.requestAnimationFrame(_this.scrollY);
          _this.scrollYTicking = true;
        }
        if (!_this.isScrolling) {
          _this.isScrolling = true;
          addClasses$2(_this.el, _this.classNames.scrolling);
        }
        _this.showScrollbar("x");
        _this.showScrollbar("y");
        _this.onStopScrolling();
      };
      this.scrollX = function () {
        if (_this.axis.x.isOverflowing) {
          _this.positionScrollbar("x");
        }
        _this.scrollXTicking = false;
      };
      this.scrollY = function () {
        if (_this.axis.y.isOverflowing) {
          _this.positionScrollbar("y");
        }
        _this.scrollYTicking = false;
      };
      this._onStopScrolling = function () {
        removeClasses(_this.el, _this.classNames.scrolling);
        if (_this.options.autoHide) {
          _this.hideScrollbar("x");
          _this.hideScrollbar("y");
        }
        _this.isScrolling = false;
      };
      this.onMouseEnter = function () {
        if (!_this.isMouseEntering) {
          addClasses$2(_this.el, _this.classNames.mouseEntered);
          _this.showScrollbar("x");
          _this.showScrollbar("y");
          _this.isMouseEntering = true;
        }
        _this.onMouseEntered();
      };
      this._onMouseEntered = function () {
        removeClasses(_this.el, _this.classNames.mouseEntered);
        if (_this.options.autoHide) {
          _this.hideScrollbar("x");
          _this.hideScrollbar("y");
        }
        _this.isMouseEntering = false;
      };
      this._onMouseMove = function (e) {
        _this.mouseX = e.clientX;
        _this.mouseY = e.clientY;
        if (_this.axis.x.isOverflowing || _this.axis.x.forceVisible) {
          _this.onMouseMoveForAxis("x");
        }
        if (_this.axis.y.isOverflowing || _this.axis.y.forceVisible) {
          _this.onMouseMoveForAxis("y");
        }
      };
      this.onMouseLeave = function () {
        _this.onMouseMove.cancel();
        if (_this.axis.x.isOverflowing || _this.axis.x.forceVisible) {
          _this.onMouseLeaveForAxis("x");
        }
        if (_this.axis.y.isOverflowing || _this.axis.y.forceVisible) {
          _this.onMouseLeaveForAxis("y");
        }
        _this.mouseX = -1;
        _this.mouseY = -1;
      };
      this._onWindowResize = function () {
        _this.scrollbarWidth = _this.getScrollbarWidth();
        _this.hideNativeScrollbar();
      };
      this.onPointerEvent = function (e) {
        if (
          !_this.axis.x.track.el ||
          !_this.axis.y.track.el ||
          !_this.axis.x.scrollbar.el ||
          !_this.axis.y.scrollbar.el
        )
          return;
        var isWithinTrackXBounds, isWithinTrackYBounds;
        _this.axis.x.track.rect = _this.axis.x.track.el.getBoundingClientRect();
        _this.axis.y.track.rect = _this.axis.y.track.el.getBoundingClientRect();
        if (_this.axis.x.isOverflowing || _this.axis.x.forceVisible) {
          isWithinTrackXBounds = _this.isWithinBounds(_this.axis.x.track.rect);
        }
        if (_this.axis.y.isOverflowing || _this.axis.y.forceVisible) {
          isWithinTrackYBounds = _this.isWithinBounds(_this.axis.y.track.rect);
        }
        if (isWithinTrackXBounds || isWithinTrackYBounds) {
          e.stopPropagation();
          if (e.type === "pointerdown" && e.pointerType !== "touch") {
            if (isWithinTrackXBounds) {
              _this.axis.x.scrollbar.rect =
                _this.axis.x.scrollbar.el.getBoundingClientRect();
              if (_this.isWithinBounds(_this.axis.x.scrollbar.rect)) {
                _this.onDragStart(e, "x");
              } else {
                _this.onTrackClick(e, "x");
              }
            }
            if (isWithinTrackYBounds) {
              _this.axis.y.scrollbar.rect =
                _this.axis.y.scrollbar.el.getBoundingClientRect();
              if (_this.isWithinBounds(_this.axis.y.scrollbar.rect)) {
                _this.onDragStart(e, "y");
              } else {
                _this.onTrackClick(e, "y");
              }
            }
          }
        }
      };
      this.drag = function (e) {
        var _a2, _b, _c, _d, _e, _f, _g, _h, _j, _k, _l;
        if (!_this.draggedAxis || !_this.contentWrapperEl) return;
        var eventOffset;
        var track = _this.axis[_this.draggedAxis].track;
        var trackSize =
          (_b =
            (_a2 = track.rect) === null || _a2 === void 0
              ? void 0
              : _a2[_this.axis[_this.draggedAxis].sizeAttr]) !== null &&
          _b !== void 0
            ? _b
            : 0;
        var scrollbar = _this.axis[_this.draggedAxis].scrollbar;
        var contentSize =
          (_d =
            (_c = _this.contentWrapperEl) === null || _c === void 0
              ? void 0
              : _c[_this.axis[_this.draggedAxis].scrollSizeAttr]) !== null &&
          _d !== void 0
            ? _d
            : 0;
        var hostSize = parseInt(
          (_f =
            (_e = _this.elStyles) === null || _e === void 0
              ? void 0
              : _e[_this.axis[_this.draggedAxis].sizeAttr]) !== null &&
            _f !== void 0
            ? _f
            : "0px",
          10,
        );
        e.preventDefault();
        e.stopPropagation();
        if (_this.draggedAxis === "y") {
          eventOffset = e.pageY;
        } else {
          eventOffset = e.pageX;
        }
        var dragPos =
          eventOffset -
          ((_h =
            (_g = track.rect) === null || _g === void 0
              ? void 0
              : _g[_this.axis[_this.draggedAxis].offsetAttr]) !== null &&
          _h !== void 0
            ? _h
            : 0) -
          _this.axis[_this.draggedAxis].dragOffset;
        dragPos =
          _this.draggedAxis === "x" && _this.isRtl
            ? ((_k =
                (_j = track.rect) === null || _j === void 0
                  ? void 0
                  : _j[_this.axis[_this.draggedAxis].sizeAttr]) !== null &&
              _k !== void 0
                ? _k
                : 0) -
              scrollbar.size -
              dragPos
            : dragPos;
        var dragPerc = dragPos / (trackSize - scrollbar.size);
        var scrollPos = dragPerc * (contentSize - hostSize);
        if (_this.draggedAxis === "x" && _this.isRtl) {
          scrollPos = (
            (_l = SimpleBarCore2.getRtlHelpers()) === null || _l === void 0
              ? void 0
              : _l.isScrollingToNegative
          )
            ? -scrollPos
            : scrollPos;
        }
        _this.contentWrapperEl[_this.axis[_this.draggedAxis].scrollOffsetAttr] =
          scrollPos;
      };
      this.onEndDrag = function (e) {
        _this.isDragging = false;
        var elDocument = getElementDocument(_this.el);
        var elWindow = getElementWindow(_this.el);
        e.preventDefault();
        e.stopPropagation();
        removeClasses(_this.el, _this.classNames.dragging);
        _this.onStopScrolling();
        elDocument.removeEventListener("mousemove", _this.drag, true);
        elDocument.removeEventListener("mouseup", _this.onEndDrag, true);
        _this.removePreventClickId = elWindow.setTimeout(function () {
          elDocument.removeEventListener("click", _this.preventClick, true);
          elDocument.removeEventListener("dblclick", _this.preventClick, true);
          _this.removePreventClickId = null;
        });
      };
      this.preventClick = function (e) {
        e.preventDefault();
        e.stopPropagation();
      };
      this.el = element;
      this.options = __assign(
        __assign({}, SimpleBarCore2.defaultOptions),
        options,
      );
      this.classNames = __assign(
        __assign({}, SimpleBarCore2.defaultOptions.classNames),
        options.classNames,
      );
      this.axis = {
        x: {
          scrollOffsetAttr: "scrollLeft",
          sizeAttr: "width",
          scrollSizeAttr: "scrollWidth",
          offsetSizeAttr: "offsetWidth",
          offsetAttr: "left",
          overflowAttr: "overflowX",
          dragOffset: 0,
          isOverflowing: true,
          forceVisible: false,
          track: { size: null, el: null, rect: null, isVisible: false },
          scrollbar: { size: null, el: null, rect: null, isVisible: false },
        },
        y: {
          scrollOffsetAttr: "scrollTop",
          sizeAttr: "height",
          scrollSizeAttr: "scrollHeight",
          offsetSizeAttr: "offsetHeight",
          offsetAttr: "top",
          overflowAttr: "overflowY",
          dragOffset: 0,
          isOverflowing: true,
          forceVisible: false,
          track: { size: null, el: null, rect: null, isVisible: false },
          scrollbar: { size: null, el: null, rect: null, isVisible: false },
        },
      };
      if (typeof this.el !== "object" || !this.el.nodeName) {
        throw new Error(
          "Argument passed to SimpleBar must be an HTML element instead of ".concat(
            this.el,
          ),
        );
      }
      this.onMouseMove = throttle(this._onMouseMove, 64);
      this.onWindowResize = debounce(this._onWindowResize, 64, {
        leading: true,
      });
      this.onStopScrolling = debounce(
        this._onStopScrolling,
        this.stopScrollDelay,
      );
      this.onMouseEntered = debounce(
        this._onMouseEntered,
        this.stopScrollDelay,
      );
      this.init();
    }
    SimpleBarCore2.getRtlHelpers = function () {
      if (SimpleBarCore2.rtlHelpers) {
        return SimpleBarCore2.rtlHelpers;
      }
      var dummyDiv = document.createElement("div");
      dummyDiv.innerHTML =
        '<div class="simplebar-dummy-scrollbar-size"><div></div></div>';
      var scrollbarDummyEl = dummyDiv.firstElementChild;
      var dummyChild =
        scrollbarDummyEl === null || scrollbarDummyEl === void 0
          ? void 0
          : scrollbarDummyEl.firstElementChild;
      if (!dummyChild) return null;
      document.body.appendChild(scrollbarDummyEl);
      scrollbarDummyEl.scrollLeft = 0;
      var dummyContainerOffset = SimpleBarCore2.getOffset(scrollbarDummyEl);
      var dummyChildOffset = SimpleBarCore2.getOffset(dummyChild);
      scrollbarDummyEl.scrollLeft = -999;
      var dummyChildOffsetAfterScroll = SimpleBarCore2.getOffset(dummyChild);
      document.body.removeChild(scrollbarDummyEl);
      SimpleBarCore2.rtlHelpers = {
        // determines if the scrolling is responding with negative values
        isScrollOriginAtZero:
          dummyContainerOffset.left !== dummyChildOffset.left,
        // determines if the origin scrollbar position is inverted or not (positioned on left or right)
        isScrollingToNegative:
          dummyChildOffset.left !== dummyChildOffsetAfterScroll.left,
      };
      return SimpleBarCore2.rtlHelpers;
    };
    SimpleBarCore2.prototype.getScrollbarWidth = function () {
      try {
        if (
          (this.contentWrapperEl &&
            getComputedStyle(this.contentWrapperEl, "::-webkit-scrollbar")
              .display === "none") ||
          "scrollbarWidth" in document.documentElement.style ||
          "-ms-overflow-style" in document.documentElement.style
        ) {
          return 0;
        } else {
          return scrollbarWidth();
        }
      } catch (e) {
        return scrollbarWidth();
      }
    };
    SimpleBarCore2.getOffset = function (el) {
      var rect2 = el.getBoundingClientRect();
      var elDocument = getElementDocument(el);
      var elWindow = getElementWindow(el);
      return {
        top:
          rect2.top +
          (elWindow.pageYOffset || elDocument.documentElement.scrollTop),
        left:
          rect2.left +
          (elWindow.pageXOffset || elDocument.documentElement.scrollLeft),
      };
    };
    SimpleBarCore2.prototype.init = function () {
      if (canUseDOM$1) {
        this.initDOM();
        this.rtlHelpers = SimpleBarCore2.getRtlHelpers();
        this.scrollbarWidth = this.getScrollbarWidth();
        this.recalculate();
        this.initListeners();
      }
    };
    SimpleBarCore2.prototype.initDOM = function () {
      var _a2, _b;
      this.wrapperEl = this.el.querySelector(
        classNamesToQuery(this.classNames.wrapper),
      );
      this.contentWrapperEl =
        this.options.scrollableNode ||
        this.el.querySelector(
          classNamesToQuery(this.classNames.contentWrapper),
        );
      this.contentEl =
        this.options.contentNode ||
        this.el.querySelector(classNamesToQuery(this.classNames.contentEl));
      this.offsetEl = this.el.querySelector(
        classNamesToQuery(this.classNames.offset),
      );
      this.maskEl = this.el.querySelector(
        classNamesToQuery(this.classNames.mask),
      );
      this.placeholderEl = this.findChild(
        this.wrapperEl,
        classNamesToQuery(this.classNames.placeholder),
      );
      this.heightAutoObserverWrapperEl = this.el.querySelector(
        classNamesToQuery(this.classNames.heightAutoObserverWrapperEl),
      );
      this.heightAutoObserverEl = this.el.querySelector(
        classNamesToQuery(this.classNames.heightAutoObserverEl),
      );
      this.axis.x.track.el = this.findChild(
        this.el,
        ""
          .concat(classNamesToQuery(this.classNames.track))
          .concat(classNamesToQuery(this.classNames.horizontal)),
      );
      this.axis.y.track.el = this.findChild(
        this.el,
        ""
          .concat(classNamesToQuery(this.classNames.track))
          .concat(classNamesToQuery(this.classNames.vertical)),
      );
      this.axis.x.scrollbar.el =
        ((_a2 = this.axis.x.track.el) === null || _a2 === void 0
          ? void 0
          : _a2.querySelector(classNamesToQuery(this.classNames.scrollbar))) ||
        null;
      this.axis.y.scrollbar.el =
        ((_b = this.axis.y.track.el) === null || _b === void 0
          ? void 0
          : _b.querySelector(classNamesToQuery(this.classNames.scrollbar))) ||
        null;
      if (!this.options.autoHide) {
        addClasses$2(this.axis.x.scrollbar.el, this.classNames.visible);
        addClasses$2(this.axis.y.scrollbar.el, this.classNames.visible);
      }
    };
    SimpleBarCore2.prototype.initListeners = function () {
      var _this = this;
      var _a2;
      var elWindow = getElementWindow(this.el);
      this.el.addEventListener("mouseenter", this.onMouseEnter);
      this.el.addEventListener("pointerdown", this.onPointerEvent, true);
      this.el.addEventListener("mousemove", this.onMouseMove);
      this.el.addEventListener("mouseleave", this.onMouseLeave);
      (_a2 = this.contentWrapperEl) === null || _a2 === void 0
        ? void 0
        : _a2.addEventListener("scroll", this.onScroll);
      elWindow.addEventListener("resize", this.onWindowResize);
      if (!this.contentEl) return;
      if (window.ResizeObserver) {
        var resizeObserverStarted_1 = false;
        var resizeObserver = elWindow.ResizeObserver || ResizeObserver;
        this.resizeObserver = new resizeObserver(function () {
          if (!resizeObserverStarted_1) return;
          elWindow.requestAnimationFrame(function () {
            _this.recalculate();
          });
        });
        this.resizeObserver.observe(this.el);
        this.resizeObserver.observe(this.contentEl);
        elWindow.requestAnimationFrame(function () {
          resizeObserverStarted_1 = true;
        });
      }
      this.mutationObserver = new elWindow.MutationObserver(function () {
        elWindow.requestAnimationFrame(function () {
          _this.recalculate();
        });
      });
      this.mutationObserver.observe(this.contentEl, {
        childList: true,
        subtree: true,
        characterData: true,
      });
    };
    SimpleBarCore2.prototype.recalculate = function () {
      if (
        !this.heightAutoObserverEl ||
        !this.contentEl ||
        !this.contentWrapperEl ||
        !this.wrapperEl ||
        !this.placeholderEl
      )
        return;
      var elWindow = getElementWindow(this.el);
      this.elStyles = elWindow.getComputedStyle(this.el);
      this.isRtl = this.elStyles.direction === "rtl";
      var contentElOffsetWidth = this.contentEl.offsetWidth;
      var isHeightAuto = this.heightAutoObserverEl.offsetHeight <= 1;
      var isWidthAuto =
        this.heightAutoObserverEl.offsetWidth <= 1 || contentElOffsetWidth > 0;
      var contentWrapperElOffsetWidth = this.contentWrapperEl.offsetWidth;
      var elOverflowX = this.elStyles.overflowX;
      var elOverflowY = this.elStyles.overflowY;
      this.contentEl.style.padding = ""
        .concat(this.elStyles.paddingTop, " ")
        .concat(this.elStyles.paddingRight, " ")
        .concat(this.elStyles.paddingBottom, " ")
        .concat(this.elStyles.paddingLeft);
      this.wrapperEl.style.margin = "-"
        .concat(this.elStyles.paddingTop, " -")
        .concat(this.elStyles.paddingRight, " -")
        .concat(this.elStyles.paddingBottom, " -")
        .concat(this.elStyles.paddingLeft);
      var contentElScrollHeight = this.contentEl.scrollHeight;
      var contentElScrollWidth = this.contentEl.scrollWidth;
      this.contentWrapperEl.style.height = isHeightAuto ? "auto" : "100%";
      this.placeholderEl.style.width = isWidthAuto
        ? "".concat(contentElOffsetWidth || contentElScrollWidth, "px")
        : "auto";
      this.placeholderEl.style.height = "".concat(contentElScrollHeight, "px");
      var contentWrapperElOffsetHeight = this.contentWrapperEl.offsetHeight;
      this.axis.x.isOverflowing =
        contentElOffsetWidth !== 0 &&
        contentElScrollWidth > contentElOffsetWidth;
      this.axis.y.isOverflowing =
        contentElScrollHeight > contentWrapperElOffsetHeight;
      this.axis.x.isOverflowing =
        elOverflowX === "hidden" ? false : this.axis.x.isOverflowing;
      this.axis.y.isOverflowing =
        elOverflowY === "hidden" ? false : this.axis.y.isOverflowing;
      this.axis.x.forceVisible =
        this.options.forceVisible === "x" || this.options.forceVisible === true;
      this.axis.y.forceVisible =
        this.options.forceVisible === "y" || this.options.forceVisible === true;
      this.hideNativeScrollbar();
      var offsetForXScrollbar = this.axis.x.isOverflowing
        ? this.scrollbarWidth
        : 0;
      var offsetForYScrollbar = this.axis.y.isOverflowing
        ? this.scrollbarWidth
        : 0;
      this.axis.x.isOverflowing =
        this.axis.x.isOverflowing &&
        contentElScrollWidth >
          contentWrapperElOffsetWidth - offsetForYScrollbar;
      this.axis.y.isOverflowing =
        this.axis.y.isOverflowing &&
        contentElScrollHeight >
          contentWrapperElOffsetHeight - offsetForXScrollbar;
      this.axis.x.scrollbar.size = this.getScrollbarSize("x");
      this.axis.y.scrollbar.size = this.getScrollbarSize("y");
      if (this.axis.x.scrollbar.el)
        this.axis.x.scrollbar.el.style.width = "".concat(
          this.axis.x.scrollbar.size,
          "px",
        );
      if (this.axis.y.scrollbar.el)
        this.axis.y.scrollbar.el.style.height = "".concat(
          this.axis.y.scrollbar.size,
          "px",
        );
      this.positionScrollbar("x");
      this.positionScrollbar("y");
      this.toggleTrackVisibility("x");
      this.toggleTrackVisibility("y");
    };
    SimpleBarCore2.prototype.getScrollbarSize = function (axis) {
      var _a2, _b;
      if (axis === void 0) {
        axis = "y";
      }
      if (!this.axis[axis].isOverflowing || !this.contentEl) {
        return 0;
      }
      var contentSize = this.contentEl[this.axis[axis].scrollSizeAttr];
      var trackSize =
        (_b =
          (_a2 = this.axis[axis].track.el) === null || _a2 === void 0
            ? void 0
            : _a2[this.axis[axis].offsetSizeAttr]) !== null && _b !== void 0
          ? _b
          : 0;
      var scrollbarRatio = trackSize / contentSize;
      var scrollbarSize;
      scrollbarSize = Math.max(
        ~~(scrollbarRatio * trackSize),
        this.options.scrollbarMinSize,
      );
      if (this.options.scrollbarMaxSize) {
        scrollbarSize = Math.min(scrollbarSize, this.options.scrollbarMaxSize);
      }
      return scrollbarSize;
    };
    SimpleBarCore2.prototype.positionScrollbar = function (axis) {
      var _a2, _b, _c;
      if (axis === void 0) {
        axis = "y";
      }
      var scrollbar = this.axis[axis].scrollbar;
      if (
        !this.axis[axis].isOverflowing ||
        !this.contentWrapperEl ||
        !scrollbar.el ||
        !this.elStyles
      ) {
        return;
      }
      var contentSize = this.contentWrapperEl[this.axis[axis].scrollSizeAttr];
      var trackSize =
        ((_a2 = this.axis[axis].track.el) === null || _a2 === void 0
          ? void 0
          : _a2[this.axis[axis].offsetSizeAttr]) || 0;
      var hostSize = parseInt(this.elStyles[this.axis[axis].sizeAttr], 10);
      var scrollOffset =
        this.contentWrapperEl[this.axis[axis].scrollOffsetAttr];
      scrollOffset =
        axis === "x" &&
        this.isRtl &&
        ((_b = SimpleBarCore2.getRtlHelpers()) === null || _b === void 0
          ? void 0
          : _b.isScrollOriginAtZero)
          ? -scrollOffset
          : scrollOffset;
      if (axis === "x" && this.isRtl) {
        scrollOffset = (
          (_c = SimpleBarCore2.getRtlHelpers()) === null || _c === void 0
            ? void 0
            : _c.isScrollingToNegative
        )
          ? scrollOffset
          : -scrollOffset;
      }
      var scrollPourcent = scrollOffset / (contentSize - hostSize);
      var handleOffset = ~~((trackSize - scrollbar.size) * scrollPourcent);
      handleOffset =
        axis === "x" && this.isRtl
          ? -handleOffset + (trackSize - scrollbar.size)
          : handleOffset;
      scrollbar.el.style.transform =
        axis === "x"
          ? "translate3d(".concat(handleOffset, "px, 0, 0)")
          : "translate3d(0, ".concat(handleOffset, "px, 0)");
    };
    SimpleBarCore2.prototype.toggleTrackVisibility = function (axis) {
      if (axis === void 0) {
        axis = "y";
      }
      var track = this.axis[axis].track.el;
      var scrollbar = this.axis[axis].scrollbar.el;
      if (!track || !scrollbar || !this.contentWrapperEl) return;
      if (this.axis[axis].isOverflowing || this.axis[axis].forceVisible) {
        track.style.visibility = "visible";
        this.contentWrapperEl.style[this.axis[axis].overflowAttr] = "scroll";
        this.el.classList.add(
          "".concat(this.classNames.scrollable, "-").concat(axis),
        );
      } else {
        track.style.visibility = "hidden";
        this.contentWrapperEl.style[this.axis[axis].overflowAttr] = "hidden";
        this.el.classList.remove(
          "".concat(this.classNames.scrollable, "-").concat(axis),
        );
      }
      if (this.axis[axis].isOverflowing) {
        scrollbar.style.display = "block";
      } else {
        scrollbar.style.display = "none";
      }
    };
    SimpleBarCore2.prototype.showScrollbar = function (axis) {
      if (axis === void 0) {
        axis = "y";
      }
      if (
        this.axis[axis].isOverflowing &&
        !this.axis[axis].scrollbar.isVisible
      ) {
        addClasses$2(this.axis[axis].scrollbar.el, this.classNames.visible);
        this.axis[axis].scrollbar.isVisible = true;
      }
    };
    SimpleBarCore2.prototype.hideScrollbar = function (axis) {
      if (axis === void 0) {
        axis = "y";
      }
      if (this.isDragging) return;
      if (
        this.axis[axis].isOverflowing &&
        this.axis[axis].scrollbar.isVisible
      ) {
        removeClasses(this.axis[axis].scrollbar.el, this.classNames.visible);
        this.axis[axis].scrollbar.isVisible = false;
      }
    };
    SimpleBarCore2.prototype.hideNativeScrollbar = function () {
      if (!this.offsetEl) return;
      this.offsetEl.style[this.isRtl ? "left" : "right"] =
        this.axis.y.isOverflowing || this.axis.y.forceVisible
          ? "-".concat(this.scrollbarWidth, "px")
          : "0px";
      this.offsetEl.style.bottom =
        this.axis.x.isOverflowing || this.axis.x.forceVisible
          ? "-".concat(this.scrollbarWidth, "px")
          : "0px";
    };
    SimpleBarCore2.prototype.onMouseMoveForAxis = function (axis) {
      if (axis === void 0) {
        axis = "y";
      }
      var currentAxis = this.axis[axis];
      if (!currentAxis.track.el || !currentAxis.scrollbar.el) return;
      currentAxis.track.rect = currentAxis.track.el.getBoundingClientRect();
      currentAxis.scrollbar.rect =
        currentAxis.scrollbar.el.getBoundingClientRect();
      if (this.isWithinBounds(currentAxis.track.rect)) {
        this.showScrollbar(axis);
        addClasses$2(currentAxis.track.el, this.classNames.hover);
        if (this.isWithinBounds(currentAxis.scrollbar.rect)) {
          addClasses$2(currentAxis.scrollbar.el, this.classNames.hover);
        } else {
          removeClasses(currentAxis.scrollbar.el, this.classNames.hover);
        }
      } else {
        removeClasses(currentAxis.track.el, this.classNames.hover);
        if (this.options.autoHide) {
          this.hideScrollbar(axis);
        }
      }
    };
    SimpleBarCore2.prototype.onMouseLeaveForAxis = function (axis) {
      if (axis === void 0) {
        axis = "y";
      }
      removeClasses(this.axis[axis].track.el, this.classNames.hover);
      removeClasses(this.axis[axis].scrollbar.el, this.classNames.hover);
      if (this.options.autoHide) {
        this.hideScrollbar(axis);
      }
    };
    SimpleBarCore2.prototype.onDragStart = function (e, axis) {
      var _a2;
      if (axis === void 0) {
        axis = "y";
      }
      this.isDragging = true;
      var elDocument = getElementDocument(this.el);
      var elWindow = getElementWindow(this.el);
      var scrollbar = this.axis[axis].scrollbar;
      var eventOffset = axis === "y" ? e.pageY : e.pageX;
      this.axis[axis].dragOffset =
        eventOffset -
        (((_a2 = scrollbar.rect) === null || _a2 === void 0
          ? void 0
          : _a2[this.axis[axis].offsetAttr]) || 0);
      this.draggedAxis = axis;
      addClasses$2(this.el, this.classNames.dragging);
      elDocument.addEventListener("mousemove", this.drag, true);
      elDocument.addEventListener("mouseup", this.onEndDrag, true);
      if (this.removePreventClickId === null) {
        elDocument.addEventListener("click", this.preventClick, true);
        elDocument.addEventListener("dblclick", this.preventClick, true);
      } else {
        elWindow.clearTimeout(this.removePreventClickId);
        this.removePreventClickId = null;
      }
    };
    SimpleBarCore2.prototype.onTrackClick = function (e, axis) {
      var _this = this;
      var _a2, _b, _c, _d;
      if (axis === void 0) {
        axis = "y";
      }
      var currentAxis = this.axis[axis];
      if (
        !this.options.clickOnTrack ||
        !currentAxis.scrollbar.el ||
        !this.contentWrapperEl
      )
        return;
      e.preventDefault();
      var elWindow = getElementWindow(this.el);
      this.axis[axis].scrollbar.rect =
        currentAxis.scrollbar.el.getBoundingClientRect();
      var scrollbar = this.axis[axis].scrollbar;
      var scrollbarOffset =
        (_b =
          (_a2 = scrollbar.rect) === null || _a2 === void 0
            ? void 0
            : _a2[this.axis[axis].offsetAttr]) !== null && _b !== void 0
          ? _b
          : 0;
      var hostSize = parseInt(
        (_d =
          (_c = this.elStyles) === null || _c === void 0
            ? void 0
            : _c[this.axis[axis].sizeAttr]) !== null && _d !== void 0
          ? _d
          : "0px",
        10,
      );
      var scrolled = this.contentWrapperEl[this.axis[axis].scrollOffsetAttr];
      var t =
        axis === "y"
          ? this.mouseY - scrollbarOffset
          : this.mouseX - scrollbarOffset;
      var dir = t < 0 ? -1 : 1;
      var scrollSize = dir === -1 ? scrolled - hostSize : scrolled + hostSize;
      var speed = 40;
      var scrollTo = function () {
        if (!_this.contentWrapperEl) return;
        if (dir === -1) {
          if (scrolled > scrollSize) {
            scrolled -= speed;
            _this.contentWrapperEl[_this.axis[axis].scrollOffsetAttr] =
              scrolled;
            elWindow.requestAnimationFrame(scrollTo);
          }
        } else {
          if (scrolled < scrollSize) {
            scrolled += speed;
            _this.contentWrapperEl[_this.axis[axis].scrollOffsetAttr] =
              scrolled;
            elWindow.requestAnimationFrame(scrollTo);
          }
        }
      };
      scrollTo();
    };
    SimpleBarCore2.prototype.getContentElement = function () {
      return this.contentEl;
    };
    SimpleBarCore2.prototype.getScrollElement = function () {
      return this.contentWrapperEl;
    };
    SimpleBarCore2.prototype.removeListeners = function () {
      var elWindow = getElementWindow(this.el);
      this.el.removeEventListener("mouseenter", this.onMouseEnter);
      this.el.removeEventListener("pointerdown", this.onPointerEvent, true);
      this.el.removeEventListener("mousemove", this.onMouseMove);
      this.el.removeEventListener("mouseleave", this.onMouseLeave);
      if (this.contentWrapperEl) {
        this.contentWrapperEl.removeEventListener("scroll", this.onScroll);
      }
      elWindow.removeEventListener("resize", this.onWindowResize);
      if (this.mutationObserver) {
        this.mutationObserver.disconnect();
      }
      if (this.resizeObserver) {
        this.resizeObserver.disconnect();
      }
      this.onMouseMove.cancel();
      this.onWindowResize.cancel();
      this.onStopScrolling.cancel();
      this.onMouseEntered.cancel();
    };
    SimpleBarCore2.prototype.unMount = function () {
      this.removeListeners();
    };
    SimpleBarCore2.prototype.isWithinBounds = function (bbox) {
      return (
        this.mouseX >= bbox.left &&
        this.mouseX <= bbox.left + bbox.width &&
        this.mouseY >= bbox.top &&
        this.mouseY <= bbox.top + bbox.height
      );
    };
    SimpleBarCore2.prototype.findChild = function (el, query2) {
      var matches2 =
        el.matches ||
        el.webkitMatchesSelector ||
        el.mozMatchesSelector ||
        el.msMatchesSelector;
      return Array.prototype.filter.call(el.children, function (child2) {
        return matches2.call(child2, query2);
      })[0];
    };
    SimpleBarCore2.rtlHelpers = null;
    SimpleBarCore2.defaultOptions = {
      forceVisible: false,
      clickOnTrack: true,
      scrollbarMinSize: 25,
      scrollbarMaxSize: 0,
      ariaLabel: "scrollable content",
      tabIndex: 0,
      classNames: {
        contentEl: "simplebar-content",
        contentWrapper: "simplebar-content-wrapper",
        offset: "simplebar-offset",
        mask: "simplebar-mask",
        wrapper: "simplebar-wrapper",
        placeholder: "simplebar-placeholder",
        scrollbar: "simplebar-scrollbar",
        track: "simplebar-track",
        heightAutoObserverWrapperEl: "simplebar-height-auto-observer-wrapper",
        heightAutoObserverEl: "simplebar-height-auto-observer",
        visible: "simplebar-visible",
        horizontal: "simplebar-horizontal",
        vertical: "simplebar-vertical",
        hover: "simplebar-hover",
        dragging: "simplebar-dragging",
        scrolling: "simplebar-scrolling",
        scrollable: "simplebar-scrollable",
        mouseEntered: "simplebar-mouse-entered",
      },
      scrollableNode: null,
      contentNode: null,
      autoHide: true,
    };
    SimpleBarCore2.getOptions = getOptions$2;
    SimpleBarCore2.helpers = helpers;
    return SimpleBarCore2;
  })();
var extendStatics = function (d, b) {
  extendStatics =
    Object.setPrototypeOf ||
    ({ __proto__: [] } instanceof Array &&
      function (d2, b2) {
        d2.__proto__ = b2;
      }) ||
    function (d2, b2) {
      for (var p in b2)
        if (Object.prototype.hasOwnProperty.call(b2, p)) d2[p] = b2[p];
    };
  return extendStatics(d, b);
};
function __extends(d, b) {
  if (typeof b !== "function" && b !== null)
    throw new TypeError(
      "Class extends value " + String(b) + " is not a constructor or null",
    );
  extendStatics(d, b);
  function __() {
    this.constructor = d;
  }
  d.prototype =
    b === null ? Object.create(b) : ((__.prototype = b.prototype), new __());
}
var _a = SimpleBarCore.helpers,
  getOptions = _a.getOptions,
  addClasses = _a.addClasses,
  canUseDOM = _a.canUseDOM;
var SimpleBar =
  /** @class */
  (function (_super) {
    __extends(SimpleBar2, _super);
    function SimpleBar2() {
      var args = [];
      for (var _i = 0; _i < arguments.length; _i++) {
        args[_i] = arguments[_i];
      }
      var _this = _super.apply(this, args) || this;
      SimpleBar2.instances.set(args[0], _this);
      return _this;
    }
    SimpleBar2.initDOMLoadedElements = function () {
      document.removeEventListener(
        "DOMContentLoaded",
        this.initDOMLoadedElements,
      );
      window.removeEventListener("load", this.initDOMLoadedElements);
      Array.prototype.forEach.call(
        document.querySelectorAll("[data-simplebar]"),
        function (el) {
          if (
            el.getAttribute("data-simplebar") !== "init" &&
            !SimpleBar2.instances.has(el)
          )
            new SimpleBar2(el, getOptions(el.attributes));
        },
      );
    };
    SimpleBar2.removeObserver = function () {
      var _a2;
      (_a2 = SimpleBar2.globalObserver) === null || _a2 === void 0
        ? void 0
        : _a2.disconnect();
    };
    SimpleBar2.prototype.initDOM = function () {
      var _this = this;
      var _a2, _b, _c;
      if (
        !Array.prototype.filter.call(this.el.children, function (child2) {
          return child2.classList.contains(_this.classNames.wrapper);
        }).length
      ) {
        this.wrapperEl = document.createElement("div");
        this.contentWrapperEl = document.createElement("div");
        this.offsetEl = document.createElement("div");
        this.maskEl = document.createElement("div");
        this.contentEl = document.createElement("div");
        this.placeholderEl = document.createElement("div");
        this.heightAutoObserverWrapperEl = document.createElement("div");
        this.heightAutoObserverEl = document.createElement("div");
        addClasses(this.wrapperEl, this.classNames.wrapper);
        addClasses(this.contentWrapperEl, this.classNames.contentWrapper);
        addClasses(this.offsetEl, this.classNames.offset);
        addClasses(this.maskEl, this.classNames.mask);
        addClasses(this.contentEl, this.classNames.contentEl);
        addClasses(this.placeholderEl, this.classNames.placeholder);
        addClasses(
          this.heightAutoObserverWrapperEl,
          this.classNames.heightAutoObserverWrapperEl,
        );
        addClasses(
          this.heightAutoObserverEl,
          this.classNames.heightAutoObserverEl,
        );
        while (this.el.firstChild) {
          this.contentEl.appendChild(this.el.firstChild);
        }
        this.contentWrapperEl.appendChild(this.contentEl);
        this.offsetEl.appendChild(this.contentWrapperEl);
        this.maskEl.appendChild(this.offsetEl);
        this.heightAutoObserverWrapperEl.appendChild(this.heightAutoObserverEl);
        this.wrapperEl.appendChild(this.heightAutoObserverWrapperEl);
        this.wrapperEl.appendChild(this.maskEl);
        this.wrapperEl.appendChild(this.placeholderEl);
        this.el.appendChild(this.wrapperEl);
        (_a2 = this.contentWrapperEl) === null || _a2 === void 0
          ? void 0
          : _a2.setAttribute("tabindex", this.options.tabIndex.toString());
        (_b = this.contentWrapperEl) === null || _b === void 0
          ? void 0
          : _b.setAttribute("role", "region");
        (_c = this.contentWrapperEl) === null || _c === void 0
          ? void 0
          : _c.setAttribute("aria-label", this.options.ariaLabel);
      }
      if (!this.axis.x.track.el || !this.axis.y.track.el) {
        var track = document.createElement("div");
        var scrollbar = document.createElement("div");
        addClasses(track, this.classNames.track);
        addClasses(scrollbar, this.classNames.scrollbar);
        track.appendChild(scrollbar);
        this.axis.x.track.el = track.cloneNode(true);
        addClasses(this.axis.x.track.el, this.classNames.horizontal);
        this.axis.y.track.el = track.cloneNode(true);
        addClasses(this.axis.y.track.el, this.classNames.vertical);
        this.el.appendChild(this.axis.x.track.el);
        this.el.appendChild(this.axis.y.track.el);
      }
      SimpleBarCore.prototype.initDOM.call(this);
      this.el.setAttribute("data-simplebar", "init");
    };
    SimpleBar2.prototype.unMount = function () {
      SimpleBarCore.prototype.unMount.call(this);
      SimpleBar2.instances["delete"](this.el);
    };
    SimpleBar2.initHtmlApi = function () {
      this.initDOMLoadedElements = this.initDOMLoadedElements.bind(this);
      if (typeof MutationObserver !== "undefined") {
        this.globalObserver = new MutationObserver(SimpleBar2.handleMutations);
        this.globalObserver.observe(document, {
          childList: true,
          subtree: true,
        });
      }
      if (
        document.readyState === "complete" || // @ts-ignore: IE specific
        (document.readyState !== "loading" &&
          !document.documentElement.doScroll)
      ) {
        window.setTimeout(this.initDOMLoadedElements);
      } else {
        document.addEventListener(
          "DOMContentLoaded",
          this.initDOMLoadedElements,
        );
        window.addEventListener("load", this.initDOMLoadedElements);
      }
    };
    SimpleBar2.handleMutations = function (mutations) {
      mutations.forEach(function (mutation) {
        mutation.addedNodes.forEach(function (addedNode) {
          if (addedNode.nodeType === 1) {
            if (addedNode.hasAttribute("data-simplebar")) {
              !SimpleBar2.instances.has(addedNode) &&
                document.documentElement.contains(addedNode) &&
                new SimpleBar2(addedNode, getOptions(addedNode.attributes));
            } else {
              addedNode
                .querySelectorAll("[data-simplebar]")
                .forEach(function (el) {
                  if (
                    el.getAttribute("data-simplebar") !== "init" &&
                    !SimpleBar2.instances.has(el) &&
                    document.documentElement.contains(el)
                  )
                    new SimpleBar2(el, getOptions(el.attributes));
                });
            }
          }
        });
        mutation.removedNodes.forEach(function (removedNode) {
          var _a2;
          if (removedNode.nodeType === 1) {
            if (removedNode.getAttribute("data-simplebar") === "init") {
              !document.documentElement.contains(removedNode) &&
                ((_a2 = SimpleBar2.instances.get(removedNode)) === null ||
                _a2 === void 0
                  ? void 0
                  : _a2.unMount());
            } else {
              Array.prototype.forEach.call(
                removedNode.querySelectorAll('[data-simplebar="init"]'),
                function (el) {
                  var _a3;
                  !document.documentElement.contains(el) &&
                    ((_a3 = SimpleBar2.instances.get(el)) === null ||
                    _a3 === void 0
                      ? void 0
                      : _a3.unMount());
                },
              );
            }
          }
        });
      });
    };
    SimpleBar2.instances = /* @__PURE__ */ new WeakMap();
    return SimpleBar2;
  })(SimpleBarCore);
if (canUseDOM) {
  SimpleBar.initHtmlApi();
}
class SelectConstructor {
  constructor(props, data = null) {
    let defaultConfig = {
      init: true,
      speed: 150,
    };
    this.config = Object.assign(defaultConfig, props);
    this.selectClasses = {
      classSelect: "select",
      classSelectBody: "select__body",
      classSelectTitle: "select__title",
      classSelectValue: "select__value",
      classSelectLabel: "select__label",
      classSelectInput: "select__input",
      classSelectText: "select__text",
      classSelectLink: "select__link",
      classSelectOptions: "select__options",
      classSelectOptionsScroll: "select__scroll",
      classSelectOption: "select__option",
      classSelectContent: "select__content",
      classSelectRow: "select__row",
      classSelectData: "select__asset",
      classSelectDisabled: "--select-disabled",
      classSelectTag: "--select-tag",
      classSelectOpen: "--select-open",
      classSelectActive: "--select-active",
      classSelectFocus: "--select-focus",
      classSelectMultiple: "--select-multiple",
      classSelectCheckBox: "--select-checkbox",
      classSelectOptionSelected: "--select-selected",
      classSelectPseudoLabel: "--select-pseudo-label",
    };
    this._this = this;
    if (this.config.init) {
      const selectItems = data
        ? document.querySelectorAll(data)
        : document.querySelectorAll("select[data-fls-select], .fls-select");
      if (selectItems.length) {
        this.selectsInit(selectItems);
      }
    }
  }
  getSelectClass(className) {
    return `.${className}`;
  }
  getSelectElement(selectItem, className) {
    return {
      originalSelect: selectItem.querySelector("select"),
      selectElement: selectItem.querySelector(this.getSelectClass(className)),
    };
  }
  selectsInit(selectItems) {
    selectItems.forEach((originalSelect, index) => {
      this.selectInit(originalSelect, index + 1);
    });
    document.addEventListener("click", (e) => this.selectsActions(e));
    document.addEventListener("keydown", (e) => this.selectsActions(e));
    document.addEventListener("focusin", (e) => this.selectsActions(e));
    document.addEventListener("focusout", (e) => this.selectsActions(e));
  }
  selectInit(originalSelect, index) {
    index ? (originalSelect.dataset.flsSelectId = index) : null;
    if (originalSelect.options.length) {
      const _this = this;
      let selectItem = document.createElement("div");
      selectItem.classList.add(this.selectClasses.classSelect);
      originalSelect.parentNode.insertBefore(selectItem, originalSelect);
      selectItem.appendChild(originalSelect);
      originalSelect.hidden = true;
      selectItem.insertAdjacentHTML(
        "beforeend",
        `<div class="${this.selectClasses.classSelectBody}"><div hidden class="${this.selectClasses.classSelectOptions}"></div></div>`,
      );
      this.selectBuild(originalSelect);
      originalSelect.dataset.flsSelectSpeed = originalSelect.dataset
        .flsSelectSpeed
        ? originalSelect.dataset.flsSelectSpeed
        : this.config.speed;
      this.config.speed = +originalSelect.dataset.flsSelectSpeed;
      originalSelect.addEventListener("change", function (e) {
        _this.selectChange(e);
      });
    }
  }
  selectBuild(originalSelect) {
    const selectItem = originalSelect.parentElement;
    if (originalSelect.id) {
      selectItem.id = originalSelect.id;
      originalSelect.removeAttribute("id");
    }
    selectItem.dataset.flsSelectId = originalSelect.dataset.flsSelectId;
    originalSelect.dataset.flsSelectModif
      ? selectItem.classList.add(
          `select--${originalSelect.dataset.flsSelectModif}`,
        )
      : null;
    originalSelect.multiple
      ? selectItem.classList.add(this.selectClasses.classSelectMultiple)
      : selectItem.classList.remove(this.selectClasses.classSelectMultiple);
    originalSelect.hasAttribute("data-fls-select-checkbox") &&
    originalSelect.multiple
      ? selectItem.classList.add(this.selectClasses.classSelectCheckBox)
      : selectItem.classList.remove(this.selectClasses.classSelectCheckBox);
    this.setSelectTitleValue(selectItem, originalSelect);
    this.setOptions(selectItem, originalSelect);
    originalSelect.hasAttribute("data-fls-select-open")
      ? this.selectAction(selectItem)
      : null;
    this.selectDisabled(selectItem, originalSelect);
  }
  setSelectTitleValue(selectItem, originalSelect) {
    const selectItemBody = this.getSelectElement(
      selectItem,
      this.selectClasses.classSelectBody,
    ).selectElement;
    const selectItemTitle = this.getSelectElement(
      selectItem,
      this.selectClasses.classSelectTitle,
    ).selectElement;
    if (selectItemTitle) selectItemTitle.remove();
    selectItemBody.insertAdjacentHTML(
      "afterbegin",
      this.getSelectTitleValue(selectItem, originalSelect),
    );
  }
  getSelectTitleValue(selectItem, originalSelect) {
    let selectTitleValue = this.getSelectedOptionsData(originalSelect, 2).html;
    selectTitleValue = selectTitleValue.length
      ? selectTitleValue
      : originalSelect.dataset.flsSelectPlaceholder
        ? originalSelect.dataset.flsSelectPlaceholder
        : "";
    selectTitleValue = selectTitleValue.map((item) =>
      item.replace(/"/g, "&quot;"),
    );
    let pseudoAttribute = "";
    let pseudoAttributeClass = "";
    if (originalSelect.hasAttribute("data-fls-select-pseudo-label")) {
      pseudoAttribute = originalSelect.dataset.flsSelectPseudoLabel
        ? ` data-fls-select-pseudo-label="${originalSelect.dataset.flsSelectPseudoLabel}"`
        : ` data-fls-select-pseudo-label="Заповніть атрибут"`;
      pseudoAttributeClass = ` ${this.selectClasses.classSelectPseudoLabel}`;
    }
    return `<button type="button" class="${this.selectClasses.classSelectTitle}">
			<span${pseudoAttribute} class="${this.selectClasses.classSelectValue}${pseudoAttributeClass}">
				<span class="${this.selectClasses.classSelectContent}">${selectTitleValue}</span>
			</span>
		</button>`;
  }
  getSelectedOptionsData(originalSelect, type) {
    let selectedOptions = [];
    if (originalSelect.multiple) {
      selectedOptions = Array.from(originalSelect.options)
        .filter((option) => option.value)
        .filter((option) => option.selected);
    } else {
      selectedOptions.push(
        originalSelect.options[originalSelect.selectedIndex],
      );
    }
    return {
      elements: selectedOptions.map((option) => option),
      values: selectedOptions
        .filter((option) => option.value)
        .map((option) => option.value),
      html: selectedOptions.map((option) =>
        // ПЕРЕДАЄМО true: це каже методу використати data-mini
        this.getSelectElementContent(option, true),
      ),
    };
  }
  getSelectElementContent(selectOption, isTitle = false) {
    if (isTitle && selectOption.dataset.mini) {
      return selectOption.dataset.mini;
    }
    const selectOptionData = selectOption.dataset.flsSelectAsset
      ? `${selectOption.dataset.flsSelectAsset}`
      : "";
    const selectOptionDataHTML =
      selectOptionData.indexOf("img") >= 0
        ? `<img src="${selectOptionData}" alt="">`
        : selectOptionData;
    let selectOptionContentHTML = ``;
    selectOptionContentHTML += selectOptionData
      ? `<span class="${this.selectClasses.classSelectRow}">`
      : "";
    selectOptionContentHTML += selectOptionData
      ? `<span class="${this.selectClasses.classSelectData}">`
      : "";
    selectOptionContentHTML += selectOptionData ? selectOptionDataHTML : "";
    selectOptionContentHTML += selectOptionData ? `</span>` : "";
    selectOptionContentHTML += selectOptionData
      ? `<span class="${this.selectClasses.classSelectText}">`
      : "";
    selectOptionContentHTML += selectOption.textContent;
    selectOptionContentHTML += selectOptionData ? `</span></span>` : "";
    return selectOptionContentHTML;
  }
  setOptions(selectItem, originalSelect) {
    const selectItemOptions = this.getSelectElement(
      selectItem,
      this.selectClasses.classSelectOptions,
    ).selectElement;
    selectItemOptions.innerHTML = this.getOptions(originalSelect);
    const scrollEl = selectItem.querySelector(".select__scroll");
    if (scrollEl) {
      selectItemOptions.hidden = false;
      scrollEl.style.setProperty(
        "--content-height",
        `${scrollEl.offsetHeight}px`,
      );
      selectItemOptions.hidden = true;
      if (!scrollEl.SimpleBar) new SimpleBar(scrollEl);
    }
    if (originalSelect.hasAttribute("data-fls-select-search")) {
      const placeholder =
        originalSelect.dataset.flsSelectPlaceholder || "Пошук";
      const searchHTML = `
        <div action="#" class="select__search search search--solid">
            <input type="text" name="search" placeholder="${placeholder}" class="search__input input">
            <div class="search__submit _icon-search"></div>
        </div>
    `;
      selectItemOptions.insertAdjacentHTML("afterbegin", searchHTML);
      this.searchActions(selectItem);
    }
  }
  getOptions(originalSelect) {
    let selectOptions = Array.from(originalSelect.options);
    let html = `<div class="select__scroll" ${originalSelect.dataset.flsSelectScroll ? "data-fls-simplebar" : ""}>`;
    selectOptions.forEach((option) => {
      if (option.value) html += this.getOption(option, originalSelect);
    });
    html += "</div>";
    return html;
  }
  getOption(selectOption, originalSelect) {
    const selectOptionSelected =
      selectOption.selected && originalSelect.multiple
        ? ` ${this.selectClasses.classSelectOptionSelected}`
        : "";
    const selectOptionHide =
      selectOption.selected &&
      !originalSelect.hasAttribute("data-fls-select-show-selected") &&
      !originalSelect.multiple
        ? `hidden`
        : ``;
    const selectOptionClass = selectOption.dataset.flsSelectClass
      ? ` ${selectOption.dataset.flsSelectClass}`
      : "";
    const selectOptionLink = selectOption.dataset.flsSelectHref
      ? selectOption.dataset.flsSelectHref
      : false;
    const selectOptionLinkTarget = selectOption.hasAttribute(
      "data-fls-select-href-blank",
    )
      ? `target="_blank"`
      : "";
    let selectOptionHTML = ``;
    selectOptionHTML += selectOptionLink
      ? `<a ${selectOptionLinkTarget} ${selectOptionHide} href="${selectOptionLink}" data-fls-select-value="${selectOption.value}" class="${this.selectClasses.classSelectOption}${selectOptionClass}${selectOptionSelected}">`
      : `<button ${selectOptionHide} class="${this.selectClasses.classSelectOption}${selectOptionClass}${selectOptionSelected}" data-fls-select-value="${selectOption.value}" type="button">`;
    selectOptionHTML += this.getSelectElementContent(selectOption);
    selectOptionHTML += selectOptionLink ? `</a>` : `</button>`;
    return selectOptionHTML;
  }
  searchActions(selectItem) {
    const selectInput = selectItem.querySelector(
      `.${this.selectClasses.classSelectInput}`,
    );
    const selectOptions = this.getSelectElement(
      selectItem,
      this.selectClasses.classSelectOptions,
    ).selectElement;
    if (!selectInput) return;
    selectInput.addEventListener("input", () => {
      const inputValue = selectInput.value.toLowerCase();
      const selectOptionsItems = selectOptions.querySelectorAll(
        `.${this.selectClasses.classSelectOption}`,
      );
      selectOptionsItems.forEach((item) => {
        const itemText = item.textContent.toLowerCase();
        item.hidden = !itemText.includes(inputValue);
      });
    });
  }
  optionAction(selectItem, originalSelect, optionItem) {
    const optionsBox = selectItem.querySelector(
      this.getSelectClass(this.selectClasses.classSelectOptions),
    );
    if (optionsBox.classList.contains("_slide")) return;
    if (originalSelect.multiple) {
      optionItem.classList.toggle(this.selectClasses.classSelectOptionSelected);
      const selectedEls = this.getSelectedOptionsData(originalSelect).elements;
      for (const el of selectedEls) el.removeAttribute("selected");
      const selectedUI = selectItem.querySelectorAll(
        this.getSelectClass(this.selectClasses.classSelectOptionSelected),
      );
      for (const el of selectedUI) {
        const val = el.dataset.flsSelectValue;
        const opt = originalSelect.querySelector(`option[value="${val}"]`);
        if (opt) opt.setAttribute("selected", "selected");
      }
    } else {
      originalSelect.value =
        optionItem.dataset.flsSelectValue || optionItem.textContent;
      this.setSelectTitleValue(selectItem, originalSelect);
      this.setOptions(selectItem, originalSelect);
      this.selectСlose(selectItem);
    }

    if (originalSelect.value && originalSelect.selectedIndex !== 0) {
      selectItem.classList.add(this.selectClasses.classSelectActive);
    } else {
      selectItem.classList.remove(this.selectClasses.classSelectActive);
    }

    this.setSelectChange(originalSelect);
  }
  selectChange(e) {
    const originalSelect = e.target;
    this.selectBuild(originalSelect);
    this.setSelectChange(originalSelect);
  }
  setSelectChange(originalSelect) {
    if (originalSelect.hasAttribute("data-fls-select-validate")) {
      formValidate.validateInput(originalSelect);
    }
    if (
      originalSelect.hasAttribute("data-fls-select-submit") &&
      originalSelect.value
    ) {
      let tempButton = document.createElement("button");
      tempButton.type = "submit";
      originalSelect.closest("form").append(tempButton);
      tempButton.click();
      tempButton.remove();
    }
    const selectItem = originalSelect.parentElement;
    this.selectCallback(selectItem, originalSelect);
  }
  selectDisabled(selectItem, originalSelect) {
    if (originalSelect.disabled) {
      selectItem.classList.add(this.selectClasses.classSelectDisabled);
      this.getSelectElement(
        selectItem,
        this.selectClasses.classSelectTitle,
      ).selectElement.disabled = true;
    } else {
      selectItem.classList.remove(this.selectClasses.classSelectDisabled);
      this.getSelectElement(
        selectItem,
        this.selectClasses.classSelectTitle,
      ).selectElement.disabled = false;
    }
  }
  selectsActions(e) {
    var _a2;
    const t = e.target;
    const type = e.type;
    const isSelect = t.closest(
      this.getSelectClass(this.selectClasses.classSelect),
    );
    const isTag = t.closest(
      this.getSelectClass(this.selectClasses.classSelectTag),
    );
    if (!isSelect && !isTag) return this.selectsСlose();
    const selectItem =
      isSelect ||
      document.querySelector(
        `.${this.selectClasses.classSelect}[data-fls-select-id="${(_a2 = isTag == null ? void 0 : isTag.dataset) == null ? void 0 : _a2.flsSelectId}"]`,
      );
    const originalSelect = this.getSelectElement(selectItem).originalSelect;
    if (originalSelect.disabled) return;
    if (type === "click") {
      const title = t.closest(
        this.getSelectClass(this.selectClasses.classSelectTitle),
      );
      const option = t.closest(
        this.getSelectClass(this.selectClasses.classSelectOption),
      );
      if (title) {
        this.selectAction(selectItem);
      } else if (option) {
        this.optionAction(selectItem, originalSelect, option);
      }
    } else if (type === "focusin" || type === "focusout") {
      if (isSelect)
        selectItem.classList.toggle(
          this.selectClasses.classSelectFocus,
          type === "focusin",
        );
    } else if (type === "keydown" && e.code === "Escape") {
      this.selectsСlose();
    }
  }
  selectsСlose(selectOneGroup) {
    const selectsGroup = selectOneGroup ? selectOneGroup : document;
    const activeItems = selectsGroup.querySelectorAll(
      `${this.getSelectClass(
        this.selectClasses.classSelect,
      )}${this.getSelectClass(this.selectClasses.classSelectOpen)}`,
    );
    if (activeItems.length) {
      activeItems.forEach((selectActiveItem) => {
        this.selectСlose(selectActiveItem);
      });
    }
  }
  selectСlose(selectItem) {
    const originalSelect = this.getSelectElement(selectItem).originalSelect;
    const selectOptions = this.getSelectElement(
      selectItem,
      this.selectClasses.classSelectOptions,
    ).selectElement;
    if (!selectOptions.classList.contains("_slide")) {
      selectItem.classList.remove(this.selectClasses.classSelectOpen);
      slideUp(selectOptions, originalSelect.dataset.flsSelectSpeed);
      setTimeout(() => {
        selectItem.style.zIndex = "";
      }, originalSelect.dataset.flsSelectSpeed);
    }
  }
  selectAction(selectItem) {
    const originalSelect = this.getSelectElement(selectItem).originalSelect;
    const selectOptions = this.getSelectElement(
      selectItem,
      this.selectClasses.classSelectOptions,
    ).selectElement;
    const zIndex = originalSelect.dataset.flsSelectZIndex
      ? originalSelect.dataset.flsSelectZIndex
      : 3;
    this.setOptionsPosition(selectItem);
    const allOpenSelects = document.querySelectorAll(
      `${this.getSelectClass(this.selectClasses.classSelect)}${this.getSelectClass(this.selectClasses.classSelectOpen)}`,
    );
    allOpenSelects.forEach((openSelect) => {
      if (openSelect !== selectItem) {
        this.selectСlose(openSelect);
      }
    });
    setTimeout(() => {
      selectItem.classList.toggle(this.selectClasses.classSelectOpen);
      slideToggle(selectOptions, originalSelect.dataset.flsSelectSpeed);
      if (selectItem.classList.contains(this.selectClasses.classSelectOpen)) {
        selectItem.style.zIndex = zIndex;
      } else {
        setTimeout(() => {
          selectItem.style.zIndex = "";
        }, originalSelect.dataset.flsSelectSpeed);
      }
    }, 0);
  }
  setOptionsPosition(selectItem) {
    const originalSelect = this.getSelectElement(selectItem).originalSelect;
    const selectOptions = this.getSelectElement(
      selectItem,
      this.selectClasses.classSelectOptions,
    ).selectElement;
    const selectItemScroll =
      selectOptions.querySelector(
        `.${this.selectClasses.classSelectOptionsScroll}`,
      ) || selectOptions;
    const margin = +originalSelect.dataset.flsSelectOptionsMargin || 10;
    if (!selectItem.classList.contains(this.selectClasses.classSelectOpen)) {
      selectOptions.hidden = false;
      const selectItemScrollHeight = selectItemScroll.offsetHeight
        ? selectItemScroll.offsetHeight
        : parseInt(
            window
              .getComputedStyle(selectItemScroll)
              .getPropertyValue("max-height"),
          );
      const selectOptionsHeight =
        selectOptions.offsetHeight > selectItemScrollHeight
          ? selectOptions.offsetHeight
          : selectItemScrollHeight + selectOptions.offsetHeight;
      const selectOptionsScrollHeight =
        selectOptionsHeight - selectItemScrollHeight;
      selectOptions.hidden = true;
      const selectItemHeight = selectItem.offsetHeight;
      const selectItemPos = selectItem.getBoundingClientRect().top;
      const total =
        selectItemPos +
        selectOptionsHeight +
        selectItemHeight +
        selectOptionsScrollHeight;
      const result = window.innerHeight - (total + margin);
      if (result < 0) {
        const newMaxHeightValue = selectOptionsHeight + result;
        if (newMaxHeightValue < 100) {
          selectItem.classList.add("select--show-top");
          selectItemScroll.style.maxHeight = `${selectItemPos - margin}px`;
        } else {
          selectItem.classList.remove("select--show-top");
        }
      }
    } else {
      setTimeout(() => {
        selectItem.classList.remove("select--show-top");
      }, +originalSelect.dataset.flsSelectSpeed);
    }
  }
  selectCallback(selectItem, originalSelect) {
    document.dispatchEvent(
      new CustomEvent("selectCallback", {
        detail: { select: originalSelect },
      }),
    );
  }
}
document.querySelector("select[data-fls-select], .fls-select")
  ? window.addEventListener(
      "load",
      () => (window.flsSelect = new SelectConstructor({})),
    )
  : null;
class ScrollWatcher {
  constructor(props) {
    let defaultConfig = {
      logging: true,
    };
    this.config = Object.assign(defaultConfig, props);
    this.observer;
    !document.documentElement.classList.contains("watcher")
      ? this.scrollWatcherRun()
      : null;
  }
  // Оновлюємо конструктор
  scrollWatcherUpdate() {
    this.scrollWatcherRun();
  }
  // Запускаємо конструктор
  scrollWatcherRun() {
    document.documentElement.classList.add("watcher");
    this.scrollWatcherConstructor(
      document.querySelectorAll("[data-fls-watcher]"),
    );
  }
  // Конструктор спостерігачів
  scrollWatcherConstructor(items) {
    if (items.length) {
      this.scrollWatcherLogging(`_FLS_WATCHER_START_WATCH`, items.length);
      let uniqParams = uniqArray(
        Array.from(items).map(function (item) {
          if (
            item.dataset.flsWatcher === "navigator" &&
            !item.dataset.flsWatcherThreshold
          ) {
            let valueOfThreshold;
            if (item.clientHeight > 2) {
              valueOfThreshold =
                window.innerHeight / 2 / (item.clientHeight - 1);
              if (valueOfThreshold > 1) {
                valueOfThreshold = 1;
              }
            } else {
              valueOfThreshold = 1;
            }
            item.setAttribute(
              "data-fls-watcher-threshold",
              valueOfThreshold.toFixed(2),
            );
          }
          return `${item.dataset.flsWatcherRoot ? item.dataset.flsWatcherRoot : null}|${item.dataset.flsWatcherMargin ? item.dataset.flsWatcherMargin : "0px"}|${item.dataset.flsWatcherThreshold ? item.dataset.flsWatcherThreshold : 0}`;
        }),
      );
      uniqParams.forEach((uniqParam) => {
        let uniqParamArray = uniqParam.split("|");
        let paramsWatch = {
          root: uniqParamArray[0],
          margin: uniqParamArray[1],
          threshold: uniqParamArray[2],
        };
        let groupItems = Array.from(items).filter(function (item) {
          let watchRoot = item.dataset.flsWatcherRoot
            ? item.dataset.flsWatcherRoot
            : null;
          let watchMargin = item.dataset.flsWatcherMargin
            ? item.dataset.flsWatcherMargin
            : "0px";
          let watchThreshold = item.dataset.flsWatcherThreshold
            ? item.dataset.flsWatcherThreshold
            : 0;
          if (
            String(watchRoot) === paramsWatch.root &&
            String(watchMargin) === paramsWatch.margin &&
            String(watchThreshold) === paramsWatch.threshold
          ) {
            return item;
          }
        });
        let configWatcher = this.getScrollWatcherConfig(paramsWatch);
        this.scrollWatcherInit(groupItems, configWatcher);
      });
    } else {
      this.scrollWatcherLogging("_FLS_WATCHER_SLEEP");
    }
  }
  // Функція створення налаштувань
  getScrollWatcherConfig(paramsWatch) {
    let configWatcher = {};
    if (document.querySelector(paramsWatch.root)) {
      configWatcher.root = document.querySelector(paramsWatch.root);
    } else if (paramsWatch.root !== "null") {
      this.scrollWatcherLogging(`_FLS_WATCHER_NOPARENT`, paramsWatch.root);
    }
    configWatcher.rootMargin = paramsWatch.margin;
    if (
      paramsWatch.margin.indexOf("px") < 0 &&
      paramsWatch.margin.indexOf("%") < 0
    ) {
      this.scrollWatcherLogging(`_FLS_WATCHER_WARN_MARGIN`);
      return;
    }
    if (paramsWatch.threshold === "prx") {
      paramsWatch.threshold = [];
      for (let i = 0; i <= 1; i += 5e-3) {
        paramsWatch.threshold.push(i);
      }
    } else {
      paramsWatch.threshold = paramsWatch.threshold.split(",");
    }
    configWatcher.threshold = paramsWatch.threshold;
    return configWatcher;
  }
  // Функція створення нового спостерігача зі своїми налаштуваннями
  scrollWatcherCreate(configWatcher) {
    this.observer = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        this.scrollWatcherCallback(entry, observer);
      });
    }, configWatcher);
  }
  // Функція ініціалізації спостерігача зі своїми налаштуваннями
  scrollWatcherInit(items, configWatcher) {
    this.scrollWatcherCreate(configWatcher);
    items.forEach((item) => this.observer.observe(item));
  }
  // Функція обробки базових дій точок спрацьовування
  scrollWatcherIntersecting(entry, targetElement) {
    if (entry.isIntersecting) {
      !targetElement.classList.contains("--watcher-view")
        ? targetElement.classList.add("--watcher-view")
        : null;
      this.scrollWatcherLogging(
        `_FLS_WATCHER_VIEW`,
        targetElement.classList[0],
      );
    } else {
      targetElement.classList.contains("--watcher-view")
        ? targetElement.classList.remove("--watcher-view")
        : null;
      this.scrollWatcherLogging(
        `_FLS_WATCHER_NOVIEW`,
        targetElement.classList[0],
      );
    }
  }
  // Функція відключення стеження за об'єктом
  scrollWatcherOff(targetElement, observer) {
    observer.unobserve(targetElement);
    this.scrollWatcherLogging(
      `_FLS_WATCHER_STOP_WATCH`,
      targetElement.classList[0],
    );
  }
  // Функція виведення в консоль
  scrollWatcherLogging(message, vars) {
    if (this.config.logging);
  }
  // Функція обробки спостереження
  scrollWatcherCallback(entry, observer) {
    const targetElement = entry.target;
    this.scrollWatcherIntersecting(entry, targetElement);
    targetElement.hasAttribute("data-fls-watcher-once") && entry.isIntersecting
      ? this.scrollWatcherOff(targetElement, observer)
      : null;
    document.dispatchEvent(
      new CustomEvent("watcherCallback", {
        detail: {
          entry,
        },
      }),
    );
  }
}
document.querySelector("[data-fls-watcher]")
  ? window.addEventListener("load", () => new ScrollWatcher({}))
  : null;
function spoilers() {
  const spoilersArray = document.querySelectorAll("[data-fls-spoilers]");
  if (spoilersArray.length > 0) {
    let initSpoilers2 = function (spoilersArray2, matchMedia2 = false) {
        spoilersArray2.forEach((spoilersBlock) => {
          spoilersBlock = matchMedia2 ? spoilersBlock.item : spoilersBlock;
          if (matchMedia2.matches || !matchMedia2) {
            spoilersBlock.classList.add("--spoiler-init");
            initSpoilerBody2(spoilersBlock);
          } else {
            spoilersBlock.classList.remove("--spoiler-init");
            initSpoilerBody2(spoilersBlock, false);
          }
        });
      },
      initSpoilerBody2 = function (spoilersBlock, hideSpoilerBody = true) {
        let spoilerItems = spoilersBlock.querySelectorAll("details");
        if (spoilerItems.length) {
          spoilerItems.forEach((spoilerItem) => {
            let spoilerTitle = spoilerItem.querySelector("summary");
            if (hideSpoilerBody) {
              spoilerTitle.removeAttribute("tabindex");
              if (!spoilerItem.hasAttribute("data-fls-spoilers-open")) {
                spoilerItem.open = false;
                spoilerTitle.nextElementSibling.hidden = true;
              } else {
                spoilerTitle.classList.add("--spoiler-active");
                spoilerItem.open = true;
              }
            } else {
              spoilerTitle.setAttribute("tabindex", "-1");
              spoilerTitle.classList.remove("--spoiler-active");
              spoilerItem.open = true;
              spoilerTitle.nextElementSibling.hidden = false;
            }
          });
        }
      },
      setSpoilerAction2 = function (e) {
        const el = e.target;
        if (
          el.closest("summary") &&
          el.closest("[data-fls-spoilers]") &&
          !el.closest("a")
        ) {
          e.preventDefault();
          if (
            el
              .closest("[data-fls-spoilers]")
              .classList.contains("--spoiler-init")
          ) {
            const spoilerTitle = el.closest("summary");
            const spoilerBlock = spoilerTitle.closest("details");
            const spoilersBlock = spoilerTitle.closest("[data-fls-spoilers]");
            const oneSpoiler = spoilersBlock.hasAttribute(
              "data-fls-spoilers-one",
            );
            const scrollSpoiler = spoilerBlock.hasAttribute(
              "data-fls-spoilers-scroll",
            );
            const spoilerSpeed = spoilersBlock.dataset.flsSpoilersSpeed
              ? parseInt(spoilersBlock.dataset.flsSpoilersSpeed)
              : 500;
            if (!spoilersBlock.querySelectorAll(".--slide").length) {
              if (oneSpoiler && !spoilerBlock.open) {
                hideSpoilersBody2(spoilersBlock);
              }
              !spoilerBlock.open
                ? (spoilerBlock.open = true)
                : setTimeout(() => {
                    spoilerBlock.open = false;
                  }, spoilerSpeed);
              spoilerTitle.classList.toggle("--spoiler-active");
              slideToggle(spoilerTitle.nextElementSibling, spoilerSpeed);
              if (
                scrollSpoiler &&
                spoilerTitle.classList.contains("--spoiler-active")
              ) {
                const scrollSpoilerValue =
                  spoilerBlock.dataset.flsSpoilersScroll;
                const scrollSpoilerOffset = +scrollSpoilerValue
                  ? +scrollSpoilerValue
                  : 0;
                const scrollSpoilerNoHeader = spoilerBlock.hasAttribute(
                  "data-fls-spoilers-scroll-noheader",
                )
                  ? document.querySelector(".header").offsetHeight
                  : 0;
                window.scrollTo({
                  top:
                    spoilerBlock.offsetTop -
                    (scrollSpoilerOffset + scrollSpoilerNoHeader),
                  behavior: "smooth",
                });
              }
            }
          }
        }
        if (!el.closest("[data-fls-spoilers]")) {
          const spoilersClose = document.querySelectorAll(
            "[data-fls-spoilers-close]",
          );
          if (spoilersClose.length) {
            spoilersClose.forEach((spoilerClose) => {
              const spoilersBlock = spoilerClose.closest("[data-fls-spoilers]");
              const spoilerCloseBlock = spoilerClose.parentNode;
              if (spoilersBlock.classList.contains("--spoiler-init")) {
                const spoilerSpeed = spoilersBlock.dataset.flsSpoilersSpeed
                  ? parseInt(spoilersBlock.dataset.flsSpoilersSpeed)
                  : 500;
                spoilerClose.classList.remove("--spoiler-active");
                slideUp(spoilerClose.nextElementSibling, spoilerSpeed);
                setTimeout(() => {
                  spoilerCloseBlock.open = false;
                }, spoilerSpeed);
              }
            });
          }
        }
      },
      hideSpoilersBody2 = function (spoilersBlock) {
        const spoilerActiveBlock = spoilersBlock.querySelector("details[open]");
        if (
          spoilerActiveBlock &&
          !spoilersBlock.querySelectorAll(".--slide").length
        ) {
          const spoilerActiveTitle =
            spoilerActiveBlock.querySelector("summary");
          const spoilerSpeed = spoilersBlock.dataset.flsSpoilersSpeed
            ? parseInt(spoilersBlock.dataset.flsSpoilersSpeed)
            : 500;
          spoilerActiveTitle.classList.remove("--spoiler-active");
          slideUp(spoilerActiveTitle.nextElementSibling, spoilerSpeed);
          setTimeout(() => {
            spoilerActiveBlock.open = false;
          }, spoilerSpeed);
        }
      };
    var initSpoilers = initSpoilers2,
      initSpoilerBody = initSpoilerBody2,
      setSpoilerAction = setSpoilerAction2,
      hideSpoilersBody = hideSpoilersBody2;
    document.addEventListener("click", setSpoilerAction2);
    const spoilersRegular = Array.from(spoilersArray).filter(
      function (item, index, self2) {
        return !item.dataset.flsSpoilers.split(",")[0];
      },
    );
    if (spoilersRegular.length) {
      initSpoilers2(spoilersRegular);
    }
    let mdQueriesArray = dataMediaQueries(spoilersArray, "flsSpoilers");
    if (mdQueriesArray && mdQueriesArray.length) {
      mdQueriesArray.forEach((mdQueriesItem) => {
        mdQueriesItem.matchMedia.addEventListener("change", function () {
          initSpoilers2(mdQueriesItem.itemsArray, mdQueriesItem.matchMedia);
        });
        initSpoilers2(mdQueriesItem.itemsArray, mdQueriesItem.matchMedia);
      });
    }
  }
}
window.addEventListener("load", spoilers);
function _defineProperties(target, props) {
  for (var i = 0; i < props.length; i++) {
    var descriptor = props[i];
    descriptor.enumerable = descriptor.enumerable || false;
    descriptor.configurable = true;
    if ("value" in descriptor) descriptor.writable = true;
    Object.defineProperty(target, descriptor.key, descriptor);
  }
}
function _createClass(Constructor, protoProps, staticProps) {
  if (protoProps) _defineProperties(Constructor.prototype, protoProps);
  Object.defineProperty(Constructor, "prototype", { writable: false });
  return Constructor;
}
/*!
 * Splide.js
 * Version  : 4.1.4
 * License  : MIT
 * Copyright: 2022 Naotoshi Fujita
 */
var MEDIA_PREFERS_REDUCED_MOTION = "(prefers-reduced-motion: reduce)";
var CREATED = 1;
var MOUNTED = 2;
var IDLE = 3;
var MOVING = 4;
var SCROLLING = 5;
var DRAGGING = 6;
var DESTROYED = 7;
var STATES = {
  CREATED,
  MOUNTED,
  IDLE,
  MOVING,
  SCROLLING,
  DRAGGING,
  DESTROYED,
};
function empty(array) {
  array.length = 0;
}
function slice(arrayLike, start, end) {
  return Array.prototype.slice.call(arrayLike, start, end);
}
function apply(func) {
  return func.bind.apply(func, [null].concat(slice(arguments, 1)));
}
var nextTick = setTimeout;
var noop = function noop2() {};
function raf(func) {
  return requestAnimationFrame(func);
}
function typeOf(type, subject) {
  return typeof subject === type;
}
function isObject(subject) {
  return !isNull(subject) && typeOf("object", subject);
}
var isArray = Array.isArray;
var isFunction = apply(typeOf, "function");
var isString = apply(typeOf, "string");
var isUndefined = apply(typeOf, "undefined");
function isNull(subject) {
  return subject === null;
}
function isHTMLElement(subject) {
  try {
    return (
      subject instanceof
      (subject.ownerDocument.defaultView || window).HTMLElement
    );
  } catch (e) {
    return false;
  }
}
function toArray(value) {
  return isArray(value) ? value : [value];
}
function forEach(values, iteratee) {
  toArray(values).forEach(iteratee);
}
function includes(array, value) {
  return array.indexOf(value) > -1;
}
function push(array, items) {
  array.push.apply(array, toArray(items));
  return array;
}
function toggleClass(elm, classes, add) {
  if (elm) {
    forEach(classes, function (name) {
      if (name) {
        elm.classList[add ? "add" : "remove"](name);
      }
    });
  }
}
function addClass(elm, classes) {
  toggleClass(elm, isString(classes) ? classes.split(" ") : classes, true);
}
function append(parent, children2) {
  forEach(children2, parent.appendChild.bind(parent));
}
function before(nodes, ref) {
  forEach(nodes, function (node) {
    var parent = (ref || node).parentNode;
    if (parent) {
      parent.insertBefore(node, ref);
    }
  });
}
function matches(elm, selector) {
  return (
    isHTMLElement(elm) &&
    (elm["msMatchesSelector"] || elm.matches).call(elm, selector)
  );
}
function children(parent, selector) {
  var children2 = parent ? slice(parent.children) : [];
  return selector
    ? children2.filter(function (child2) {
        return matches(child2, selector);
      })
    : children2;
}
function child(parent, selector) {
  return selector ? children(parent, selector)[0] : parent.firstElementChild;
}
var ownKeys = Object.keys;
function forOwn(object, iteratee, right) {
  if (object) {
    (right ? ownKeys(object).reverse() : ownKeys(object)).forEach(
      function (key) {
        key !== "__proto__" && iteratee(object[key], key);
      },
    );
  }
  return object;
}
function assign(object) {
  slice(arguments, 1).forEach(function (source) {
    forOwn(source, function (value, key) {
      object[key] = source[key];
    });
  });
  return object;
}
function merge(object) {
  slice(arguments, 1).forEach(function (source) {
    forOwn(source, function (value, key) {
      if (isArray(value)) {
        object[key] = value.slice();
      } else if (isObject(value)) {
        object[key] = merge(
          {},
          isObject(object[key]) ? object[key] : {},
          value,
        );
      } else {
        object[key] = value;
      }
    });
  });
  return object;
}
function omit(object, keys) {
  forEach(keys || ownKeys(object), function (key) {
    delete object[key];
  });
}
function removeAttribute(elms, attrs) {
  forEach(elms, function (elm) {
    forEach(attrs, function (attr) {
      elm && elm.removeAttribute(attr);
    });
  });
}
function setAttribute(elms, attrs, value) {
  if (isObject(attrs)) {
    forOwn(attrs, function (value2, name) {
      setAttribute(elms, name, value2);
    });
  } else {
    forEach(elms, function (elm) {
      isNull(value) || value === ""
        ? removeAttribute(elm, attrs)
        : elm.setAttribute(attrs, String(value));
    });
  }
}
function create(tag, attrs, parent) {
  var elm = document.createElement(tag);
  if (attrs) {
    isString(attrs) ? addClass(elm, attrs) : setAttribute(elm, attrs);
  }
  parent && append(parent, elm);
  return elm;
}
function style(elm, prop, value) {
  if (isUndefined(value)) {
    return getComputedStyle(elm)[prop];
  }
  if (!isNull(value)) {
    elm.style[prop] = "" + value;
  }
}
function display(elm, display2) {
  style(elm, "display", display2);
}
function focus(elm) {
  (elm["setActive"] && elm["setActive"]()) ||
    elm.focus({
      preventScroll: true,
    });
}
function getAttribute(elm, attr) {
  return elm.getAttribute(attr);
}
function hasClass(elm, className) {
  return elm && elm.classList.contains(className);
}
function rect(target) {
  return target.getBoundingClientRect();
}
function remove(nodes) {
  forEach(nodes, function (node) {
    if (node && node.parentNode) {
      node.parentNode.removeChild(node);
    }
  });
}
function parseHtml(html) {
  return child(new DOMParser().parseFromString(html, "text/html").body);
}
function prevent(e, stopPropagation) {
  e.preventDefault();
  if (stopPropagation) {
    e.stopPropagation();
    e.stopImmediatePropagation();
  }
}
function query(parent, selector) {
  return parent && parent.querySelector(selector);
}
function queryAll(parent, selector) {
  return selector ? slice(parent.querySelectorAll(selector)) : [];
}
function removeClass(elm, classes) {
  toggleClass(elm, classes, false);
}
function timeOf(e) {
  return e.timeStamp;
}
function unit(value) {
  return isString(value) ? value : value ? value + "px" : "";
}
var PROJECT_CODE = "splide";
var DATA_ATTRIBUTE = "data-" + PROJECT_CODE;
function assert(condition, message) {
  if (!condition) {
    throw new Error("[" + PROJECT_CODE + "] " + (message || ""));
  }
}
var min = Math.min,
  max = Math.max,
  floor = Math.floor,
  ceil = Math.ceil,
  abs = Math.abs;
function approximatelyEqual(x, y, epsilon) {
  return abs(x - y) < epsilon;
}
function between(number, x, y, exclusive) {
  var minimum = min(x, y);
  var maximum = max(x, y);
  return exclusive
    ? minimum < number && number < maximum
    : minimum <= number && number <= maximum;
}
function clamp(number, x, y) {
  var minimum = min(x, y);
  var maximum = max(x, y);
  return min(max(minimum, number), maximum);
}
function sign(x) {
  return +(x > 0) - +(x < 0);
}
function format(string, replacements) {
  forEach(replacements, function (replacement) {
    string = string.replace("%s", "" + replacement);
  });
  return string;
}
function pad(number) {
  return number < 10 ? "0" + number : "" + number;
}
var ids = {};
function uniqueId(prefix) {
  return "" + prefix + pad((ids[prefix] = (ids[prefix] || 0) + 1));
}
function EventBinder() {
  var listeners = [];
  function bind(targets, events, callback, options) {
    forEachEvent(targets, events, function (target, event, namespace) {
      var isEventTarget = "addEventListener" in target;
      var remover = isEventTarget
        ? target.removeEventListener.bind(target, event, callback, options)
        : target["removeListener"].bind(target, callback);
      isEventTarget
        ? target.addEventListener(event, callback, options)
        : target["addListener"](callback);
      listeners.push([target, event, namespace, callback, remover]);
    });
  }
  function unbind(targets, events, callback) {
    forEachEvent(targets, events, function (target, event, namespace) {
      listeners = listeners.filter(function (listener) {
        if (
          listener[0] === target &&
          listener[1] === event &&
          listener[2] === namespace &&
          (!callback || listener[3] === callback)
        ) {
          listener[4]();
          return false;
        }
        return true;
      });
    });
  }
  function dispatch(target, type, detail) {
    var e;
    var bubbles = true;
    if (typeof CustomEvent === "function") {
      e = new CustomEvent(type, {
        bubbles,
        detail,
      });
    } else {
      e = document.createEvent("CustomEvent");
      e.initCustomEvent(type, bubbles, false, detail);
    }
    target.dispatchEvent(e);
    return e;
  }
  function forEachEvent(targets, events, iteratee) {
    forEach(targets, function (target) {
      target &&
        forEach(events, function (events2) {
          events2.split(" ").forEach(function (eventNS) {
            var fragment = eventNS.split(".");
            iteratee(target, fragment[0], fragment[1]);
          });
        });
    });
  }
  function destroy() {
    listeners.forEach(function (data) {
      data[4]();
    });
    empty(listeners);
  }
  return {
    bind,
    unbind,
    dispatch,
    destroy,
  };
}
var EVENT_MOUNTED = "mounted";
var EVENT_READY = "ready";
var EVENT_MOVE = "move";
var EVENT_MOVED = "moved";
var EVENT_CLICK = "click";
var EVENT_ACTIVE = "active";
var EVENT_INACTIVE = "inactive";
var EVENT_VISIBLE = "visible";
var EVENT_HIDDEN = "hidden";
var EVENT_REFRESH = "refresh";
var EVENT_UPDATED = "updated";
var EVENT_RESIZE = "resize";
var EVENT_RESIZED = "resized";
var EVENT_DRAG = "drag";
var EVENT_DRAGGING = "dragging";
var EVENT_DRAGGED = "dragged";
var EVENT_SCROLL = "scroll";
var EVENT_SCROLLED = "scrolled";
var EVENT_OVERFLOW = "overflow";
var EVENT_DESTROY = "destroy";
var EVENT_ARROWS_MOUNTED = "arrows:mounted";
var EVENT_ARROWS_UPDATED = "arrows:updated";
var EVENT_PAGINATION_MOUNTED = "pagination:mounted";
var EVENT_PAGINATION_UPDATED = "pagination:updated";
var EVENT_NAVIGATION_MOUNTED = "navigation:mounted";
var EVENT_AUTOPLAY_PLAY = "autoplay:play";
var EVENT_AUTOPLAY_PLAYING = "autoplay:playing";
var EVENT_AUTOPLAY_PAUSE = "autoplay:pause";
var EVENT_LAZYLOAD_LOADED = "lazyload:loaded";
var EVENT_SLIDE_KEYDOWN = "sk";
var EVENT_SHIFTED = "sh";
var EVENT_END_INDEX_CHANGED = "ei";
function EventInterface(Splide2) {
  var bus = Splide2 ? Splide2.event.bus : document.createDocumentFragment();
  var binder = EventBinder();
  function on(events, callback) {
    binder.bind(bus, toArray(events).join(" "), function (e) {
      callback.apply(callback, isArray(e.detail) ? e.detail : []);
    });
  }
  function emit(event) {
    binder.dispatch(bus, event, slice(arguments, 1));
  }
  if (Splide2) {
    Splide2.event.on(EVENT_DESTROY, binder.destroy);
  }
  return assign(binder, {
    bus,
    on,
    off: apply(binder.unbind, bus),
    emit,
  });
}
function RequestInterval(interval, onInterval, onUpdate, limit) {
  var now2 = Date.now;
  var startTime;
  var rate = 0;
  var id;
  var paused = true;
  var count = 0;
  function update() {
    if (!paused) {
      rate = interval ? min((now2() - startTime) / interval, 1) : 1;
      onUpdate && onUpdate(rate);
      if (rate >= 1) {
        onInterval();
        startTime = now2();
        if (limit && ++count >= limit) {
          return pause();
        }
      }
      id = raf(update);
    }
  }
  function start(resume) {
    resume || cancel();
    startTime = now2() - (resume ? rate * interval : 0);
    paused = false;
    id = raf(update);
  }
  function pause() {
    paused = true;
  }
  function rewind() {
    startTime = now2();
    rate = 0;
    if (onUpdate) {
      onUpdate(rate);
    }
  }
  function cancel() {
    id && cancelAnimationFrame(id);
    rate = 0;
    id = 0;
    paused = true;
  }
  function set(time) {
    interval = time;
  }
  function isPaused() {
    return paused;
  }
  return {
    start,
    rewind,
    pause,
    cancel,
    set,
    isPaused,
  };
}
function State(initialState) {
  var state = initialState;
  function set(value) {
    state = value;
  }
  function is(states) {
    return includes(toArray(states), state);
  }
  return {
    set,
    is,
  };
}
function Throttle(func, duration) {
  var interval = RequestInterval(0, func, null, 1);
  return function () {
    interval.isPaused() && interval.start();
  };
}
function Media(Splide2, Components2, options) {
  var state = Splide2.state;
  var breakpoints = options.breakpoints || {};
  var reducedMotion = options.reducedMotion || {};
  var binder = EventBinder();
  var queries = [];
  function setup() {
    var isMin = options.mediaQuery === "min";
    ownKeys(breakpoints)
      .sort(function (n, m) {
        return isMin ? +n - +m : +m - +n;
      })
      .forEach(function (key) {
        register(
          breakpoints[key],
          "(" + (isMin ? "min" : "max") + "-width:" + key + "px)",
        );
      });
    register(reducedMotion, MEDIA_PREFERS_REDUCED_MOTION);
    update();
  }
  function destroy(completely) {
    if (completely) {
      binder.destroy();
    }
  }
  function register(options2, query2) {
    var queryList = matchMedia(query2);
    binder.bind(queryList, "change", update);
    queries.push([options2, queryList]);
  }
  function update() {
    var destroyed = state.is(DESTROYED);
    var direction = options.direction;
    var merged = queries.reduce(function (merged2, entry) {
      return merge(merged2, entry[1].matches ? entry[0] : {});
    }, {});
    omit(options);
    set(merged);
    if (options.destroy) {
      Splide2.destroy(options.destroy === "completely");
    } else if (destroyed) {
      destroy(true);
      Splide2.mount();
    } else {
      direction !== options.direction && Splide2.refresh();
    }
  }
  function reduce(enable) {
    if (matchMedia(MEDIA_PREFERS_REDUCED_MOTION).matches) {
      enable
        ? merge(options, reducedMotion)
        : omit(options, ownKeys(reducedMotion));
    }
  }
  function set(opts, base, notify) {
    merge(options, opts);
    base && merge(Object.getPrototypeOf(options), opts);
    if (notify || !state.is(CREATED)) {
      Splide2.emit(EVENT_UPDATED, options);
    }
  }
  return {
    setup,
    destroy,
    reduce,
    set,
  };
}
var ARROW = "Arrow";
var ARROW_LEFT = ARROW + "Left";
var ARROW_RIGHT = ARROW + "Right";
var ARROW_UP = ARROW + "Up";
var ARROW_DOWN = ARROW + "Down";
var RTL = "rtl";
var TTB = "ttb";
var ORIENTATION_MAP = {
  width: ["height"],
  left: ["top", "right"],
  right: ["bottom", "left"],
  x: ["y"],
  X: ["Y"],
  Y: ["X"],
  ArrowLeft: [ARROW_UP, ARROW_RIGHT],
  ArrowRight: [ARROW_DOWN, ARROW_LEFT],
};
function Direction(Splide2, Components2, options) {
  function resolve(prop, axisOnly, direction) {
    direction = direction || options.direction;
    var index = direction === RTL && !axisOnly ? 1 : direction === TTB ? 0 : -1;
    return (
      (ORIENTATION_MAP[prop] && ORIENTATION_MAP[prop][index]) ||
      prop.replace(/width|left|right/i, function (match, offset) {
        var replacement = ORIENTATION_MAP[match.toLowerCase()][index] || match;
        return offset > 0
          ? replacement.charAt(0).toUpperCase() + replacement.slice(1)
          : replacement;
      })
    );
  }
  function orient(value) {
    return value * (options.direction === RTL ? 1 : -1);
  }
  return {
    resolve,
    orient,
  };
}
var ROLE = "role";
var TAB_INDEX = "tabindex";
var DISABLED = "disabled";
var ARIA_PREFIX = "aria-";
var ARIA_CONTROLS = ARIA_PREFIX + "controls";
var ARIA_CURRENT = ARIA_PREFIX + "current";
var ARIA_SELECTED = ARIA_PREFIX + "selected";
var ARIA_LABEL = ARIA_PREFIX + "label";
var ARIA_LABELLEDBY = ARIA_PREFIX + "labelledby";
var ARIA_HIDDEN = ARIA_PREFIX + "hidden";
var ARIA_ORIENTATION = ARIA_PREFIX + "orientation";
var ARIA_ROLEDESCRIPTION = ARIA_PREFIX + "roledescription";
var ARIA_LIVE = ARIA_PREFIX + "live";
var ARIA_BUSY = ARIA_PREFIX + "busy";
var ARIA_ATOMIC = ARIA_PREFIX + "atomic";
var ALL_ATTRIBUTES = [
  ROLE,
  TAB_INDEX,
  DISABLED,
  ARIA_CONTROLS,
  ARIA_CURRENT,
  ARIA_LABEL,
  ARIA_LABELLEDBY,
  ARIA_HIDDEN,
  ARIA_ORIENTATION,
  ARIA_ROLEDESCRIPTION,
];
var CLASS_PREFIX = PROJECT_CODE + "__";
var STATUS_CLASS_PREFIX = "is-";
var CLASS_ROOT = PROJECT_CODE;
var CLASS_TRACK = CLASS_PREFIX + "track";
var CLASS_LIST = CLASS_PREFIX + "list";
var CLASS_SLIDE = CLASS_PREFIX + "slide";
var CLASS_CLONE = CLASS_SLIDE + "--clone";
var CLASS_CONTAINER = CLASS_SLIDE + "__container";
var CLASS_ARROWS = CLASS_PREFIX + "arrows";
var CLASS_ARROW = CLASS_PREFIX + "arrow";
var CLASS_ARROW_PREV = CLASS_ARROW + "--prev";
var CLASS_ARROW_NEXT = CLASS_ARROW + "--next";
var CLASS_PAGINATION = CLASS_PREFIX + "pagination";
var CLASS_PAGINATION_PAGE = CLASS_PAGINATION + "__page";
var CLASS_PROGRESS = CLASS_PREFIX + "progress";
var CLASS_PROGRESS_BAR = CLASS_PROGRESS + "__bar";
var CLASS_TOGGLE = CLASS_PREFIX + "toggle";
var CLASS_SPINNER = CLASS_PREFIX + "spinner";
var CLASS_SR = CLASS_PREFIX + "sr";
var CLASS_INITIALIZED = STATUS_CLASS_PREFIX + "initialized";
var CLASS_ACTIVE = STATUS_CLASS_PREFIX + "active";
var CLASS_PREV = STATUS_CLASS_PREFIX + "prev";
var CLASS_NEXT = STATUS_CLASS_PREFIX + "next";
var CLASS_VISIBLE = STATUS_CLASS_PREFIX + "visible";
var CLASS_LOADING = STATUS_CLASS_PREFIX + "loading";
var CLASS_FOCUS_IN = STATUS_CLASS_PREFIX + "focus-in";
var CLASS_OVERFLOW = STATUS_CLASS_PREFIX + "overflow";
var STATUS_CLASSES = [
  CLASS_ACTIVE,
  CLASS_VISIBLE,
  CLASS_PREV,
  CLASS_NEXT,
  CLASS_LOADING,
  CLASS_FOCUS_IN,
  CLASS_OVERFLOW,
];
var CLASSES = {
  slide: CLASS_SLIDE,
  clone: CLASS_CLONE,
  arrows: CLASS_ARROWS,
  arrow: CLASS_ARROW,
  prev: CLASS_ARROW_PREV,
  next: CLASS_ARROW_NEXT,
  pagination: CLASS_PAGINATION,
  page: CLASS_PAGINATION_PAGE,
  spinner: CLASS_SPINNER,
};
function closest(from, selector) {
  if (isFunction(from.closest)) {
    return from.closest(selector);
  }
  var elm = from;
  while (elm && elm.nodeType === 1) {
    if (matches(elm, selector)) {
      break;
    }
    elm = elm.parentElement;
  }
  return elm;
}
var FRICTION = 5;
var LOG_INTERVAL = 200;
var POINTER_DOWN_EVENTS = "touchstart mousedown";
var POINTER_MOVE_EVENTS = "touchmove mousemove";
var POINTER_UP_EVENTS = "touchend touchcancel mouseup click";
function Elements(Splide2, Components2, options) {
  var _EventInterface = EventInterface(Splide2),
    on = _EventInterface.on,
    bind = _EventInterface.bind;
  var root2 = Splide2.root;
  var i18n = options.i18n;
  var elements = {};
  var slides = [];
  var rootClasses = [];
  var trackClasses = [];
  var track;
  var list;
  var isUsingKey;
  function setup() {
    collect();
    init();
    update();
  }
  function mount() {
    on(EVENT_REFRESH, destroy);
    on(EVENT_REFRESH, setup);
    on(EVENT_UPDATED, update);
    bind(
      document,
      POINTER_DOWN_EVENTS + " keydown",
      function (e) {
        isUsingKey = e.type === "keydown";
      },
      {
        capture: true,
      },
    );
    bind(root2, "focusin", function () {
      toggleClass(root2, CLASS_FOCUS_IN, !!isUsingKey);
    });
  }
  function destroy(completely) {
    var attrs = ALL_ATTRIBUTES.concat("style");
    empty(slides);
    removeClass(root2, rootClasses);
    removeClass(track, trackClasses);
    removeAttribute([track, list], attrs);
    removeAttribute(
      root2,
      completely ? attrs : ["style", ARIA_ROLEDESCRIPTION],
    );
  }
  function update() {
    removeClass(root2, rootClasses);
    removeClass(track, trackClasses);
    rootClasses = getClasses(CLASS_ROOT);
    trackClasses = getClasses(CLASS_TRACK);
    addClass(root2, rootClasses);
    addClass(track, trackClasses);
    setAttribute(root2, ARIA_LABEL, options.label);
    setAttribute(root2, ARIA_LABELLEDBY, options.labelledby);
  }
  function collect() {
    track = find("." + CLASS_TRACK);
    list = child(track, "." + CLASS_LIST);
    assert(track && list, "A track/list element is missing.");
    push(
      slides,
      children(list, "." + CLASS_SLIDE + ":not(." + CLASS_CLONE + ")"),
    );
    forOwn(
      {
        arrows: CLASS_ARROWS,
        pagination: CLASS_PAGINATION,
        prev: CLASS_ARROW_PREV,
        next: CLASS_ARROW_NEXT,
        bar: CLASS_PROGRESS_BAR,
        toggle: CLASS_TOGGLE,
      },
      function (className, key) {
        elements[key] = find("." + className);
      },
    );
    assign(elements, {
      root: root2,
      track,
      list,
      slides,
    });
  }
  function init() {
    var id = root2.id || uniqueId(PROJECT_CODE);
    var role = options.role;
    root2.id = id;
    track.id = track.id || id + "-track";
    list.id = list.id || id + "-list";
    if (!getAttribute(root2, ROLE) && root2.tagName !== "SECTION" && role) {
      setAttribute(root2, ROLE, role);
    }
    setAttribute(root2, ARIA_ROLEDESCRIPTION, i18n.carousel);
    setAttribute(list, ROLE, "presentation");
  }
  function find(selector) {
    var elm = query(root2, selector);
    return elm && closest(elm, "." + CLASS_ROOT) === root2 ? elm : void 0;
  }
  function getClasses(base) {
    return [
      base + "--" + options.type,
      base + "--" + options.direction,
      options.drag && base + "--draggable",
      options.isNavigation && base + "--nav",
      base === CLASS_ROOT && CLASS_ACTIVE,
    ];
  }
  return assign(elements, {
    setup,
    mount,
    destroy,
  });
}
var SLIDE = "slide";
var LOOP = "loop";
var FADE = "fade";
function Slide$1(Splide2, index, slideIndex, slide) {
  var event = EventInterface(Splide2);
  var on = event.on,
    emit = event.emit,
    bind = event.bind;
  var Components = Splide2.Components,
    root2 = Splide2.root,
    options = Splide2.options;
  var isNavigation = options.isNavigation,
    updateOnMove = options.updateOnMove,
    i18n = options.i18n,
    pagination = options.pagination,
    slideFocus = options.slideFocus;
  var resolve = Components.Direction.resolve;
  var styles = getAttribute(slide, "style");
  var label = getAttribute(slide, ARIA_LABEL);
  var isClone = slideIndex > -1;
  var container = child(slide, "." + CLASS_CONTAINER);
  var destroyed;
  function mount() {
    if (!isClone) {
      slide.id = root2.id + "-slide" + pad(index + 1);
      setAttribute(slide, ROLE, pagination ? "tabpanel" : "group");
      setAttribute(slide, ARIA_ROLEDESCRIPTION, i18n.slide);
      setAttribute(
        slide,
        ARIA_LABEL,
        label || format(i18n.slideLabel, [index + 1, Splide2.length]),
      );
    }
    listen();
  }
  function listen() {
    bind(slide, "click", apply(emit, EVENT_CLICK, self2));
    bind(slide, "keydown", apply(emit, EVENT_SLIDE_KEYDOWN, self2));
    on([EVENT_MOVED, EVENT_SHIFTED, EVENT_SCROLLED], update);
    on(EVENT_NAVIGATION_MOUNTED, initNavigation);
    if (updateOnMove) {
      on(EVENT_MOVE, onMove);
    }
  }
  function destroy() {
    destroyed = true;
    event.destroy();
    removeClass(slide, STATUS_CLASSES);
    removeAttribute(slide, ALL_ATTRIBUTES);
    setAttribute(slide, "style", styles);
    setAttribute(slide, ARIA_LABEL, label || "");
  }
  function initNavigation() {
    var controls = Splide2.splides
      .map(function (target) {
        var Slide2 = target.splide.Components.Slides.getAt(index);
        return Slide2 ? Slide2.slide.id : "";
      })
      .join(" ");
    setAttribute(
      slide,
      ARIA_LABEL,
      format(i18n.slideX, (isClone ? slideIndex : index) + 1),
    );
    setAttribute(slide, ARIA_CONTROLS, controls);
    setAttribute(slide, ROLE, slideFocus ? "button" : "");
    slideFocus && removeAttribute(slide, ARIA_ROLEDESCRIPTION);
  }
  function onMove() {
    if (!destroyed) {
      update();
    }
  }
  function update() {
    if (!destroyed) {
      var curr = Splide2.index;
      updateActivity();
      updateVisibility();
      toggleClass(slide, CLASS_PREV, index === curr - 1);
      toggleClass(slide, CLASS_NEXT, index === curr + 1);
    }
  }
  function updateActivity() {
    var active = isActive();
    if (active !== hasClass(slide, CLASS_ACTIVE)) {
      toggleClass(slide, CLASS_ACTIVE, active);
      setAttribute(slide, ARIA_CURRENT, (isNavigation && active) || "");
      emit(active ? EVENT_ACTIVE : EVENT_INACTIVE, self2);
    }
  }
  function updateVisibility() {
    var visible = isVisible();
    var hidden = !visible && (!isActive() || isClone);
    if (!Splide2.state.is([MOVING, SCROLLING])) {
      setAttribute(slide, ARIA_HIDDEN, hidden || "");
    }
    setAttribute(
      queryAll(slide, options.focusableNodes || ""),
      TAB_INDEX,
      hidden ? -1 : "",
    );
    if (slideFocus) {
      setAttribute(slide, TAB_INDEX, hidden ? -1 : 0);
    }
    if (visible !== hasClass(slide, CLASS_VISIBLE)) {
      toggleClass(slide, CLASS_VISIBLE, visible);
      emit(visible ? EVENT_VISIBLE : EVENT_HIDDEN, self2);
    }
    if (!visible && document.activeElement === slide) {
      var Slide2 = Components.Slides.getAt(Splide2.index);
      Slide2 && focus(Slide2.slide);
    }
  }
  function style$1(prop, value, useContainer) {
    style((useContainer && container) || slide, prop, value);
  }
  function isActive() {
    var curr = Splide2.index;
    return curr === index || (options.cloneStatus && curr === slideIndex);
  }
  function isVisible() {
    if (Splide2.is(FADE)) {
      return isActive();
    }
    var trackRect = rect(Components.Elements.track);
    var slideRect = rect(slide);
    var left = resolve("left", true);
    var right = resolve("right", true);
    return (
      floor(trackRect[left]) <= ceil(slideRect[left]) &&
      floor(slideRect[right]) <= ceil(trackRect[right])
    );
  }
  function isWithin(from, distance) {
    var diff = abs(from - index);
    if (!isClone && (options.rewind || Splide2.is(LOOP))) {
      diff = min(diff, Splide2.length - diff);
    }
    return diff <= distance;
  }
  var self2 = {
    index,
    slideIndex,
    slide,
    container,
    isClone,
    mount,
    destroy,
    update,
    style: style$1,
    isWithin,
  };
  return self2;
}
function Slides(Splide2, Components2, options) {
  var _EventInterface2 = EventInterface(Splide2),
    on = _EventInterface2.on,
    emit = _EventInterface2.emit,
    bind = _EventInterface2.bind;
  var _Components2$Elements = Components2.Elements,
    slides = _Components2$Elements.slides,
    list = _Components2$Elements.list;
  var Slides2 = [];
  function mount() {
    init();
    on(EVENT_REFRESH, destroy);
    on(EVENT_REFRESH, init);
  }
  function init() {
    slides.forEach(function (slide, index) {
      register(slide, index, -1);
    });
  }
  function destroy() {
    forEach$1(function (Slide2) {
      Slide2.destroy();
    });
    empty(Slides2);
  }
  function update() {
    forEach$1(function (Slide2) {
      Slide2.update();
    });
  }
  function register(slide, index, slideIndex) {
    var object = Slide$1(Splide2, index, slideIndex, slide);
    object.mount();
    Slides2.push(object);
    Slides2.sort(function (Slide1, Slide2) {
      return Slide1.index - Slide2.index;
    });
  }
  function get(excludeClones) {
    return excludeClones
      ? filter(function (Slide2) {
          return !Slide2.isClone;
        })
      : Slides2;
  }
  function getIn(page) {
    var Controller2 = Components2.Controller;
    var index = Controller2.toIndex(page);
    var max2 = Controller2.hasFocus() ? 1 : options.perPage;
    return filter(function (Slide2) {
      return between(Slide2.index, index, index + max2 - 1);
    });
  }
  function getAt(index) {
    return filter(index)[0];
  }
  function add(items, index) {
    forEach(items, function (slide) {
      if (isString(slide)) {
        slide = parseHtml(slide);
      }
      if (isHTMLElement(slide)) {
        var ref = slides[index];
        ref ? before(slide, ref) : append(list, slide);
        addClass(slide, options.classes.slide);
        observeImages(slide, apply(emit, EVENT_RESIZE));
      }
    });
    emit(EVENT_REFRESH);
  }
  function remove$1(matcher) {
    remove(
      filter(matcher).map(function (Slide2) {
        return Slide2.slide;
      }),
    );
    emit(EVENT_REFRESH);
  }
  function forEach$1(iteratee, excludeClones) {
    get(excludeClones).forEach(iteratee);
  }
  function filter(matcher) {
    return Slides2.filter(
      isFunction(matcher)
        ? matcher
        : function (Slide2) {
            return isString(matcher)
              ? matches(Slide2.slide, matcher)
              : includes(toArray(matcher), Slide2.index);
          },
    );
  }
  function style2(prop, value, useContainer) {
    forEach$1(function (Slide2) {
      Slide2.style(prop, value, useContainer);
    });
  }
  function observeImages(elm, callback) {
    var images = queryAll(elm, "img");
    var length = images.length;
    if (length) {
      images.forEach(function (img) {
        bind(img, "load error", function () {
          if (!--length) {
            callback();
          }
        });
      });
    } else {
      callback();
    }
  }
  function getLength(excludeClones) {
    return excludeClones ? slides.length : Slides2.length;
  }
  function isEnough() {
    return Slides2.length > options.perPage;
  }
  return {
    mount,
    destroy,
    update,
    register,
    get,
    getIn,
    getAt,
    add,
    remove: remove$1,
    forEach: forEach$1,
    filter,
    style: style2,
    getLength,
    isEnough,
  };
}
function Layout(Splide2, Components2, options) {
  var _EventInterface3 = EventInterface(Splide2),
    on = _EventInterface3.on,
    bind = _EventInterface3.bind,
    emit = _EventInterface3.emit;
  var Slides2 = Components2.Slides;
  var resolve = Components2.Direction.resolve;
  var _Components2$Elements2 = Components2.Elements,
    root2 = _Components2$Elements2.root,
    track = _Components2$Elements2.track,
    list = _Components2$Elements2.list;
  var getAt = Slides2.getAt,
    styleSlides = Slides2.style;
  var vertical;
  var rootRect;
  var overflow;
  function mount() {
    init();
    bind(window, "resize load", Throttle(apply(emit, EVENT_RESIZE)));
    on([EVENT_UPDATED, EVENT_REFRESH], init);
    on(EVENT_RESIZE, resize);
  }
  function init() {
    vertical = options.direction === TTB;
    style(root2, "maxWidth", unit(options.width));
    style(track, resolve("paddingLeft"), cssPadding(false));
    style(track, resolve("paddingRight"), cssPadding(true));
    resize(true);
  }
  function resize(force) {
    var newRect = rect(root2);
    if (
      force ||
      rootRect.width !== newRect.width ||
      rootRect.height !== newRect.height
    ) {
      style(track, "height", cssTrackHeight());
      styleSlides(resolve("marginRight"), unit(options.gap));
      styleSlides("width", cssSlideWidth());
      styleSlides("height", cssSlideHeight(), true);
      rootRect = newRect;
      emit(EVENT_RESIZED);
      if (overflow !== (overflow = isOverflow())) {
        toggleClass(root2, CLASS_OVERFLOW, overflow);
        emit(EVENT_OVERFLOW, overflow);
      }
    }
  }
  function cssPadding(right) {
    var padding = options.padding;
    var prop = resolve(right ? "right" : "left");
    return (
      (padding && unit(padding[prop] || (isObject(padding) ? 0 : padding))) ||
      "0px"
    );
  }
  function cssTrackHeight() {
    var height = "";
    if (vertical) {
      height = cssHeight();
      assert(height, "height or heightRatio is missing.");
      height =
        "calc(" +
        height +
        " - " +
        cssPadding(false) +
        " - " +
        cssPadding(true) +
        ")";
    }
    return height;
  }
  function cssHeight() {
    return unit(options.height || rect(list).width * options.heightRatio);
  }
  function cssSlideWidth() {
    return options.autoWidth
      ? null
      : unit(options.fixedWidth) || (vertical ? "" : cssSlideSize());
  }
  function cssSlideHeight() {
    return (
      unit(options.fixedHeight) ||
      (vertical ? (options.autoHeight ? null : cssSlideSize()) : cssHeight())
    );
  }
  function cssSlideSize() {
    var gap = unit(options.gap);
    return (
      "calc((100%" +
      (gap && " + " + gap) +
      ")/" +
      (options.perPage || 1) +
      (gap && " - " + gap) +
      ")"
    );
  }
  function listSize() {
    return rect(list)[resolve("width")];
  }
  function slideSize(index, withoutGap) {
    var Slide2 = getAt(index || 0);
    return Slide2
      ? rect(Slide2.slide)[resolve("width")] + (withoutGap ? 0 : getGap())
      : 0;
  }
  function totalSize(index, withoutGap) {
    var Slide2 = getAt(index);
    if (Slide2) {
      var right = rect(Slide2.slide)[resolve("right")];
      var left = rect(list)[resolve("left")];
      return abs(right - left) + (withoutGap ? 0 : getGap());
    }
    return 0;
  }
  function sliderSize(withoutGap) {
    return (
      totalSize(Splide2.length - 1) - totalSize(0) + slideSize(0, withoutGap)
    );
  }
  function getGap() {
    var Slide2 = getAt(0);
    return (
      (Slide2 && parseFloat(style(Slide2.slide, resolve("marginRight")))) || 0
    );
  }
  function getPadding(right) {
    return (
      parseFloat(
        style(track, resolve("padding" + (right ? "Right" : "Left"))),
      ) || 0
    );
  }
  function isOverflow() {
    return Splide2.is(FADE) || sliderSize(true) > listSize();
  }
  return {
    mount,
    resize,
    listSize,
    slideSize,
    sliderSize,
    totalSize,
    getPadding,
    isOverflow,
  };
}
var MULTIPLIER = 2;
function Clones(Splide2, Components2, options) {
  var event = EventInterface(Splide2);
  var on = event.on;
  var Elements2 = Components2.Elements,
    Slides2 = Components2.Slides;
  var resolve = Components2.Direction.resolve;
  var clones = [];
  var cloneCount;
  function mount() {
    on(EVENT_REFRESH, remount);
    on([EVENT_UPDATED, EVENT_RESIZE], observe);
    if ((cloneCount = computeCloneCount())) {
      generate(cloneCount);
      Components2.Layout.resize(true);
    }
  }
  function remount() {
    destroy();
    mount();
  }
  function destroy() {
    remove(clones);
    empty(clones);
    event.destroy();
  }
  function observe() {
    var count = computeCloneCount();
    if (cloneCount !== count) {
      if (cloneCount < count || !count) {
        event.emit(EVENT_REFRESH);
      }
    }
  }
  function generate(count) {
    var slides = Slides2.get().slice();
    var length = slides.length;
    if (length) {
      while (slides.length < count) {
        push(slides, slides);
      }
      push(slides.slice(-count), slides.slice(0, count)).forEach(
        function (Slide2, index) {
          var isHead = index < count;
          var clone = cloneDeep(Slide2.slide, index);
          isHead
            ? before(clone, slides[0].slide)
            : append(Elements2.list, clone);
          push(clones, clone);
          Slides2.register(
            clone,
            index - count + (isHead ? 0 : length),
            Slide2.index,
          );
        },
      );
    }
  }
  function cloneDeep(elm, index) {
    var clone = elm.cloneNode(true);
    addClass(clone, options.classes.clone);
    clone.id = Splide2.root.id + "-clone" + pad(index + 1);
    return clone;
  }
  function computeCloneCount() {
    var clones2 = options.clones;
    if (!Splide2.is(LOOP)) {
      clones2 = 0;
    } else if (isUndefined(clones2)) {
      var fixedSize =
        options[resolve("fixedWidth")] && Components2.Layout.slideSize(0);
      var fixedCount =
        fixedSize && ceil(rect(Elements2.track)[resolve("width")] / fixedSize);
      clones2 =
        fixedCount ||
        (options[resolve("autoWidth")] && Splide2.length) ||
        options.perPage * MULTIPLIER;
    }
    return clones2;
  }
  return {
    mount,
    destroy,
  };
}
function Move(Splide2, Components2, options) {
  var _EventInterface4 = EventInterface(Splide2),
    on = _EventInterface4.on,
    emit = _EventInterface4.emit;
  var set = Splide2.state.set;
  var _Components2$Layout = Components2.Layout,
    slideSize = _Components2$Layout.slideSize,
    getPadding = _Components2$Layout.getPadding,
    totalSize = _Components2$Layout.totalSize,
    listSize = _Components2$Layout.listSize,
    sliderSize = _Components2$Layout.sliderSize;
  var _Components2$Directio = Components2.Direction,
    resolve = _Components2$Directio.resolve,
    orient = _Components2$Directio.orient;
  var _Components2$Elements3 = Components2.Elements,
    list = _Components2$Elements3.list,
    track = _Components2$Elements3.track;
  var Transition;
  function mount() {
    Transition = Components2.Transition;
    on(
      [EVENT_MOUNTED, EVENT_RESIZED, EVENT_UPDATED, EVENT_REFRESH],
      reposition,
    );
  }
  function reposition() {
    if (!Components2.Controller.isBusy()) {
      Components2.Scroll.cancel();
      jump(Splide2.index);
      Components2.Slides.update();
    }
  }
  function move(dest, index, prev, callback) {
    if (dest !== index && canShift(dest > prev)) {
      cancel();
      translate(shift(getPosition(), dest > prev), true);
    }
    set(MOVING);
    emit(EVENT_MOVE, index, prev, dest);
    Transition.start(index, function () {
      set(IDLE);
      emit(EVENT_MOVED, index, prev, dest);
      callback && callback();
    });
  }
  function jump(index) {
    translate(toPosition(index, true));
  }
  function translate(position, preventLoop) {
    if (!Splide2.is(FADE)) {
      var destination = preventLoop ? position : loop(position);
      style(
        list,
        "transform",
        "translate" + resolve("X") + "(" + destination + "px)",
      );
      position !== destination && emit(EVENT_SHIFTED);
    }
  }
  function loop(position) {
    if (Splide2.is(LOOP)) {
      var index = toIndex(position);
      var exceededMax = index > Components2.Controller.getEnd();
      var exceededMin = index < 0;
      if (exceededMin || exceededMax) {
        position = shift(position, exceededMax);
      }
    }
    return position;
  }
  function shift(position, backwards) {
    var excess = position - getLimit(backwards);
    var size = sliderSize();
    position -=
      orient(size * (ceil(abs(excess) / size) || 1)) * (backwards ? 1 : -1);
    return position;
  }
  function cancel() {
    translate(getPosition(), true);
    Transition.cancel();
  }
  function toIndex(position) {
    var Slides2 = Components2.Slides.get();
    var index = 0;
    var minDistance = Infinity;
    for (var i = 0; i < Slides2.length; i++) {
      var slideIndex = Slides2[i].index;
      var distance = abs(toPosition(slideIndex, true) - position);
      if (distance <= minDistance) {
        minDistance = distance;
        index = slideIndex;
      } else {
        break;
      }
    }
    return index;
  }
  function toPosition(index, trimming) {
    var position = orient(totalSize(index - 1) - offset(index));
    return trimming ? trim(position) : position;
  }
  function getPosition() {
    var left = resolve("left");
    return rect(list)[left] - rect(track)[left] + orient(getPadding(false));
  }
  function trim(position) {
    if (options.trimSpace && Splide2.is(SLIDE)) {
      position = clamp(position, 0, orient(sliderSize(true) - listSize()));
    }
    return position;
  }
  function offset(index) {
    var focus2 = options.focus;
    return focus2 === "center"
      ? (listSize() - slideSize(index, true)) / 2
      : +focus2 * slideSize(index) || 0;
  }
  function getLimit(max2) {
    return toPosition(
      max2 ? Components2.Controller.getEnd() : 0,
      !!options.trimSpace,
    );
  }
  function canShift(backwards) {
    var shifted = orient(shift(getPosition(), backwards));
    return backwards
      ? shifted >= 0
      : shifted <= list[resolve("scrollWidth")] - rect(track)[resolve("width")];
  }
  function exceededLimit(max2, position) {
    position = isUndefined(position) ? getPosition() : position;
    var exceededMin =
      max2 !== true && orient(position) < orient(getLimit(false));
    var exceededMax =
      max2 !== false && orient(position) > orient(getLimit(true));
    return exceededMin || exceededMax;
  }
  return {
    mount,
    move,
    jump,
    translate,
    shift,
    cancel,
    toIndex,
    toPosition,
    getPosition,
    getLimit,
    exceededLimit,
    reposition,
  };
}
function Controller(Splide2, Components2, options) {
  var _EventInterface5 = EventInterface(Splide2),
    on = _EventInterface5.on,
    emit = _EventInterface5.emit;
  var Move2 = Components2.Move;
  var getPosition = Move2.getPosition,
    getLimit = Move2.getLimit,
    toPosition = Move2.toPosition;
  var _Components2$Slides = Components2.Slides,
    isEnough = _Components2$Slides.isEnough,
    getLength = _Components2$Slides.getLength;
  var omitEnd = options.omitEnd;
  var isLoop = Splide2.is(LOOP);
  var isSlide = Splide2.is(SLIDE);
  var getNext = apply(getAdjacent, false);
  var getPrev = apply(getAdjacent, true);
  var currIndex = options.start || 0;
  var endIndex;
  var prevIndex = currIndex;
  var slideCount;
  var perMove;
  var perPage;
  function mount() {
    init();
    on([EVENT_UPDATED, EVENT_REFRESH, EVENT_END_INDEX_CHANGED], init);
    on(EVENT_RESIZED, onResized);
  }
  function init() {
    slideCount = getLength(true);
    perMove = options.perMove;
    perPage = options.perPage;
    endIndex = getEnd();
    var index = clamp(currIndex, 0, omitEnd ? endIndex : slideCount - 1);
    if (index !== currIndex) {
      currIndex = index;
      Move2.reposition();
    }
  }
  function onResized() {
    if (endIndex !== getEnd()) {
      emit(EVENT_END_INDEX_CHANGED);
    }
  }
  function go(control, allowSameIndex, callback) {
    if (!isBusy()) {
      var dest = parse(control);
      var index = loop(dest);
      if (index > -1 && (allowSameIndex || index !== currIndex)) {
        setIndex(index);
        Move2.move(dest, index, prevIndex, callback);
      }
    }
  }
  function scroll(destination, duration, snap, callback) {
    Components2.Scroll.scroll(destination, duration, snap, function () {
      var index = loop(Move2.toIndex(getPosition()));
      setIndex(omitEnd ? min(index, endIndex) : index);
      callback && callback();
    });
  }
  function parse(control) {
    var index = currIndex;
    if (isString(control)) {
      var _ref = control.match(/([+\-<>])(\d+)?/) || [],
        indicator = _ref[1],
        number = _ref[2];
      if (indicator === "+" || indicator === "-") {
        index = computeDestIndex(
          currIndex + +("" + indicator + (+number || 1)),
          currIndex,
        );
      } else if (indicator === ">") {
        index = number ? toIndex(+number) : getNext(true);
      } else if (indicator === "<") {
        index = getPrev(true);
      }
    } else {
      index = isLoop ? control : clamp(control, 0, endIndex);
    }
    return index;
  }
  function getAdjacent(prev, destination) {
    var number = perMove || (hasFocus() ? 1 : perPage);
    var dest = computeDestIndex(
      currIndex + number * (prev ? -1 : 1),
      currIndex,
      !(perMove || hasFocus()),
    );
    if (dest === -1 && isSlide) {
      if (!approximatelyEqual(getPosition(), getLimit(!prev), 1)) {
        return prev ? 0 : endIndex;
      }
    }
    return destination ? dest : loop(dest);
  }
  function computeDestIndex(dest, from, snapPage) {
    if (isEnough() || hasFocus()) {
      var index = computeMovableDestIndex(dest);
      if (index !== dest) {
        from = dest;
        dest = index;
        snapPage = false;
      }
      if (dest < 0 || dest > endIndex) {
        if (
          !perMove &&
          (between(0, dest, from, true) || between(endIndex, from, dest, true))
        ) {
          dest = toIndex(toPage(dest));
        } else {
          if (isLoop) {
            dest = snapPage
              ? dest < 0
                ? -(slideCount % perPage || perPage)
                : slideCount
              : dest;
          } else if (options.rewind) {
            dest = dest < 0 ? endIndex : 0;
          } else {
            dest = -1;
          }
        }
      } else {
        if (snapPage && dest !== from) {
          dest = toIndex(toPage(from) + (dest < from ? -1 : 1));
        }
      }
    } else {
      dest = -1;
    }
    return dest;
  }
  function computeMovableDestIndex(dest) {
    if (isSlide && options.trimSpace === "move" && dest !== currIndex) {
      var position = getPosition();
      while (
        position === toPosition(dest, true) &&
        between(dest, 0, Splide2.length - 1, !options.rewind)
      ) {
        dest < currIndex ? --dest : ++dest;
      }
    }
    return dest;
  }
  function loop(index) {
    return isLoop ? (index + slideCount) % slideCount || 0 : index;
  }
  function getEnd() {
    var end = slideCount - (hasFocus() || (isLoop && perMove) ? 1 : perPage);
    while (omitEnd && end-- > 0) {
      if (toPosition(slideCount - 1, true) !== toPosition(end, true)) {
        end++;
        break;
      }
    }
    return clamp(end, 0, slideCount - 1);
  }
  function toIndex(page) {
    return clamp(hasFocus() ? page : perPage * page, 0, endIndex);
  }
  function toPage(index) {
    return hasFocus()
      ? min(index, endIndex)
      : floor((index >= endIndex ? slideCount - 1 : index) / perPage);
  }
  function toDest(destination) {
    var closest2 = Move2.toIndex(destination);
    return isSlide ? clamp(closest2, 0, endIndex) : closest2;
  }
  function setIndex(index) {
    if (index !== currIndex) {
      prevIndex = currIndex;
      currIndex = index;
    }
  }
  function getIndex(prev) {
    return prev ? prevIndex : currIndex;
  }
  function hasFocus() {
    return !isUndefined(options.focus) || options.isNavigation;
  }
  function isBusy() {
    return Splide2.state.is([MOVING, SCROLLING]) && !!options.waitForTransition;
  }
  return {
    mount,
    go,
    scroll,
    getNext,
    getPrev,
    getAdjacent,
    getEnd,
    setIndex,
    getIndex,
    toIndex,
    toPage,
    toDest,
    hasFocus,
    isBusy,
  };
}
var XML_NAME_SPACE = "http://www.w3.org/2000/svg";
var PATH =
  "m15.5 0.932-4.3 4.38 14.5 14.6-14.5 14.5 4.3 4.4 14.6-14.6 4.4-4.3-4.4-4.4-14.6-14.6z";
var SIZE = 40;
function Arrows(Splide2, Components2, options) {
  var event = EventInterface(Splide2);
  var on = event.on,
    bind = event.bind,
    emit = event.emit;
  var classes = options.classes,
    i18n = options.i18n;
  var Elements2 = Components2.Elements,
    Controller2 = Components2.Controller;
  var placeholder = Elements2.arrows,
    track = Elements2.track;
  var wrapper = placeholder;
  var prev = Elements2.prev;
  var next = Elements2.next;
  var created;
  var wrapperClasses;
  var arrows = {};
  function mount() {
    init();
    on(EVENT_UPDATED, remount);
  }
  function remount() {
    destroy();
    mount();
  }
  function init() {
    var enabled = options.arrows;
    if (enabled && !(prev && next)) {
      createArrows();
    }
    if (prev && next) {
      assign(arrows, {
        prev,
        next,
      });
      display(wrapper, enabled ? "" : "none");
      addClass(
        wrapper,
        (wrapperClasses = CLASS_ARROWS + "--" + options.direction),
      );
      if (enabled) {
        listen();
        update();
        setAttribute([prev, next], ARIA_CONTROLS, track.id);
        emit(EVENT_ARROWS_MOUNTED, prev, next);
      }
    }
  }
  function destroy() {
    event.destroy();
    removeClass(wrapper, wrapperClasses);
    if (created) {
      remove(placeholder ? [prev, next] : wrapper);
      prev = next = null;
    } else {
      removeAttribute([prev, next], ALL_ATTRIBUTES);
    }
  }
  function listen() {
    on(
      [
        EVENT_MOUNTED,
        EVENT_MOVED,
        EVENT_REFRESH,
        EVENT_SCROLLED,
        EVENT_END_INDEX_CHANGED,
      ],
      update,
    );
    bind(next, "click", apply(go, ">"));
    bind(prev, "click", apply(go, "<"));
  }
  function go(control) {
    Controller2.go(control, true);
  }
  function createArrows() {
    wrapper = placeholder || create("div", classes.arrows);
    prev = createArrow(true);
    next = createArrow(false);
    created = true;
    append(wrapper, [prev, next]);
    !placeholder && before(wrapper, track);
  }
  function createArrow(prev2) {
    var arrow =
      '<button class="' +
      classes.arrow +
      " " +
      (prev2 ? classes.prev : classes.next) +
      '" type="button"><svg xmlns="' +
      XML_NAME_SPACE +
      '" viewBox="0 0 ' +
      SIZE +
      " " +
      SIZE +
      '" width="' +
      SIZE +
      '" height="' +
      SIZE +
      '" focusable="false"><path d="' +
      (options.arrowPath || PATH) +
      '" />';
    return parseHtml(arrow);
  }
  function update() {
    if (prev && next) {
      var index = Splide2.index;
      var prevIndex = Controller2.getPrev();
      var nextIndex = Controller2.getNext();
      var prevLabel =
        prevIndex > -1 && index < prevIndex ? i18n.last : i18n.prev;
      var nextLabel =
        nextIndex > -1 && index > nextIndex ? i18n.first : i18n.next;
      prev.disabled = prevIndex < 0;
      next.disabled = nextIndex < 0;
      setAttribute(prev, ARIA_LABEL, prevLabel);
      setAttribute(next, ARIA_LABEL, nextLabel);
      emit(EVENT_ARROWS_UPDATED, prev, next, prevIndex, nextIndex);
    }
  }
  return {
    arrows,
    mount,
    destroy,
    update,
  };
}
var INTERVAL_DATA_ATTRIBUTE = DATA_ATTRIBUTE + "-interval";
function Autoplay(Splide2, Components2, options) {
  var _EventInterface6 = EventInterface(Splide2),
    on = _EventInterface6.on,
    bind = _EventInterface6.bind,
    emit = _EventInterface6.emit;
  var interval = RequestInterval(
    options.interval,
    Splide2.go.bind(Splide2, ">"),
    onAnimationFrame,
  );
  var isPaused = interval.isPaused;
  var Elements2 = Components2.Elements,
    _Components2$Elements4 = Components2.Elements,
    root2 = _Components2$Elements4.root,
    toggle = _Components2$Elements4.toggle;
  var autoplay = options.autoplay;
  var hovered;
  var focused;
  var stopped = autoplay === "pause";
  function mount() {
    if (autoplay) {
      listen();
      toggle && setAttribute(toggle, ARIA_CONTROLS, Elements2.track.id);
      stopped || play();
      update();
    }
  }
  function listen() {
    if (options.pauseOnHover) {
      bind(root2, "mouseenter mouseleave", function (e) {
        hovered = e.type === "mouseenter";
        autoToggle();
      });
    }
    if (options.pauseOnFocus) {
      bind(root2, "focusin focusout", function (e) {
        focused = e.type === "focusin";
        autoToggle();
      });
    }
    if (toggle) {
      bind(toggle, "click", function () {
        stopped ? play() : pause(true);
      });
    }
    on([EVENT_MOVE, EVENT_SCROLL, EVENT_REFRESH], interval.rewind);
    on(EVENT_MOVE, onMove);
  }
  function play() {
    if (isPaused() && Components2.Slides.isEnough()) {
      interval.start(!options.resetProgress);
      focused = hovered = stopped = false;
      update();
      emit(EVENT_AUTOPLAY_PLAY);
    }
  }
  function pause(stop) {
    if (stop === void 0) {
      stop = true;
    }
    stopped = !!stop;
    update();
    if (!isPaused()) {
      interval.pause();
      emit(EVENT_AUTOPLAY_PAUSE);
    }
  }
  function autoToggle() {
    if (!stopped) {
      hovered || focused ? pause(false) : play();
    }
  }
  function update() {
    if (toggle) {
      toggleClass(toggle, CLASS_ACTIVE, !stopped);
      setAttribute(
        toggle,
        ARIA_LABEL,
        options.i18n[stopped ? "play" : "pause"],
      );
    }
  }
  function onAnimationFrame(rate) {
    var bar = Elements2.bar;
    bar && style(bar, "width", rate * 100 + "%");
    emit(EVENT_AUTOPLAY_PLAYING, rate);
  }
  function onMove(index) {
    var Slide2 = Components2.Slides.getAt(index);
    interval.set(
      (Slide2 && +getAttribute(Slide2.slide, INTERVAL_DATA_ATTRIBUTE)) ||
        options.interval,
    );
  }
  return {
    mount,
    destroy: interval.cancel,
    play,
    pause,
    isPaused,
  };
}
function Cover(Splide2, Components2, options) {
  var _EventInterface7 = EventInterface(Splide2),
    on = _EventInterface7.on;
  function mount() {
    if (options.cover) {
      on(EVENT_LAZYLOAD_LOADED, apply(toggle, true));
      on([EVENT_MOUNTED, EVENT_UPDATED, EVENT_REFRESH], apply(cover, true));
    }
  }
  function cover(cover2) {
    Components2.Slides.forEach(function (Slide2) {
      var img = child(Slide2.container || Slide2.slide, "img");
      if (img && img.src) {
        toggle(cover2, img, Slide2);
      }
    });
  }
  function toggle(cover2, img, Slide2) {
    Slide2.style(
      "background",
      cover2 ? 'center/cover no-repeat url("' + img.src + '")' : "",
      true,
    );
    display(img, cover2 ? "none" : "");
  }
  return {
    mount,
    destroy: apply(cover, false),
  };
}
var BOUNCE_DIFF_THRESHOLD = 10;
var BOUNCE_DURATION = 600;
var FRICTION_FACTOR = 0.6;
var BASE_VELOCITY = 1.5;
var MIN_DURATION = 800;
function Scroll(Splide2, Components2, options) {
  var _EventInterface8 = EventInterface(Splide2),
    on = _EventInterface8.on,
    emit = _EventInterface8.emit;
  var set = Splide2.state.set;
  var Move2 = Components2.Move;
  var getPosition = Move2.getPosition,
    getLimit = Move2.getLimit,
    exceededLimit = Move2.exceededLimit,
    translate = Move2.translate;
  var isSlide = Splide2.is(SLIDE);
  var interval;
  var callback;
  var friction = 1;
  function mount() {
    on(EVENT_MOVE, clear);
    on([EVENT_UPDATED, EVENT_REFRESH], cancel);
  }
  function scroll(destination, duration, snap, onScrolled, noConstrain) {
    var from = getPosition();
    clear();
    if (snap && (!isSlide || !exceededLimit())) {
      var size = Components2.Layout.sliderSize();
      var offset =
        sign(destination) * size * floor(abs(destination) / size) || 0;
      destination =
        Move2.toPosition(Components2.Controller.toDest(destination % size)) +
        offset;
    }
    var noDistance = approximatelyEqual(from, destination, 1);
    friction = 1;
    duration = noDistance
      ? 0
      : duration || max(abs(destination - from) / BASE_VELOCITY, MIN_DURATION);
    callback = onScrolled;
    interval = RequestInterval(
      duration,
      onEnd,
      apply(update, from, destination, noConstrain),
      1,
    );
    set(SCROLLING);
    emit(EVENT_SCROLL);
    interval.start();
  }
  function onEnd() {
    set(IDLE);
    callback && callback();
    emit(EVENT_SCROLLED);
  }
  function update(from, to, noConstrain, rate) {
    var position = getPosition();
    var target = from + (to - from) * easing(rate);
    var diff = (target - position) * friction;
    translate(position + diff);
    if (isSlide && !noConstrain && exceededLimit()) {
      friction *= FRICTION_FACTOR;
      if (abs(diff) < BOUNCE_DIFF_THRESHOLD) {
        scroll(
          getLimit(exceededLimit(true)),
          BOUNCE_DURATION,
          false,
          callback,
          true,
        );
      }
    }
  }
  function clear() {
    if (interval) {
      interval.cancel();
    }
  }
  function cancel() {
    if (interval && !interval.isPaused()) {
      clear();
      onEnd();
    }
  }
  function easing(t) {
    var easingFunc = options.easingFunc;
    return easingFunc ? easingFunc(t) : 1 - Math.pow(1 - t, 4);
  }
  return {
    mount,
    destroy: clear,
    scroll,
    cancel,
  };
}
var SCROLL_LISTENER_OPTIONS = {
  passive: false,
  capture: true,
};
function Drag(Splide2, Components2, options) {
  var _EventInterface9 = EventInterface(Splide2),
    on = _EventInterface9.on,
    emit = _EventInterface9.emit,
    bind = _EventInterface9.bind,
    unbind = _EventInterface9.unbind;
  var state = Splide2.state;
  var Move2 = Components2.Move,
    Scroll2 = Components2.Scroll,
    Controller2 = Components2.Controller,
    track = Components2.Elements.track,
    reduce = Components2.Media.reduce;
  var _Components2$Directio2 = Components2.Direction,
    resolve = _Components2$Directio2.resolve,
    orient = _Components2$Directio2.orient;
  var getPosition = Move2.getPosition,
    exceededLimit = Move2.exceededLimit;
  var basePosition;
  var baseEvent;
  var prevBaseEvent;
  var isFree;
  var dragging;
  var exceeded = false;
  var clickPrevented;
  var disabled;
  var target;
  function mount() {
    bind(track, POINTER_MOVE_EVENTS, noop, SCROLL_LISTENER_OPTIONS);
    bind(track, POINTER_UP_EVENTS, noop, SCROLL_LISTENER_OPTIONS);
    bind(track, POINTER_DOWN_EVENTS, onPointerDown, SCROLL_LISTENER_OPTIONS);
    bind(track, "click", onClick, {
      capture: true,
    });
    bind(track, "dragstart", prevent);
    on([EVENT_MOUNTED, EVENT_UPDATED], init);
  }
  function init() {
    var drag = options.drag;
    disable(!drag);
    isFree = drag === "free";
  }
  function onPointerDown(e) {
    clickPrevented = false;
    if (!disabled) {
      var isTouch = isTouchEvent(e);
      if (isDraggable(e.target) && (isTouch || !e.button)) {
        if (!Controller2.isBusy()) {
          target = isTouch ? track : window;
          dragging = state.is([MOVING, SCROLLING]);
          prevBaseEvent = null;
          bind(
            target,
            POINTER_MOVE_EVENTS,
            onPointerMove,
            SCROLL_LISTENER_OPTIONS,
          );
          bind(target, POINTER_UP_EVENTS, onPointerUp, SCROLL_LISTENER_OPTIONS);
          Move2.cancel();
          Scroll2.cancel();
          save(e);
        } else {
          prevent(e, true);
        }
      }
    }
  }
  function onPointerMove(e) {
    if (!state.is(DRAGGING)) {
      state.set(DRAGGING);
      emit(EVENT_DRAG);
    }
    if (e.cancelable) {
      if (dragging) {
        Move2.translate(basePosition + constrain(diffCoord(e)));
        var expired = diffTime(e) > LOG_INTERVAL;
        var hasExceeded = exceeded !== (exceeded = exceededLimit());
        if (expired || hasExceeded) {
          save(e);
        }
        clickPrevented = true;
        emit(EVENT_DRAGGING);
        prevent(e);
      } else if (isSliderDirection(e)) {
        dragging = shouldStart(e);
        prevent(e);
      }
    }
  }
  function onPointerUp(e) {
    if (state.is(DRAGGING)) {
      state.set(IDLE);
      emit(EVENT_DRAGGED);
    }
    if (dragging) {
      move(e);
      prevent(e);
    }
    unbind(target, POINTER_MOVE_EVENTS, onPointerMove);
    unbind(target, POINTER_UP_EVENTS, onPointerUp);
    dragging = false;
  }
  function onClick(e) {
    if (!disabled && clickPrevented) {
      prevent(e, true);
    }
  }
  function save(e) {
    prevBaseEvent = baseEvent;
    baseEvent = e;
    basePosition = getPosition();
  }
  function move(e) {
    var velocity = computeVelocity(e);
    var destination = computeDestination(velocity);
    var rewind = options.rewind && options.rewindByDrag;
    reduce(false);
    if (isFree) {
      Controller2.scroll(destination, 0, options.snap);
    } else if (Splide2.is(FADE)) {
      Controller2.go(
        orient(sign(velocity)) < 0 ? (rewind ? "<" : "-") : rewind ? ">" : "+",
      );
    } else if (Splide2.is(SLIDE) && exceeded && rewind) {
      Controller2.go(exceededLimit(true) ? ">" : "<");
    } else {
      Controller2.go(Controller2.toDest(destination), true);
    }
    reduce(true);
  }
  function shouldStart(e) {
    var thresholds = options.dragMinThreshold;
    var isObj = isObject(thresholds);
    var mouse = (isObj && thresholds.mouse) || 0;
    var touch = (isObj ? thresholds.touch : +thresholds) || 10;
    return abs(diffCoord(e)) > (isTouchEvent(e) ? touch : mouse);
  }
  function isSliderDirection(e) {
    return abs(diffCoord(e)) > abs(diffCoord(e, true));
  }
  function computeVelocity(e) {
    if (Splide2.is(LOOP) || !exceeded) {
      var time = diffTime(e);
      if (time && time < LOG_INTERVAL) {
        return diffCoord(e) / time;
      }
    }
    return 0;
  }
  function computeDestination(velocity) {
    return (
      getPosition() +
      sign(velocity) *
        min(
          abs(velocity) * (options.flickPower || 600),
          isFree
            ? Infinity
            : Components2.Layout.listSize() * (options.flickMaxPages || 1),
        )
    );
  }
  function diffCoord(e, orthogonal) {
    return coordOf(e, orthogonal) - coordOf(getBaseEvent(e), orthogonal);
  }
  function diffTime(e) {
    return timeOf(e) - timeOf(getBaseEvent(e));
  }
  function getBaseEvent(e) {
    return (baseEvent === e && prevBaseEvent) || baseEvent;
  }
  function coordOf(e, orthogonal) {
    return (isTouchEvent(e) ? e.changedTouches[0] : e)[
      "page" + resolve(orthogonal ? "Y" : "X")
    ];
  }
  function constrain(diff) {
    return diff / (exceeded && Splide2.is(SLIDE) ? FRICTION : 1);
  }
  function isDraggable(target2) {
    var noDrag = options.noDrag;
    return (
      !matches(target2, "." + CLASS_PAGINATION_PAGE + ", ." + CLASS_ARROW) &&
      (!noDrag || !matches(target2, noDrag))
    );
  }
  function isTouchEvent(e) {
    return typeof TouchEvent !== "undefined" && e instanceof TouchEvent;
  }
  function isDragging() {
    return dragging;
  }
  function disable(value) {
    disabled = value;
  }
  return {
    mount,
    disable,
    isDragging,
  };
}
var NORMALIZATION_MAP = {
  Spacebar: " ",
  Right: ARROW_RIGHT,
  Left: ARROW_LEFT,
  Up: ARROW_UP,
  Down: ARROW_DOWN,
};
function normalizeKey(key) {
  key = isString(key) ? key : key.key;
  return NORMALIZATION_MAP[key] || key;
}
var KEYBOARD_EVENT = "keydown";
function Keyboard(Splide2, Components2, options) {
  var _EventInterface10 = EventInterface(Splide2),
    on = _EventInterface10.on,
    bind = _EventInterface10.bind,
    unbind = _EventInterface10.unbind;
  var root2 = Splide2.root;
  var resolve = Components2.Direction.resolve;
  var target;
  var disabled;
  function mount() {
    init();
    on(EVENT_UPDATED, destroy);
    on(EVENT_UPDATED, init);
    on(EVENT_MOVE, onMove);
  }
  function init() {
    var keyboard = options.keyboard;
    if (keyboard) {
      target = keyboard === "global" ? window : root2;
      bind(target, KEYBOARD_EVENT, onKeydown);
    }
  }
  function destroy() {
    unbind(target, KEYBOARD_EVENT);
  }
  function disable(value) {
    disabled = value;
  }
  function onMove() {
    var _disabled = disabled;
    disabled = true;
    nextTick(function () {
      disabled = _disabled;
    });
  }
  function onKeydown(e) {
    if (!disabled) {
      var key = normalizeKey(e);
      if (key === resolve(ARROW_LEFT)) {
        Splide2.go("<");
      } else if (key === resolve(ARROW_RIGHT)) {
        Splide2.go(">");
      }
    }
  }
  return {
    mount,
    destroy,
    disable,
  };
}
var SRC_DATA_ATTRIBUTE = DATA_ATTRIBUTE + "-lazy";
var SRCSET_DATA_ATTRIBUTE = SRC_DATA_ATTRIBUTE + "-srcset";
var IMAGE_SELECTOR =
  "[" + SRC_DATA_ATTRIBUTE + "], [" + SRCSET_DATA_ATTRIBUTE + "]";
function LazyLoad(Splide2, Components2, options) {
  var _EventInterface11 = EventInterface(Splide2),
    on = _EventInterface11.on,
    off = _EventInterface11.off,
    bind = _EventInterface11.bind,
    emit = _EventInterface11.emit;
  var isSequential = options.lazyLoad === "sequential";
  var events = [EVENT_MOVED, EVENT_SCROLLED];
  var entries = [];
  function mount() {
    if (options.lazyLoad) {
      init();
      on(EVENT_REFRESH, init);
    }
  }
  function init() {
    empty(entries);
    register();
    if (isSequential) {
      loadNext();
    } else {
      off(events);
      on(events, check);
      check();
    }
  }
  function register() {
    Components2.Slides.forEach(function (Slide2) {
      queryAll(Slide2.slide, IMAGE_SELECTOR).forEach(function (img) {
        var src = getAttribute(img, SRC_DATA_ATTRIBUTE);
        var srcset = getAttribute(img, SRCSET_DATA_ATTRIBUTE);
        if (src !== img.src || srcset !== img.srcset) {
          var className = options.classes.spinner;
          var parent = img.parentElement;
          var spinner =
            child(parent, "." + className) || create("span", className, parent);
          entries.push([img, Slide2, spinner]);
          img.src || display(img, "none");
        }
      });
    });
  }
  function check() {
    entries = entries.filter(function (data) {
      var distance = options.perPage * ((options.preloadPages || 1) + 1) - 1;
      return data[1].isWithin(Splide2.index, distance) ? load(data) : true;
    });
    entries.length || off(events);
  }
  function load(data) {
    var img = data[0];
    addClass(data[1].slide, CLASS_LOADING);
    bind(img, "load error", apply(onLoad, data));
    setAttribute(img, "src", getAttribute(img, SRC_DATA_ATTRIBUTE));
    setAttribute(img, "srcset", getAttribute(img, SRCSET_DATA_ATTRIBUTE));
    removeAttribute(img, SRC_DATA_ATTRIBUTE);
    removeAttribute(img, SRCSET_DATA_ATTRIBUTE);
  }
  function onLoad(data, e) {
    var img = data[0],
      Slide2 = data[1];
    removeClass(Slide2.slide, CLASS_LOADING);
    if (e.type !== "error") {
      remove(data[2]);
      display(img, "");
      emit(EVENT_LAZYLOAD_LOADED, img, Slide2);
      emit(EVENT_RESIZE);
    }
    isSequential && loadNext();
  }
  function loadNext() {
    entries.length && load(entries.shift());
  }
  return {
    mount,
    destroy: apply(empty, entries),
    check,
  };
}
function Pagination(Splide2, Components2, options) {
  var event = EventInterface(Splide2);
  var on = event.on,
    emit = event.emit,
    bind = event.bind;
  var Slides2 = Components2.Slides,
    Elements2 = Components2.Elements,
    Controller2 = Components2.Controller;
  var hasFocus = Controller2.hasFocus,
    getIndex = Controller2.getIndex,
    go = Controller2.go;
  var resolve = Components2.Direction.resolve;
  var placeholder = Elements2.pagination;
  var items = [];
  var list;
  var paginationClasses;
  function mount() {
    destroy();
    on([EVENT_UPDATED, EVENT_REFRESH, EVENT_END_INDEX_CHANGED], mount);
    var enabled = options.pagination;
    placeholder && display(placeholder, enabled ? "" : "none");
    if (enabled) {
      on([EVENT_MOVE, EVENT_SCROLL, EVENT_SCROLLED], update);
      createPagination();
      update();
      emit(
        EVENT_PAGINATION_MOUNTED,
        {
          list,
          items,
        },
        getAt(Splide2.index),
      );
    }
  }
  function destroy() {
    if (list) {
      remove(placeholder ? slice(list.children) : list);
      removeClass(list, paginationClasses);
      empty(items);
      list = null;
    }
    event.destroy();
  }
  function createPagination() {
    var length = Splide2.length;
    var classes = options.classes,
      i18n = options.i18n,
      perPage = options.perPage;
    var max2 = hasFocus() ? Controller2.getEnd() + 1 : ceil(length / perPage);
    list =
      placeholder ||
      create("ul", classes.pagination, Elements2.track.parentElement);
    addClass(
      list,
      (paginationClasses = CLASS_PAGINATION + "--" + getDirection()),
    );
    setAttribute(list, ROLE, "tablist");
    setAttribute(list, ARIA_LABEL, i18n.select);
    setAttribute(
      list,
      ARIA_ORIENTATION,
      getDirection() === TTB ? "vertical" : "",
    );
    for (var i = 0; i < max2; i++) {
      var li = create("li", null, list);
      var button = create(
        "button",
        {
          class: classes.page,
          type: "button",
        },
        li,
      );
      var controls = Slides2.getIn(i).map(function (Slide2) {
        return Slide2.slide.id;
      });
      var text = !hasFocus() && perPage > 1 ? i18n.pageX : i18n.slideX;
      bind(button, "click", apply(onClick, i));
      if (options.paginationKeyboard) {
        bind(button, "keydown", apply(onKeydown, i));
      }
      setAttribute(li, ROLE, "presentation");
      setAttribute(button, ROLE, "tab");
      setAttribute(button, ARIA_CONTROLS, controls.join(" "));
      setAttribute(button, ARIA_LABEL, format(text, i + 1));
      setAttribute(button, TAB_INDEX, -1);
      items.push({
        li,
        button,
        page: i,
      });
    }
  }
  function onClick(page) {
    go(">" + page, true);
  }
  function onKeydown(page, e) {
    var length = items.length;
    var key = normalizeKey(e);
    var dir = getDirection();
    var nextPage = -1;
    if (key === resolve(ARROW_RIGHT, false, dir)) {
      nextPage = ++page % length;
    } else if (key === resolve(ARROW_LEFT, false, dir)) {
      nextPage = (--page + length) % length;
    } else if (key === "Home") {
      nextPage = 0;
    } else if (key === "End") {
      nextPage = length - 1;
    }
    var item = items[nextPage];
    if (item) {
      focus(item.button);
      go(">" + nextPage);
      prevent(e, true);
    }
  }
  function getDirection() {
    return options.paginationDirection || options.direction;
  }
  function getAt(index) {
    return items[Controller2.toPage(index)];
  }
  function update() {
    var prev = getAt(getIndex(true));
    var curr = getAt(getIndex());
    if (prev) {
      var button = prev.button;
      removeClass(button, CLASS_ACTIVE);
      removeAttribute(button, ARIA_SELECTED);
      setAttribute(button, TAB_INDEX, -1);
    }
    if (curr) {
      var _button = curr.button;
      addClass(_button, CLASS_ACTIVE);
      setAttribute(_button, ARIA_SELECTED, true);
      setAttribute(_button, TAB_INDEX, "");
    }
    emit(
      EVENT_PAGINATION_UPDATED,
      {
        list,
        items,
      },
      prev,
      curr,
    );
  }
  return {
    items,
    mount,
    destroy,
    getAt,
    update,
  };
}
var TRIGGER_KEYS = [" ", "Enter"];
function Sync(Splide2, Components2, options) {
  var isNavigation = options.isNavigation,
    slideFocus = options.slideFocus;
  var events = [];
  function mount() {
    Splide2.splides.forEach(function (target) {
      if (!target.isParent) {
        sync(Splide2, target.splide);
        sync(target.splide, Splide2);
      }
    });
    if (isNavigation) {
      navigate();
    }
  }
  function destroy() {
    events.forEach(function (event) {
      event.destroy();
    });
    empty(events);
  }
  function remount() {
    destroy();
    mount();
  }
  function sync(splide, target) {
    var event = EventInterface(splide);
    event.on(EVENT_MOVE, function (index, prev, dest) {
      target.go(target.is(LOOP) ? dest : index);
    });
    events.push(event);
  }
  function navigate() {
    var event = EventInterface(Splide2);
    var on = event.on;
    on(EVENT_CLICK, onClick);
    on(EVENT_SLIDE_KEYDOWN, onKeydown);
    on([EVENT_MOUNTED, EVENT_UPDATED], update);
    events.push(event);
    event.emit(EVENT_NAVIGATION_MOUNTED, Splide2.splides);
  }
  function update() {
    setAttribute(
      Components2.Elements.list,
      ARIA_ORIENTATION,
      options.direction === TTB ? "vertical" : "",
    );
  }
  function onClick(Slide2) {
    Splide2.go(Slide2.index);
  }
  function onKeydown(Slide2, e) {
    if (includes(TRIGGER_KEYS, normalizeKey(e))) {
      onClick(Slide2);
      prevent(e);
    }
  }
  return {
    setup: apply(
      Components2.Media.set,
      {
        slideFocus: isUndefined(slideFocus) ? isNavigation : slideFocus,
      },
      true,
    ),
    mount,
    destroy,
    remount,
  };
}
function Wheel(Splide2, Components2, options) {
  var _EventInterface12 = EventInterface(Splide2),
    bind = _EventInterface12.bind;
  var lastTime = 0;
  function mount() {
    if (options.wheel) {
      bind(
        Components2.Elements.track,
        "wheel",
        onWheel,
        SCROLL_LISTENER_OPTIONS,
      );
    }
  }
  function onWheel(e) {
    if (e.cancelable) {
      var deltaY = e.deltaY;
      var backwards = deltaY < 0;
      var timeStamp = timeOf(e);
      var _min = options.wheelMinThreshold || 0;
      var sleep = options.wheelSleep || 0;
      if (abs(deltaY) > _min && timeStamp - lastTime > sleep) {
        Splide2.go(backwards ? "<" : ">");
        lastTime = timeStamp;
      }
      shouldPrevent(backwards) && prevent(e);
    }
  }
  function shouldPrevent(backwards) {
    return (
      !options.releaseWheel ||
      Splide2.state.is(MOVING) ||
      Components2.Controller.getAdjacent(backwards) !== -1
    );
  }
  return {
    mount,
  };
}
var SR_REMOVAL_DELAY = 90;
function Live(Splide2, Components2, options) {
  var _EventInterface13 = EventInterface(Splide2),
    on = _EventInterface13.on;
  var track = Components2.Elements.track;
  var enabled = options.live && !options.isNavigation;
  var sr = create("span", CLASS_SR);
  var interval = RequestInterval(SR_REMOVAL_DELAY, apply(toggle, false));
  function mount() {
    if (enabled) {
      disable(!Components2.Autoplay.isPaused());
      setAttribute(track, ARIA_ATOMIC, true);
      sr.textContent = "…";
      on(EVENT_AUTOPLAY_PLAY, apply(disable, true));
      on(EVENT_AUTOPLAY_PAUSE, apply(disable, false));
      on([EVENT_MOVED, EVENT_SCROLLED], apply(toggle, true));
    }
  }
  function toggle(active) {
    setAttribute(track, ARIA_BUSY, active);
    if (active) {
      append(track, sr);
      interval.start();
    } else {
      remove(sr);
      interval.cancel();
    }
  }
  function destroy() {
    removeAttribute(track, [ARIA_LIVE, ARIA_ATOMIC, ARIA_BUSY]);
    remove(sr);
  }
  function disable(disabled) {
    if (enabled) {
      setAttribute(track, ARIA_LIVE, disabled ? "off" : "polite");
    }
  }
  return {
    mount,
    disable,
    destroy,
  };
}
var ComponentConstructors = /* @__PURE__ */ Object.freeze({
  __proto__: null,
  Media,
  Direction,
  Elements,
  Slides,
  Layout,
  Clones,
  Move,
  Controller,
  Arrows,
  Autoplay,
  Cover,
  Scroll,
  Drag,
  Keyboard,
  LazyLoad,
  Pagination,
  Sync,
  Wheel,
  Live,
});
var I18N = {
  prev: "Previous slide",
  next: "Next slide",
  first: "Go to first slide",
  last: "Go to last slide",
  slideX: "Go to slide %s",
  pageX: "Go to page %s",
  play: "Start autoplay",
  pause: "Pause autoplay",
  carousel: "carousel",
  slide: "slide",
  select: "Select a slide to show",
  slideLabel: "%s of %s",
};
var DEFAULTS = {
  type: "slide",
  role: "region",
  speed: 400,
  perPage: 1,
  cloneStatus: true,
  arrows: true,
  pagination: true,
  paginationKeyboard: true,
  interval: 5e3,
  pauseOnHover: true,
  pauseOnFocus: true,
  resetProgress: true,
  easing: "cubic-bezier(0.25, 1, 0.5, 1)",
  drag: true,
  direction: "ltr",
  trimSpace: true,
  focusableNodes: "a, button, textarea, input, select, iframe",
  live: true,
  classes: CLASSES,
  i18n: I18N,
  reducedMotion: {
    speed: 0,
    rewindSpeed: 0,
    autoplay: "pause",
  },
};
function Fade(Splide2, Components2, options) {
  var Slides2 = Components2.Slides;
  function mount() {
    EventInterface(Splide2).on([EVENT_MOUNTED, EVENT_REFRESH], init);
  }
  function init() {
    Slides2.forEach(function (Slide2) {
      Slide2.style("transform", "translateX(-" + 100 * Slide2.index + "%)");
    });
  }
  function start(index, done) {
    Slides2.style(
      "transition",
      "opacity " + options.speed + "ms " + options.easing,
    );
    nextTick(done);
  }
  return {
    mount,
    start,
    cancel: noop,
  };
}
function Slide(Splide2, Components2, options) {
  var Move2 = Components2.Move,
    Controller2 = Components2.Controller,
    Scroll2 = Components2.Scroll;
  var list = Components2.Elements.list;
  var transition = apply(style, list, "transition");
  var endCallback;
  function mount() {
    EventInterface(Splide2).bind(list, "transitionend", function (e) {
      if (e.target === list && endCallback) {
        cancel();
        endCallback();
      }
    });
  }
  function start(index, done) {
    var destination = Move2.toPosition(index, true);
    var position = Move2.getPosition();
    var speed = getSpeed(index);
    if (abs(destination - position) >= 1 && speed >= 1) {
      if (options.useScroll) {
        Scroll2.scroll(destination, speed, false, done);
      } else {
        transition("transform " + speed + "ms " + options.easing);
        Move2.translate(destination, true);
        endCallback = done;
      }
    } else {
      Move2.jump(index);
      done();
    }
  }
  function cancel() {
    transition("");
    Scroll2.cancel();
  }
  function getSpeed(index) {
    var rewindSpeed = options.rewindSpeed;
    if (Splide2.is(SLIDE) && rewindSpeed) {
      var prev = Controller2.getIndex(true);
      var end = Controller2.getEnd();
      if ((prev === 0 && index >= end) || (prev >= end && index === 0)) {
        return rewindSpeed;
      }
    }
    return options.speed;
  }
  return {
    mount,
    start,
    cancel,
  };
}
var _Splide = /* @__PURE__ */ (function () {
  function _Splide2(target, options) {
    this.event = EventInterface();
    this.Components = {};
    this.state = State(CREATED);
    this.splides = [];
    this._o = {};
    this._E = {};
    var root2 = isString(target) ? query(document, target) : target;
    assert(root2, root2 + " is invalid.");
    this.root = root2;
    options = merge(
      {
        label: getAttribute(root2, ARIA_LABEL) || "",
        labelledby: getAttribute(root2, ARIA_LABELLEDBY) || "",
      },
      DEFAULTS,
      _Splide2.defaults,
      options || {},
    );
    try {
      merge(options, JSON.parse(getAttribute(root2, DATA_ATTRIBUTE)));
    } catch (e) {
      assert(false, "Invalid JSON");
    }
    this._o = Object.create(merge({}, options));
  }
  var _proto = _Splide2.prototype;
  _proto.mount = function mount(Extensions, Transition) {
    var _this = this;
    var state = this.state,
      Components2 = this.Components;
    assert(state.is([CREATED, DESTROYED]), "Already mounted!");
    state.set(CREATED);
    this._C = Components2;
    this._T = Transition || this._T || (this.is(FADE) ? Fade : Slide);
    this._E = Extensions || this._E;
    var Constructors = assign({}, ComponentConstructors, this._E, {
      Transition: this._T,
    });
    forOwn(Constructors, function (Component, key) {
      var component = Component(_this, Components2, _this._o);
      Components2[key] = component;
      component.setup && component.setup();
    });
    forOwn(Components2, function (component) {
      component.mount && component.mount();
    });
    this.emit(EVENT_MOUNTED);
    addClass(this.root, CLASS_INITIALIZED);
    state.set(IDLE);
    this.emit(EVENT_READY);
    return this;
  };
  _proto.sync = function sync(splide) {
    this.splides.push({
      splide,
    });
    splide.splides.push({
      splide: this,
      isParent: true,
    });
    if (this.state.is(IDLE)) {
      this._C.Sync.remount();
      splide.Components.Sync.remount();
    }
    return this;
  };
  _proto.go = function go(control) {
    this._C.Controller.go(control);
    return this;
  };
  _proto.on = function on(events, callback) {
    this.event.on(events, callback);
    return this;
  };
  _proto.off = function off(events) {
    this.event.off(events);
    return this;
  };
  _proto.emit = function emit(event) {
    var _this$event;
    (_this$event = this.event).emit.apply(
      _this$event,
      [event].concat(slice(arguments, 1)),
    );
    return this;
  };
  _proto.add = function add(slides, index) {
    this._C.Slides.add(slides, index);
    return this;
  };
  _proto.remove = function remove2(matcher) {
    this._C.Slides.remove(matcher);
    return this;
  };
  _proto.is = function is(type) {
    return this._o.type === type;
  };
  _proto.refresh = function refresh() {
    this.emit(EVENT_REFRESH);
    return this;
  };
  _proto.destroy = function destroy(completely) {
    if (completely === void 0) {
      completely = true;
    }
    var event = this.event,
      state = this.state;
    if (state.is(CREATED)) {
      EventInterface(this).on(EVENT_READY, this.destroy.bind(this, completely));
    } else {
      forOwn(
        this._C,
        function (component) {
          component.destroy && component.destroy(completely);
        },
        true,
      );
      event.emit(EVENT_DESTROY);
      event.destroy();
      completely && empty(this.splides);
      state.set(DESTROYED);
    }
    return this;
  };
  _createClass(_Splide2, [
    {
      key: "options",
      get: function get() {
        return this._o;
      },
      set: function set(options) {
        this._C.Media.set(options, true, true);
      },
    },
    {
      key: "length",
      get: function get() {
        return this._C.Slides.getLength(true);
      },
    },
    {
      key: "index",
      get: function get() {
        return this._C.Controller.getIndex();
      },
    },
  ]);
  return _Splide2;
})();
var Splide = _Splide;
Splide.defaults = {};
Splide.STATES = STATES;
document.addEventListener("DOMContentLoaded", function () {
  var sectionSliderEls = document.querySelectorAll(".slider-section__slider");
  if (sectionSliderEls.length > 0) {
    sectionSliderEls.forEach(function (sliderEl) {
      var sectionSlider = new Splide(sliderEl, {
        type: "loop",
        arrows: false,
        pagination: false,
        gap: 26,
        autoWidth: true,
        breakpoints: {
          767.98: {
            gap: 12,
          },
          389.98: {
            autoWidth: false,
            perPage: 1,
            gap: 0,
          },
        },
      });
      sliderEl.splide = sectionSlider;
      var wrapper = sliderEl.closest("[data-sl-wrapper]");
      if (wrapper) {
        var prevBtn = wrapper.querySelector("[data-sl-arrow-prev]");
        var nextBtn = wrapper.querySelector("[data-sl-arrow-next]");
        if (prevBtn) {
          prevBtn.addEventListener("click", function () {
            sectionSlider.go("<");
          });
        }
        if (nextBtn) {
          nextBtn.addEventListener("click", function () {
            sectionSlider.go(">");
          });
        }
      }
      sectionSlider.mount();
    });
  }
  var benefitsSliderEls = document.querySelectorAll(".benefits-pr__slider");
  if (benefitsSliderEls.length > 0) {
    benefitsSliderEls.forEach(function (sliderEl) {
      var benefitsSlider = new Splide(sliderEl, {
        arrows: false,
        pagination: false,
        gap: 20,
        updateOnMove: true,
        destroy: true,
        autoWidth: true,
        breakpoints: {
          991.98: {
            destroy: false,
          },
        },
      });
      sliderEl.splide = benefitsSlider;
      benefitsSlider.mount();
    });
  }
  var heroSliderEls = document.querySelectorAll(".hero-premia__slider");
  if (heroSliderEls.length > 0) {
    heroSliderEls.forEach(function (sliderEl) {
      var heroSlider = new Splide(sliderEl, {
        type: "fade",
        perPage: 1,
        arrows: false,
        pagination: true,
        autoplay: true,
        pauseOnHover: true,
        interval: 10000,
        rewind: true,
        gap: 0,
        updateOnMove: true,
        breakpoints: {
          767.98: {
            pagination: false,
          },
        },
      });
      sliderEl.splide = heroSlider;
      heroSlider.mount();
    });
  }
});
class Popup {
  constructor(options) {
    let config = {
      logging: true,
      init: true,
      attributeOpenButton: "data-fls-popup-link",
      attributeCloseButton: "data-fls-popup-close",
      fixElementSelector: "[data-fls-lp]",
      attributeMain: "data-fls-popup",
      youtubeAttribute: "data-fls-popup-youtube",
      youtubePlaceAttribute: "data-fls-popup-youtube-place",
      setAutoplayYoutube: true,
      classes: {
        popup: "popup",
        popupContent: "data-fls-popup-body",
        popupActive: "data-fls-popup-active",
        bodyActive: "data-fls-popup-open",
      },
      focusCatch: true,
      closeEsc: true,
      bodyLock: true,
      hashSettings: {
        location: false,
        goHash: false,
      },
      on: {
        beforeOpen: function () {},
        afterOpen: function () {},
        beforeClose: function () {},
        afterClose: function () {},
      },
    };
    this.youTubeCode;
    this.isOpen = false;
    this.targetOpen = {
      selector: false,
      element: false,
    };
    this.previousOpen = {
      selector: false,
      element: false,
    };
    this.lastClosed = {
      selector: false,
      element: false,
    };
    this._dataValue = false;
    this.hash = false;
    this._reopen = false;
    this._selectorOpen = false;
    this.lastFocusEl = false;
    this._focusEl = [
      "a[href]",
      'input:not([disabled]):not([type="hidden"]):not([aria-hidden])',
      "button:not([disabled]):not([aria-hidden])",
      "select:not([disabled]):not([aria-hidden])",
      "textarea:not([disabled]):not([aria-hidden])",
      "area[href]",
      "iframe",
      "object",
      "embed",
      "[contenteditable]",
      '[tabindex]:not([tabindex^="-"])',
    ];
    this.options = {
      ...config,
      ...options,
      classes: {
        ...config.classes,
        ...(options == null ? void 0 : options.classes),
      },
      hashSettings: {
        ...config.hashSettings,
        ...(options == null ? void 0 : options.hashSettings),
      },
      on: {
        ...config.on,
        ...(options == null ? void 0 : options.on),
      },
    };
    this.bodyLock = false;
    this.options.init ? this.initPopups() : null;
  }
  initPopups() {
    this.buildPopup();
    this.eventsPopup();
  }
  buildPopup() {}
  eventsPopup() {
    document.addEventListener(
      "click",
      function (e) {
        const buttonOpen = e.target.closest(
          `[${this.options.attributeOpenButton}]`,
        );
        if (buttonOpen) {
          e.preventDefault();
          this._dataValue = buttonOpen.getAttribute(
            this.options.attributeOpenButton,
          )
            ? buttonOpen.getAttribute(this.options.attributeOpenButton)
            : "error";
          this.youTubeCode = buttonOpen.getAttribute(
            this.options.youtubeAttribute,
          )
            ? buttonOpen.getAttribute(this.options.youtubeAttribute)
            : null;
          if (this._dataValue !== "error") {
            if (!this.isOpen) this.lastFocusEl = buttonOpen;
            this.targetOpen.selector = `${this._dataValue}`;
            this._selectorOpen = true;
            this.open();
            return;
          }
          return;
        }
        const buttonClose = e.target.closest(
          `[${this.options.attributeCloseButton}]`,
        );
        if (buttonClose && this.isOpen) {
          e.preventDefault();
          this.close();
          return;
        }
      }.bind(this),
    );
    document.addEventListener(
      "keydown",
      function (e) {
        if (
          this.options.closeEsc &&
          e.which == 27 &&
          e.code === "Escape" &&
          this.isOpen
        ) {
          e.preventDefault();
          this.close();
          return;
        }
        if (this.options.focusCatch && e.which == 9 && this.isOpen) {
          this._focusCatch(e);
          return;
        }
      }.bind(this),
    );
    if (this.options.hashSettings.goHash) {
      window.addEventListener(
        "hashchange",
        function () {
          if (window.location.hash) {
            this._openToHash();
          } else {
            this.close(this.targetOpen.selector);
          }
        }.bind(this),
      );
      window.addEventListener(
        "load",
        function () {
          if (window.location.hash) {
            this._openToHash();
          }
        }.bind(this),
      );
    }
  }
  open(selectorValue) {
    if (bodyLockStatus) {
      this.bodyLock =
        document.documentElement.hasAttribute("data-fls-scrolllock") &&
        !this.isOpen
          ? true
          : false;
      if (
        selectorValue &&
        typeof selectorValue === "string" &&
        selectorValue.trim() !== ""
      ) {
        this.targetOpen.selector = selectorValue;
        this._selectorOpen = true;
      }
      if (this.isOpen) {
        this._reopen = true;
        this.close();
      }
      if (!this._selectorOpen)
        this.targetOpen.selector = this.lastClosed.selector;
      if (!this._reopen) this.previousActiveElement = document.activeElement;
      this.targetOpen.element = document.querySelector(
        `[${this.options.attributeMain}=${this.targetOpen.selector}]`,
      );
      if (this.targetOpen.element) {
        const codeVideo =
          this.youTubeCode ||
          this.targetOpen.element.getAttribute(
            `${this.options.youtubeAttribute}`,
          );
        if (codeVideo) {
          const urlVideo = `https://www.youtube.com/embed/${codeVideo}?rel=0&showinfo=0&autoplay=1`;
          const iframe = document.createElement("iframe");
          const autoplay = this.options.setAutoplayYoutube ? "autoplay;" : "";
          iframe.setAttribute("allowfullscreen", "");
          iframe.setAttribute("allow", `${autoplay}; encrypted-media`);
          iframe.setAttribute("src", urlVideo);
          if (
            !this.targetOpen.element.querySelector(
              `[${this.options.youtubePlaceAttribute}]`,
            )
          ) {
            this.targetOpen.element
              .querySelector("[data-fls-popup-content]")
              .setAttribute(`${this.options.youtubePlaceAttribute}`, "");
          }
          this.targetOpen.element
            .querySelector(`[${this.options.youtubePlaceAttribute}]`)
            .appendChild(iframe);
        }
        if (this.options.hashSettings.location) {
          this._getHash();
          this._setHash();
        }
        this.options.on.beforeOpen(this);
        document.dispatchEvent(
          new CustomEvent("beforePopupOpen", {
            detail: {
              popup: this,
            },
          }),
        );
        this.targetOpen.element.setAttribute(
          this.options.classes.popupActive,
          "",
        );
        document.documentElement.setAttribute(
          this.options.classes.bodyActive,
          "",
        );
        if (!this._reopen) {
          !this.bodyLock ? bodyLock() : null;
        } else this._reopen = false;
        this.targetOpen.element.setAttribute("aria-hidden", "false");
        this.previousOpen.selector = this.targetOpen.selector;
        this.previousOpen.element = this.targetOpen.element;
        this._selectorOpen = false;
        this.isOpen = true;
        setTimeout(() => {
          this._focusTrap();
        }, 50);
        this.options.on.afterOpen(this);
        document.dispatchEvent(
          new CustomEvent("afterPopupOpen", {
            detail: {
              popup: this,
            },
          }),
        );
      }
    }
  }
  close(selectorValue) {
    if (
      selectorValue &&
      typeof selectorValue === "string" &&
      selectorValue.trim() !== ""
    ) {
      this.previousOpen.selector = selectorValue;
    }
    if (!this.isOpen || !bodyLockStatus) {
      return;
    }
    this.options.on.beforeClose(this);
    document.dispatchEvent(
      new CustomEvent("beforePopupClose", {
        detail: {
          popup: this,
        },
      }),
    );
    if (
      this.targetOpen.element.querySelector(
        `[${this.options.youtubePlaceAttribute}]`,
      )
    ) {
      setTimeout(() => {
        this.targetOpen.element.querySelector(
          `[${this.options.youtubePlaceAttribute}]`,
        ).innerHTML = "";
      }, 500);
    }
    this.previousOpen.element.removeAttribute(this.options.classes.popupActive);
    this.previousOpen.element.setAttribute("aria-hidden", "true");
    if (!this._reopen) {
      document.documentElement.removeAttribute(this.options.classes.bodyActive);
      !this.bodyLock ? bodyUnlock() : null;
      this.isOpen = false;
    }
    this._removeHash();
    if (this._selectorOpen) {
      this.lastClosed.selector = this.previousOpen.selector;
      this.lastClosed.element = this.previousOpen.element;
    }
    this.options.on.afterClose(this);
    document.dispatchEvent(
      new CustomEvent("afterPopupClose", {
        detail: {
          popup: this,
        },
      }),
    );
    setTimeout(() => {
      this._focusTrap();
    }, 50);
  }
  // Отримання хешу
  _getHash() {
    if (this.options.hashSettings.location) {
      this.hash = `#${this.targetOpen.selector}`;
    }
  }
  _openToHash() {
    let classInHash = window.location.hash.replace("#", "");
    const openButton = document.querySelector(
      `[${this.options.attributeOpenButton}="${classInHash}"]`,
    );
    if (openButton) {
      this.youTubeCode = openButton.getAttribute(this.options.youtubeAttribute)
        ? openButton.getAttribute(this.options.youtubeAttribute)
        : null;
    }
    if (classInHash) this.open(classInHash);
  }
  // Встановлення хеша
  _setHash() {
    history.pushState("", "", this.hash);
  }
  _removeHash() {
    history.pushState("", "", window.location.href.split("#")[0]);
  }
  _focusCatch(e) {
    const focusable = this.targetOpen.element.querySelectorAll(this._focusEl);
    const focusArray = Array.prototype.slice.call(focusable);
    const focusedIndex = focusArray.indexOf(document.activeElement);
    if (e.shiftKey && focusedIndex === 0) {
      focusArray[focusArray.length - 1].focus();
      e.preventDefault();
    }
    if (!e.shiftKey && focusedIndex === focusArray.length - 1) {
      focusArray[0].focus();
      e.preventDefault();
    }
  }
  _focusTrap() {
    const focusable = this.previousOpen.element.querySelectorAll(this._focusEl);
    if (!this.isOpen && this.lastFocusEl) {
      this.lastFocusEl.focus();
    } else {
      focusable[0].focus();
    }
  }
}

class DynamicAdapt {
  constructor() {
    this.type = "max";
    this.init();
  }
  init() {
    this.objects = [];
    this.daClassname = "--dynamic";
    this.nodes = [...document.querySelectorAll("[data-fls-dynamic]")];
    this.nodes.forEach((node) => {
      const data = node.dataset.flsDynamic.trim();
      const dataArray = data.split(`,`);
      const object = {};
      object.element = node;
      object.parent = node.parentNode;
      object.destinationParent = dataArray[3]
        ? node.closest(dataArray[3].trim()) || document
        : document;
      dataArray[3] ? dataArray[3].trim() : null;
      const objectSelector = dataArray[0] ? dataArray[0].trim() : null;
      if (objectSelector) {
        const foundDestination =
          object.destinationParent.querySelector(objectSelector);
        if (foundDestination) {
          object.destination = foundDestination;
        }
      }
      object.breakpoint = dataArray[1] ? dataArray[1].trim() : `767.98`;
      object.place = dataArray[2] ? dataArray[2].trim() : `last`;
      object.index = this.indexInParent(object.parent, object.element);
      this.objects.push(object);
    });
    this.arraySort(this.objects);
    this.mediaQueries = this.objects
      .map(
        ({ breakpoint }) =>
          `(${this.type}-width: ${breakpoint / 16}em),${breakpoint}`,
      )
      .filter((item, index, self2) => self2.indexOf(item) === index);
    this.mediaQueries.forEach((media) => {
      const mediaSplit = media.split(",");
      const matchMedia2 = window.matchMedia(mediaSplit[0]);
      const mediaBreakpoint = mediaSplit[1];
      const objectsFilter = this.objects.filter(
        ({ breakpoint }) => breakpoint === mediaBreakpoint,
      );
      matchMedia2.addEventListener("change", () => {
        this.mediaHandler(matchMedia2, objectsFilter);
      });
      this.mediaHandler(matchMedia2, objectsFilter);
    });
  }
  mediaHandler(matchMedia2, objects) {
    if (matchMedia2.matches) {
      objects.forEach((object) => {
        if (object.destination) {
          this.moveTo(object.place, object.element, object.destination);
        }
      });
    } else {
      objects.forEach(({ parent, element, index }) => {
        if (element.classList.contains(this.daClassname)) {
          this.moveBack(parent, element, index);
        }
      });
    }
  }
  moveTo(place, element, destination) {
    element.classList.add(this.daClassname);
    const index =
      place === "last" || place === "first" ? place : parseInt(place, 10);
    if (index === "last" || index >= destination.children.length) {
      destination.append(element);
    } else if (index === "first") {
      destination.prepend(element);
    } else {
      destination.children[index].before(element);
    }
  }
  moveBack(parent, element, index) {
    element.classList.remove(this.daClassname);
    if (parent.children[index] !== void 0) {
      parent.children[index].before(element);
    } else {
      parent.append(element);
    }
  }
  indexInParent(parent, element) {
    return [...parent.children].indexOf(element);
  }
  arraySort(arr) {
    if (this.type === "min") {
      arr.sort((a, b) => {
        if (a.breakpoint === b.breakpoint) {
          if (a.place === b.place) {
            return 0;
          }
          if (a.place === "first" || b.place === "last") {
            return -1;
          }
          if (a.place === "last" || b.place === "first") {
            return 1;
          }
          return 0;
        }
        return a.breakpoint - b.breakpoint;
      });
    } else {
      arr.sort((a, b) => {
        if (a.breakpoint === b.breakpoint) {
          if (a.place === b.place) {
            return 0;
          }
          if (a.place === "first" || b.place === "last") {
            return 1;
          }
          if (a.place === "last" || b.place === "first") {
            return -1;
          }
          return 0;
        }
        return b.breakpoint - a.breakpoint;
      });
      return;
    }
  }
}
if (document.querySelector("[data-fls-dynamic]")) {
  window.addEventListener("load", () => new DynamicAdapt());
}

document.addEventListener("afterPopupOpen", function (e) {
  const currentPopup = e.detail.popup.targetOpen.element;
  const splideEl = currentPopup.querySelector(".splide");
  if (splideEl && splideEl.splide) {
    setTimeout(() => {
      splideEl.splide.refresh();
    }, 20);
  }
});
document.addEventListener("afterPopupClose", function (e) {
  const currentPopup = e.detail.popup.targetOpen.element;
  const iframes = currentPopup.querySelectorAll("iframe");
  iframes.forEach((iframe) => {
    if (
      iframe.hasAttribute("data-deferred-youtube-src") ||
      iframe.closest(".info-popup__video--slide")
    ) {
      return;
    }
    const currentSrc = iframe.src;
    iframe.src = "";
    iframe.src = currentSrc;
  });
});
window.flsPopup = new Popup({});

document.addEventListener("click", function (e) {
  const trigger = e.target.closest("[data-fls-scrollto]");

  if (!trigger) {
    return;
  }

  e.preventDefault();

  const targetSelector = trigger.getAttribute("data-fls-scrollto");

  if (targetSelector) {
    const targetElement = document.querySelector(targetSelector);

    if (targetElement) {
      let totalOffset = 100;
      const header = document.querySelector("header");
      if (header) {
        totalOffset += header.offsetHeight;
      }
      const appointmentSection = document.querySelector(
        ".request-appointment-section.fixed",
      );
      if (appointmentSection) {
        totalOffset += appointmentSection.offsetHeight;
      }
      const elementPosition =
        targetElement.getBoundingClientRect().top + window.scrollY;
      const offsetPosition = elementPosition - totalOffset;
      window.scrollTo({
        top: offsetPosition,
        behavior: "smooth",
      });
    }
  }
});
