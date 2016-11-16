angular.module('remark.services', [])

.service('installStart', ['$http', function($http) {

  this.getInstall = function(query) {
    return $http.jsonp('getAPIInstall?callback=JSON_CALLBACK');
  };

}])

.service('mainStart', ['$http', function($http) {

  this.getMain = function(query) {
    return $http.jsonp('api/main?callback=JSON_CALLBACK', {ignoreLoadingBar: true});
  };

}])

.service('detailStart', ['$http', function($http) {

  this.getDetail = function(query) {
    return $http.jsonp('api/getDetail/' + query + '?callback=JSON_CALLBACK');
  };

  this.getReplies = function(query) {
    return $http.jsonp('api/getReplies/' + query + '?callback=JSON_CALLBACK');
  };

}])

.service('channelStart', ['$http', function($http) {

  this.getChannels = function(query) {
    return $http.jsonp('api/getChannels?callback=JSON_CALLBACK');
  };

  this.getChannel = function(query) {
    return $http.jsonp('api/getChannel/' + query + '?callback=JSON_CALLBACK');
  };

}])

.service('dashboardStart', ['$http', '$rootScope', function($http, $rootScope) {

  this.getMain = function(query) {
    return $http.jsonp('api/main?&callback=JSON_CALLBACK');
  };

}])

.service('dashboardHomeStart', ['$http', '$rootScope', '$state', function($http, $rootScope, $state) {

  this.getHome = function(query) {
    return $http.jsonp('dashboard?token='+$rootScope.currentToken+'&callback=JSON_CALLBACK');
  };

  this.getNotifications = function(query) {
    return $http.jsonp('dashboard/notifications?token='+ query +'&callback=JSON_CALLBACK');
  };

  this.getFeeds = function(query) {
    return $http.jsonp('dashboard/getFeeds?token='+$rootScope.currentToken+'&callback=JSON_CALLBACK');
  };

}])

.service('dashboardContentStart', ['$http', '$rootScope', function($http, $rootScope) {

  this.getContent = function(query) {
    return $http.jsonp('dashboard/getContent?token='+$rootScope.currentToken+'&callback=JSON_CALLBACK');
  };

}])

.service('dashboardUserStart', ['$http', '$rootScope', function($http, $rootScope) {

  this.getUsers = function(query) {
    return $http.jsonp('dashboard/getUsers?token='+$rootScope.currentToken+'&callback=JSON_CALLBACK');
  };

}])

.service('dashboardOptionStart', ['$http', '$rootScope', function($http, $rootScope) {

  this.getOptions = function(query) {
    return $http.jsonp('dashboard/getOptions?token='+$rootScope.currentToken+'&callback=JSON_CALLBACK');
  };

}])
