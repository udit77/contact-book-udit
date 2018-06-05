cacheApp.factory('ContactService', function ($timeout, $http, $state) {
  
    self = {};
    var uri = "http://ec2-52-14-15-6.us-east-2.compute.amazonaws.com:5000";
    var token = "4074338336406583e2d6be69f37ebae56a3862e5";

    self.initialize = function(){
       return $http({
            method: "post",
            url: "/server/initializeDb",
            data: {
            },
            timeout: 8000,
            headers:{'Authorization':token} 
        }) 
    }

    self.fetchByOffset = function(offset){
        return $http({
            method: "get",
            url: uri+"/contacts/range",
            params: {
                offset:offset
            },
            timeout: 8000,
	   headers:{'Authorization':token}
        })      
    }

    self.fetchByOffsetAndSearch = function(offset,searchString){
        return $http({
            method: "get",
            url: uri+"/search/range",
            params: {
                offset:offset,
                search_string:searchString
            },
            timeout: 8000,
	    headers:{'Authorization':token}
        }) 
    }

    self.getData = function(){
        return $http({
            method: "get",
            url: uri+"/contacts",
            params: {
            },
            timeout: 8000,
	    headers:{'Authorization':token}
        })
    }

    self.addData = function(information){
        console.log(information);
       return $http({
            method: "post",
            url: uri+"/add",
            data: {
                name : information.name,
                email : information.email
            },
            timeout: 8000,
	    headers:{'Authorization':token}
        }) 
    }

    self.updateContact = function(information){
        console.log(information);
       return $http({
            method: "post",
            url: uri+"/update",
            data: {
                name : information.name,
                email : information.email
            },
            timeout: 8000,
	    headers:{'Authorization':token}
        }) 
    }

    self.deleteContact = function(email){
       return $http({
            method: "post",
            url: uri+"/delete",
            data: {
                email:email
            },
            timeout: 8000,
	    headers:{'Authorization':token}
        }) 
    }
         
    return self;

})
