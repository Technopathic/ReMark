angular.module('remark.directives', [])

.directive("navbar", function() {
  return {
    restrict: 'E',
    link: function(scope, element, attrs) {

    },
    templateUrl: 'views/templates/navbar.html'
  }
})

.directive("banner", function($interval, $http) {
  return {
    restrict: 'E',
    scope: {},
    link: function(scope, element, attrs) {
      scope.selectClass = attrs.selectclass;
      scope.transition = attrs.transition;
      scope.featureIndex = 0;

      scope.getFeatured = function() {
        $http.jsonp('api/getFeatured?callback=JSON_CALLBACK')
        .success(function(data)
        {
          scope.features = data;
        });
      };

      scope.setFeature = function (index) {
        scope.featureIndex = index;
      };

      if(scope.transition != "false") {
        $interval(function() {
          if(scope.featureIndex < scope.features.length - 1)
          {
            scope.setFeature(scope.featureIndex + 1);
          } else {
            scope.setFeature(0);
          }
        },10000);
      }

      scope.getFeatured();
    },
    templateUrl: 'views/templates/banner.html'
  }
})

.directive("channels", function($timeout, $http, $mdDialog) {
  return {
    restrict: 'E',
    scope: {},
    link: function(scope, element, attrs) {
      scope.boxSize = attrs.box;
      scope.channelsExcerpt = attrs.excerpt;

      scope.getChannels = function() {
        $http.jsonp('api/getChannels?callback=JSON_CALLBACK')
        .success(function (data){
          scope.channels = data;
        });
      };

      scope.getChannels();
    },
    templateUrl: 'views/templates/channels.html'
  }
})

.directive("topics", function($timeout, $http, $mdDialog) {
  return {
    restrict: 'E',
    scope: {},
    link: function(scope, element, attrs) {
      scope.boxSize = attrs.box;
      scope.topicsChannel = attrs.channel;
      scope.topicsCount = attrs.count;
      scope.topicsLength = attrs.length;
      scope.topicsPage = 1;
      scope.topicsTitle = attrs.title;
      scope.topicsExcerpt = attrs.excerpt;

      scope.getTopics = function(channel = 0, count = 6, length = 500, page = 1) {
        $http.jsonp('api/getTopics/channel='+channel+'&count='+count+'&length='+length+'?page='+page+'&callback=JSON_CALLBACK')
        .success(function (data){
          scope.topics = data;
        });
      };

      scope.getTopics(scope.topicsChannel, scope.topicsCount, scope.topicsLength, scope.topicsPage);
    },
    templateUrl: 'views/templates/topics.html'
  }
})

.directive("detail", function($timeout) {
  return {
    restrict: 'E',
    link: function(scope, element, attrs) {
      scope.headerClass = attrs.headerclass;
      scope.imageClass = attrs.imageclass;
      scope.audioClass = attrs.audioclass;
      scope.videoClass = attrs.videoclass;
      scope.skinClass = attrs.skinclass;
      scope.contentClass = attrs.contentclass;
    },
    templateUrl: 'views/templates/detail.html',
  }
})

.directive("stats", function() {
  return {
    restrict: 'E',
    link: function(scope, element, attrs) {
      scope.detailStatsClass = attrs.detailstatsclass;
      scope.statsTextClass = attrs.statstextclass;
    },
    templateUrl: 'views/templates/stats.html',
  }
})

.directive("author", function() {
  return {
    restrict: 'E',
    link: function(scope, element, attrs) {
      scope.authorClass = attrs.authorclass;
    },
    templateUrl: 'views/templates/author.html',
  }
})

.directive("replies", function($mdBottomSheet) {
  return {
    restrict: 'E',
    link: function(scope, element, attrs) {
      scope.replyClass = attrs.replyclass;
      scope.replyList = attrs.listclass;
      scope.itemClass = attrs.itemclass;
      scope.contentClass = attrs.contentclass;
      scope.buttonClass = attrs.buttonclass;
      scope.childClass = attrs.childclass;

      scope.makeReply = function(id) {
        $mdBottomSheet.show({
          templateUrl: 'views/templates/reply-Sheet.html',
          disableDrag:true,
          locals: {topicID: scope.topic.id, parentID: id},
          scope: scope.$new(),
          controller: 'DetailSheetCtrl'
        });
      };

      scope.toggleReplies = function(index) {
        if(scope.replies[index].showChildren == 0)
        {
          scope.replies[index].showChildren = 1;
        }
        else
        {
          scope.replies[index].showChildren = 0;
        }
      };

    },
    templateUrl: 'views/templates/replies.html',
  }
})
