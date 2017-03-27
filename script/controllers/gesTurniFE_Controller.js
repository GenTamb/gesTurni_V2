/**
 * 
 */


gesTurniFE.controller("leftBarController",function($scope,$location){
	$scope.toMesi = function(){
		$location.path("/mesi");
	}
	
	$scope.toDipendenti = function(){
		$location.path("/dipendenti");
	}

});

gesTurniFE.controller("recuperaListaMesi",['$rootScope','$scope','$http',function($rootScope,$scope,$http){
	$scope.mostraSpinner = false;
	$scope.ListaPresente = false;
	$scope.caricaListaMesi = function(){
		$scope.mostraSpinner = true;
		$http.get('_backend_logic/mostraListaMesi.php?mostraTutti').
		then(function(response){
			$rootScope.ListaMesi = response.data;
			$rootScope.$broadcast('ListaMesiRecuperata');
			$scope.ListaPresente = true;
			$scope.mostraSpinner = false;
		},function(response){
			alert(response.statusText);
		});
	
	}
	$scope.IfListaPresente = function(){
		return $scope.ListaPresente;
	};
	
	$rootScope.$on('meseGenerato',function(){
		$scope.caricaListaMesi();
	});
}]);

gesTurniFE.controller("mostraListaMesi",['$rootScope','$scope',function($rootScope,$scope){
	$scope.$on('ListaMesiRecuperata',function(){
		$scope.Lista = $rootScope.ListaMesi;
	});
	
}]);

gesTurniFE.controller("recuperaMeseByLista",['$rootScope','$scope','$http',function($rootScope,$scope,$http){
	$scope.mostraSpinner = false;
	$scope.cercaMese = function(anno,mese){
		$scope.mostraSpinner = true;
		$http.get('_backend_logic/mostraMese.php?anno='+anno+'&mese='+mese).
		then(function(response){
			$rootScope.Mese = response.data;
			$rootScope.$broadcast('MeseRecuperato');
			$scope.mostraSpinner = false;
		},function(response){
			alert(response.statusText);
		}
		);
		
	}
}]);


gesTurniFE.service("gestisciTurni",function($http){
	 return {
		 callBackend : function(){
			return $http.get('_backend_logic/ritornaListaTurni.php?getTurni');
	 		}
	 }
});

gesTurniFE.controller("mostraMese",['$rootScope','$scope','gestisciTurni',function($rootScope,$scope,gestisciTurni){
	$scope.mesePresente = {
			stato : false,
			cambiaStato : function(){
				this.stato = true;
				},
			ritornaStato : function(){return this.stato;}
			
	}
	
	$scope.$on('MeseRecuperato',function(){
		$scope.Mese = $rootScope.Mese;
		$scope.mesePresente.cambiaStato();
	});
	var decodificaTurni4 = {};
	var decodificaTurni6 = {};
	var decodificaTurni8 = {};
	var listaTurni = gestisciTurni.callBackend().then(function(response){
		decodificaTurni4.numTurniGiornalieri4 = response.data.turniGiornalieri8.length; 
		decodificaTurni6.numTurniGiornalieri6 = response.data.turniGiornalieri8.length; 
		decodificaTurni8.numTurniGiornalieri8 = response.data.turniGiornalieri8.length; 
	
			});
	$scope.decodificaNomeTurno = function(value){
		switch(value.substr(0,1)){
		case "4":
			switch(value.substr(7,1)){
			case "0":
				return "PT";
				break;
			}
			break;
		case "6":
			switch(value.substr(7,1)){
			case "0":
				return "XL";
				break;
			}
			break;
		case "8":
			switch(value.substr(7,1)){
			case "0":
				return "XL";
				break;
			case (decodificaTurni8.numTurniGiornalieri8 - 1).toString():
				return "RXL";
				break;
			default:
				return "N"+ value.substr(7,1).toString();
				break;
			}
			break;
		}
	}
	
	$scope.print = function(elemento){
		console.log(elemento);
		var div = document.getElementById(elemento).innerHTML;
		var toPrint = window.open('', '_blank', 'width=300,height=300');
		toPrint.document.open();
		toPrint.document.write("<html><head><link rel='stylesheet' href='Bootstrap/bootstrap.min.css'><link rel='stylesheet' href='css/style.css'></head><body onLoad='window.print()'><div class='container-fluid'>" + div + "</div></body></html>");
		toPrint.document.close();
	}
	
}]);

