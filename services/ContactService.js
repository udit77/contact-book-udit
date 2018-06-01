cacheApp.factory('ContactService', function ($timeout, $http, $state) {
  
    self = {};

    self.initialize = function(){
       return $http({
            method: "post",
            url: "../plivo/server/index.php/initializeDb",
            data: {
            },
            timeout: 8000,
            headers:{} 
        }) 
    }

    self.fetchByOffset = function(offset){
        return $http({
            method: "get",
            url: "../plivo/server/index.php/allKeyValuesRange",
            params: {
                offset:offset
            },
            timeout: 8000,
            headers:{}  
        })      
    }

    self.fetchByOffsetAndSearch = function(offset,searchString){
        return $http({
            method: "get",
            url: "../plivo/server/index.php/searchByRange",
            params: {
                offset:offset,
                searchString:searchString
            },
            timeout: 8000,
            headers:{}  
        }) 
    }

    self.getData = function(){
        return $http({
            method: "get",
            url: "../plivo/server/index.php/allKeyValues",
            params: {
            },
            timeout: 8000,
            headers:{} 
        })
    }

    self.addData = function(information){
        console.log(information);
       return $http({
            method: "post",
            url: "../plivo/server/index.php/addContact",
            data: {
                name : information.name,
                email : information.email
            },
            timeout: 8000,
            headers:{} 
        }) 
    }

    self.updateContact = function(information){
        console.log(information);
       return $http({
            method: "post",
            url: "../plivo/server/index.php/updateContact",
            data: {
                name : information.name,
                email : information.email
            },
            timeout: 8000,
            headers:{} 
        }) 
    }

    self.deleteContact = function(email){
       return $http({
            method: "post",
            url: "../plivo/server/index.php/deleteContact",
            data: {
                email:email
            },
            timeout: 8000,
            headers:{} 
        }) 
    }
         
    return self;

})