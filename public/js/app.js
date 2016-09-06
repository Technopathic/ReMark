angular.module('remark', ['ui.router', 'angular-loading-bar', 'angularMoment', 'ngMaterial', 'ngMessages', 'ngSanitize', 'ng-showdown', 'cfp.hotkeys', 'ngFileUpload', 'remark.controllers', 'remark.dashboard', 'remark.directives', 'remark.services'])

.run(['$rootScope', '$state', '$interval', '$http', function($rootScope, $state, $interval, $http) {
  $rootScope.$on('$stateChangeStart', function(event, toState) {
    var user = JSON.parse(localStorage.getItem('user'));
    var token = JSON.parse(localStorage.getItem('token'));
    if(user && token) {
      $rootScope.authenticated = true;
      $rootScope.currentUser = user;
      $rootScope.currentToken = token;
    }
  });

  $http.jsonp('api/getInfo?callback=JSON_CALLBACK').success(function(data) {
    $rootScope.title = data.website;
  });

  if($rootScope.authenticated === true) {
    $interval(function() {
      $http.jsonp('api/refreshToken?token='+$rootScope.currentToken+'&callback=JSON_CALLBACK')
      .success(function(data) {
        localStorage.setItem('token', data);
        $rootScope.currentToken = data;
      });
    }, 3600000);
  }
}])

.config(['cfpLoadingBarProvider', '$stateProvider', '$urlRouterProvider', '$locationProvider', '$mdThemingProvider', '$showdownProvider', function(cfpLoadingBarProvider, $stateProvider, $urlRouterProvider, $locationProvider, $mdThemingProvider, $showdownProvider) {
  $mdThemingProvider.theme('default').primaryPalette('grey', { 'hue-1': '50'});
  $locationProvider.html5Mode(true);
  cfpLoadingBarProvider.includeSpinner = false;
  $showdownProvider.loadExtension('youtube')

  $stateProvider

  .state('install', {
    url: '/install',
    templateUrl: 'views/install.html',
    controller: 'InstallCtrl',
    resolve: {
      installDep: ['installStart', function(installStart) {
        return installStart.getInstall();
      }]
    }
  })

  .state('passwordReset', {
    url: '/passwordReset/:token',
    templateUrl: 'views/auth.html',
    controller: 'AuthCtrl'
  })

  .state('main', {
    templateUrl: 'views/main.html',
    controller: 'MainCtrl',
    resolve: {
      mainData: ['mainStart', function(mainStart) {
          return mainStart.getMain();
      }]
    },
    abstract: true
  })

  .state('main.home', {
    url: '/',
    templateUrl: 'views/home.html',
    controller: 'HomeCtrl'
  })

  .state('main.details', {
    url: '/topic/:topicSlug',
    templateUrl: 'views/details.html',
    controller: 'DetailCtrl',
    resolve: {
      detailData: ['detailStart','$stateParams', function(detailStart, $stateParams) {
          return detailStart.getDetail($stateParams.topicSlug);
      }],
      replyData: ['detailStart','$stateParams', function(detailStart, $stateParams) {
          return detailStart.getReplies($stateParams.topicSlug);
      }]
    }
  })

  .state('main.channels', {
    url: '/channels',
    templateUrl: 'views/channels.html',
    controller: 'ChannelsCtrl',
    resolve: {
      channelsData: ['channelStart', function(channelStart) {
          return channelStart.getChannels();
      }]
    }
  })

  .state('main.channel', {
    url: '/channel/:channelSlug',
    templateUrl: 'views/channel.html',
    controller: 'ChannelCtrl',
    resolve: {
      channelData: ['channelStart','$stateParams', function(channelStart, $stateParams) {
          return channelStart.getChannel($stateParams.channelSlug);
      }]
    }
  })

  .state('main.notify', {
    url: '/notify/:notifyType/:token',
    templateUrl: 'views/notify.html',
    controller: 'NotifyCtrl'
  })

  .state('dashboard', {
    url: '/dashboard',
    templateUrl: 'views/dashboard/main.html',
    controller: 'DashboardCtrl',
    resolve: {
      security: ['$q', '$rootScope',  function($q, $rootScope){
          if($rootScope.currentUser.role !== "1") {
            return $q.reject("Not Authorized");
          }
      }],
      mainData: ['dashboardStart', function(dashboardStart) {
          return dashboardStart.getMain();
      }]
    },
    abstract: true
  })

  .state('dashboard.home', {
    url: '/home',
    templateUrl: 'views/dashboard/home.html',
    controller: 'DashboardHomeCtrl',
    resolve: {
      homeData: ['dashboardHomeStart', function(dashboardHomeStart) {
          return dashboardHomeStart.getHome();
      }],
      notificationData: ['dashboardHomeStart', '$rootScope', function(dashboardHomeStart, $rootScope) {
          return dashboardHomeStart.getNotifications($rootScope.currentToken);
      }]
    }
  })

  .state('dashboard.content', {
    url: '/content',
    templateUrl: 'views/dashboard/content.html',
    controller: 'DashboardContentCtrl',
    resolve: {
      contentData: ['dashboardContentStart', function(dashboardContentStart) {
          return dashboardContentStart.getContent();
      }]
    }
  })

  .state('dashboard.users', {
    url: '/users',
    templateUrl: 'views/dashboard/users.html',
    controller: 'DashboardUsersCtrl',
    resolve: {
      userData: ['dashboardUserStart', function(dashboardUserStart) {
          return dashboardUserStart.getUsers();
      }]
    }
  })

  .state('dashboard.options', {
    url: '/options',
    templateUrl: 'views/dashboard/options.html',
    controller: 'DashboardOptionsCtrl',
    resolve: {
      optionData: ['dashboardOptionStart', function(dashboardOptionStart) {
          return dashboardOptionStart.getOptions();
      }]
    }
  })

  $urlRouterProvider.otherwise('/');


}]);