gesTurniFE.controller("gestisciTabella",['$rootScope','$scope',function($rootScope,$scope){
	$scope.recuperaKey = function(value){
		var arrayProp = Object.keys(value);
		return arrayProp[0];
	};
	
	$scope.recuperaValue = function(value){
		var arrayProp = Object.values(value);
		return arrayProp[0];
	};
	
	$scope.listaDipendenti = {};
	$scope.$on('MeseRecuperato',function(){
		var Mese = $rootScope.Mese;
		console.log(Mese);
		var continua = true;
		while(continua){
			for (var int = 0; int < Mese.calendarioGiorni.length; int++) {
				var giorno = Mese.calendarioGiorni[int];
				if(giorno.turniGiornalieri8.length != 0){
					$scope.listaDipendenti.turnisti8 = giorno.turniGiornalieri8;
					$scope.listaDipendenti.turnisti4 = giorno.turniGiornalieri4;
					$scope.listaDipendenti.turnisti6 = giorno.turniGiornalieri6;
					continua = false;
					break;
				}
			}
		}
	});
	
}]);

gesTurniFE.controller("generaMese",['$rootScope','$scope','$http',function($rootScope,$scope,$http){
	$scope.mostraSpinner = false;
	$scope.mostraPanel = false;
	$scope.mostraPanelBTN = function(){
		$scope.mostraPanel = !$scope.mostraPanel;
	}
	$scope.mese = {
			anno : '',
			nomeMese : '',
			numGiorni : '',
			primoGiorno : '',
			festivi : [],
			rigenerazione : false,
			pushFestivo : function(x){
				if(this.festivi.indexOf(x)==-1) this.festivi.push(x);
				else this.festivi = this.festivi.splice(this.festivi.indexOf(x), 1);
			},
			
			forzaRigenerazione : function(){
				this.rigenerazione = !this.rigenerazione;
			},

			inviaRichiesta : function(){
				if(this.anno!='' && this.numMese != '' && this.numGiorni !=''){
					$scope.mostraSpinner = true;
					$http.post('_backend_logic/generaMese.php',{
						generaMese : true, 
						forzaRigenerazione : this.rigenerazione, 
						anno : this.anno,
						nomeMese : this.nomeMese,
						numeroGiorni : this.numGiorni,
						primoGiornoMese : this.primoGiorno,
						forzaRigenerazione : this.rigenerazione,
						listaFestivi : this.festivi}).
						then(function(response){
							alert(response.data);
							$scope.mostraPanel = false;
							$scope.mostraSpinner = false;
							$rootScope.$emit('meseGenerato');
						},
						function error(response){
							alert(response.data);
							$scope.mostraSpinner = false;
						});
					}
				else alert('Uno o più campi vuoti');
			}
	}
		
}]);

gesTurniFE.service("getDipendenti",function($http){
	 return {
		 callBackendDip : function(){
			return $http.get('_backend_logic/ritornaListaDipendenti_Turni.php?richiediListaDipendenti_Turni');
	 		}
	 }
});


