jQuery(document).ready(function($){
	//set body width for IE8
	if (/MSIE (\d+\.\d+);/.test(navigator.userAgent)) {
		var ieversion=new Number(RegExp.$1)
		if (ieversion==8) {
			$('body').css('max-width', $(window).width());
		}
	}
	
	var $masonry = $('#masonry');
	
	$('#navigation').css({'visibility':'hidden', 'height':'1px'});

	if ($masonry.length) {
		$('#ajax-loader-masonry').hide();
		if ($(document).width() <= 480) {
			$masonry.imagesLoaded(function(){
				$masonry.masonry({
					itemSelector : '.thumb',
					isFitWidth: true,
					transitionDuration: 0
				}).css('visibility', 'visible');
			});
		} else {
			$masonry.masonry({
				itemSelector : '.thumb',
				isFitWidth: true,
				transitionDuration: 0
			}).css('visibility', 'visible');
		}

		$masonry.infinitescroll({
			navSelector : '#navigation',
			nextSelector : '#navigation #navigation-next a',
			itemSelector : '.thumb',
			loading: {
				msgText: '',
				finishedMsg: obj_ipin.__allitemsloaded,
				img: obj_ipin.stylesheet_directory_uri + '/img/ajax-loader.gif',
				finished: function() {},
			},
		}, function(newElements) {
			var $newElems = $(newElements);
			if ($(document).width() <= 480) {
				$newElems.imagesLoaded(function(){
					$('#infscr-loading').fadeOut('normal');
					$masonry.masonry('appended', $newElems, true);
				});
			} else {
				$('#infscr-loading').fadeOut('normal');
				$masonry.masonry('appended', $newElems, true);	
			}
		});
	}
	
	if (!($(document).width() <= 480)) {
		$masonry.on('mouseenter', '.thumb-holder', function() {
			$(this).children('.masonry-actionbar').show();
		});
		
		$masonry.on('mouseleave', '.thumb-holder', function() {
			$(this).children('.masonry-actionbar').hide();
		});
	}
	
	$(window).resize(function() {
		if ($masonry.length) {
			$masonry.width($(window).width()-28).masonry('reloadItems').masonry('layout');
		}
	});
	
	var $scrolltotop = $("#scrolltotop");
	$scrolltotop.css('display', 'none');

	$(function () {
		$(window).scroll(function () {
			if ($(this).scrollTop() > 100) {
				$scrolltotop.slideDown('fast');
			} else {
				$scrolltotop.slideUp('fast');
			}
		});

		$scrolltotop.click(function () {
			$('body,html').animate({
				scrollTop: 0
			}, 'fast');
			return false;
		});
	});
});