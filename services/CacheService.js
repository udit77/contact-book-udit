cacheApp.factory('CacheService', function ($timeout, $http, $state) {
  
    self = {};

    self.get = function(key){
        return $http({
            method: "get",
            url: "../plivo/server/index.php/key",
            params: {
                word:key
            },
            timeout: 8000,
            headers:{} 
        })
    }

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


    self.getWordMeaningData = function(){
        return $http({
            method: "get",
            url: "../plivo/server/index.php/allKeyValues",
            params: {
            },
            timeout: 8000,
            headers:{} 
        })
    }

    self.getEvictedKeys = function(){
        return $http({
            method: "get",
            url: "../plivo/server/index.php/allEvictedkeys",
            params: {
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


    self.addToEvictedKey = function(key){
        return $http({
            method: "post",
            url: "../plivo/server/index.php/evictedKey",
            data: {
                word:key
            },
            timeout: 8000,
            headers:{}  
        })      
    }         
    return self;

})