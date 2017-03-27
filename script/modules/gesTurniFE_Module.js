/**
 * 
 */
var gesTurniFE = angular.module("gesTurniFE", ['ngRoute']);

gesTurniFE.config(function($routeProvider) {
    $routeProvider
    .when("/mesi", {
        templateUrl : "template/mesi.html"
    })
    .when("/dipendenti", {
        templateUrl : "template/dipendenti.html"
    })
});