gesTurniFE.controller("gestisciDipendenti",['$rootScope','$scope','getDipendenti','$http','$route',function($rootScope,$scope,getDipendenti,$http,$route){
	$scope.mostraSpinner = false;
	$scope.mostraPanel = false;
	$scope.listaDipendenti = {
			listaOrari : ['4','6','8'],
			resp : ['1','0'],
			lista : {}
	};
	
	$scope.recuperaListaTurni = function (){
		$scope.mostraSpinner = true;
		getDipendenti.callBackendDip().then(function(response){
			$scope.listaDipendenti.lista = response.data;
			$scope.mostraSpinner = false;
		});
	};
	
	$scope.recuperaListaTurni();
	
	$scope.mostraAddRow = false;
	$scope.mostraAddBtn = true;
	$scope.mostraEditBtn = true;
	$scope.editOn = false;
	$scope.editFormOn = false;
	
	$scope.mostraAddRowFN = function(){
		$scope.mostraAddRow = !$scope.mostraAddRow;
		$scope.mostraAddBtn = false;
	}
	
	$scope.nascondiAddRowFN = function(){
		$scope.mostraAddRow = !$scope.mostraAddRow;
		$scope.mostraAddBtn = true;
	}
	
	$scope.mostraEditFN = function(){
		$scope.mostraAddBtn = false;
		$scope.mostraEditBtn = false;
		$scope.editOn = true;
	}
	
	$scope.nascondiEditFN = function(){
		$scope.mostraAddBtn = true;
		$scope.mostraEditBtn = true;
		$scope.editOn = false;
	}
	
	$scope.add_Dip = {
			cognome_nome : '',
			orario : '',
			responsabile : '',
			add : function(){
				if(this.cognome_nome == '' || this.orario == '' || this.responsabile == ''){
					alert('I campi vanno riempiti');
				}
				else{
					try{
						$scope.mostraSpinner = true;
						var cogNome = this.cognome_nome.split(',');
						console.log(cogNome);
						$http.post('_backend_logic/aggiungiDip.php',{
							aggiundiDip : true,
							cognome : cogNome[0],
							nome : cogNome[1],
							orario : this.orario,
							responsabile : this.responsabile
						}).
						then(function(response){
							alert(response.data);
							$scope.mostraAddBtn = true;
							$scope.mostraAddRow = false;
							$route.reload();
						},
						function(response){
							alert(response.statusText);
						});
					}
					catch(e){
						alert(e.message);
					}
//					if(ok) $scope.listaDipendenti.lista.listaDipendenti.push({'cognome':cogNome[0], 'nome':cogNome[1], 'orario':this.orario, 'speciale':this.responsabile});
				}
			},
			
			del : function(e){
				var tr = angular.element(e.srcElement).parent().parent();
				var tds = angular.element(tr).children();
				var cogNome = tds[0].innerHTML;
				var cog = cogNome.split(',');
				try{
					$scope.mostraSpinner = true;
					var ok = false;
					$http.post('_backend_logic/cancellaDip.php',{
						cancellaDip : true,
						cognome : cog[0],
					}).
					then(function(response){
						alert(response.data);
						$scope.mostraAddBtn = true;
						$scope.mostraAddRow = false;
						$route.reload();
					},
					function(response){
						alert(response.statusText);
					});
				}
				catch(error){
					alert(error.message);
				}
			}
	}
	
	$scope.edit_Dip = {
		showEditForm : function(cognome,nome,orario,responsabile){
			$scope.edit_Dip.origCognome = cognome;
			$scope.edit_Dip.origNome = nome;
			$scope.edit_Dip.origOrario = orario;
			$scope.edit_Dip.origResponsabile = responsabile;
			$scope.edit_Dip.cognome = $scope.edit_Dip.origCognome;
			$scope.edit_Dip.nome = $scope.edit_Dip.origNome;
			$scope.edit_Dip.orario = $scope.edit_Dip.origOrario;
			$scope.edit_Dip.responsabile = $scope.edit_Dip.origResponsabile;
			$scope.editFormOn = true;
		},
		
		chiudiEdit : function(){
			$scope.editFormOn = false;
		},
		
		inoltraEdit : function(){
			console.log($scope.edit_Dip);
			$scope.mostraSpinner = true;
			$http.post('_backend_logic/modificaDip.php',{
				modificaDip : true,
				origCognome : $scope.edit_Dip.origCognome,
				origNome : $scope.edit_Dip.origNome,
				origOrario : $scope.edit_Dip.origOrario,
				origResponsabile : $scope.edit_Dip.origResponsabile,
				cognome : $scope.edit_Dip.cognome,
				nome : $scope.edit_Dip.nome,
				orario : $scope.edit_Dip.orario,
				responsabile : $scope.edit_Dip.responsabile
			}).then(function(response){
				alert(response.data);
				$scope.mostraSpinner = false;
				$scope.editFormOn = false;
				$route.reload();
				
			}, function(response){
				alert(response.statusText)
			});
		}
	}
	
		
}]);




