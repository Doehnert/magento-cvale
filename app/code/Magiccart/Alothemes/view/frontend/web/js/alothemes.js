/**
 * Magiccart 
 * @category 	Magiccart 
 * @copyright 	Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license 	http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2014-04-25 13:16:48
 * @@Modify Date: 2018-05-21 21:18:41
 * @@Function:
 */

define([
    'jquery',
    'magiccart/slick',
	'magiccart/zoom',
	'magiccart/parallax',
	'Magento_Ui/js/modal/modal'
    ], function ($, slick, zoom, parallax, modal) {
	"use strict";
	window.magicproduct = function(el, iClass) {
		if( !el.data( 'vertical') && $('body').hasClass('rtl') ){
			el.attr('dir', 'rtl');
			el.data( 'rtl', true );
			// el.data( 'vertical-reverse', true );
		}
		var options = el.data();
		if(iClass === undefined){
			el.children().addClass('alo-item');
			iClass = '.alo-item';
		}
		var selector = el.selector;
		var classes = selector + ' '+ iClass;
		var padding = options.padding;
		var style = padding ? classes + '{float: left; padding-left: '+padding+'px; padding-right:'+padding+'px} ' + selector + '{margin-left: -'+padding+'px; margin-right: -'+padding+'px}' : '';
		if(options.slidesToShow){
			if(el.hasClass('slick-initialized')) el.slick("refresh");
			else $(el).slick(options);
		} else {
			var responsive 	= options.responsive;
			if(responsive == undefined) return;
			var length = Object.keys(responsive).length;
			/*
			jQuery.each( responsive, function( key, value ) { // data-responsive="[{"1":"1"},{"361":"1"},{"480":"2"},{"640":"3"},{"768":"3"},{"992":"4"},{"1200":"4"}]"
				var col = 0;
				var maxWith = 3600;
				var minWith = 0;
				jQuery.each( value , function(size, num) { minWith = size; col = num; });
				if(key+1<length){
					jQuery.each( responsive[key+1], function( size, num) { maxWith = size-1; });
					// padding = options.padding*(maxWith/1200); // padding responsive
				}
				style += ' @media (min-width: '+minWith+'px) and (max-width: '+maxWith+'px) {'+classes+'{padding-left: '+padding+'px; padding-right:'+padding+'px; width: '+(Math.floor((10/col) * 100000000000) / 10000000000)+'%} '+classes+':nth-child('+col+'n+1){clear: left;}}';
			});
			*/
			$.each( responsive, function( key, value ) { // data-responsive="[{"1":"1"},{"361":"1"},{"480":"2"},{"640":"3"},{"768":"3"},{"992":"4"},{"1200":"4"}]"
				var col = 0;
				var maxWith = 0;
				var minWith = 0;
				$.each( value , function(size, num) { minWith = parseInt(size) + 1; col = num;});
				if(key+2<length){
					$.each( responsive[key+1], function( size, num) { maxWith = size; col = num;});
					// padding = options.padding*(maxWith/1200); // padding responsive
					style += ' @media (min-width: '+minWith+'px) and (max-width: '+maxWith+'px)';
				} else { 
					if(key+2 == length) return; // don't use key = length - 1;
					$.each( responsive[key], function( size, num) { maxWith = size; col = num;});
					style += ' @media (min-width: '+maxWith+'px)';
				}
				style += ' {'+selector + '{margin-left: -'+padding+'px; margin-right: -'+padding+'px}'+classes+'{padding-left: '+padding+'px; padding-right:'+padding+'px; width: '+(Math.floor((10/col) * 100000000000) / 10000000000)+'%} '+classes+':nth-child('+col+'n+1){clear: left;}}';
			});

			// $.each( responsive, function( key, value ) { // data-responsive="[{"col":"1","min":1,"max":360},{"col":"2","min":361,"max":479},{"col":"3","min":480,"max":639},{"col":"3","min":640,"max":767},{"col":"4","min":768,"max":991},{"col":"4","min":992,"max":1199},{"col":"4","min":1200,"max":3600}]"
			// 	style += ' @media (min-width: '+value.min+'px) and (max-width: '+value.max+'px) {'+classes+'{padding: 0 '+padding+'px; width: '+(Math.floor((10/value.col) * 100000000000) / 10000000000)+'%} '+classes+':nth-child('+value.col+'n+1){clear: left;}}';
			// });
		}

		$('head').append('<style type="text/css">'+style+'</style>'); // return '<style type="text/css">'+style+'</style>';
	};

	/* Timer */
	(function ($) {
		"use strict";
		$.fn.timer = function (options) {
			var defaults = {
				classes  	 : '.countdown',
				layout	 	 : '<span class="day">%%D%%</span><span class class="colon">:</span><span class="hour">%%H%%</span><span class="colon">:</span><span class="min">%%M%%</span><span class="colon">:</span><span class="sec">%%S%%</span>',
				layoutcaption: '<div class="timer-box"><span class="day">%%D%%</span><span class="title">Days</span></div><div class="timer-box"><span class="hour">%%H%%</span><span class="title">Hrs</span></div><div class="timer-box"><span class="min">%%M%%</span><span class="title">Mins</span></div><div class="timer-box"><span class="sec">%%S%%</span><span class="title">Secs</span></div>',
				leadingZero	 : true,
				countStepper : -1, // s: -1 // min: -60 // hour: -3600
				timeout	 	 : '<span class="timeout">Time out!</span>',
			};

			var settings = $.extend(defaults, options);
			var layout			 = settings.layout;
			var layoutcaption	 = settings.layoutcaption;
			var leadingZero 	 = settings.leadingZero;
			var countStepper 	 = settings.countStepper;
			var setTimeOutPeriod = (Math.abs(countStepper)-1)*1000 + 990;
			var timeout 		 = settings.timeout;

			var methods = {
				init : function() {
					return this.each(function() {
						var $countdown 	= $(settings.classes, $(this));
						if( $countdown.length )methods.timerLoad($countdown);
					});
				},
				
				timerLoad: function(el){
					var gsecs = el.data('timer');
					if(isNaN(gsecs)){
						var start = Date.parse(new Date());
						var end = Date.parse(gsecs);
						gsecs  = (end - start)/1000;	
					}
					if(gsecs > 0 ){
						methods.CountBack(el, gsecs);
					}
				},

				calcage: function (secs, num1, num2) {
					var s = ((Math.floor(secs/num1)%num2)).toString();
					if (leadingZero && s.length < 2) s = "0" + s;
					return "<b>" + s + "</b>";
				},

				CountBack: function (el, secs) {
					if (secs < 0) {
						el.html(timeout);
						return;
					}
					if(el.hasClass('caption')){
						var timerStr = layoutcaption.replace(/%%D%%/g, methods.calcage(secs,86400,100000));
					}else {
						var timerStr = layout.replace(/%%D%%/g, methods.calcage(secs,86400,100000));					
					}
					timerStr = timerStr.replace(/%%H%%/g, methods.calcage(secs,3600,24));
					timerStr = timerStr.replace(/%%M%%/g, methods.calcage(secs,60,60));
					timerStr = timerStr.replace(/%%S%%/g, methods.calcage(secs,1,60));
					el.html(timerStr);
					setTimeout(function(){ methods.CountBack(el, (secs+countStepper))}, setTimeOutPeriod);
				},

			};

			if (methods[options]) { // $("#element").pluginName('methodName', 'arg1', 'arg2');
				return methods[options].apply(this, Array.prototype.slice.call(arguments, 1));
			} else if (typeof options === 'object' || !options) { // $("#element").pluginName({ option: 1, option:2 });
				return methods.init.apply(this);
			} else {
				$.error('Method "' + method + '" does not exist in timer plugin!');
			}
		}

		$(document).ready(function($) {
			if (typeof alo_timer_layoutcaption != 'undefined'){
				$('.alo-count-down').not('.exception').timer({
					classes			: '.countdown',
					layout			: alo_timer_layout, 
					layoutcaption	: alo_timer_layoutcaption, 
					timeout 		: alo_timer_timeout
				});
			} else {
				$('.alo-count-down').not('.exception').timer({classes : '.countdown'});			
			}
		});
	})($);
	/* End Timer */

	$(document).ready(function($) {

		 var specialOffer = $('#header-offer');
		 specialOffer.find('.header-offer-close').click(function() {
		 	specialOffer.slideUp('slow');
		 });

		$("*[class^='home-slider']").each(function() { // home-slider
			magicproduct($(this));
		});

		// Realated + Upsell + Crosssell
		var related = $('body.catalog-product-view .products-related .product-items');
		if(related.length) magicproduct(related);
		var upsell = $('body.catalog-product-view .products-upsell .product-items');
		if(upsell.length) magicproduct(upsell);
		var crosssell = $('body.checkout-cart-index .products-crosssell .product-items');
		if(crosssell.length) magicproduct(crosssell);

		// End Realated + Upsell + Crosssell

		// add Js click cart 
	  	var $toggleTab  = $('.toggle-tab');
	    var $toggleContent  = $('.toggle-content');
	    $(window).click(function(event) {
	        if (!$toggleContent.is(event.target) && $toggleContent.has(event.target).length === 0) 
	        {
	            $toggleTab.each(function() {
	                if($(this).hasClass('outside-close')){
	                    $(this).parent().removeClass('toggle-visible').find('.toggle-content').removeClass('visible');
	                }
	            });
	        }
	    });
	    $toggleTab.click(function(event){
	        event.stopPropagation();
	        $(this).parent().toggleClass('toggle-visible').find('.toggle-content').toggleClass('visible');
	    });
	    var $closeTab = $('.closetab .btn-close');
	    $closeTab.click(function() {
	        $(this).closest('.alo-toggle').removeClass('toggle-visible').find('.dropdown-switcher').removeClass('visible');
	    });
		// add click map

		var $toggleTabMap  = $('.onclick-map');

		$toggleTabMap.click(function(){
			$(this).parent().toggleClass('toggle-visible').find('.toggle-content').toggleClass('visible');
		});
		var $closeMap = $('.onmap .btn-close');
		$closeMap.click(function() {
			$(this).closest('.tool-map').removeClass('toggle-visible').find('.toggle-content-close').removeClass('visible');
		});

		(function($){
			var selector= ".hot-categories .slide-inner";
			var options = $(".hot-categories .slide-inner").data();
			var padding = '10';
			//var autoplay = 'false';
			var classes = '.products-grid-categories .item';
			$('head').append('<style type="text/css">' + classes + '{float: left; padding-left: '+padding+'px; padding-right:'+padding+'px} ' + selector + '{margin-left: -'+padding+'px; margin-right: -'+padding+'px}</style>');
			var slider = $(".hot-categories .slide-inner .main-content");
			if(slider.hasClass('slick-initialized')) slider.slick("refresh");
			else{
				slider.slick(options);
			}
		})($);

	    function _increaseJnit(){
			$('.main').on("click", '.alo_qty_dec', function(){
			    var input = $(this).closest('.custom-qty').find('input');
		        var value  = parseInt(input.val());
		        if(value) input.val(value-1);
			});
		    $('.main').on("click", '.alo_qty_inc', function(){
		        var input = $(this).closest('.custom-qty').find('input');
		        var value  = parseInt(input.val());
		        input.val(value+1);
		    });			    	
	    }

	    function _goTopJnit(){
			var $backtotop = $('#backtotop');
			$backtotop.hide();
			var height =  $(document).height();
			$(window).scroll(function () {
				if ($(this).scrollTop() > height/10) {
					$backtotop.fadeIn();
				} else {
					$backtotop.fadeOut();
				}
			});
			$backtotop.click(function () {
				$('body,html').animate({
					scrollTop: 0
				}, 800);
				return false;
			});
	    }

	    function _sktickyCartJnit(){
	    	var topmenu  	 = $('.magicmenu')
            var menuHeight 	 = topmenu.height()/2;
            var postionTop 	 = topmenu.offset().top + menuHeight;
            var headerHeight = $('header').height();
            var minicart 	 = $('.minicart-wrapper');
            var minicartParent = minicart.parent();
            $(window).scroll(function () {
                var postion = $(this).scrollTop();
                if (postion > postionTop ){
                    $('.magicmenu .nav-desktop').append(minicart);
                } else {
                	('.magicmenu .nav-desktop')
                	minicartParent.prepend($('.magicmenu .nav-desktop').find('.minicart-wrapper'))
                }
            });
	    }

		function _elevatorJnit(){
			/* elevator click*/ 
			var $megashop = $('.megashop');
			var length = $megashop.length;
			$megashop.each(function(index, el) {
				var elevator = $(this).find('.floor-elevator');
				elevator.attr('id', 'elevator-' +index);
				var bntUp 	= elevator.find('.btn-elevator.up');
				var bntDown = elevator.find('.btn-elevator.down');
				bntUp.attr('href', '#elevator-' + (index-1));
				bntDown.attr('href', '#elevator-' +(index+1));
				if(!index) bntUp.addClass('disabled');
				if(index == length-1) bntDown.addClass('disabled');
				elevator.find('.btn-elevator').click(function(e) {
					 e.preventDefault();
					var target = this.hash;
					if($(document).find(target).length <=0){
						return false;
					}
					var $target = $(target);
					$('html, body').stop().animate({
						'scrollTop': $target.offset().top-50
					}, 500);
					return false;
				});
			});
		}

	    // add equalheight category
	    $.fn.equalHeights = function() {
	        var maxHeight = 0,
	            $this = $(this);

	        $this.each( function() {
	        	$(this).height('auto');
	            var height = $(this).innerHeight();

	            if ( height > maxHeight ) { maxHeight = height; }
	        });

	        return $this.css('height', maxHeight);
	    };

	    // add equalheight category
		function _equalHeightJnit(selector){
			if(selector === undefined) selector = '.category-products.products-grid .items';
		    $(selector).each(function(){
		        var $this = $(this),
		            target = $this.find('>.item');
		        $this.find(target).equalHeights();
		    });
		}

		function _zoomJnit(){
			if( $(window).width() < 768 ) return;
		    var loaded = false;
		    $('.product.media .gallery-placeholder').bind("DOMSubtreeModified",function(){
		        $('.product.media .fotorama').on('fotorama:ready', function (e, fotorama, extra) {
		            loaded = false;
		            $('.product.media .fotorama').on('fotorama:load', function (e, fotorama, extra) {
		                if(!loaded){
		                    $('.product.media .fotorama__stage .fotorama__active').addClass('zoomed').zoom({
		                        touch:false
		                    });
		                    loaded = true;
		                }
		            });
		            $('.product.media .fotorama').on('fotorama:showend', function (e, fotorama, extra) {
		                $('.product.media .fotorama__stage .fotorama__active').not('.zoomed').addClass('zoomed').zoom({
		                    touch:false
		                });
		            });
		        });
		    });
		}

		function _qsJnit(){
			var obj = arguments[0];
			var _qsModalContent = '<div class="content-quickview">quickview placeholder</div>';
			if(!$('#modals_quickview').length){
				$(document.body).append('<div id="modals_quickview" style="display:none">' + _qsModalContent + '</div>');
			}
			var _qsModal = $('#modals_quickview .content-quickview');
			var quickajax= function(url){
				if(_qsModal.length) $('#modals_quickview').html(_qsModalContent);
				_qsModal.trigger('contentUpdated');
                $.ajax({
                    url:url,
                    type:'POST',
                    showLoader: true,
                    cache:false,   
                    success:function(data){
                    	var $result = $(data);
                    	var id = $result.find('input[name=product]').val();
                    	var html = data.replace(/data-product-id/g,"data-quickview-product-id");
                    	html = html.replace(/data-role=priceBox/g,"data-role=priceBox-" + id);
                    	// html = html.replace(/data-role="priceBox"/g,'data-role="priceBox-' + id + '"');
                    	// html = html.replace(/priceBoxes/g,'priceBoxes' + id);
                    	// html = html.replace(/product-price/g,'product-price-abv' + id);
                    	_qsModal.html(html);
			            modal({
			                type: 'popup',
			                modalClass: 'modals-quickview',
			                responsive: true, 
			                buttons: false,
			                closed: function(){
			                	$('.modals-quickview').remove();
			                }                       	
			            }, _qsModal);
	                    _qsModal.modal('openModal');
	                    _qsModal.trigger('contentUpdated');
                    }
                });
			}
			if(obj.url){
				quickajax(obj.url)
			} else {
				$(document).on('click', obj.itemClass, function(e) {
					e.preventDefault();
	                quickajax($(this).data('url'))
				});
			}
		}

		_increaseJnit()

		_goTopJnit()

		// _sktickyCartJnit()

		_elevatorJnit()

		$(window).resize(function(){
			_equalHeightJnit();
		});
		$(document).on('swatch.initialized', function() {
			_equalHeightJnit();
		})
		
		_zoomJnit();

		_qsJnit({
			url : '',
			itemClass : '.quickview.autoplay',
		});

		$.fn.quickview 	 = _qsJnit;
		$.fn.equalheight = _equalHeightJnit;

		if($('.bg-parallax').length >0){

			$('.bg-parallax').each(function(){

				$(this).parallax("50%",0.1);

			})  

		}
	
	});

});

