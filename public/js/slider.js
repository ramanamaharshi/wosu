(function($){
	
	
	
	
	window.Slider = function (jContainer, aImages) {
		
		var oSlider = this;
		
		oSlider.jSlider = jDiv('slider').appendTo(jContainer);
		oSlider.aImages = aImages;
		
		for (var iI = 0; iI < oSlider.aImages.length; iI ++) {
			var oImage = oSlider.aImages[iI];
			if (typeof oImage == 'string') {
				oImage = {sSrc: oImage};
				oSlider.aImages[iI] = oImage;
			}
		}
		
		oSlider.iAt = 0;
		
		oSlider.vInitDoms();
		
	};
	
	
	
	
	Slider.prototype.vInitDoms = function () {
		
		var oSlider = this;
		
		oSlider.jWindow = oSlider.jSlider.jDiv('slider__window');
		oSlider.jContainer = oSlider.jWindow.jDiv('slider__container');
		
		oSlider.jNavigation = oSlider.jSlider.jDiv('slider__navigation');
		oSlider.jPrev = oSlider.jNavigation.jDiv('slider__navigation__prev');
		oSlider.jNext = oSlider.jNavigation.jDiv('slider__navigation__next');
		oSlider.jPrev.click(function(){oSlider.vPrev()});
		oSlider.jNext.click(function(){oSlider.vNext()});
		oSlider.jBullets = oSlider.jNavigation.jDiv('slider__navigation__bullets');
		
		oSlider.aItems = [];
		
		for (var iI = 0; iI < oSlider.aImages.length; iI ++) {
			
			var oItem = {oImage: oSlider.aImages[iI]};
			oSlider.aItems.push(oItem);
			
			oItem.jItem = oSlider.jContainer.jDiv('slider__item');
			oItem.jItem.css({left: (100 * iI) + '%'});
			oItem.jImage = oItem.jItem.jDom('img', 'slider__item__image');
			oItem.jImage.attr('src', oItem.oImage.sSrc);
			
		}
		
		oSlider.iItems = oSlider.aItems.length;
		
	};
	
	
	
	
	Slider.prototype.vPrev = function () {
		
		var oSlider = this;
		
		var iPrev = (oSlider.iAt + oSlider.iItems - 1) % oSlider.iItems;
		
		oSlider.vGoTo(iPrev);
		
	};
	
	
	
	
	Slider.prototype.vNext = function () {
		
		var oSlider = this;
		
		var iNext = (oSlider.iAt + 1) % oSlider.iItems;
		
		oSlider.vGoTo(iNext);
		
	};
	
	
	
	
	Slider.prototype.vGoTo = function (iNew) {
		
		var oSlider = this;
		
		oSlider.jContainer.css({left: (-100 * oSlider.iAt) + '%'});
		oSlider.jContainer.animate({left: (-100 * iNew) + '%'});
		
		oSlider.iAt = iNew;
		
	};
	
	
	
	
	$.fn.jDiv = function (oAttributes) {
		
		return (jDiv(oAttributes)).appendTo($(this));
		
	};
	
	
	
	
	$.fn.jDom = function (sTag, oAttributes) {
		
		return (jDom(sTag, oAttributes)).appendTo($(this));
		
	}
	
	
	
	
	var jDiv = function (oAttributes) {
		
		return jDom('div', oAttributes);
		
	}
	
	
	
	
	var jDom = function (sTag, oAttributes) {
		
		if (typeof oAttributes == 'undefined') oAttributes = {};
		
		if (typeof oAttributes == 'string') {
			oAttributes = {class: oAttributes};
		}
		
		var jDom = $('<' + sTag + '/>');
		
		for (var sAttr in oAttributes) {
			jDom.attr(sAttr, oAttributes[sAttr]);
		}
		
		return jDom;
		
	};
	
	
	
	
})(jQuery);
