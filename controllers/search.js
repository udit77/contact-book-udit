cacheApp.controller('search', function($timeout, $http, $scope, $state, ContactService){
	
    $scope.contactDetails = [];
    $scope.dataCount = null;
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

    var setPagination = function(response){
        console.log(response);
        $scope.dataCount = response.total;
        $scope.page.total = response.total%10 ? Math.floor((response.total/10))+1 : response.total/10;
        $scope.page.max = $scope.page.total < $scope.page.min+$scope.page.range-1 ? $scope.page.total : $scope.page.min+$scope.page.range-1;
        console.log($scope.page);
    }

    var setData = function(response){
        $scope.errorString = null;
        $scope.contactDetails = [];
        for(var i=0;i<response.data.length;i++){
            $scope.contactDetails.push(response.data[i]);
        }
    }

    var setPreviousPage = function(){
        $scope.page.current = $scope.page.current-1;
        if($scope.page.current < $scope.page.min){
            $scope.page.min = $scope.page.min-$scope.page.range;
            $scope.page.max = $scope.page.total < $scope.page.min+$scope.page.range-1 ? $scope.page.total : $scope.page.min+$scope.page.range-1;
        }
    }

    var setNextPage = function(){
        $scope.page.current = $scope.page.current+1;
        if($scope.page.current > $scope.page.min+$scope.page.range-1){
            $scope.page.min = $scope.page.current;
            $scope.page.max = $scope.page.total < $scope.page.min+$scope.page.range-1 ? $scope.page.total : $scope.page.min+$scope.page.range-1;
        }
    }


    $scope.showPrevious = function(){
        var offset = ($scope.page.current-2)*10;
        if(!$scope.data.searchString || !$scope.data.searchString.trim()){
            ContactService.fetchByOffset(offset).success(function(response){
                setData(response);
                setPreviousPage();
            }).error(function(error){
                $scope.errorString = "Error occured. Please try again.";
            })
        }else{
            ContactService.fetchByOffsetAndSearch(offset,$scope.data.searchString).success(function(response){
                setData(response);
                setPreviousPage();
            }).error(function(error){
                $scope.errorString = "Error occured. Please try again.";
            })
        }       
    }

    $scope.showNext = function(){
        var offset = ($scope.page.current)*10;
        if(!$scope.data.searchString || !$scope.data.searchString.trim()){
            ContactService.fetchByOffset(offset).success(function(response){
               setData(response);
               setNextPage();
            }).error(function(error){
                $scope.errorString = "Error occured. Please try again.";
            })
        }else{
            ContactService.fetchByOffsetAndSearch(offset,$scope.data.searchString).success(function(response){
                setData(response);
                setNextPage();
            }).error(function(error){
                $scope.errorString = "Error occured. Please try again.";
            })
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
        $scope.updateShownForEmail = value.email;
        $scope.updateInformation = angular.copy(value);
        console.log($scope.updateShownForEmail);
    }

    $scope.updateCalledFor = function(value){
        return value.email === $scope.updateShownForEmail;
    }

    var populateTable = function(){
        ContactService.getData().success(function(response){
	    $scope.data.searchString = null;
            setPagination(response);
            setData(response);
        }).error(function(error){
            $scope.errorString = "Error occure in fetching data. Please refresh the page again.";
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
        ContactService.addData($scope.information).success(function(response){
            $scope.information = {};
            populateTable();       
        }).error(function(error){
	    if(error && error.msg)
		$scope.errorString = error.msg;
	    else
            $scope.errorString = "Error occured. Please try again.";
        })
    }

    $scope.updateContact = function(email,name){
        console.log(email,name);
        if(!name || !name.trim()){
            $scope.errorString = "Please enter a contact name.";
            return;
        }
        $scope.errorString = null;
        ContactService.updateContact({email:email,name:name}).success(function(response){
            $scope.updateShownForEmail = null;
            $scope.fetchByOffset($scope.page.current);       
        }).error(function(error){
            $scope.errorString = "Error occured. Please try again.";
        })
    }


    var handleDeleteContact = function(){
        if($scope.contactDetails.length){
            if($scope.contactDetails.length == 1){
               $scope.fetchByOffset($scope.page.current-1); 
            }else{
                $scope.fetchByOffset($scope.page.current);
            }
        }
    }


    $scope.deleteContact = function(value){
        console.log(value);
        $scope.errorString = null;
        ContactService.deleteContact(value.email).success(function(response){
            setPagination({total:$scope.dataCount-1});
            handleDeleteContact();       
        }).error(function(error){
            $scope.errorString = "Error occured. Please try again.";
        })
    }

    $scope.fetchByOffset = function(offset){
        console.log(offset);
        if(!$scope.data.searchString || !$scope.data.searchString.trim()){
            $scope.page.current = offset;
            var offset = (offset-1)*10;
            ContactService.fetchByOffset(offset).success(function(response){
                setData(response);
            }).error(function(error){
                 $scope.errorString = "Error occured. Please try again.";
            })    
        }else{
            $scope.page.current = offset;
            var offset = (offset-1)*10;
            ContactService.fetchByOffsetAndSearch(offset,$scope.data.searchString).success(function(response){
                setData(response);
            }).error(function(error){
                 $scope.errorString = "Error occured. Please try again.";
            })
        }
        
    }


    $scope.initializeSystem = function(){
        $scope.data.searchString = null;
        $scope.errorString = null;
        ContactService.initialize().success(function(response){
            if(response && response.status == 'SUCCESS'){
                populateTable();
            }else if(response && response.status == 'FAILURE'){
                alert("Data initialization failed. Please try again.");
            }
        }).error(function(error){
            alert("Error occured in fetching data. Please try again.");
        })
    }
    populateTable();



    var search_query_object = null;

    $scope.$watch('data.searchString',function(newValue, oldValue){
        if(newValue){
            if(newValue.length > 2){
                $scope.errorString = null;
                if(search_query_object != null){
                    $timeout.cancel(search_query_object);
                }

                search_query_object = $timeout(function(){
                    $http({
                        method: "get",
                        url: "/server/search",
                        timeout: 8000,
                        headers:{'Authorization':'plivo123'},
                        params: {
                            searchString: encodeURIComponent(newValue)
                        } 
                    }).success(function(response) {
			$scope.contactDetails = [];
			if(response.data.length){
                        $scope.errorString = null;
                        setPagination(response);
                        $scope.page.current = $scope.page.min;
                        for(var i=0;i<response.data.length;i++){
                            $scope.contactDetails.push(response.data[i]);
                            $scope.lastPaginatedId = response.data[i].id;
                        }
			}else{
				$scope.errorString = "No search results";
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
