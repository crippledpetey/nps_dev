

(function() {
	

if (typeof BLANK_IMG == 'undefined') 
	var BLANK_IMG = '';

// declare namespace method
String.prototype.namespace = function(separator) {
  this.split(separator || '.').inject(window, function(parent, child) {
    var o = parent[child] = { }; return o;
  });
};





/*-----SLider 1--*/
function decorateSlideshowFeaturedProducts() {
	var $$li = $$('#widget-mostpopular ul li.item');
	if ($$li.length > 0) {
		
		// reset UL's width
		var ul = $$('#widget-mostpopular ul')[0];
		var w = 0;
		
		$$li.each(function(li) {
			w += parseInt(li.getStyle('width'));
		});
		ul.setStyle({'width':w+'px'});
		
		// private variables
		var previous = $$('#widget-mostpopular a.previous')[0];
		var next = $$('#widget-mostpopular a.next')[0];
		var num = 1;
		var width = parseInt(ul.down().getStyle('width')) * num;
		var slidePeriod = 3; // seconds
		var manualSliding = false;
		
		// next slide
		function nextSlide() {
			new Effect.Move(ul, { 
				x: -width,
				mode: 'relative',
				queue: 'end',
				duration: 1.0,
				//transition: Effect.Transitions.sinoidal,
				afterFinish: function() {
					for (var i = 0; i < num; i++)
						ul.insert({ bottom: ul.down() });
					ul.setStyle('left:0');
				}
			});
		}
		
		// previous slide
		function previousSlide() {
			new Effect.Move(ul, { 
				x: width,
				mode: 'relative',
				queue: 'end',
				duration: 1.0,
				//transition: Effect.Transitions.sinoidal,
				beforeSetup: function() {
					for (var i = 0; i < num; i++)
						ul.insert({ top: ul.down('li.item:last-child') });
					ul.setStyle({'position': 'relative', 'left': -width+'px'});
				}
			});
		}
		
		function startSliding() {
			sliding = true;
		}
		
		function stopSliding() {
			sliding = false;
		}
		
		// bind next button's onlick event
		next.observe('click', function(event) {
			Event.stop(event);
			manualSliding = true;
			nextSlide();
		});
		
		// bind previous button's onclick event
		previous.observe('click', function(event) {
			Event.stop(event);
			manualSliding = true;
			previousSlide();
		});
		
		
		// auto run slideshow
		/*new PeriodicalExecuter(function() {
			if (!manualSliding) previousSlide();
			manualSliding = false;
		}, slidePeriod);*/
		
		
	}
}
/*-----SLider new--*/
function decorateSlideshowNew() {
	var $$li = $$('#widget-new ul li.item');
	if ($$li.length > 0) {
		
		// reset UL's width
		var ul = $$('#widget-new ul')[0];
		var w = 0;
		
		$$li.each(function(li) {
			w += parseInt(li.getStyle('width'));
		});
		ul.setStyle({'width':w+'px'});
		
		// private variables
		var previous = $$('#widget-new a.previous')[0];
		var next = $$('#widget-new a.next')[0];
		var num = 1;
		var width = parseInt(ul.down().getStyle('width')) * num;
		var slidePeriod = 3; // seconds
		var manualSliding = false;
		
		// next slide
		function nextSlide() {
			new Effect.Move(ul, { 
				x: -width,
				mode: 'relative',
				queue: 'end',
				duration: 1.0,
				//transition: Effect.Transitions.sinoidal,
				afterFinish: function() {
					for (var i = 0; i < num; i++)
						ul.insert({ bottom: ul.down() });
					ul.setStyle('left:0');
				}
			});
		}
		
		// previous slide
		function previousSlide() {
			new Effect.Move(ul, { 
				x: width,
				mode: 'relative',
				queue: 'end',
				duration: 1.0,
				//transition: Effect.Transitions.sinoidal,
				beforeSetup: function() {
					for (var i = 0; i < num; i++)
						ul.insert({ top: ul.down('li.item:last-child') });
					ul.setStyle({'position': 'relative', 'left': -width+'px'});
				}
			});
		}
		
		function startSliding() {
			sliding = true;
		}
		
		function stopSliding() {
			sliding = false;
		}
		
		// bind next button's onlick event
		next.observe('click', function(event) {
			Event.stop(event);
			manualSliding = true;
			nextSlide();
		});
		
		// bind previous button's onclick event
		previous.observe('click', function(event) {
			Event.stop(event);
			manualSliding = true;
			previousSlide();
		});
		
		
		// auto run slideshow
		/*new PeriodicalExecuter(function() {
			if (!manualSliding) previousSlide();
			manualSliding = false;
		}, slidePeriod);*/
		
		
	}
}

/*-----SLider 2--*/
function decorateSlideshowFeaturedProducts2() {
	var $$li = $$('#widget-featured-products2 ul li.item');
	if ($$li.length > 0) {
		
		// reset UL's width
		var ul = $$('#widget-featured-products2 ul')[0];
		var w = 0;
		
		$$li.each(function(li) {
			w += parseInt(li.getStyle('width'));
		});
		ul.setStyle({'width':w+'px'});
		
		// private variables
		var previous = $$('#widget-featured-products2 a.previous')[0];
		var next = $$('#widget-featured-products2 a.next')[0];
		var num = 1;
		var width = parseInt(ul.down().getStyle('width')) * num;
		var slidePeriod = 3; // seconds
		var manualSliding = false;
		
		// next slide
		function nextSlide() {
			new Effect.Move(ul, { 
				x: -width,
				mode: 'relative',
				queue: 'end',
				duration: 1.0,
				//transition: Effect.Transitions.sinoidal,
				afterFinish: function() {
					for (var i = 0; i < num; i++)
						ul.insert({ bottom: ul.down() });
					ul.setStyle('left:0');
				}
			});
		}
		
		// previous slide
		function previousSlide() {
			new Effect.Move(ul, { 
				x: width,
				mode: 'relative',
				queue: 'end',
				duration: 1.0,
				//transition: Effect.Transitions.sinoidal,
				beforeSetup: function() {
					for (var i = 0; i < num; i++)
						ul.insert({ top: ul.down('li.item:last-child') });
					ul.setStyle({'position': 'relative', 'left': -width+'px'});
				}
			});
		}
		
		function startSliding() {
			sliding = true;
		}
		
		function stopSliding() {
			sliding = false;
		}
		
		// bind next button's onlick event
		next.observe('click', function(event) {
			Event.stop(event);
			manualSliding = true;
			nextSlide();
		});
		
		// bind previous button's onclick event
		previous.observe('click', function(event) {
			Event.stop(event);
			manualSliding = true;
			previousSlide();
		});
		
		
		// auto run slideshow
		/*new PeriodicalExecuter(function() {
			if (!manualSliding) previousSlide();
			manualSliding = false;
		}, slidePeriod);*/
		
		
	}
}

/*-----SLider 2--*/
function decorateSlideshowBestseller() {
	var $$li = $$('#widget-bestseller ul li.item');
	if ($$li.length > 0) {
		
		// reset UL's width
		var ul = $$('#widget-bestseller ul')[0];
		var w = 0;
		
		$$li.each(function(li) {
			w += parseInt(li.getStyle('width'));
		});
		ul.setStyle({'width':w+'px'});
		
		// private variables
		var previous = $$('#widget-bestseller a.previous')[0];
		var next = $$('#widget-bestseller a.next')[0];
		var num = 1;
		var width = parseInt(ul.down().getStyle('width')) * num;
		var slidePeriod = 3; // seconds
		var manualSliding = false;
		
		// next slide
		function nextSlide() {
			new Effect.Move(ul, { 
				x: -width,
				mode: 'relative',
				queue: 'end',
				duration: 1.0,
				//transition: Effect.Transitions.sinoidal,
				afterFinish: function() {
					for (var i = 0; i < num; i++)
						ul.insert({ bottom: ul.down() });
					ul.setStyle('left:0');
				}
			});
		}
		
		// previous slide
		function previousSlide() {
			new Effect.Move(ul, { 
				x: width,
				mode: 'relative',
				queue: 'end',
				duration: 1.0,
				//transition: Effect.Transitions.sinoidal,
				beforeSetup: function() {
					for (var i = 0; i < num; i++)
						ul.insert({ top: ul.down('li.item:last-child') });
					ul.setStyle({'position': 'relative', 'left': -width+'px'});
				}
			});
		}
		
		function startSliding() {
			sliding = true;
		}
		
		function stopSliding() {
			sliding = false;
		}
		
		// bind next button's onlick event
		next.observe('click', function(event) {
			Event.stop(event);
			manualSliding = true;
			nextSlide();
		});
		
		// bind previous button's onclick event
		previous.observe('click', function(event) {
			Event.stop(event);
			manualSliding = true;
			previousSlide();
		});
		
		
		// auto run slideshow
		/*new PeriodicalExecuter(function() {
			if (!manualSliding) previousSlide();
			manualSliding = false;
		}, slidePeriod);*/
		
		
	}
}

document.observe("dom:loaded", function() {
	/*decorateProductOverlay();*/	
	decorateSlideshowNew();
	decorateSlideshowFeaturedProducts();
	 decorateSlideshowFeaturedProducts2();
	decorateSlideshowBestseller();
	//decorateSlideshow2();
});

// }}}

})();