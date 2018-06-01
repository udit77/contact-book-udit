cacheApp.factory('ContactService', function ($timeout, $http, $state) {
  
    self = {};

    self.initialize = function(){
       return $http({
            method: "post",
            url: "/server/initializeDb",
            data: {
            },
            timeout: 8000,
            headers:{'Authorization':'plivo123'} 
        }) 
    }

    self.fetchByOffset = function(offset){
        return $http({
            method: "get",
            url: "/server/allKeyValuesRange",
            params: {
                offset:offset
            },
            timeout: 8000,
	   headers:{'Authorization':'plivo123'}
        })      
    }

    self.fetchByOffsetAndSearch = function(offset,searchString){
        return $http({
            method: "get",
            url: "/server/searchByRange",
            params: {
                offset:offset,
                searchString:searchString
            },
            timeout: 8000,
	    headers:{'Authorization':'plivo123'}
        }) 
    }

    self.getData = function(){
        return $http({
            method: "get",
            url: "/server/allKeyValues",
            params: {
            },
            timeout: 8000,
	    headers:{'Authorization':'plivo123'}
        })
    }

    self.addData = function(information){
        console.log(information);
       return $http({
            method: "post",
            url: "/server/addContact",
            data: {
                name : information.name,
                email : information.email
            },
            timeout: 8000,
	    headers:{'Authorization':'plivo123'}
        }) 
    }

    self.updateContact = function(information){
        console.log(information);
       return $http({
            method: "post",
            url: "/server/updateContact",
            data: {
                name : information.name,
                email : information.email
            },
            timeout: 8000,
	    headers:{'Authorization':'plivo123'}
        }) 
    }

    self.deleteContact = function(email){
       return $http({
            method: "post",
            url: "/server/deleteContact",
            data: {
                email:email
            },
            timeout: 8000,
	    headers:{'Authorization':'plivo123'}
        }) 
    }
         
    return self;

})
