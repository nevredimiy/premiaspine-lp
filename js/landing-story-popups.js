(function () {
  var activePopup = null;

  function getPopupById(id) {
    return document.querySelector('[data-fls-popup="' + id + '"]');
  }

  function getPopupGallerySlides(slider) {
    if (!slider) {
      return [];
    }

    return Array.prototype.slice.call(
      slider.querySelectorAll(".splide__list > .splide__slide"),
    );
  }

  function isSinglePopupGallerySlide(slider) {
    return getPopupGallerySlides(slider).length <= 1;
  }

  function syncPopupGallerySingleState(slider) {
    if (!slider) {
      return;
    }

    slider.classList.toggle(
      "info-popup__slider--single",
      isSinglePopupGallerySlide(slider),
    );
  }

  function shouldAutoplayGalleryVideo(popup, slider, activeSlideEl) {
    if (
      isSinglePopupGallerySlide(slider) &&
      getSlideYoutubeIframe(activeSlideEl)
    ) {
      return true;
    }

    return popupGalleryAutoplay(popup);
  }

  function getPopupGallerySlider(popup) {
    return popup ? popup.querySelector(".info-popup__slider.splide") : null;
  }

  function getSlideYoutubeIframe(slideEl) {
    if (!slideEl) {
      return null;
    }

    return slideEl.querySelector(".info-popup__video--slide iframe");
  }

  function getStandaloneYoutubeIframe(popup) {
    if (!popup || getPopupGallerySlider(popup)) {
      return null;
    }

    return popup.querySelector(
      ".info-popup__video:not(.info-popup__video--slide) iframe",
    );
  }

  function withAutoplayParam(embedUrl, autoplay) {
    try {
      var url = new URL(embedUrl, window.location.href);
      url.searchParams.set("autoplay", autoplay ? "1" : "0");
      return url.toString();
    } catch (error) {
      var clean = embedUrl
        .replace(/([?&])autoplay=[01]\b/gi, "")
        .replace(/[?&]$/, "");
      var separator = clean.indexOf("?") >= 0 ? "&" : "?";

      return clean + separator + "autoplay=" + (autoplay ? "1" : "0");
    }
  }

  function sendYoutubeCommand(iframe, func, args) {
    if (!iframe || !iframe.contentWindow) {
      return;
    }

    iframe.contentWindow.postMessage(
      JSON.stringify({
        event: "command",
        func: func,
        args: args || [],
      }),
      "*",
    );
  }

  function getIframeEmbedUrl(iframe) {
    if (!iframe) {
      return "";
    }

    return (
      iframe.getAttribute("data-deferred-youtube-src") ||
      iframe.getAttribute("src") ||
      ""
    ).trim();
  }

  function pauseYoutubeIframe(iframe) {
    if (!iframe) {
      return;
    }

    sendYoutubeCommand(iframe, "pauseVideo");

    if (!iframe.getAttribute("src")) {
      return;
    }

    if (!iframe.getAttribute("data-deferred-youtube-src")) {
      iframe.setAttribute(
        "data-deferred-youtube-src",
        iframe.getAttribute("src"),
      );
    }

    iframe.removeAttribute("src");
  }

  function activateYoutubeIframe(iframe, autoplay) {
    var embedUrl = getIframeEmbedUrl(iframe);
    if (!embedUrl) {
      return;
    }

    var nextSrc = withAutoplayParam(embedUrl, !!autoplay);
    if (iframe.getAttribute("src") !== nextSrc) {
      iframe.setAttribute("src", nextSrc);
    }
  }

  var gallerySyncFrame = null;

  function scheduleSyncPopupGalleryVideos(popup) {
    if (gallerySyncFrame) {
      cancelAnimationFrame(gallerySyncFrame);
    }

    gallerySyncFrame = requestAnimationFrame(function () {
      gallerySyncFrame = requestAnimationFrame(function () {
        gallerySyncFrame = null;
        syncPopupGalleryVideos(popup);
      });
    });
  }

  function popupGalleryAutoplay(popup) {
    return popup && popup.getAttribute("data-story-type") === "surgeon";
  }

  function syncPopupGalleryVideos(popup) {
    var slider = getPopupGallerySlider(popup);
    if (!slider || !slider.splide || !slider.splide.Components) {
      return;
    }

    var slidesApi = slider.splide.Components.Slides;
    var activeSlide = slidesApi.getAt(slider.splide.index);
    var autoplay = shouldAutoplayGalleryVideo(
      popup,
      slider,
      activeSlide ? activeSlide.slide : null,
    );

    slider
      .querySelectorAll(".info-popup__video--slide iframe")
      .forEach(function (iframe) {
        pauseYoutubeIframe(iframe);
      });

    if (!activeSlide) {
      return;
    }

    var iframe = getSlideYoutubeIframe(activeSlide.slide);
    if (iframe) {
      activateYoutubeIframe(iframe, autoplay);
    }
  }

  function pauseAllGalleryVideos(popup) {
    var slider = getPopupGallerySlider(popup);
    if (!slider) {
      return;
    }

    slider
      .querySelectorAll(".info-popup__video--slide iframe")
      .forEach(function (iframe) {
        pauseYoutubeIframe(iframe);
      });
  }

  function shouldAutoplayStandaloneVideo(popup, iframe) {
    if (!iframe) {
      return false;
    }

    if (iframe.getAttribute("data-youtube-autoplay") === "1") {
      return true;
    }

    var popupName = popup ? popup.getAttribute("data-fls-popup") || "" : "";

    return popupName.indexOf("about-video-popup-") === 0;
  }

  function loadStandaloneYoutubePlayer(popup) {
    var iframe = getStandaloneYoutubeIframe(popup);
    if (!iframe) {
      return;
    }

    activateYoutubeIframe(iframe, shouldAutoplayStandaloneVideo(popup, iframe));
  }

  function pauseStandaloneYoutubePlayer(popup) {
    pauseYoutubeIframe(getStandaloneYoutubeIframe(popup));
  }

  function initPopupGallery(popup) {
    var slider = getPopupGallerySlider(popup);
    if (!slider) {
      return;
    }

    syncPopupGallerySingleState(slider);

    if (slider.dataset.popupSplideMounted === "1") {
      scheduleSyncPopupGalleryVideos(popup);
      return;
    }

    if (typeof Splide === "undefined") {
      return;
    }

    if (slider.splide) {
      slider.splide.destroy(true);
      slider.splide = null;
    }

    var splide = new Splide(slider, {
      perPage: 1,
      arrows: true,
      pagination: !isSinglePopupGallerySlide(slider),
      gap: 0,
      updateOnMove: true,
      classes: {
        prev: "splide__arrow--prev _sprite-ch-thin-left",
        next: "splide__arrow--next _sprite-ch-thin-right",
      },
    });

    slider.splide = splide;
    slider.dataset.popupSplideMounted = "1";

    splide.on("mounted moved active", function () {
      scheduleSyncPopupGalleryVideos(popup);
    });

    splide.mount();
    scheduleSyncPopupGalleryVideos(popup);
  }

  function activateDeferredIframes(popup) {
    popup
      .querySelectorAll(".info-popup__text iframe[data-deferred-youtube-src]")
      .forEach(function (iframe) {
        if (iframe.getAttribute("src")) {
          return;
        }

        iframe.setAttribute(
          "src",
          iframe.getAttribute("data-deferred-youtube-src"),
        );
      });
  }

  function pauseDeferredIframes(popup) {
    popup
      .querySelectorAll(".info-popup__text iframe[data-deferred-youtube-src]")
      .forEach(function (iframe) {
        pauseYoutubeIframe(iframe);
      });
  }

  function cancelScheduledGallerySync() {
    if (!gallerySyncFrame) {
      return;
    }

    cancelAnimationFrame(gallerySyncFrame);
    gallerySyncFrame = null;
  }

  function isStoryInfoPopup(popup) {
    return popup && popup.querySelector(".info-popup");
  }

  function pauseStoryPopupMedia(popup) {
    if (!popup) {
      return;
    }

    cancelScheduledGallerySync();
    pauseStandaloneYoutubePlayer(popup);
    pauseAllGalleryVideos(popup);
    pauseDeferredIframes(popup);
  }

  function openPopup(popup) {
    if (!popup) {
      return;
    }

    if (activePopup && activePopup !== popup) {
      closePopup(activePopup);
    }

    activePopup = popup;
    document.documentElement.setAttribute("data-fls-popup-open", "");
    popup.setAttribute("data-fls-popup-active", "");
    popup.setAttribute("aria-hidden", "false");

    initPopupGallery(popup);
    loadStandaloneYoutubePlayer(popup);
    activateDeferredIframes(popup);
  }

  function closePopup(popup) {
    if (!popup) {
      return;
    }

    popup.removeAttribute("data-fls-popup-active");
    popup.setAttribute("aria-hidden", "true");
    pauseStoryPopupMedia(popup);

    if (activePopup === popup) {
      activePopup = null;
    }

    if (!document.querySelector("[data-fls-popup][data-fls-popup-active]")) {
      document.documentElement.removeAttribute("data-fls-popup-open");
    }
  }

  document.addEventListener("afterPopupOpen", function (e) {
    var popup =
      e.detail &&
      e.detail.popup &&
      e.detail.popup.targetOpen &&
      e.detail.popup.targetOpen.element;

    if (!isStoryInfoPopup(popup)) {
      return;
    }

    activePopup = popup;
    initPopupGallery(popup);
    loadStandaloneYoutubePlayer(popup);
    activateDeferredIframes(popup);
  });

  document.addEventListener("afterPopupClose", function (e) {
    var popup =
      e.detail &&
      e.detail.popup &&
      e.detail.popup.targetOpen &&
      e.detail.popup.targetOpen.element;

    if (!isStoryInfoPopup(popup)) {
      return;
    }

    pauseStoryPopupMedia(popup);

    if (activePopup === popup) {
      activePopup = null;
    }
  });

  document.addEventListener("click", function (e) {
    var link = e.target.closest("[data-fls-popup-link]");
    if (link) {
      e.preventDefault();
      var id = link.getAttribute("data-fls-popup-link");
      var popup = getPopupById(id);
      if (popup) {
        openPopup(popup);
      }
      return;
    }

    if (e.target.closest("[data-fls-popup-close]")) {
      e.preventDefault();
      var closeTarget = e.target.closest("[data-fls-popup]");
      if (closeTarget) {
        closePopup(closeTarget);
      }
      return;
    }
  });

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && activePopup) {
      closePopup(activePopup);
    }
  });
})();
