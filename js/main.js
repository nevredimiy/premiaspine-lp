(function ($) {
  function getSchemaRuleMessage(form, fieldName, ruleName) {
    var schema = form.wpcf7 && form.wpcf7.schema;

    if (!schema || !Array.isArray(schema.rules)) {
      return "";
    }

    for (var i = 0; i < schema.rules.length; i++) {
      var rule = schema.rules[i];

      if (rule.field === fieldName && rule.rule === ruleName && rule.error) {
        return rule.error;
      }
    }

    return "";
  }

  function getPreferredFieldMessage(form, fieldName, control) {
    if (control.classList.contains("wpcf7-validates-as-tel")) {
      return getSchemaRuleMessage(form, fieldName, "tel");
    }

    if (control.classList.contains("wpcf7-validates-as-email")) {
      return getSchemaRuleMessage(form, fieldName, "email");
    }

    return "";
  }

  function getWpcf7FormElement(formEl) {
    return formEl && formEl.tagName === "FORM"
      ? formEl
      : formEl && formEl.querySelector
        ? formEl.querySelector("form")
        : null;
  }

  function usesNativeCf7Validation(form) {
    return !!form.closest(
      ".contact-section, .request-appointment-section, [data-cf7-native-validation]",
    );
  }

  function patchFieldErrors(formEl) {
    var form = getWpcf7FormElement(formEl);

    if (!form || !form.wpcf7) {
      return;
    }

    form
      .querySelectorAll(".wpcf7-form-control-wrap[data-name]")
      .forEach(function (wrap) {
        var fieldName = wrap.dataset.name;
        var control = wrap.querySelector(".wpcf7-form-control");
        var tip = wrap.querySelector(".wpcf7-not-valid-tip");
        var preferredMessage = control
          ? getPreferredFieldMessage(form, fieldName, control)
          : "";

        if (!preferredMessage) {
          return;
        }

        if (tip && tip.textContent !== preferredMessage) {
          tip.textContent = preferredMessage;
        }

        if (control && typeof control.setCustomValidity === "function") {
          control.setCustomValidity(preferredMessage);
        }
      });
  }

  function syncTopSectionErrorTexts(formEl) {
    var form = getWpcf7FormElement(formEl);

    if (!form) {
      return;
    }

    form.querySelectorAll(".top-section .field").forEach(function (field) {
      var tip = field.querySelector(".wpcf7-not-valid-tip");
      var errorText = field.querySelector(".error-text");

      if (!errorText || !tip || !tip.textContent) {
        return;
      }

      errorText.textContent = tip.textContent;
    });
  }

  function handleCf7Invalid(event) {
    var target = event.target;
    var form = getWpcf7FormElement(target);
    var invalidFields =
      event.detail &&
      event.detail.apiResponse &&
      event.detail.apiResponse.invalid_fields;

    if (form && Array.isArray(invalidFields)) {
      invalidFields.forEach(function (field) {
        if (!field || !field.field) {
          return;
        }

        var wrap = form.querySelector(
          '.wpcf7-form-control-wrap[data-name="' + field.field + '"]',
        );
        var control = wrap && wrap.querySelector(".wpcf7-form-control");
        var preferredMessage = control
          ? getPreferredFieldMessage(form, field.field, control)
          : "";

        if (preferredMessage) {
          field.message = preferredMessage;
        }
      });
    }

    patchFieldErrors(target);
    syncTopSectionErrorTexts(target);

    window.setTimeout(function () {
      patchFieldErrors(target);
      syncTopSectionErrorTexts(target);
    }, 0);
  }

  document.addEventListener("wpcf7invalid", handleCf7Invalid);

  $(document).ready(function () {
    fixSizes();
    $("a[data-fancybox], .video a").fancybox({
      smallBtn: true,
      helpers: {
        title: {
          type: "inside",
        },
        media: true,
      },
      caption: function (instance, item) {
        var caption = $(this).attr("title") || "";
        var description = $(this).attr("data-description") || "";

        caption = caption.length ? caption + "<br />" + description : "";
        return caption;
      },
      youtube: {
        autoplay: 1,
      },
    });
    $(".faq").accordion({
      collapsible: true,
      active: false,
      heightStyle: "content",
    });
    $(".wpcf7").on("wpcf7submit", function (e) {
      console.log(e);
      $(e.target)
        .find(".wpcf7-response-output")
        .addClass("wpcf7-validation-errors")
        .show();
    });

    $(".wpcf7-submit").click(function (e) {
      var form = $(this).closest("form")[0];

      // Let CF7 handle validation for landing forms configured below.
      if (usesNativeCf7Validation(form)) {
        return;
      }

      var $form = $(form);
      $form.find("input,textarea,select").each(function (index, el) {
        wpcf7.validate(form, {
          target: el,
        });
      });
      var tel = $form.find('[name="tel"]');
      var email = $form.find('[name="email"]');
      var errors = $form.find(".wpcf7-not-valid-tip");
      if (tel.length && email.length) {
        if (
          !errors.length &&
          (emailValidation(email.val()) || phoneValidation(tel.val()))
        ) {
          $form.find(".wpcf7-response-output").hide();
          $form.find(".wpcf7-response-output").html("");
        } else {
          e.preventDefault();
          var text = [];
          if (!phoneValidation(tel.val())) {
            tel.addClass("wpcf7-not-valid");
            text.push(
              "<span>We'd love to get back to you. Please fill in phone field.</span>",
            );
          }

          if (email.val() != "" && !emailValidation(email.val())) {
            email.addClass("wpcf7-not-valid");
            text.push("<span>Please fill in email field.</span>");
          }
          errors = $form.find(".wpcf7-not-valid-tip");
          errors.each(function (index, el) {
            var $error = $(el);
            text.push("<span>" + $error.text() + "</span>");
          });
          $form
            .find(".wpcf7-response-output")
            .addClass("wpcf7-validation-errors")
            .html(text.join("<br/>"));
          $form.find(".wpcf7-response-output").show();
        }
      }
    });

    var $fixedBlock = $(
      ".new-landing .top-section .request-appointment-section",
    );
    var $showBtn = $(".form-show-btn");

    if ($fixedBlock.length) {
      var blockTop = $fixedBlock.offset().top + 70;
      var isClosedDesktop = false;
      var isFormOpenMobile = false;
      function handleVisibility() {
        var isMobile = window.innerWidth <= 767.98;
        var isScrolledPast = $(window).scrollTop() > blockTop;

        if (isScrolledPast) {
          if (isMobile) {
            if (isFormOpenMobile) {
              $fixedBlock.addClass("fixed");
              $showBtn.removeClass("show");
            } else {
              $fixedBlock.removeClass("fixed");
              $showBtn.addClass("show");
            }
          } else {
            $showBtn.removeClass("show");
            if (!isClosedDesktop) {
              $fixedBlock.addClass("fixed");
            } else {
              $fixedBlock.removeClass("fixed");
            }
          }
        } else {
          $fixedBlock.removeClass("fixed");
          $showBtn.removeClass("show");
          isFormOpenMobile = false;
        }
      }

      $(window).on("scroll resize", handleVisibility);

      $showBtn.on("click", function (e) {
        e.preventDefault();
        isFormOpenMobile = true;
        handleVisibility();
      });

      $(".form-hide").on("click", function (e) {
        e.preventDefault();
        var isMobile = window.innerWidth <= 767.98;

        if (isMobile) {
          isFormOpenMobile = false;
        } else {
          isClosedDesktop = true;
        }

        handleVisibility();
      });
    }
  });

  $(window).resize(function () {
    fixSizes();
  });

  function fixSizes() {}
})(jQuery);

