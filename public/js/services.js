angular.module('remark.services', [])

.service('MetaService', function() {
  var title = '';
  var metaDescription = '';
  var metaKeywords = '';

  return {
    set: function(newTitle, newMetaDescription, newKeywords) {
      metaKeywords = newKeywords;
      metaDescription = newMetaDescription;
      title = newTitle;
    },
    metaTitle: function(){ return title; },
    metaDescription: function() { return metaDescription; },
    metaKeywords: function() { return metaKeywords; }
  }
})

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

.service('detailStart', ['$http', '$rootScope', function($http, $rootScope) {

  this.getDetail = function(query) {
    return $http.get('api/getDetail/' + query).success(function(data) {
      $rootScope.metaservice.set(data.topic.topicTitle, data.topic.topicBody.substring(0,250), data.topic.topicTags);
    })
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
