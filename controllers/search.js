cacheApp.controller('search', function($timeout, $http, $scope, $state, CacheService){
	
    $scope.wordMeaningData = [];
    $scope.errorString = null;
    $scope.information = {};
    $scope.updateShownForEmail = null;
    $scope.updateInformation = null;
    $scope.data = {
        searchString : null   
    }

    $scope.page = {
        min:1,
        max:1,
        current:1,
        total:1,
        range:5
    };


    var isValidEmail = function(){
        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test($scope.information.email);
    }

    $scope.range = function() {
        var input = [];
        for (var i = $scope.page.min; i <= $scope.page.max; i += 1) {
            input.push(i);
        }
        return input;
    };

    $scope.showPrevious = function(){
        $scope.page.current = $scope.page.current-1;
        if($scope.page.current < $scope.page.min){
            $scope.page.min = $scope.page.min-$scope.page.range;
            $scope.page.max = $scope.page.total < $scope.page.min+$scope.page.range-1 ? $scope.page.total : $scope.page.min+$scope.page.range-1;
        }
    }

    $scope.showNext = function(){
        $scope.page.current = $scope.page.current+1;
        if($scope.page.current > $scope.page.min+$scope.page.range-1){
            $scope.page.min = $scope.page.current;
            $scope.page.max = $scope.page.total < $scope.page.min+$scope.page.range-1 ? $scope.page.total : $scope.page.min+$scope.page.range-1;
        }
    }    

    $scope.shouldShowPrevious = function(){
        if($scope.page.current != 1 && $scope.page.total > 1)
            return true;
        return false;
    }

    $scope.shouldShowNext = function(){
        if($scope.page.current != $scope.page.total)
            return true;

        return false;
    }

    $scope.cancelUpdate = function(){
        $scope.updateShownForEmail = null;
        $scope.updateInformation = null;
    }

    $scope.showUpdate = function(value){
        console.log(value);
        $scope.updateShownForEmail = value.word;
        $scope.updateInformation = angular.copy(value);
        console.log($scope.updateShownForEmail);
    }

    $scope.updateCalledFor = function(value){
        return value.word === $scope.updateShownForEmail;
    }

    var populateTable = function(){
        CacheService.getWordMeaningData().success(function(response){
            $scope.wordMeaningData = [];
            $scope.page.total = response.total%10 ? Math.floor((response.total/10))+1 : response.total/10;
            $scope.page.max = $scope.page.total < $scope.page.min+$scope.page.range-1 ? $scope.page.total : $scope.page.min+$scope.page.range-1;
            for(var i=0;i<response.data.length;i++){
                $scope.wordMeaningData.push(response.data[i]);
                $scope.lastPaginatedId = response.data[i].id;
            }
        }).error(function(error){
            
        })
    }

    $scope.addContact = function(){
        if(!isValidEmail()){
            $scope.errorString = "Please enter a valid email id.";
            return;
        }
        if(!$scope.information.name || !$scope.information.name.trim()){
            $scope.errorString = "Please enter a contact name.";
            return;
        }
        $scope.errorString = null;
        CacheService.addData($scope.information).success(function(response){
            $scope.information = {};
            populateTable();       
        }).error(function(error){
            $scope.errorString = "Error occured. Please try again.";
            return;    
        })
    }

    $scope.fetchByOffset = function(offset){
        if(!$scope.data.searchString || !$scope.data.searchString.trim()){
            $scope.page.current = offset;
            var offset = (offset-1)*10;
            CacheService.fetchByOffset(offset).success(function(response){
                $scope.wordMeaningData = [];
                for(var i=0;i<response.data.length;i++){
                    $scope.wordMeaningData.push(response.data[i]);
                }
            }).error(function(error){
                
            })    
        }else{
            $scope.page.current = offset;
            var offset = (offset-1)*10;
            CacheService.fetchByOffsetAndSearch(offset,$scope.data.searchString).success(function(response){
                $scope.wordMeaningData = [];
                for(var i=0;i<response.data.length;i++){
                    $scope.wordMeaningData.push(response.data[i]);
                }
            }).error(function(error){
                
            })
        }
        
    }


    $scope.initializeSystem = function(){
        arcCache = null;
        CacheService.initialize().success(function(response){
            if(response && response.status == 'SUCCESS'){
                populateTable();
            }else if(response && response.status == 'FAILURE'){
                alert("Data initialization failed. Please try again.");
            }
        }).error(function(error){
            alert("Error occured in fetchin data. Please try again.");
        })
    }

    var search_query_object = null;

    $scope.$watch('data.searchString',function(newValue, oldValue){
        if(newValue){
            if(newValue.length > 2){
                if(search_query_object != null){
                    $timeout.cancel(search_query_object);
                }

                search_query_object = $timeout(function(){
                    $http({
                        method: "get",
                        url: "../plivo/server/index.php/search",
                        timeout: 8000,
                        headers:{},
                        params: {
                            searchString:newValue
                        } 
                    }).success(function(response) {
                        $scope.errorString = null;
                        $scope.wordMeaningData = [];
                        $scope.page.total = response.total%10 ? Math.floor((response.total/10))+1 : response.total/10;
                        $scope.page.max = $scope.page.total < $scope.page.min+$scope.page.range-1 ? $scope.page.total : $scope.page.min+$scope.page.range-1;
                        $scope.page.current = $scope.page.min;
                        for(var i=0;i<response.data.length;i++){
                            $scope.wordMeaningData.push(response.data[i]);
                            $scope.lastPaginatedId = response.data[i].id;
                        }
                    }).error(function(error,status) {
                        $scope.errorString = "Error occured.Please try again.";
                    });
                },250);
            }else if(oldValue && oldValue.length > 2 && newValue.length <= 2){
                $scope.data.searchString = null;
                populateTable();
            }
        }
    });


});