/**
 * 
 */

gesTurniFE.directive("mostraMese", function(){
			return{
				restrict: "E",
				templateUrl: "template/mostraMese.html"
			}
});

gesTurniFE.directive("mostraListaMesi", function(){
	return{
		restrict: "E",
		templateUrl: "template/mostraListaMesi.html"
	}
});

gesTurniFE.directive("generaMese", function(){
	return{
		restrict: "E",
		templateUrl: "template/generaMese.html"
	}
});

gesTurniFE.directive("leftBar", function(){
	return{
		restrict: "E",
		templateUrl: "template/leftBar.html"
	}
});