function emailValidation(data) {
  if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(data)) {
    return true;
  }
  return false;
}

function phoneValidation(data) {
  if (!data || data.match(/[\d]/g) == null) {
    return false;
  }
  var length = data.match(/[\d]/g).length;
  return /^([\d\+\-]+)$/.test(data) && length > 8;
}

document.addEventListener("DOMContentLoaded", function () {
  const targetSelectors =
    "#closeXButton, .child-field-question, .field-radio, .child-field label, .poptin-form-submit-button, .froala-editor-button, .poptin-design-fields-form, .froala-editor-text";

  const observer = new MutationObserver(function () {
    const elements = document.querySelectorAll(targetSelectors);

    elements.forEach(function (el) {
      if (el.hasAttribute("style")) {
        let currentStyles = el.style.cssText;
        if (currentStyles.includes("!important")) {
          let newStyles = currentStyles.replace(/!\s*important/gi, "");
          el.style.cssText = newStyles;
        }
      }
    });
    const labels = document.querySelectorAll(".poptin-popup label");

    labels.forEach(function (label) {
      if (label.querySelector(".bracket-wrap")) return;

      Array.from(label.childNodes).forEach(function (node) {
        if (node.nodeType === 3 && node.nodeValue.includes("(")) {
          const tempContainer = document.createElement("div");
          tempContainer.innerHTML = node.nodeValue.replace(
            /(\([^)]+\))/g,
            '<span class="bracket-wrap">$1</span>',
          );
          const actualParent = node.parentNode;
          if (actualParent) {
            while (tempContainer.firstChild) {
              actualParent.insertBefore(tempContainer.firstChild, node);
            }
            actualParent.removeChild(node);
          }
        }
      });
    });
  });
  observer.observe(document.body, { childList: true, subtree: true });
});
