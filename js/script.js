jQuery(document).ready(function ($) {
  var $pinItBtn = $('<button id="pin-it-btn" style="display:none;"></button>');
  $("body").append($pinItBtn);

  function updateButtonStyle() {
    var baseStyles = {
      position: "absolute",
      zIndex: 1000,
      cursor: "pointer",
      border: "none",
      display: "inline-flex",
      alignItems: "center",
      justifyContent: "center",
      fontFamily: "Helvetica, Arial, sans-serif",
      fontWeight: "bold",
      transition: "all 0.3s ease",
      boxShadow: "0 2px 4px rgba(0, 0, 0, 0.2)",
      color: "white",
      padding: "0 6px",
      textDecoration: "none",
      whiteSpace: "nowrap",
    };

    var sizeStyles = {
      small: {
        height: "24px",
        fontSize: "12px",
        iconSize: "14px",
      },
      medium: {
        height: "32px",
        fontSize: "14px",
        iconSize: "16px",
      },
      large: {
        height: "40px",
        fontSize: "16px",
        iconSize: "18px",
      },
    };

    var buttonSize = pinItOptions.button_size || "medium";
    var buttonColor = pinItOptions.button_color || "#E60023";
    var buttonShape = pinItOptions.button_shape || "rectangle";

    $pinItBtn.css({
      ...baseStyles,
      backgroundColor: buttonColor,
      borderRadius: buttonShape === "round" ? "50%" : "4px",
      height: sizeStyles[buttonSize].height,
      fontSize: sizeStyles[buttonSize].fontSize,
    });

    var iconSize = sizeStyles[buttonSize].iconSize;
    var iconSvg = `
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" fill="currentColor" style="width: ${iconSize}; height: ${iconSize}; margin-right: 4px;">
        <path d="M204 6.5C101.4 6.5 0 74.9 0 185.6 0 256 39.6 296 63.6 296c9.9 0 15.6-27.6 15.6-35.4 0-9.3-23.7-29.1-23.7-67.8 0-80.4 61.2-137.4 140.4-137.4 68.1 0 118.5 38.7 118.5 109.8 0 53.1-21.3 152.7-90.3 152.7-24.9 0-46.2-18-46.2-43.8 0-37.8 26.4-74.4 26.4-113.4 0-66.2-93.9-54.2-93.9 25.8 0 16.8 2.1 35.4 9.6 50.7-13.8 59.4-42 147.9-42 209.1 0 18.9 2.7 37.5 4.5 56.4 3.4 3.8 1.7 3.4 6.9 1.5 50.4-69 48.6-82.5 71.4-172.8 12.3 23.4 44.1 36 69.3 36 106.2 0 153.9-103.5 153.9-196.8C384 71.3 298.2 6.5 204 6.5z"/>
      </svg>
    `;

    var buttonText = pinItOptions.button_text === 'pin-it' ? 'Pin it' : 'Save';
$pinItBtn.html(iconSvg + `<span>${buttonText}</span>`);
$pinItBtn.attr('data-pin-text', pinItOptions.button_text);

    if (buttonShape === "round") {
      $pinItBtn.css({
        width: sizeStyles[buttonSize].height,
        padding: "0",
      });
      $pinItBtn.find("span").hide();
    } else {
      $pinItBtn.find("span").show();
    }
  }

  updateButtonStyle();

  $(window).on("resize", updateButtonStyle);

  function shouldShowPinIt() {
    var isMobile =
      /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
        navigator.userAgent
      );

    if (isMobile && !pinItOptions.show_on_mobile) {
      return false;
    }

    if (pinItOptions.show_on_posts && $("body").hasClass("single-post")) {
      return true;
    }

    if (pinItOptions.show_on_pages && $("body").hasClass("page")) {
      return true;
    }

    if (
      pinItOptions.show_on_archives &&
      ($("body").hasClass("archive") || $("body").hasClass("blog"))
    ) {
      return true;
    }

    return false;
  }

  if (shouldShowPinIt()) {
    $(document).on(
      {
        mouseenter: function () {
          var $img = $(this);

          // Check if the image is within the site logo or author avatar areas
          if (
            $img.closest(
              ".site-logo, .header-logo, .logo, .footer, .header, .site-footer, .site-logo-img, .title-area, .site-header, .author-box, .avatar"
            ).length > 0
          ) {
            return;
          }

          var imgOffset = $img.offset();
          var buttonPosition = pinItOptions.button_position || "top-left";
          var buttonWidth = $pinItBtn.outerWidth();
          var buttonHeight = $pinItBtn.outerHeight();
          var xOffset, yOffset;

          if (buttonPosition.includes("right")) {
            xOffset = $img.width() - buttonWidth - 10;
          } else {
            xOffset = 10;
          }

          if (buttonPosition.includes("bottom")) {
            yOffset = $img.height() - buttonHeight - 10;
          } else {
            yOffset = 10;
          }

          $pinItBtn.css({
            top: imgOffset.top + yOffset + "px",
            left: imgOffset.left + xOffset + "px",
          });

          $pinItBtn.data("image-url", $img.attr("src"));
          $pinItBtn.data("image-description", $img.attr("alt") || "");

          // Use setTimeout to ensure the button appears only when the mouse fully enters the image
          setTimeout(function () {
            if ($img.is(":hover")) {
              $pinItBtn.show();
            }
          }, 50);
        },
        mouseleave: function () {
          // Hide the button immediately when the mouse leaves the image
          $pinItBtn.hide();
        },
      },
      "img"
    );

    $pinItBtn.on({
      mouseenter: function () {
        $(this).show();
      },
      mouseleave: function () {
        $(this).hide();
      },
      click: function (e) {
        e.preventDefault();
        var imageUrl = $(this).data("image-url");
        var imageDescription = $(this).data("image-description");
        var pinUrl = "https://www.pinterest.com/pin/create/button/";
        pinUrl += "?url=" + encodeURIComponent(window.location.href);
        pinUrl += "&media=" + encodeURIComponent(imageUrl);
        pinUrl += "&description=" + encodeURIComponent(imageDescription);
        window.open(
          pinUrl,
          "pin" + new Date().getTime(),
          "toolbar=0,status=0,resizable=1,width=750,height=320"
        );
      },
    });
  }
});
