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
						var sDescription = oAd.oData.oPrice.iWarm + oAd.oData.oPhysical.nSquareMeters + 'm² ';
						oAd.oStuff.jListItem.jAppend('list__item__title').text(sTitle);
						oAd.oStuff.jListItem.jAppend('list__item__description').text(sDescription);
						
						oAd.oStuff.jListItem.click(function(){
							oD.vSetAd(oAd);
						});
						
					}
					
					oD.oList.jList.append(oAd.oStuff.jListItem);
					
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
				url: '/public/img/marker_icon_a.svg',
				scale: [nIconSize,nIconSize],
				bounds: [nIconSize/4,0,nIconSize/2,nIconSize],
				origin: [nIconSize/4,nIconSize],
			};
			
			oD.vOnFilterChange(function(){
				oD.oState.aFilteredAds.forEach(function(oAd){
					oAd.oMarker = {
						title: '',
						icon: oMarkerIcon,
						position: {
							nLat: oAd.oData.oAddress.oCoords.iY,
							nLon: oAd.oData.oAddress.oCoords.iX,
						}
					};
					vAddMarker(oD.oMap.oMap, oAd.oMarker);
				});
			});
			
		},
		
		
		
		
		vInitDetail: function () {
			
			var oD = this;
			
			oD.oDetail = {};
			oD.oDetail.jDetail = oD.jMain.jAppend('detail');
			
			oD.vOnAdChange(function(){
				oD.oDetail.jDetail.empty();
				oD.oState.oCurrentAd.oData.oPage.aImages.forEach(function(oImage){
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
