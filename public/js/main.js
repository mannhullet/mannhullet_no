
// Textfill plugin for jquery
(function($) {
    $.fn.textfill = function(options) {
        var fontSize = options.maxFontPixels;
        var ourText = $('span:visible:first', this);
        //var maxHeight = $(this).height();
        var maxWidth = $(this).width();
        var textHeight;
        var textWidth;
        do {
            ourText.css('font-size', fontSize);
            //textHeight = ourText.height();
            textWidth = ourText.outerWidth();
            fontSize = fontSize - 1;
        } while ((/*textHeight > maxHeight ||*/ textWidth > maxWidth) && fontSize > 3);
        return this;
    }
})(jQuery);

$(window).ready(function() {

    var options = {
        'headerAnimation': {
            'timeout': 150,
            'height': 290,
            'duration': 'medium'
        },
        'mainnav': {
            'stickHeight': 290
        }
    };

    var isMobileDevice = false;
    if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
        isMobileDevice = true;
    }

    if (isMobileDevice) {
        $('div.header').css('position', 'relative');
        $('div.wrap').css('margin-top', '0px');
        $('#slider').css('opacity', '1');
        $('#footer').css('opacity', '1');
        $('#slider').carousel('cycle');
    }

    // Large browsers bug the scroll animations when the document height is to small,
    // thus we deactivate them.
    var doAnimate = ($(window).height() < $(document).height()-400) && !isMobileDevice;
    $(window).resize(function() {
        doAnimate = ($(window).height() < $(document).height()-400) && !isMobileDevice;
    });

    // Activate Skrollr.js
    if (!isMobileDevice) skrollr.init({
        edgeStrategy: 'set',
        easing: {
            WTF: Math.random,
            inverted: function(p) {
            return 1-p;
        }
    }, smoothScrolling: true, forceHeight: false });

    // The slide effect of the header
    var scrollTimer;
    var animating = false;
    var lastScrollTop = 0;
    var scrollDirection = 0;
    var scrollHandler = function(event) {
        var cST = $(window).scrollTop();

        if (cST < 0) cST = 0;

        if (cST > options.headerAnimation.height-1) {
            $('#slider').carousel('cycle');
            $('div.userMenu').css('display', 'none');
        }else{
            $('#slider').carousel('pause');
            $('div.userMenu').css('display', 'block');
        }

        // Fix for userMenu
        $('div.userMenu').css('opacity', 1 - cST/100);
        $('div.header div.nav').css('opacity', 1 - cST/100);
        $('div.header div.user').css('opacity', 1 - cST/100);

        if (animating || cST > (options.headerAnimation.height-1)) {
            lastScrollTop = cST;
            return;
        }

        if (lastScrollTop < cST) scrollDirection = 0;
        else scrollDirection = 1;
        lastScrollTop = cST;

        clearTimeout(scrollTimer);
        scrollTimer = setTimeout(function() {
            if ($(window).scrollTop() > options.headerAnimation.height) return;
            if (scrollDirection == 0) { // Scroll direction down
                animating = true;
                if (doAnimate) $('html, body').animate({scrollTop:options.headerAnimation.height}, options.headerAnimation.duration, 'easeInOutCirc', function() {
                    animating = false;
                });
            }else{ // Scroll direction up
                animating = true;
                if (doAnimate) $('html, body').animate({scrollTop:0}, options.headerAnimation.duration, 'easeInOutCirc', function() {
                    animating = false;
                });
            }
        }, options.headerAnimation.timeout);
    };
    $(window).scroll(scrollHandler);
    scrollHandler();

    // Make the main nav stick to the window-top
    var stickyFunc = function() {
        if (!doAnimate) return; // This fixes a bug where small pages messes up in large browser resolutions
        if ($(window).scrollTop() > options.mainnav.stickHeight-10) {
            $('ul.mainnav:first').addClass('navstick');
            $('div.wrap div.content div.container').css('padding-top', '30px');
        }else{
            $('ul.mainnav:first').removeClass('navstick');
            $('div.wrap div.content div.container').css('padding-top', '0px');
        }
    };
    $(window).scroll(stickyFunc);
    stickyFunc();

    var userMenuResize = function() {
        var width = $('div.userMenu').width();
        var topPos = 100; //$('div.header div.nav ul.nav').offset().top;
        var rightPos = $('div.header div.container').offset().left + 45;

        if ($(window).width() < 750) rightPos = rightPos - (750-$(window).width())
        $('div.userMenu').css('top', topPos + 'px');
        $('div.userMenu').css('right', rightPos + 'px');
    };
    $(window).resize(userMenuResize);
    userMenuResize();

    $('table tbody tr td span').popover({
        html: true,
        trigger: 'hover',
        container: 'body',
    });

    $('div.styremedlem').popover({
        html: true,
        trigger: 'hover',
        container: 'body',
        placement: 'auto right',
    });

    $('.input-daterange#datepicker-mbkreservasjonssystem').datepicker({
        format: "yyyy-mm-dd",
        weekStart: 1,
        todayBtn: "linked",
        language: "nb",
        forceParse: false,
        calendarWeeks: true,
        todayHighlight: true
    });

    var fixMinHeightStyremedlemResize = function() {
        var height = 440;
        $('div.col-sm-6.col-md-4.styremedlem').css('height', 'auto');
        $('div.col-sm-6.col-md-4.styremedlem').each(function() {
            height = Math.max(height, $(this).outerHeight());
        });
        $('div.col-sm-6.col-md-4.styremedlem').each(function() {
            $(this).css('height', height);
        });
    };
    $(window).resize(fixMinHeightStyremedlemResize);
    fixMinHeightStyremedlemResize();
    setInterval(fixMinHeightStyremedlemResize, 1000); // Fixes bug where images are still loading and the dom changing

    // var fixMinHeightMarinaResize = function() {
    //     var height = 440;
    //     $('div.col-xs-6.col-md-3.marinaPub').css('height', 'auto');
    //     $('div.col-xs-6.col-md-3.marinaPub').each(function() {
    //         height = Math.max(height, $(this).outerHeight());
    //     });
    //     $('div.col-xs-6.col-md-3.marinaPub').each(function() {
    //         $(this).css('height', height);
    //     });
    // };
    // $(window).resize(fixMinHeightMarinaResize);
    // fixMinHeightMarinaResize();
    // setInterval(fixMinHeightMarinaResize, 1000); // Fixes bug where images are still loading and the dom changing

    // var fixMinHeightDocumentsResize = function() {
    //     var height = 440;
    //     $('div.col-xs-6.col-md-3.documentsTag').css('height', 'auto');
    //     $('div.col-xs-6.col-md-3.documentsTag').each(function() {
    //         height = Math.max(height, $(this).outerHeight());
    //     });
    //     $('div.col-xs-6.col-md-3.documentsTag').each(function() {
    //         $(this).css('height', height);
    //     });
    // };
    // $(window).resize(fixMinHeightDocumentsResize);
    // fixMinHeightDocumentsResize();
    // setInterval(fixMinHeightDocumentsResize, 1000); // Fixes bug where images are still loading and the dom changing

    // Make sure the text fills the box, but not overflow or cover multiple lines.
    $('.textfill').each(function() {
        $(this).textfill({maxFontPixels: 36});
    });
});
