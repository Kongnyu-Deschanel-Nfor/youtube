/* -------------------------------------------------------------------------------- /

	Plugin Name: Go Portfolio - WordPress Responsive Portfolio
	Author: Granth
	Version: 1.7.4

	+----------------------------------------------------+
		TABLE OF CONTENTS
	+----------------------------------------------------+

    [1] SETUP
	[2]	AJAX PAGINATION
	[2.1] LOAD MORE
    [3] SLIDER
    [4] POPUP
    [5] ISOTOPE 
    [6] EFFECTS
	[7] DEEP LINKING		
    [8] OTHERS

/ -------------------------------------------------------------------------------- */

;(function ($, undefined) {
	"use strict";
	
	window.gopojQ = $;
	
	$(function () {

	/* -------------------------------------------------------------------------------- /
		[1]	SETUP
	/ -------------------------------------------------------------------------------- */
		
		/* Detect IE & Chrome */
		var isIE = document.documentMode != undefined && document.documentMode >5 ? document.documentMode : false,
			isChrome = !!window.chrome && !!window.chrome.webstore;
	
		/* Detect touch devices */
		function isTouchDevice() { return 'ontouchstart' in window || navigator.msMaxTouchPoints; };
		var isTouchDevice = isTouchDevice();
		
		/* Detect orintationchange */
		function supportsOrientationchange() { return 'onorientationchange' in window };
		var supportsOrientationchange = supportsOrientationchange();
		
		/* Detect CSS transition support */
		function supportsTransitions() {
			var s = document.createElement('p').style;
		    return 'transition' in s || 'WebkitTransition' in s || 'MozTransition' in s || 'msTransition' in s || 'OTransition' in s;		
		}
		var supportsTransitions = supportsTransitions();
		
		/* Detect retina screens */
		function isRetina() {
			var retinaMediaQuery = '(-webkit-min-device-pixel-ratio: 2),(min--moz-device-pixel-ratio: 2),(-o-min-device-pixel-ratio: 2/1),(min-device-pixel-ratio: 2),(min-resolution: 2dppx)';
			if (window.matchMedia && window.matchMedia(retinaMediaQuery).matches) {
				return true;
			} else {
				return false;
			};
		};
		
		var isRetina = isRetina(),
			retinaImageList = [];
		
		function loadRetinaImages() {
			var $retinaReadyImages = $portfolio.find('.gw-gopf-retina'),
				retinaReadyImageList = [];
							
			$retinaReadyImages.each(function(index, element) {
				retinaReadyImageList.push(this.src);
			});
			
			retinaReadyImageList = $.grep(retinaReadyImageList, function(v, k){
				return $.inArray(v ,retinaReadyImageList) === k;
			});
			
			var ImgsLoadedInterval = setInterval(function(){
				$retinaReadyImages.filter(':not(.gw-gopf-loaded)').each(function(index, element) {
					if (this.naturalWidth>0) {
						$(this).addClass('gw-gopf-loaded');
						this.style.width=this.naturalWidth+'px';
						this.style.height=this.naturalHeight+'px';
					};
				});
				if (!$retinaReadyImages.filter(':not(.gw-gopf-loaded)').length) { 
					clearInterval(ImgsLoadedInterval);
					for (var x=0;x<retinaReadyImageList.length;x++) {
						var RetinaImageFileName=retinaReadyImageList[x].substr(0,retinaReadyImageList[x].lastIndexOf('.')),
							RetinaImageFileExt=retinaReadyImageList[x].substr(retinaReadyImageList[x].lastIndexOf('.'));
						
						if ($.inArray(RetinaImageFileName+'@2x'+RetinaImageFileExt ,retinaImageList)>-1) { 
							retinaImageList.push(RetinaImageFileName+'@2x'+RetinaImageFileExt);
							$retinaReadyImages.each(function(index, element) {
								$(this).removeClass('gw-gopf-retina');
								if (this.src==RetinaImageFileName+RetinaImageFileExt) {
									this.src=RetinaImageFileName+'@2x'+RetinaImageFileExt;
								};
							});
						} else {
							var http = new XMLHttpRequest();
							http.open('HEAD', RetinaImageFileName+'@2x'+RetinaImageFileExt, true);
							http.onreadystatechange = function() {
								if (http.readyState==4 && http.status==200) {
									retinaImageList.push(RetinaImageFileName+'@2x'+RetinaImageFileExt);
									$retinaReadyImages.each(function(index, element) {
										$(this).removeClass('gw-gopf-retina');
										if (this.src==RetinaImageFileName+RetinaImageFileExt) {
											this.src=RetinaImageFileName+'@2x'+RetinaImageFileExt;
										};
									});
								};
							};
							http.send();
						};
					};					
				};
			}, 100);	
		};
			
		var $portfolio=$('.gw-gopf'),
			$portfolioFilter=$portfolio.filter('.gw-gopf-grid-type').find('.gw-gopf-filter'),
			$portfolioPosts=$portfolio.filter('.gw-gopf-grid-type').find('.gw-gopf-posts'),
			$sliders = $portfolio.filter('.gw-gopf-slider-type').find('.gw-gopf-posts');

		/* Add CSS correction for chrome */
		//if (isChrome) $portfolio.addClass('gw-gopf-chrome');

		/* Add 'gw-gopf-touch' class for touch devices */
		if (isTouchDevice) { $portfolio.addClass('gw-gopf-touch'); };
		
		if (isRetina) { loadRetinaImages() };
		
		/* Fix iframe hover */
		if (isIE) { 
			$portfolio.find('.gw-gopf-post iframe').delegate(this, 'mouseenter mouseleave', function (event) {
				var $this = $(this);
				if (event.type == 'mouseenter') {
					$this.closest('.gw-gopf-post').trigger('mouseenter').addClass('gw-gopf-current');
					$this.closest('.gw-gopf-col-wrap').css('zIndex',3);
				} else {
					/* This is not required, just in case
					$this.closest('.gw-gopf-post').trigger('mouseleave').removeClass('gw-gopf-current');
					$this.closest('.gw-gopf-col-wrap').css('zIndex',2);*/
				};
			});
		};		
				
		/* Add show wrappers in sliders */
		$portfolio.filter('.gw-gopf-slider-type').find('.gw-gopf-col-wrap').css({'display' : 'block', 'visibility' : 'visible' });


	/* -------------------------------------------------------------------------------- /
		[2]	AJAX PAGINATION
	/ -------------------------------------------------------------------------------- */

	/* -------------------------------------------------------------------------------- /
		[2.1] LOAD MORE
	/ -------------------------------------------------------------------------------- */
		
		
		$portfolio.on('click', '.gw-gopf-pagination-load-more:not(.gw-gopf-disabled)', function() {
			var $this=$(this),
				$wrapper = $this.closest('.gw-gopf-pagination-wrapper'),
				$currentPortfolio = $this.closest('.gw-gopf');
				$wrapper.data('current-page', $wrapper.data('current-page')+1);
			$.ajax({  
				type: 'post', 
				url: gw_go_portfolio_settings.ajaxurl,
				data: $.param({ 
					action : 'go_portfolio_ajax_load_portfolio', 
					portfolio_id : $currentPortfolio.parent().attr('id').split('gw_go_portfolio_')[1],
					current_page : $wrapper.data('current-page'),
					current_id : $wrapper.data('current-id'),
					loaded_ids : $wrapper.data('loaded'),
					taxonomy : $wrapper.data('tax'),
					term_slug : $wrapper.data('term'),
					post_per_page : $wrapper.data('posts-per-page')
				}),
				beforeSend: function () {
					$this.html($this.data('modified'));
					$this.addClass('gw-gopf-disabled');
				}
			}).always(function() {
				$this.html($this.data('original'));
				$this.removeClass('gw-gopf-disabled');
				if (parseInt($wrapper.data('current-page')) == parseInt($wrapper.data('pages'))) { 
					$wrapper.stop().fadeTo(550, 0, function() { 
						$wrapper.stop().slideUp(); 
					});
				};
			}).fail(function(jqXHR, textStatus) {
				//if (textStatus!='abort') { alert('Ajax error!'); };
			}).done(function(data) {
				var $ajaxResponse=$('<div />', { 'class':'ajax-response', 'html' : data }),
					$ajaxResult=$ajaxResponse.find('.gw-gopf-posts'),
					$newPosts=$ajaxResponse.find('.gw-gopf-col-wrap'),
					newPostsLength=$newPosts.length;
					var currentPostsIds = ($currentPortfolio.find('.gw-gopf-pagination-wrapper').data('loaded')+'').split(',');
					currentPostsIds = $.grep(currentPostsIds,function(n){ return(n) });						
					if ($ajaxResponse.find('.gw-gopf-pagination-wrapper').length) {
						var loadedPostsIds = ($ajaxResponse.find('.gw-gopf-pagination-wrapper').data('loaded')+'').split(',');
						loadedPostsIds = $.grep(loadedPostsIds,function(n){ return(n) });
						$currentPortfolio.find('.gw-gopf-pagination-wrapper').data('loaded', loadedPostsIds.concat(currentPostsIds).join(','));
					}
					var images = $newPosts.find('.gw-gopf-post-media-wrap img:not(.gw-gopf-loaded)').length;
					var $imgelems = $newPosts.find('.gw-gopf-post-media-wrap img:not(.gw-gopf-loaded)');
					var loaded=0;
					var interval = setInterval(function(){
						if (loaded==images) {
							$currentPortfolio.removeClass('gw-gopf-isotope-ready').addClass('gw-gopf-isotope-new-added').find('.gw-gopf-posts').GWisotope( 'insert', $newPosts, function() { 
								$currentPortfolio.addClass('gw-gopf-isotope-ready');
								$currentPortfolio.removeClass('gw-gopf-isotope-new-added');
								$currentPortfolio.find('.gw-gopf-cats .gw-gopf-hidden').each(function(index, element) {
									var $this = $(this);
									if ($newPosts.filter('[data-filter~="'+$this.data('filter')+'"]').length) { $this.removeClass('gw-gopf-hidden'); };
								});
								if (isRetina) { loadRetinaImages() };
								DoMagnificPopup();
								updatePopupGallery($currentPortfolio.data('id'));
							});
							var $currentFilter = $currentPortfolio.find('.gw-gopf-filter .gw-gopf-current');
							if ($currentFilter.data('filter')!=undefined) {
								$currentPortfolio.find('.gw-gopf-posts').GWisotope('reLayout', function(){ 
									$currentPortfolio.find('.gw-gopf-posts').callIsotope('[data-filter~="'+$currentFilter.data('filter')+'"]');
									setTimeout(function(){ 
										$currentPortfolio.addClass('gw-gopf-isotope-ready');						
									},5);
								});
							};
							clearInterval(interval);							
						} else {
							$imgelems.filter(':not(.gw-gopf-loaded)').each(function(index, element) {
								if (this.naturalWidth>0) {
									$(this).addClass('gw-gopf-loaded');
									loaded++;
								}
								this.onerror = function() {
									
									$(this).addClass('gw-gopf-loaded');
									$(this).closest('.gw-gopf-post-media-wrap').css('display','none');
									loaded++;
								};								
							});
						}
					}, 100);

			});
			return false;
		});			
				
	/* -------------------------------------------------------------------------------- /
		[3]	SLIDER - CarouFredSel Slider
	/ -------------------------------------------------------------------------------- */		
		
		if ($.fn.carouFredSel && $sliders.length) {
			
			var $scrollOverlay = $('<div class="gw-gopf-posts-wrap-inner-overlay">').appendTo('.gw-gopf-posts-wrap-inner').css({
				'position' : 'absolute',
				'top' : 0,
				'z-index' : '2',
				'width' : '100%',
				'height' : '100%',
				'display' : 'none'
			});			
				
			$sliders.each(function(index, element) {
				var $this=$(this);
				if ($this.closest('.gw-gopf').hasClass('gw-gopf-rtl')) {
					$this.find('.gw-gopf-col-wrap').each(function(index, element) {
						$(this).prependTo($this);
					});
				};
				var startIndex = 0;
				if ($this.closest('.gw-gopf').hasClass('gw-gopf-rtl')) {
					startIndex = ($this.find('.gw-gopf-col-wrap').length-$this.data('col'))>0 ? $this.find('.gw-gopf-col-wrap').length-$this.data('col') : 0;
				}
				$this.addClass('gw-gopf-slider').data('sliderDefaults', {				
					responsive : true,
					height : 'variable',
					width : '100%',				
					next : {
						button : $this.closest('.gw-gopf-posts-wrap').find('.gw-gopf-slider-controls-wrap').find('.gw-gopf-control-next'),
						onAfter : function(data) {
							$this.find('.gw-gopf-col-wrap').css('pointer-events','auto');
							if ($this.css('letterSpacing')=='30px' || $this.css('letterSpacing')=='20px') {
								var id=0;
							} else if ($this.css('letterSpacing')=='10px') {
								var id=$this.data('col') == 1 ? 0 : 1;
							} else {
								var id=$this.data('col')-1;						
							};						

							$this.closest('.gw-gopf-posts-wrap').css('overflow', 'visible');
							var items = $this.triggerHandler('currentVisible');
							$this.find('.gw-gopf-col-wrap').css({ 
								'opacity' : 0,
								'z-index' : 0,
								'position': 'absolute',
								'visibility' : 'hidden',
								'left': 0						
							}).eq(id).css({
								'position' : 'relative',
								'left' : '0',
								'z-index' : 'auto'
							});
							items.each(function(index, element) {
								$(element).css({ 'visibility' : 'visible', 'opacity' : 1, 'position': 'relative', 'z-index' : 'auto' });
							});
							$this.closest('.gw-gopf-posts-wrap-inner').find('.gw-gopf-posts-wrap-inner-overlay').hide();								
						}
					},
					prev : {
						button : $this.closest('.gw-gopf-posts-wrap').find('.gw-gopf-slider-controls-wrap').find('.gw-gopf-control-prev'),
						onAfter : function(data) {
							$this.find('.gw-gopf-col-wrap').css('pointer-events','auto');
							if ($this.css('letterSpacing')=='30px' || $this.css('letterSpacing')=='20px') {
								var id=1;
							} else if ($this.css('letterSpacing')=='10px') {
								var id=2;
							} else {
								var id=$this.data('col');						
							};								
							$this.closest('.gw-gopf-posts-wrap').css('overflow', 'visible');
							var items = $this.triggerHandler('currentVisible');
							$this.find('.gw-gopf-col-wrap').css({ 
								'opacity' : 0,
								'z-index' : 0,
								'position': 'absolute',
								'visibility' : 'hidden',
								'left': 0
							}).eq(id).css({
								'position' : 'relative',
								'left': '0',
								'z-index' : 'auto'
							});						
							items.each(function(index, element) {
								$(element).css({ 'visibility' : 'visible', 'opacity' : 1, 'position': 'relative', 'z-index' : 'auto' });
							});	
							$this.closest('.gw-gopf-posts-wrap-inner').find('.gw-gopf-posts-wrap-inner-overlay').hide();
						}
					},
					scroll : {
						queue : true,
						items: 1,
						onBefore : function(data) {
							var items = $this.triggerHandler('currentVisible');
							$this.find('.gw-gopf-col-wrap').css('pointer-events','none');
							$portfolio.find('.gw-gopf-current.gw-gopf-post').removeClass('gw-gopf-current').trigger('tapOff');
							
							if ($this.css('letterSpacing')=='30px' || $this.css('letterSpacing')=='20px') {
								var id=1;
							} else if ($this.css('letterSpacing')=='10px') {
								var id=$this.data('col') == 1 ? 1 : 2;
							} else {
								var id=$this.data('col');						
							};

							$this.closest('.gw-gopf-posts-wrap').css('overflow', 'hidden');
							var items = $this.triggerHandler('currentVisible');						
							$this.find('.gw-gopf-col-wrap').css({ 
								'visibility' : 'hidden',
								'position': 'relative',
								'opacity' : 0,
								'zIndex' :	0
							}).eq(id).css({
								'position' : 'absolute',
								'left' : $this.closest('.caroufredsel_wrapper').width()+'px',
								'z-index' : '200',
								'visibility' : 'visible', 
								'opacity' : 1,
								'zIndex' : 'auto'
							});
							items.each(function(index, element) {
								$(element).css({ 'visibility' : 'visible', 'opacity' : 1, 'position': 'relative', 'z-index' : 'auto' });
							});	

							$this.closest('.gw-gopf-posts-wrap-inner').find('.gw-gopf-posts-wrap-inner-overlay').show();
							$this.trigger('resume');
						},
						onAfter : function(data) {
							var direction = $this.triggerHandler('configuration', 'direction');
							$this.find('.gw-gopf-col-wrap').css('pointer-events','auto');
							
							/* Scrolling left */
							if (direction=='left') {
								if ($this.css('letterSpacing')=='30px' || $this.css('letterSpacing')=='20px') {
									var id=0;
								} else if ($this.css('letterSpacing')=='10px') {
									var id=1;
								} else {
									var id=$this.data('col')-1;						
								};							
								
								$this.closest('.gw-gopf-posts-wrap').css('overflow', 'visible');
								var items = $this.triggerHandler('currentVisible');
								$this.find('.gw-gopf-col-wrap').css({ 
									'opacity' : 0,
									'z-index' : 0,
									'position': 'absolute',
									'visibility' : 'hidden',
									'left': 0							
								}).eq(id).css({
									'position' : 'relative',
									'left' : '0',
									'z-index' : 'auto'
								});
								items.each(function(index, element) {
									$(element).css({ 'visibility' : 'visible', 'opacity' : 1, 'position': 'relative', 'z-index' : 'auto' });
								});	
								$this.closest('.gw-gopf-posts-wrap-inner').find('.gw-gopf-posts-wrap-inner-overlay').hide();
							}
							
							/* Scrolling right */
							if (direction=='right') {
								if ($this.css('letterSpacing')=='30px' || $this.css('letterSpacing')=='20px') {
									var id=1;
								} else if ($this.css('letterSpacing')=='10px') {
									var id=2;
								} else {
									var id=$this.data('col');						
								}								
	
								$this.closest('.gw-gopf-posts-wrap').css('overflow', 'visible');
								var items = $this.triggerHandler('currentVisible');
								$this.find('.gw-gopf-col-wrap').css({ 
									'opacity' : 0,
									'z-index' : 0,
									'position': 'absolute',
									'visibility' : 'hidden',
									'left': 0														
								}).eq(id).css({
									'position' : 'relative',
									'left' : '0',
									'z-index' : 'auto'
								});					
								items.each(function(index, element) {
									$(element).css({ 'visibility' : 'visible', 'opacity' : 1, 'position': 'relative', 'z-index' : 'auto' });
								});	
								$this.closest('.gw-gopf-posts-wrap-inner').find('.gw-gopf-posts-wrap-inner-overlay').hide();
							} 	
						}							
					},
					swipe : {
						onMouse : true
					},	
					items :	{
						height : 'variable',
						visible : {
							min : 1,
							max : $this.data('col')
						},
						start: startIndex
						
					},								
				    onCreate : function (data) {
						$this.closest('.gw-gopf-posts-wrap').css('overflow', 'visible');
						var items = $this.triggerHandler('currentVisible');
						$this.find('.gw-gopf-col-wrap').css({ 
							'visibility' : 'hidden', 
							'position': 'absolute',
							'opacity' : 0
						});
						items.each(function(index, element) {
							$(element).css({ 'visibility' : 'visible', 'opacity' : 1, 'position': 'relative' });
						});

				        $(window).on('resize', function(){
							var paused = $this.triggerHandler('isPaused');
							if ($this.css('letterSpacing')=='30px' || $this.css('letterSpacing')=='20px') {
								$this.trigger('configuration', ['items.visible', 1]);			
							} else if ($this.css('letterSpacing')=='10px') {
								$this.trigger('configuration', ['items.visible', $this.data('col')==1 ? 1 : 2 ]);						
							} else {
								$this.trigger('configuration', ['items.visible', $this.data('col')]);
										
							};
							if (paused) { $this.trigger('pause', true); }
							
							var items = $this.triggerHandler('currentVisible');
							$this.find('.gw-gopf-col-wrap').css({ 
								'visibility' : 'hidden', 
								'position': 'absolute',
								'opacity' : 0
							});
							items.each(function(index, element) {
								$(element).css({ 'visibility' : 'visible', 'opacity' : 1, 'position': 'relative' });
							});						
				        }).resize();
				    }
				});
				
				/* Call slider */
				$this.carouFredSel($.extend($this.data('slider'), $this.data('sliderDefaults')));
			});

		};
		
	/* -------------------------------------------------------------------------------- /
		[4]	POPUP - Magnific Popup
	/ -------------------------------------------------------------------------------- */		

		var portfolioGallery = [], portfolioGalleryO = [], mfpOpened=false;
		function DoMagnificPopup() {
			
			if ($.fn.magnificPopup) {
				$portfolio.each(function(index, element) {
					var $this = $(this);
					if ( $this.data('lbenabled') ) {
						$this.find('.gw-gopf-magnific-popup[href!=""], .gw-gopf-magnific-popup-html[href!=""]').attr('href','#');
						portfolioGallery[$this.data('id')] = $this.find('.gw-gopf-magnific-popup[data-mfp-src!="#"][data-mfp-src!=""], .gw-gopf-magnific-popup-html[data-mfp-src!="#"][data-mfp-src!=""]').magnificPopup({
							type : 'image',
							closeOnContentClick : true,
							removalDelay : 300,
							mainClass : 'my-mfp-slide-bottom',
							closeMarkup : '<a title="%title%" class="gw-gopf-mfp-close"></a>',
							titleSrc: 'title',
							gallery : {
								enabled : $this.data('lbgallery'),
								arrowMarkup: '<a title="%title%" class="gw-gopf-mfp-arrow mfp-arrow mfp-arrow-%dir%"></a>'
							},
							image : {
								markup: '<div class="mfp-figure">'+
										'<div class="mfp-close"></div>'+
										'<div class="mfp-img"></div>'+
										'<div class="mfp-bottom-bar">'+
										  '<div class="gw-gopf-mfp-title mfp-title"></div>'+
										  '<div class="gw-gopf-mfp-counter mfp-counter"></div>'+
										'</div>'+
									  '</div>'
							},
							iframe : {
								patterns : {
									vimeo : {
										index: 'vimeo.com/',
										id: '/',
										src: '//player.vimeo.com/video/%id%&amp;autoplay=1'
									},
									dailymotion : {
										index : 'dailymotion.com/',
										id : '/',
										src :'//dailymotion.com/embed/video/%id%?autoPlay=1'
									},
									metacafe : {
										index : 'metacafe.com/',
										id : '/',
										src : 'http://www.metacafe.com/embed/%id%?ap=1'
									},
									soundcloud : {
										index : 'soundcloud.com',
										id : null,
										src : '%id%'
									},
									mixcloud : {
										index : 'mixcloud.com',
										id: null,
										src: '%id%'
									},
									beatport : {
										index : 'beatport.com',
										id : null,
										src : '%id%'
									}																										
								},						
								markup : '<div class="mfp-iframe-scaler">'+
										'<div class="mfp-close"></div>'+
										'<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>'+
										'<div class="mfp-bottom-bar" style="margin-top:4px;"><div class="gw-gopf-mfp-title mfp-title"></div><div class="gw-gopf-mfp-counter mfp-counter"></div></div>'+
										'</div>'
							},					
							callbacks : {
								elementParse : function(item) {
									if ($(item.el).hasClass('gw-gopf-magnific-popup-html')) {
										item.type = 'iframe' 
									};
								},
								change : function() {
									var $currentItem = $(this.currItem.el);
									if ($currentItem.hasClass('gw-gopf-magnific-popup-html')) {									
										setTimeout(function(){ 
											$('.mfp-title').html($currentItem.attr('title'))						
										},5);									
									}
									
									/* Deep linking */
									if ($this.data('deepLinking')) {
										mfpOpened = $(this.currItem.el);
										if(history.pushState) {
											location.hash = '#mpf-popup@'+$currentItem.attr('data-mfp-src')+'|'+$currentItem.data('id').split('_')[0]+'|'+$currentItem.data('id').split('_')[1];
											if (history.state==$currentItem.data('id')) {
												history.pushState($currentItem.data('id'), null, window.location.href.replace(window.location.origin,'').split('#')[0]+'#mpf-popup@'+$currentItem.attr('href')+'|'+$currentItem.data('id').split('_')[0]+'|'+$currentItem.data('id').split('_')[1]);
											};
										} else {
											location.hash = '#mpf-popup@'+$currentItem.attr('href')+'|'+$currentItem.data('id').split('_')[0]+'|'+$currentItem.data('id').split('_')[1];
										};
									};
	
									var forcedHeight = $(this.currItem.el).data('height');
									
									if (forcedHeight != undefined) {	
										setTimeout(function(){ 
											$('.mfp-iframe-scaler').css({
												'paddingTop' : 0,
												'display' : 'table-cell',
												'verticalAlign' : 'middle',
												'height' : forcedHeight
											});						
										},5);
									} else {
										setTimeout(function(){ 
											$('.mfp-iframe-scaler').css({
												'padding-top': '56.25%', 
												'display' : 'block',
												'verticalAlign' : 'baseline',
												'height' : 0
											});						
										},5);
									};
								},
								beforeClose : function() {
									if ($this.data('deepLinking')) {
										if (history.pushState)
											history.pushState('', null, window.location.pathname + window.location.search);
										else {									
											var scrollPosX = document.body.scrollTop;
											var scrollPosY = document.body.scrollLeft;
											window.location.hash = '';
											document.body.scrollTop = scrollPosX;
											document.body.scrollLeft = scrollPosY;
										};
									};
									if ($(this.currItem.el).hasClass('gw-gopf-magnific-popup-html')) {
										$('.mfp-wrap').css('display','none');
									};									
									mfpOpened=false;
								},
								afterClose : function() {
									if (this.items[this.index].type == 'iframe') {
										/* Firefox bug fix - force to redraw thumbnail */
										var timer=setInterval(function() {
										if ($('.mfp-bg').length==0) {
											clearInterval(timer);
											$portfolio.find('.gw-gopf-post').css('opacity','0.99');
											setTimeout(function(){ $portfolio.find('.gw-gopf-post').css('opacity','1'); },20)
										}
										},50);
									};
								}						
							}
						});
					} else {
						$this.delegate('.gw-gopf-magnific-popup, .gw-gopf-magnific-popup-html','click', function(e) {
							e.preventDefault();
						});
					};

				});
			};
		};
		
		DoMagnificPopup();
		
		function updatePopupGallery( portfolioId ) {
			if ($portfolio.filter('[data-id="'+portfolioId+'"]').data('lbenabled')) {
				var $galleryItems = $portfolio.filter('[data-id="'+portfolioId+'"]').find('.gw-gopf-magnific-popup[data-mfp-src!="#"][data-mfp-src!=""], .gw-gopf-magnific-popup-html[data-mfp-src!="#"][data-mfp-src!=""]');
				portfolioGallery[portfolioId].splice(0, $galleryItems.length);
				$galleryItems.each(function(index, element) {
					if (!$(this).closest('.gw-gopf-isotope-hidden').length) {
					   portfolioGallery[portfolioId].push(this);
					};
				});
			};
		};
		
		$portfolio.find('.gw-gopf-magnific-popup, .gw-gopf-magnific-popup-html').on('mfpOpen', function(e) {
			if ($.fn.carouFredSel && $sliders.length) {
				$portfolio.find('.gw-gopf-posts-wrap-inner-overlay').show();
				setTimeout(function() {
					$sliders.each(function(index, element) {
						var $this = $(this);
						$this.trigger('pause', true);
					});
				}, 10);
			}
		});

		$portfolio.find('.gw-gopf-magnific-popup, .gw-gopf-magnific-popup-html').on('mfpAfterClose', function(e) {
			if ($.fn.carouFredSel && $sliders.length) {
				$portfolio.find('.gw-gopf-posts-wrap-inner-overlay').hide();
				setTimeout(function() {
					$sliders.each(function(index, element) {
						var $this = $(this);
						$this.trigger('resume');
					});
				}, 10);
			}
		});
		
	/* -------------------------------------------------------------------------------- /
		[5]	ISOTOPE 
	/ -------------------------------------------------------------------------------- */		
		
		/* Isotope */

		if ($.fn.GWisotope) {
			
			/* Call Isotope plugin */
			$.fn.callIsotope = function ( filter, noImage ) {
				var $this = $(this);
				
				$this.data( 'noImage', noImage ? true : false );
				
				filter = $this.closest('.gw-gopf').data('filter-type')==undefined || $this.closest('.gw-gopf').data('filter-type')=='opacity' ? '*' : filter;
				$this.GWisotope({
					filter : filter,
					transformsEnabled: $this.closest('.gw-gopf').data('transenabled') ? true : false,
					animationEngine: isIE && isIE == 8 ? 'css' : 'best-available',
					containerClass : 'gw-gopf-isotope',
					hiddenClass : 'gw-gopf-isotope-hidden',
					itemClass : 'gw-gopf-isotope-item',
					layoutMode : $this.closest( '.gw-gopf' ).data('layout-mode') || 'masonry',
					onLayout: function( $elems, instance ) {
						if (instance.element.closest('.gw-gopf').data('id')!=undefined) {
							updatePopupGallery(instance.element.closest('.gw-gopf').data('id'));
						};
						instance.element.css('marginBottom',0).closest('.gw-gopf-posts-wrap-inner').css('marginTop',0).end().find('.gw-gopf-post-col').css('marginTop',0);
						
						if ( !$this.data( 'noImage' ) ) {
							setTimeout(function() { 							
								imgIsInview();
								loadGalleryImages();
							}, 800 );
						}

					}
				}, 
				function( $elems, instance ) {

					/* auto load on filtering */
					if (instance.element.closest('.gw-gopf').hasClass('gw-gopf-pagination')) {
						var visibleItems = !instance.element.closest('.gw-gopf').hasClass('gw-gopf-filter-opacity') ? instance.$filteredAtoms.length : instance.element.closest('.gw-gopf').find('.gw-gopf-col-wrap').filter(':not(.gw-gopf-disabled)').length;
						var visibleItemsTerm = instance.element.closest('.gw-gopf').find('.gw-gopf-cats .gw-gopf-current:last').data('count');
						var postsCount = instance.element.closest('.gw-gopf').find('.gw-gopf-pagination-wrapper').data('posts');
						var pagesCount = instance.element.closest('.gw-gopf').find('.gw-gopf-pagination-wrapper').data('pages');
						var currentPage = instance.element.closest('.gw-gopf').find('.gw-gopf-pagination-wrapper').data('current-page');
						var currItemsPerPage = instance.element.closest('.gw-gopf').find('.gw-gopf-pagination-wrapper').data('posts-per-page');
						
						var loadsCnt = 0;
						if (visibleItems < currItemsPerPage) {
							if (visibleItemsTerm<currItemsPerPage) {
								loadsCnt = visibleItemsTerm - visibleItems;
							} else {
								loadsCnt = currItemsPerPage - visibleItems;
							}
						} else {
							if (visibleItemsTerm - visibleItems >= currItemsPerPage ) {
								loadsCnt = currItemsPerPage
							} else {
								loadsCnt = visibleItemsTerm % visibleItems;
							}
						}
						instance.element.closest('.gw-gopf').find('.gw-gopf-pagination-wrapper').data('required', loadsCnt);
						
						if (instance.element.closest('.gw-gopf').find('.gw-gopf-cats .gw-gopf-current').data('filter')!=undefined) {
							$(instance.element.closest('.gw-gopf')).find('.gw-gopf-pagination-wrapper').data('tax',  instance.element.closest('.gw-gopf').find('.gw-gopf-filter').data('tax'));
							$(instance.element.closest('.gw-gopf')).find('.gw-gopf-pagination-wrapper').data('term',  instance.element.closest('.gw-gopf').find('.gw-gopf-cats .gw-gopf-current:last').data('filter'));							
						} else {
							$(instance.element.closest('.gw-gopf')).find('.gw-gopf-pagination-wrapper').removeData('tax');
							$(instance.element.closest('.gw-gopf')).find('.gw-gopf-pagination-wrapper').removeData('term');							
						}
						
						if (currItemsPerPage>-1 && visibleItemsTerm>visibleItems && visibleItems<currItemsPerPage) {			
							$(instance.element.closest('.gw-gopf')).find('.gw-gopf-pagination-load-more').trigger('click');
						} 
						if (visibleItemsTerm == visibleItems || postsCount == visibleItems) {
							if (instance.element.closest('.gw-gopf').find('.gw-gopf-pagination-wrapper').is(':visible')) {
								instance.element.closest('.gw-gopf').find('.gw-gopf-pagination-wrapper').stop().fadeTo(550, 0, function() { 
									instance.element.closest('.gw-gopf').find('.gw-gopf-pagination-wrapper').stop().slideUp(); 
								});	
							};
						} else {
							if (!instance.element.closest('.gw-gopf').find('.gw-gopf-pagination-wrapper').is(':visible')) {
								instance.element.closest('.gw-gopf').find('.gw-gopf-pagination-wrapper').stop().fadeTo(550, 1, function() { 
									instance.element.closest('.gw-gopf').find('.gw-gopf-pagination-wrapper').stop().slideDown(); 
								});	
							};
						};
					};
				}				
				);
			};
			
			/* Extend the plugin to hack change column number if required */
			$.extend( $.GWIsotope.prototype, {
				_masonryReset : function() {
					// layout-specific props
					this.masonry = {};
					
					// FIXME shouldn't have to call this again
					this._getSegments();
					
					/* Hack - set col number manually */
					if (this.element.hasClass('gw-gopf-posts')) {
						if (this.element.css('letterSpacing')=='30px' || this.element.css('letterSpacing')=='20px') {
							this.masonry.cols = 1;
						} else if (this.element.css('letterSpacing')=='10px') {
							if (this.element.data('col')!=1) {
								this.masonry.cols = 2;
							} else {
								this.masonry.cols = 1;
							}
						} else {
							this.masonry.cols = this.element.data('col');					
						};	
						var i = this.masonry.cols;

					};
					/* end of Hack */
					
					/* modify margin */
					this.element.css('marginBottom',this.element.closest('.gw-gopf').data('rowspace')).closest('.gw-gopf-posts-wrap-inner').css('marginTop',this.element.closest('.gw-gopf').data('rowspace')*-1).end().find('.gw-gopf-post-col').css('marginTop',this.element.closest('.gw-gopf').data('rowspace'));		
					
					this.masonry.colYs = [];
					while (i--) {
						this.masonry.colYs.push( 0 );
					}
				},
				_masonryGetContainerSize : function() {
				  var containerHeight = Math.max.apply( Math, this.masonry.colYs );
				  containerHeight -= this.element.closest('.gw-gopf').data('rowspace');
				  return { height: containerHeight };
				},
				_fitRowsReset: function() {
					this.fitRows = {
						x: 0,
						y: 0,
						height: 0
					}
					this.element.css('marginBottom',this.element.closest('.gw-gopf').data('rowspace')).closest('.gw-gopf-posts-wrap-inner').css('marginTop',this.element.closest('.gw-gopf').data('rowspace')*-1).end().find('.gw-gopf-post-col').css('marginTop',this.element.closest('.gw-gopf').data('rowspace'));	
				},
				_fitRowsLayout : function( $elems ) {
				  var instance = this,
					  containerWidth = this.element.width()+2, // Add 2px extra to make it goes to the correct line
					  props = this.fitRows;
			
				  $elems.each( function() {
					var $this = $(this),
						atomW = $this.outerWidth(true),
						atomH = $this.outerHeight(true);
			
					if ( props.x !== 0 && atomW + props.x > containerWidth ) {
					  // if this element cannot fit in the current row
					  props.x = 0;
					  props.y = props.height;
					}
			
					// position the atom
					instance._pushPosition( $this, props.x, props.y );
			
					props.height = Math.max( props.y + atomH, props.height );
					props.x += atomW;
			
				  });
				},								
				_fitRowsGetContainerSize: function() {
					return {
						height: this.fitRows.height - this.element.closest('.gw-gopf').data('rowspace')
					}
				}		
			});
			
			$.GWIsotope.prototype._positionAbs = function( x, y ) {
		 		if (this.element.closest('.gw-gopf.gw-gopf-rtl').length) {
					return { right: x, top: y };
				} else {
					return { left: x, top: y };	
				}
			};
			
					
			/* Filter button events */
			$portfolioFilter.delegate('div a', 'click', function(e) {
				var $this=$(this), $parent=$this.closest('span'), filter;
				e.preventDefault();
				
				$parent.addClass('gw-gopf-current').siblings().removeClass('gw-gopf-current');
				if ($parent.data('filter')==undefined) {
					$this.closest('.gw-gopf').find('.gw-gopf-posts').callIsotope('*');
					$this.closest('.gw-gopf').find('.gw-gopf-posts').find('.gw-gopf-col-wrap').removeClass('gw-gopf-disabled');					
				} else {
					$this.closest('.gw-gopf').find('.gw-gopf-posts').callIsotope('[data-filter~="'+$parent.data('filter')+'"]');
					$this.closest('.gw-gopf').find('.gw-gopf-posts').find('.gw-gopf-col-wrap').addClass('gw-gopf-disabled').filter('[data-filter~="'+$parent.data('filter')+'"]').removeClass('gw-gopf-disabled');
				};			
				
			});
			
			
			/* Call Isotope plugin */
			$portfolio.filter('.gw-gopf-grid-type').each(function(index, element) {
				var $this = $(this);
				$this.find('.gw-gopf-posts').callIsotope('*');
				if (!$this.hasClass('gw-gopf-isotope-ready')) { 
					$this.closest('.gw-gopf').addClass('gw-gopf-isotope-ready');
					
				};
			});
			
		};

	/* -------------------------------------------------------------------------------- /
		[6]	EFFECTS
	/ -------------------------------------------------------------------------------- */	

		/* Handle taps on touch devices */
		
		if (isTouchDevice) { 
			$portfolio.delegate('.gw-gopf-post', 'touchstart', function (event) {
				var $this = $(this);
				$portfolio.find('.gw-gopf-current.gw-gopf-post').not($this).removeClass('gw-gopf-current').trigger('tapOff');					
				if ( !$this.hasClass('gw-gopf-current')) {
					$this.addClass('gw-gopf-current');
					$this.trigger('tapOn');
				};
			});

			/* Remove effect when you tap anywhere out */
			$(document).delegate(this, 'touchstart', function (event) {
				if ($(event.target).closest('.gw-gopf-posts-wrap').length) {
					$(event.target).closest('.gw-gopf-posts-wrap').addClass('gw-gopf-touch');
				} else {
					$('.gw-gopf-posts-wrap').removeClass('gw-gopf-touch');
				}
				if (!$(event.target).closest('.gw-gopf-current.gw-gopf-post').length) {
					$portfolio.find('.gw-gopf-current.gw-gopf-post').removeClass('gw-gopf-current').trigger('tapOff');
				};
				if (!$(event.target).closest('.gw-gopf-posts-wrap').length) {
					$portfolio.find('.gw-gopf-current.gw-gopf-post').removeClass('gw-gopf-current').trigger('tapOff');
				};
				
			});			
						
			/* Some trick to re-enable filtering (exluded element fix) */
			$portfolio.delegate('.gw-gopf-cats > span a', 'touchstart', function (event) {
				var $this = $(this);
				$this.off('transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd');
				$this.one('transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd', function() { 
					/* $(this).trigger('click');* - just in case */
				});
				$portfolio.find('.gw-gopf-current.gw-gopf-post').removeClass('gw-gopf-current').trigger('tapOff');
			});

			/* Remove effect - exluded elements correction */
			$('body').delegate('button, input, select, textarea, a, .noSwipe', 'touchstart', function (event) {
				if (!$(event.target).closest('.gw-gopf-current.gw-gopf-post').length) {
					$portfolio.find('.gw-gopf-current.gw-gopf-post').removeClass('gw-gopf-current').trigger('tapOff');
				};
			});

		};
					
		$portfolio.delegate('.gw-gopf-post', 'mouseenter mouseleave tapOn tapOff', function (event) {
			var $this = $(this),
				postHeight = $this.outerHeight(),
				$content = $this.find('.gw-gopf-post-content'),
				contentHeight = $content.outerHeight(),
				$overlayInner = $this.find('.gw-gopf-post-overlay-inner'),
				overlayInnerHeight = $overlayInner.height();
			
			if (event.type == 'mouseenter' || event.type == 'tapOn') {
				if ($this.hasClass('gw-gopf-current') && event.type == 'mouseenter' ) { return false; };
				$(event.target).closest('.gw-gopf').removeClass('gw-gopf-touch');
				$this.find('.gw-gopf-post-overlay').css('height', $this.find('.gw-gopf-post-header').outerHeight());
				$this.find('.gw-gopf-post-content-wrap').show();
			} else if (event.type == 'mouseleave' || event.type == 'tapOff') {
				if ($this.hasClass('gw-gopf-current') && event.type == 'mouseleave' ) { return false; };
				$this.removeClass('gw-gopf-current');
				$(event.target).closest('.gw-gopf').addClass('gw-gopf-touch');
			}
			
			/* Flex Slide Up */
			if ($this.closest('.gw-gopf').hasClass('gw-gopf-style-flex-slide-up')) {
				if (event.type == 'mouseenter' || event.type == 'tapOn') {
					if ( postHeight-contentHeight-overlayInnerHeight <= 0 ) {
						$overlayInner.css('display', 'none');
					} else {
						$overlayInner.css('display', 'inline-block');	
					};
											
					/* Media wrap */
					$this.find('.gw-gopf-post-content-wrap').css({
						'top' : postHeight
					}).off('transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd');
					
					setTimeout(function(){ 
						$this.find('.gw-gopf-post-content-wrap').css({
							'top' : postHeight-contentHeight < 0 ? 0 : postHeight-contentHeight
						});
					}, 1);
					
					/* Overlay */
					$this.find('.gw-gopf-post-overlay').css({
						'height' : postHeight
					}).off('transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd');					

					setTimeout(function(){ 					
						$this.find('.gw-gopf-post-overlay').css({
							'height' : postHeight-contentHeight < 0 ? 0 : postHeight-contentHeight
						}).off('transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd');
					}, 1);											
				};
				
				if (event.type == 'mouseleave' || event.type == 'tapOff') {
					$this.find('.gw-gopf-post-content-wrap').css({
						'top' : supportsTransitions ? postHeight : '100%'
					}).one('transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd', function() { 
						$(this).css({ 'top' : '100%' });
					});
					
					$this.find('.gw-gopf-post-overlay').css({
						'height' : supportsTransitions ? postHeight : '100%'
					}).one('transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd', function() { 
						$(this).css({ 'height' : '100%' });
					});
				};
			};	
			
			/* Flex Slide & Push Up */
			if ($this.closest('.gw-gopf').hasClass('gw-gopf-style-flex-slide-push-up')) {
				if (event.type == 'mouseenter' || event.type == 'tapOn') {
					if ( postHeight-contentHeight-overlayInnerHeight <= 0 ) {
						$overlayInner.css('display', 'none');
					} else {
						$overlayInner.css('display', 'inline-block');	
					};
											
					/* Media wrap */
					$this.find('.gw-gopf-post-content-wrap').css({
						'top' : postHeight
					}).off('transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd');
					
					setTimeout(function(){ 
						$this.find('.gw-gopf-post-content-wrap').css({
							'top' : postHeight-contentHeight < 0 ? 0 : postHeight-contentHeight
						});
					}, 1);
					
					/* Overlay */
					$this.find('.gw-gopf-post-overlay').css({
						'top' : postHeight-contentHeight < 0 ? 100 : contentHeight,
						'height' : postHeight
					}).off('transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd');					

					setTimeout(function(){ 					
						$this.find('.gw-gopf-post-overlay').css({
							'height' : postHeight-contentHeight < 0 ? 0 : postHeight-contentHeight
						}).off('transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd');
					}, 1);
										
					/* Header */
					$this.find('.gw-gopf-post-header').css({
						'marginTop' : (postHeight-contentHeight < 0 ? postHeight : contentHeight)*-1,
						'marginBottom' : postHeight-contentHeight < 0 ? postHeight : contentHeight
					});																
				};
				
				if (event.type == 'mouseleave' || event.type == 'tapOff') {
					$this.find('.gw-gopf-post-content-wrap').css({
						'top' : supportsTransitions ? postHeight : '100%'
					}).one('transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd', function() { 
						$(this).css({ 'top' : '100%' });
					});
					
					$this.find('.gw-gopf-post-overlay').css({
						'top' : 0,
						'height' : supportsTransitions ? postHeight : '100%'
					}).one('transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd', function() { 
						$(this).css({ 'height' : '100%' });
					});
					
					$this.find('.gw-gopf-post-header').css({
						'marginTop' : 0,
						'marginBottom' : 0
					});																
				};
			};
			
			/* Flex Slide & Push Up Full */
			if ($this.closest('.gw-gopf').hasClass('gw-gopf-style-flex-slide-push-up-full')) {
				if (event.type == 'mouseenter' || event.type == 'tapOn') {
					$this.find('.gw-gopf-post-header').css({
						'marginTop' : postHeight*-1,
						'marginBottom' : postHeight
					});														
				};
				
				if (event.type == 'mouseleave' || event.type == 'tapOff') {
					$this.find('.gw-gopf-post-header').css({
						'marginTop' : 0,
						'marginBottom' : 0
					});															
				};
			};			
			
			/* Flex Slide Down */
			if ($this.closest('.gw-gopf').hasClass('gw-gopf-style-flex-slide-down')) {
				if (event.type == 'mouseenter' || event.type == 'tapOn') {
					if ( postHeight-contentHeight-overlayInnerHeight <= 0 ) {
						$overlayInner.css('display', 'none');
					} else {
						$overlayInner.css('display', 'inline-block');	
					};
											
					/* Media wrap */
					$this.find('.gw-gopf-post-content-wrap').css({
						'bottom' : postHeight
					}).off('transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd');
					
					setTimeout(function(){ 
						$this.find('.gw-gopf-post-content-wrap').css({
							'bottom' : postHeight-contentHeight < 0 ? 0 : postHeight-contentHeight
						});
					}, 1);
					
					/* Overlay */
					$this.find('.gw-gopf-post-overlay').css({
						'height' : postHeight
					}).off('transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd');					

					setTimeout(function(){ 					
						$this.find('.gw-gopf-post-overlay').css({
							'height' : postHeight-contentHeight < 0 ? 0 : postHeight-contentHeight
						}).off('transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd');
					}, 1);											
				};
				
				if (event.type == 'mouseleave' || event.type == 'tapOff') {
					$this.find('.gw-gopf-post-content-wrap').css({
						'bottom' : supportsTransitions ? postHeight : '100%'
					}).one('transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd', function() { 
						$(this).css({ 'bottom' : '100%' });
					});
					
					$this.find('.gw-gopf-post-overlay').css({
						'height' : supportsTransitions ? postHeight : '100%'
					}).one('transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd', function() { 
						$(this).css({ 'height' : '100%' });
					});
				};
			};
			
			/* Flex Slide & Push Down */
			if ($this.closest('.gw-gopf').hasClass('gw-gopf-style-flex-slide-push-down')) {
				if (event.type == 'mouseenter' || event.type == 'tapOn') {
					if ( postHeight-contentHeight-overlayInnerHeight <= 0 ) {
						$overlayInner.css('display', 'none');
					} else {
						$overlayInner.css('display', 'inline-block');	
					};
											
					/* Media wrap */
					$this.find('.gw-gopf-post-content-wrap').css({
						'bottom' : postHeight
					}).off('transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd');
					
					setTimeout(function(){ 
						$this.find('.gw-gopf-post-content-wrap').css({
							'bottom' : postHeight-contentHeight < 0 ? 0 : postHeight-contentHeight
						});
					}, 1);
					
					/* Overlay */
					$this.find('.gw-gopf-post-overlay').css({
						'bottom' : postHeight-contentHeight < 0 ? 100 : contentHeight,
						'height' : postHeight
					}).off('transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd');					

					setTimeout(function(){ 					
						$this.find('.gw-gopf-post-overlay').css({
							'height' : postHeight-contentHeight < 0 ? 0 : postHeight-contentHeight
						}).off('transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd');
					}, 1);
										
					/* Header */
					$this.find('.gw-gopf-post-header').css({
						'marginBottom' : (postHeight-contentHeight < 0 ? postHeight : contentHeight)*-1,
						'marginTop' : postHeight-contentHeight < 0 ? postHeight : contentHeight
					});																
				};
				
				if (event.type == 'mouseleave' || event.type == 'tapOff') {
					$this.find('.gw-gopf-post-content-wrap').css({
						'bottom' : supportsTransitions ? postHeight : '100%'
					}).one('transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd', function() { 
						$(this).css({ 'bottom' : '100%' });
					});
					
					$this.find('.gw-gopf-post-overlay').css({
						'bottom' : 0,
						'height' : supportsTransitions ? postHeight : '100%'
					}).one('transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd', function() { 
						$(this).css({ 'height' : '100%' });
					});
					
					$this.find('.gw-gopf-post-header').css({
						'marginBottom' : 0,
						'marginTop' : 0
					});																
				};
			};
			
			/* Flex Slide & Push Down Full */
			if ($this.closest('.gw-gopf').hasClass('gw-gopf-style-flex-slide-push-down-full')) {
				if (event.type == 'mouseenter' || event.type == 'tapOn') {
					$this.find('.gw-gopf-post-header').css({
						'marginTop' : postHeight,
						'marginBottom' : postHeight*-1
					});
				};
				
				if (event.type == 'mouseleave' || event.type == 'tapOff') {
					$this.find('.gw-gopf-post-header').css({
						'marginTop' : 0,
						'marginBottom' : 0
					});															
				};
			};
			
			/* Door style */
			if ($this.closest('.gw-gopf').hasClass('gw-gopf-style-door-slide-down')) {
				if (event.type == 'mouseenter' || event.type == 'tapOn') {
					$this.css({
						'marginBottom' : contentHeight * -1,
						'paddingBottom' : contentHeight
					});
				};
				
				if (event.type == 'mouseleave' || event.type == 'tapOff') {
					$this.css({
						'marginBottom' : 0,
						'paddingBottom' : 0
					});
				};
			};
			
			/* Delux Push Up */
			if ($this.closest('.gw-gopf').hasClass('gw-gopf-style-delux-push-up')) {
				if (event.type == 'mouseenter' || event.type == 'tapOn') {
					$this.find('.gw-gopf-post-content-wrap').css('top', $this.find('.gw-gopf-post-header').outerHeight());
					$this.css({
						'marginTop' : contentHeight * -1,
						'paddingBottom' : contentHeight
					});
				
				};
				
				if (event.type == 'mouseleave' || event.type == 'tapOff') {
					$this.css({
						'marginTop' : 0,
						'paddingBottom' : 0
					});
				};
			};		
			
			/* Delux Push Down */
			if ($this.closest('.gw-gopf').hasClass('gw-gopf-style-delux-push-down')) {
				if (event.type == 'mouseenter' || event.type == 'tapOn') {
					$this.find('.gw-gopf-post-content-wrap').css('bottom', $this.find('.gw-gopf-post-header').outerHeight());
					$this.find('.gw-gopf-post-overlay').css('height', $this.find('.gw-gopf-post-header').outerHeight());
					$this.css({
						'marginBottom' : contentHeight * -1,
						'paddingTop' : contentHeight
					});
				};
				
				if (event.type == 'mouseleave' || event.type == 'tapOff') {
					$this.css({
						'marginBottom' : 0,
						'paddingTop' : 0
					});
				};
			};									
											
		});			

	/* -------------------------------------------------------------------------------- /
		[7]	DEEP LINKING
	/ -------------------------------------------------------------------------------- */

	function doHashChange() {
		var hashinfo = getHashInfo(),
			galleryItemIndex;
				
		if (!hashinfo || hashinfo==undefined) { 
			if (mfpOpened!=false) {
				$.magnificPopup.close();
			}
			return false;
		} else {
			if (!$portfolio.filter('[data-id="'+hashinfo[2]+'"]').data('deepLinking')) { return false; };
		};
		
		if (portfolioGallery[hashinfo[2]]!=undefined) {
			portfolioGallery[hashinfo[2]].each(function(index, element) {
				if ($(this).data('id')==hashinfo[1]+'_'+hashinfo[2]) {
					galleryItemIndex=index;
				};
			});

			if (galleryItemIndex>-1) {
				if (mfpOpened!=false) {
					if (mfpOpened.data('id')!=$(portfolioGallery[hashinfo[2]]).eq(galleryItemIndex).data('id')) {
						portfolioGallery[hashinfo[2]].magnificPopup('open', galleryItemIndex);
					};
				} else {
					portfolioGallery[hashinfo[2]].magnificPopup('open', galleryItemIndex);
				};
			};
		};
	};

	doHashChange();
	
	function getHash() {
		var currentHash = location.hash && location.hash !='' ? location.hash.split('#')[1] : null;
		return currentHash;
	};
	
	function getHashInfo() {
		var hashData = getHash(); 
		if (!hashData) return false;
		hashData = hashData.replace(/%7C/g, '|');
		if (hashData && hashData.split('@').length == 2 && hashData.split('@')[0] == 'mpf-popup' && hashData.split('@')[1].split('|').length == 3) { 
			var hashParts = hashData.split('@')[1].split('|');
			return hashParts;
		};
	};
	
	if (window.addEventListener) {
    	window.addEventListener("hashchange", doHashChange, false);
	} else if (window.attachEvent) {
    	window.attachEvent("onhashchange", doHashChange);    
	};

	/* -------------------------------------------------------------------------------- /
		[8]	OTHERS
	/ -------------------------------------------------------------------------------- */
	
		$(window).on('resize', function() { 
			$portfolio.filter('.gw-gopf-isotope-ready').find('.gw-gopf-posts').GWisotope('reLayout');
		});
		
		$(window).on('load', function() {
			if (supportsOrientationchange) {
				if (gw_go_portfolio_settings.mobileTransition=='enabled') {
					$('.gw-gopf-no-trans').removeClass('gw-gopf-no-trans');	
				};
			} else {
				$('.gw-gopf-no-trans').removeClass('gw-gopf-no-trans');
			};
		});

		
		function loadImages(images, callback) {
			
			if (!images) return;
			
			images.each(function(index, element) {
                
				var $el = $(element);
				
				if ( !$el.data('src') ) return;
				
				if ($el.closest('.gw-gopf-col-wrap').hasClass('gw-gopf-isotope-hidden') ) return;

				if ( $el.data('loading') && $el.data('fixh') ) {
										
					if ( parseFloat( $el.data('fixh') / $el.closest('.gw-gopf-post-media-wrap').width() ) > $el.data('img-aspect-ratio') ) {
						if ( $el.data('curent-orientaion') != 'landscape' ) $el.attr('data-orientation', 'landsacpe');	
					} else {
						if ( $el.data('curent-orientaion') != 'portrait' ) $el.attr('data-orientation', 'portrait');
					}
				}


				if ( $el.hasClass('gw-gopf-image-ready') || !$el.data('inview') || $el.data('loading') ) return;
				$el.data('loading', 1);
				var newImg = new Image;
					newImg.onload = function() {
					$el.attr('src', newImg.src);
					setTimeout($.proxy(function() {
						$el.closest('.gw-gopf-post-media-wrap').css('background-image', 'url("' + newImg.src +'")');
					}, $el), 1);
											
					// Modify the size if the given size is incorrect since v1.7.3						
					if ( !$el.data('view-aspect-ratio') && !$el.data('fixh') ) {							
						var w = $el.width(),
							h = $el.height(),
							aspectRatio = h/w,
							currentAspectRatio = $el.data('img-aspect-ratio') || 0;
						
						if (Math.abs(currentAspectRatio-aspectRatio).toFixed(2) != 0) {
							$el.data('width', w),
							$el.data('height', h);
							$el.data('img-aspect-ratio', aspectRatio);
							$el.closest('.gw-gopf-post-media-wrap').css('padding-bottom', aspectRatio*100+'%');						
							$el.closest('.gw-gopf-posts').GWisotope('reLayout' );							
						}

					}					
					
					if ( $el.data('fixh') && !$el.data('img-aspect-ratio') ) {
						
						$el.data('width', $el.width());
						$el.data('height', $el.height());	
						$el.data('img-aspect-ratio', $el.data('height') / $el.data('width'));						
						
					}
					
					// portrait or landscape
					if ( $el.data('fixh') ) {
						
						if ( parseFloat( $el.data('fixh') / $el.width() )  > $el.data('img-aspect-ratio') ) {
							$el.data('curent-orientaion','landsacpe');
							$el.attr('data-orientation', 'landsacpe');	
						} else {
							$el.data('curent-orientaion','portrait');							
							$el.attr('data-orientation', 'portrait');
						}
					}

					setTimeout($.proxy(function() {
						$el.closest('.gw-gopf-post-media-wrap-outer').css('opacity',1);
					}, $el), 10);					
								

				}
				newImg.onerror=function(){}
				newImg.src=$el.data('src');
				
				
            });
			
		}
		
		function loadGalleryImages() {
			
			var images = $portfolio.find('.gw-gopf-post-media-wrap img:visible');
			var xImages = images.filter('[data-x]');
			var wpImages = images.not('[data-x]');
			loadImages(xImages);		
			loadImages(wpImages)

		};
		
		function imgIsInview() {

			$portfolio.find('.gw-gopf-post-media-wrap img').each(function(index, element) {
				var $el = $(element);
				if ( $el.offset().top<parseInt($(document).scrollTop()+window.innerHeight ) ) {
					$el.data('inview', true);
				} else {
					$el.data('inview', false);
				}
				
            });
		}
		
		$(window).on('scroll resize', function() { 
			imgIsInview();
			loadGalleryImages();
		});
		
		imgIsInview();
		loadGalleryImages();		
				
	});
	
})( jQuery );