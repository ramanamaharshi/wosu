(function($){
	
	
	
	
	$.fn.jAppend = function (sClasses, sTag) {
		
		if (typeof sTag == 'undefined') sTag = 'div';
		
		var oReturn = $('<' + sTag + '></' + sTag + '>');
		
		if (typeof sClasses == 'object') sClasses = sClasses.join(' ');
		if (typeof sClasses == 'string') oReturn.addClass(sClasses);
		
		$(this).append(oReturn);
		
		return oReturn;
		
	};
	
	
	
	
	window.oDashboard = {
		
		
		
		
		vInit: function () {
			
			var oD = this;
			
			oD.oState = {
				oFilter: {},
				aLoadedAds: [],
				aFilteredAds: [],
				oCurrentAd: null,
			};
			
			oD.jMain = $('body').jAppend('dashboard');
			
			oD.vInitFilter();
			oD.vInitList();
			oD.vInitMap();
			oD.vInitDetail();
			
			oD.vGetAds();
			
		},
		
		
		
		
		vInitFilter: function () {
			
			var oD = this;
			
			oD.oFilter = {};
			oD.oFilter.jFilter = oD.jMain.jAppend('filter');
			oD.oFilter.jFilter.text('filter').attr('style', 'vertical-align:middle;text-align:center;');
			
			oD.vOnLoadedAdsChange(function(){
				oD.vSetFilter(oD.oState.oFilter);
			});
			
		},
		
		
		
		
		vInitList: function () {
			
			var oD = this;
			
			oD.oList = {};
			oD.oList.jList = oD.jMain.jAppend('list');
			
			oD.vOnFilterChange(function(){
				
				oD.oList.jList.empty();
				
				oD.oState.aFilteredAds.forEach(function(oAd){
					
					if (typeof oAd.oStuff.jListItem == 'undefined') {
						
						oAd.oStuff.jListItem = oD.oList.jList.jAppend('list__item');
						
						var sTitle = oAd.oData.oAddress.sStreet;
						var sArea = oAd.oData.oPhysical.nSquareMeters + 'm²';
						var sPrice = Math.round(oAd.oData.oPrice.iWarm / 100) + '€';
						var sDescription = sArea + ' ' + sPrice;
						oAd.oStuff.jListItem.jAppend('list__item__title').text(sTitle);
						oAd.oStuff.jListItem.jAppend('list__item__description').text(sDescription);
						
						oAd.oStuff.jListItem.click(function(){
							oD.vSetAd(oAd);
						});
						
					}
					
					oD.oList.jList.append(oAd.oStuff.jListItem);
					
				});
				
				oD.vOnAdChange(function(oAd){
					oD.oState.aFilteredAds.forEach(function(oAd){
						oAd.oStuff.jListItem.removeClass('active');
					});
					var jList = oD.oList.jList;
					var jItem = oD.oState.oCurrentAd.oStuff.jListItem;
					var iListTopPlus = (jItem.outerHeight()/* / 2*/ - jList.innerHeight() / 2)
					jItem.addClass('active');
					jList.scrollTop(0);
					jList.scrollTop(jItem.position().top + iListTopPlus);
				});
				
			});
			
		},
		
		
		
		
		vInitMap: function () {
			
			var oD = this;
			
			oD.oMap = {};
			oD.oMap.jMap = oD.jMain.jAppend('map');
			oD.oMap.jMap.text('map').attr('style', 'vertical-align:middle;text-align:center;');
			
			var oOptions = {
				zoom: 13,
				type: 'satellite',
				center: '50.7753222,6.0838673',
			};
			oD.oMap.oMap = oD.oMap.jMap.oMakeMap(oOptions);
			
			var nIconSize = 12;
			var oMarkerIcon = {
				url: '/public/img/marker_icon_b.png',
				scale: [5,5],
				bounds: [0,0,5,5],
				origin: [1,1],
				//url: '/public/img/marker_icon_a.svg',
				//scale: [nIconSize,nIconSize],
				//bounds: [nIconSize/4,0,nIconSize/2,nIconSize],
				//origin: [nIconSize/4,nIconSize],
			};
			
			var nJitter = function (nL) {
				return (0.00002 * (Math.random() - 0.5)) + parseFloat(nL);
			}
			
			oD.vOnFilterChange(function(){
				oD.oState.aFilteredAds.forEach(function(oAd){
					oAd.oMarker = {
						title: '',
						icon: oMarkerIcon,
						position: {
							nLat: nJitter(oAd.oData.oAddress.oCoords.nY),
							nLon: nJitter(oAd.oData.oAddress.oCoords.nX),
						}
					};
					vAddMarker(oD.oMap.oMap, oAd.oMarker);
					oAd.oMarker.gMarker.vBind('click', function(){
						console.log('click', arguments);
						oD.vSetAd(oAd);
					});
				});
			});
			
		},
		
		
		
		
		vInitDetail: function () {
			
			var oD = this;
			
			oD.oDetail = {};
			oD.oDetail.jDetail = oD.jMain.jAppend('detail');
			var jDetail = oD.oDetail.jDetail;
			
			oD.vOnAdChange(function(){
				var oAdData = oD.oState.oCurrentAd.oData;
				jDetail.empty();
				var jLink = jDetail.jAppend('detail_link', 'a');
				jLink.attr('target', '_blank').attr('href', oAdData.oPage.sUrl);
				jLink.text(oAdData.oAddress.sStreet);
				oD.oDetail.jDetail.append();
				oAdData.oPage.aImages.forEach(function(oImage){
					oD.oDetail.jDetail.append('<img src="/' + oImage.sFile + '">');
				});
			});
			
		},
		
		
		
		
		vSetAd: function (oAd) {
			
			var oD = this;
			
			oD.oState.oCurrentAd = oAd;
			
			oD.vOnAdChange();
			
		},
		
		
		
		
		vSetFilter: function (oFilter) {
			
			var oD = this;
			
			oD.oState.oFilter = oFilter;
			
			oD.oState.aFilteredAds = [];
			oD.oState.aLoadedAds.forEach(function(oAd){
				oD.oState.aFilteredAds.push(oAd);
			});
			
console.log('vSetFilter()');
			oD.vOnFilterChange();
			
		},
		
		
		
		
		vGetAds: function (oLoadFilter, fDone) {
			
			var oD = this;
			
			if (typeof oLoadFilter == 'undefined') oLoadFilter = {};
			if (typeof fDone == 'undefined') fDone = function(){};
			
			$.getJSON('/pages/ajax.php', {oFilter: oLoadFilter}, function (oResponse) {
				var aAdDatas = oResponse.aAds;
				oD.oState.aLoadedAds = [];
				oD.oState.aFilteredAds = [];
				oD.oCurrentAd = [];
				aAdDatas.forEach(function(oAd){
					oD.oState.aLoadedAds.push({oData: oAd.oData, oStuff: {}});
				});
				oD.vOnLoadedAdsChange();
				fDone();
			});
			
		},
		
		
		
		
		vOnLoadedAdsChange: function (f) {
			
			this.vBind(this, 'vOnLoadedAdsChange', f);
			
		},
		
		
		
		
		vOnFilterChange: function (f) {
			
			this.vBind(this, 'vOnFilterChange', f);
			
		},
		
		
		
		
		vOnAdChange: function (f) {
			
			this.vBind(this, 'vOnAdChange', f, this.oState.oCurrentAd);
			
		},
		
		
		
		
		vBind: function (oHost, sKey, f, aArguments) {
			
			var oD = this;
			
			if (typeof oHost.oBinds == 'undefined') {
				oHost.oBinds = {};
			}
			if (typeof oHost.oBinds[sKey] == 'undefined') {
				oHost.oBinds[sKey] = [];
			}
			
			if (typeof f == 'function') {
				oHost.oBinds[sKey].push(f);
			} else {
				if (typeof aArguments == 'undefined') {
					aArguments = [];
				}
				oHost.oBinds[sKey].forEach(function(f){
					f.apply(oD, aArguments);
				});
			}
			
		},
		
		
		
		
	};
	
	
	
	
})(jQuery);
