angular.module('remark.services', [])

.service('installStart', ['$http', function($http) {

  this.getInstall = function(query) {
    return $http.get('getAPIInstall');
  };

}])

.service('mainStart', ['$http', function($http) {

  this.getMain = function(query) {
    return $http.get('api/main', {ignoreLoadingBar: true});
  };

}])

.service('detailStart', ['$http', function($http) {

  this.getDetail = function(query) {
    return $http.get('api/getDetail/' + query);
  };

  this.getReplies = function(query) {
    return $http.get('api/getReplies/' + query);
  };

}])

.service('channelStart', ['$http', function($http) {

  this.getChannels = function(query) {
    return $http.get('api/getChannels');
  };

  this.getChannel = function(query) {
    return $http.get('api/getChannel/' + query);
  };

}])

.service('dashboardStart', ['$http', '$rootScope', function($http, $rootScope) {

  this.getMain = function(query) {
    return $http.get('api/main');
  };

}])

.service('dashboardHomeStart', ['$http', '$rootScope', '$state', function($http, $rootScope, $state) {

  this.getHome = function(query) {
    return $http.get('dashboard?token='+$rootScope.currentToken);
  };

  this.getNotifications = function(query) {
    return $http.get('dashboard/notifications?token='+ query);
  };

  this.getFeeds = function(query) {
    return $http.get('dashboard/getFeeds?token='+$rootScope.currentToken);
  };

}])

.service('dashboardContentStart', ['$http', '$rootScope', function($http, $rootScope) {

  this.getContent = function(query) {
    return $http.get('dashboard/getContent?token='+$rootScope.currentToken);
  };

}])

.service('dashboardUserStart', ['$http', '$rootScope', function($http, $rootScope) {

  this.getUsers = function(query) {
    return $http.get('dashboard/getUsers?token='+$rootScope.currentToken);
  };

}])

.service('dashboardOptionStart', ['$http', '$rootScope', function($http, $rootScope) {

  this.getOptions = function(query) {
    return $http.get('dashboard/getOptions?token='+$rootScope.currentToken);
  };

}])
