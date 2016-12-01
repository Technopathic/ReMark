angular.module('remark.dashboard', [])

.controller('DashboardCtrl', ['$scope', '$state', '$http', '$rootScope', '$mdDialog', '$mdToast', '$mdBottomSheet', 'mainData', function($scope, $state, $http, $rootScope, $mdDialog, $mdToast, $mdBottomSheet, mainData) {

    $scope.mainOptions = mainData.data.options;

    $scope.notifyToast = function(message) {
      $mdToast.show(
        $mdToast.simple()
          .textContent(message)
          .position('bottom left')
          .hideDelay(3000)
      );
    };

    $scope.editProfileDialog = function(ev) {
      $mdDialog.show({
        templateUrl: 'views/templates/profileEdit-Dialog.html',
        parent: angular.element(document.body),
        targetEvent: ev,
        clickOutsideToClose:true,
        bindToController: true,
        controller: 'ProfileEditCtrl'
      })
    };

    $scope.openMenu = function($mdOpenMenu, ev) {
      originatorEv = ev;
      $mdOpenMenu(ev);
    };

    $scope.dialogClose = function() {
      $mdDialog.hide();
    };

    $scope.signOut = function() {
     localStorage.removeItem('user');
     $rootScope.authenticated = false;
     $rootScope.currentUser = null;
     $rootScope.currentToken = null;
     $state.go('main.home');
     $scope.notifyToast('Bye for now! Hope to see you again soon!');
   };

    $scope.footerMenu = function() {
      $mdBottomSheet.show({
        templateUrl: 'footerMenu.html',
        escapeToClose: true,
        clickOutsideToClose: true,
        locals: {mainOptions:$scope.mainOptions},
        controller:
         function($scope, $mdBottomSheet, mainOptions) {
           $scope.mainOptions = mainOptions;
           $scope.closeSheet = function(){
             $mdBottomSheet.hide();
           };
         }
      });
    };

    $scope.userMenu = function() {
      $mdBottomSheet.show({
        templateUrl: 'userMenu.html',
        escapeToClose: true,
        clickOutsideToClose: true,
        locals: {editProfile: $scope.editProfileDialog, signOut: $scope.signOut},
        controller:
          function($scope, $mdBottomSheet, editProfile, signOut) {
            $scope.editProfileDialog = editProfile;
            $scope.signOut = signOut;
            $scope.closeSheet = function(){
              $mdBottomSheet.hide();
            };
          }
      });
    };

    $scope.makeContent = function() {
      $mdBottomSheet.show({
        templateUrl: 'makeTopic.html',
        scope: $scope.$new(),
        escapeToClose: true,
        clickOutsideToClose: true,
        locals: {topicData: {data:""}, channelData: {data:""}, replyData:{data: ""}},
        controller: 'ContentSheetCtrl'
      });
    };

}])

.controller('NotificationsCtrl', ['$scope', '$state', '$rootScope', '$http', '$mdDialog', '$mdToast', 'showType', function($scope, $state, $rootScope, $http, $mdDialog, $mdToast, showType) {

  $scope.notifications = {};

  $scope.type = showType;

  $scope.showNotifications = function() {
    $http.get('dashboard/showNotifications/'+$scope.type+'?token='+$rootScope.currentToken)
    .success(function (data){
      $scope.notifications = data;
    });
  };

  $scope.moreNotiReply = function(page = 1) {
    $http.get('dashboard/showNotifications/'+$scope.type+'?page='+page+'token='+$rootScope.currentToken)
    .success(function (data){
      $scope.notifications.replies.data.push(data.replies.data);
      $scope.notifications.replies.current_page = data.replies.current_page;
    });
  };

  $scope.moreNotiVote = function(page = 1) {
    $http.get('dashboard/showNotifications/'+$scope.type+'?page='+page+'token='+$rootScope.currentToken)
    .success(function (data){
      $scope.notifications.votes.data.push(data.votes.data);
      $scope.notifications.votes.current_page = data.votes.current_page;
    });
  };

  $scope.moreNotiMessage = function(page = 1) {
    $http.get('dashboard/showNotifications/'+$scope.type+'?page='+page+'token='+$rootScope.currentToken)
    .success(function (data){
      $scope.notifications.messages.data.push(data.messages.data);
      $scope.notifications.messages.current_page = data.messages.current_page;
    });
  };

  $scope.openNotification = function(id) {
    $http.get('dashboard/openNotification/'+id+'?token='+$rootScope.currentToken);
  };

  $scope.showMessage = function(ev, id) {
    $mdDialog.show({
      templateUrl: 'showMessage.html',
      parent: angular.element(document.body),
      targetEvent: ev,
      clickOutsideToClose:true,
      locals: {id: id},
      controller: 'messageCtrl'
    })
  };

  $scope.deleteNotification = function(id, index, subType = 0) {
    $http({
        method: 'POST',
        url: 'dashboard/deleteNotification/'+id+'?token='+$rootScope.currentToken,
        data: {},
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function (data){
      if(data == 1)
      {
        $scope.notifyToast('Notification Deleted');
        if($scope.type == 'Alert')
        {
          if(subType == 'Reply')
          {
            $scope.notifications.replies.data.splice(index, 1);
          }
          else if(subType == 'Vote')
          {
            $scope.notifications.votes.data.splice(index, 1);
          }
        }
        else if($scope.type == 'Message')
        {
          $scope.notifications.messages.data.splice(index, 1);
        }
      }
    });
  };

  $scope.showNotifications();
}])

.controller('messageCtrl', ['$scope', '$state', '$rootScope', '$http', '$mdDialog', '$mdToast', 'id', function($scope, $state, $rootScope, $http, $mdDialog, $mdToast, id) {

  $scope.message = {};

  var messageID = id;

  $scope.getMessage = function() {
    $http.get('dashboard/showMessage/'+messageID+'?token='+$rootScope.currentToken)
    .success(function (data){
      $scope.message = data;
    });
  };

  $scope.dialogClose = function() {
    $mdDialog.hide();
  };

  $scope.getMessage();
}])

.controller('DashboardHomeCtrl', ['$scope', '$rootScope', '$state', '$http', '$mdDialog', '$mdBottomSheet', 'homeData', 'notificationData', 'feedData', function($scope, $rootScope, $state, $http, $mdDialog, $mdBottomSheet, homeData, notificationData, feedData) {

  $scope.replies = homeData.data.replies;
  $scope.topics = homeData.data.topics;
  $scope.users = homeData.data.users;
  $scope.feeds = feedData.data;
  $scope.catalogue = {};
  $scope.bookmarks = {};

  $scope.loadFeed = null;

  $scope.notifications = {
    alerts: notificationData.data.alerts,
    messages: notificationData.data.messages,
    globals: notificationData.data.globals
  };

  $scope.showNotifications = function(ev, type) {
    $mdDialog.show({
      templateUrl: 'showNotifications.html',
      parent: angular.element(document.body),
      targetEvent: ev,
      clickOutsideToClose:true,
      scope: $scope.$new(),
      locals: {showType:type},
      controller: 'NotificationsCtrl'
    })
  };


  $scope.getCatalogue = function() {
    $http.get('dashboard/getCatalogue?token='+$rootScope.currentToken)
    .success(function (data){
      $scope.catalogue = data;
    });
  };

  $scope.getBookmarks = function() {
    $http.get('dashboard/getBookmarks?token='+$rootScope.currentToken)
    .success(function (data){
      $scope.bookmarks = data;
    });
  };

  $scope.runFeed = function(ev, feed, input = "false") {
    if(input == "true" )
    {
      $mdDialog.show({
        templateUrl: 'showCustomFeed.html',
        parent: angular.element(document.body),
        targetEvent: ev,
        clickOutsideToClose:true,
        locals: {feed: feed, input: input},
        scope: $scope.$new(),
        preserveScope: true,
        controller:
          function($scope, $rootScope, $mdDialog, $mdToast, feed, input) {
            $scope.customFeedUrl = null;
            var feed = feed;
            var input = input
            $scope.closeDialog = function() {
              $mdDialog.hide();
            };
            $scope.customFeed = function() {
              $http({
                  method: 'POST',
                  url: 'dashboard/runFeed?token='+$rootScope.currentToken,
                  data: { feed: feed, input:input, custom:$scope.customFeedUrl },
                  headers: {'Content-Type': 'application/x-www-form-urlencoded'}
              }).success(function (data){
                if(data != 2 && data != 0 && data != 403)
                {
                  $scope.notifyToast('Feed Installed.');
                  $scope.feeds.push(data);
                  $scope.closeDialog();
                }
                else if(data == 2)
                {
                  $scope.notifyToast('Feed already installed.');
                }
                else if(data == 0)
                {
                  $scope.notifyToast('Not a valid Feed.');
                }
              }).error(function() {
                $mdToast.show(
                  $mdToast.simple()
                    .textContent("Not a valid URL.")
                    .position('bottom left')
                    .hideDelay(3000)
                );
              });
            };
          }
      })
    }
    else {
      $http({
          method: 'POST',
          url: 'dashboard/runFeed?token='+$rootScope.currentToken,
          data: { feed: feed, input:input, custom:"" },
          headers: {'Content-Type': 'application/x-www-form-urlencoded'}
      }).success(function(data){
        if(data != 2 && data != 0 && data != 403)
        {
          $scope.notifyToast('Feed Installed.');
          $scope.feeds.push(data);
        }
        else if(data == 2)
        {
          $scope.notifyToast('Feed already installed.');
        }
        else if(data == 0)
        {
          $scope.notifyToast('Not a valid Feed.');
        }
      }).error(function(data) {
        $scope.notifyToast('Feed unable to be Installed');
      });
    }
  };

  $scope.selectFeed = function(id) {
    $http.get('dashboard/selectFeed/'+id+'?token='+$rootScope.currentToken)
    .success(function (data){
      $scope.loadFeed = data;
    });
  };

  $scope.deleteFeed = function(id) {
    $http({
      method: 'POST',
      url: 'dashboard/deleteFeed?token='+$rootScope.currentToken,
      data: {id:id},
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function(data){
      $scope.notifyToast('Feed deleted');
      $scope.goBack();
      angular.forEach($scope.feeds, function(value, key) {
        if(value.id == id)
        {
          $scope.feeds.splice(key, 1);
        }
      })
    })
  };

  $scope.getBookmarks = function(page = 1) {
    $http.get('dashboard/getBookmarks?page='+page+'&token='+$rootScope.currentToken)
    .success(function (data){
      $scope.bookmarks = data;
    });
  };

  $scope.bookmarkFeed = function(index) {
    angular.forEach($scope.loadFeed.result, function(value, key) {
      if(index === key)
      {
        $http({
            method: 'POST',
            url: 'dashboard/bookmarkFeed?token='+$rootScope.currentToken,
            data: { feedID: $scope.loadFeed.feed.id, bookmarkDomain: $scope.loadFeed.feed.feedUrl, bookmarkTitle: value.title , bookmarkImg: value.media, bookmarkAuthor: value.author , bookmarkSource: value.link  },
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        }).success(function(data){
          if(data == 1)
          {
            $scope.notifyToast('Feed Bookmarked!');
          } else {
            $scope.notifyToast('Feed unbookmarked.');
            $scope.bookmarks.data.splice(index, 1);
          }
        });
      }
    });
  };

  $scope.unBookmarkFeed = function(id, index) {
    $http({
        method: 'POST',
        url: 'dashboard/unBookmarkFeed?token='+$rootScope.currentToken,
        data: { id: id  },
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function(data){
      if(data == 1)
      {
        $scope.notifyToast('Feed unbookmarked!');
        $scope.bookmarks.data.splice(index, 1);
      }
    });
  };

  $scope.goBack = function() {
    $scope.loadFeed = null;
  };

  $scope.replyFeed = function(feed, options) {
    if(options.prependMedia)
    {
      feed.media = options.prependMedia+feed.media;
    }
    if(options.prependLinks)
    {
      feed.link = options.prependLinks+feed.link;
    }
    var feedTitle = feed.title.replace(/["']/g, "");
    var feedAuthor = feed.author.replace(/["']/g, "");
    var topicBody = "<div class='uniBox' layout='row' layout-margin ng-href='"+feed.link+"'><img src='"+feed.media+"' flex-sm='10' flex-gt-sm='10'/><div layout='column' flex-sm='10' flex-gt-sm='80'><h2>"+feedTitle+"</h2><span>"+feedAuthor+"</span></div></div>";

    $scope.topicDetail = {
      topicTitle: "Re: "+feed.title,
      topicBody: topicBody,
      topicImg: feed.media,
      topicStatus: "",
    };
    $mdBottomSheet.show({
      templateUrl: 'makeTopic.html',
      scope: $scope.$new(),
      escapeToClose: true,
      clickOutsideToClose: true,
      locals: {topicData: {data:$scope.topicDetail}, channelData: {data:""}, replyData:{data:""}},
      controller: 'ContentSheetCtrl'
    });
  };

}])


//Begin Dashboard Content
.controller('DashboardContentCtrl', ['$scope', '$rootScope', '$state', '$http', '$filter', '$mdBottomSheet', '$mdToast', '$mdSidenav', 'contentData', function($scope, $rootScope, $state, $http, $filter, $mdBottomSheet, $mdToast, $mdSidenav, contentData) {

  $scope.channels = contentData.data.channels;
  $scope.topics = contentData.data.topics;
  $scope.topicsFilter = contentData.data.topics;
  $scope.replies = {};
  $scope.activeChannel = 0;
  $scope.activeTopic = null;
  $scope.topicDetail = null;
  $scope.channelDetail = null;

  $scope.channelDelete = null;
  $scope.topicDelete = null;

  var orderBy = $filter('orderBy');

  $scope.convertPageMenu = function() {
    angular.forEach($scope.topics.data, function(value, key) {
      value.pageMenu = Number(value.pageMenu);
    })
  };

  $scope.convertChannelMenu = function() {
    angular.forEach($scope.channels.data, function(value, key) {
      value.channelMenu = Number(value.channelMenu);
    })
  };

  $scope.notifyToast = function(message) {
    $mdToast.show(
      $mdToast.simple()
        .textContent(message)
        .position('bottom left')
        .hideDelay(3000)
    );
  };

  $scope.sortMenu = function($mdOpenMenu, ev) {
    originatorEv = ev;
    $mdOpenMenu(ev);
  };

  $scope.openLeftContent = function() {
    $mdSidenav('leftContent').toggle();
  };

  $scope.openRightContent = function() {
    $mdSidenav('rightContent').toggle();
  };

  $scope.getTopics = function(id, index) {
    $http.get('dashboard/getTopics/'+id+'?token='+$rootScope.currentToken)
    .success(function (data){
      $scope.topics = data;
      $scope.convertPageMenu();
    });
    $scope.activeChannel = id;
    $scope.channelDetail = $scope.channels.data[index];
  };

  $scope.moreChannels = function(){
    var page = $scope.channels.current_page + 1;
    $http.get('dashboard/getChannels?page='+page+'&token='+$rootScope.currentToken)
    .success(function (data) {
      angular.forEach(data.data, function(value) {
        $scope.channels.data.push(data.data);
        $scope.convertChannelMenu();
      });
      $scope.channels.current_page = data.current_page;
    });
  };

  $scope.moreTopics = function() {
    var page = $scope.topics.current_page + 1;
    $http.get('dashboard/getTopics/'+$scope.activeChannel+'?page='+page+'&token='+$rootScope.currentToken)
    .success(function (data){
      angular.forEach(data.data, function(value) {
        $scope.topics.data.push(value);
        $scope.convertPageMenu();
      });
      $scope.topics.current_page = data.current_page;
    });
  };

  $scope.selectTopic = function(id) {
    $scope.activeTopic = id;
    $http.get('dashboard/getTopic/'+id+'?token='+$rootScope.currentToken)
    .success(function (data){
      $scope.topicDetail = data.topic;
      $scope.replies = data.replies;
      //$scope.activeChannel = $scope.topicDetail.topicChannel;
    });
  };

  $scope.setFeature = function(id, index) {
    $http.get('dashboard/setFeature/'+id+'?token='+$rootScope.currentToken)
    .success(function (data){
      if(data == 1)
      {
        $scope.topics.data[index].topicFeature = 1;
        $scope.notifyToast('Topic is now Featured!');
      }
      else if(data == 0)
      {
        $scope.topics.data[index].topicFeature = 0;
        $scope.notifyToast('Topic removed from Featured.');
      }
      else if(data == 2)
      {
        $scope.notifyToast('Featured Topic must contain a cover image.');
      }
    });
  };

  $scope.sortTopics = function(sortType) {
    var inverse = true;
    if(sortType == 'topicTitle' && $scope.topicSort != 'topicTitleInverse')
    {
      var inverse = false;
      $scope.topics.data = orderBy($scope.topics.data, sortType, inverse);
      $scope.topicSort = 'topicTitleInverse';
    }
    else if(sortType == 'topicTitle' && $scope.topicSort != 'topicTitle')
    {
      $scope.topics.data = orderBy($scope.topics.data, sortType, inverse);
      $scope.topicSort = 'topicTitle';
    }
    else if(sortType == 'topicDate' && $scope.topicSort != 'topicDate')
    {
      $scope.topics.data = orderBy($scope.topics.data, sortType, inverse);
      $scope.topicSort = 'topicDate';
    }
    else if(sortType == 'topicDate' && $scope.topicSort != 'topicDateInverse')
    {
      var inverse = false;
      $scope.topics.data = orderBy($scope.topics.data, sortType, inverse);
      $scope.topicSort = 'topicDateInverse';
    }
    else if(sortType == 'topicModified' && $scope.topicSort != 'topicModified')
    {
      $scope.topics.data = orderBy($scope.topics.data, sortType, inverse);
      $scope.topicSort = 'topicModified';
    }
    else if(sortType == 'topicModified' && $scope.topicSort != 'topicModifiedInverse')
    {
      var inverse = false;
      $scope.topics.data = orderBy($scope.topics.data, sortType, inverse);
      $scope.topicSort = 'topicModifiedInverse';
    }
  };

  $scope.deleteTopicConfirm = function(id) {
    $scope.topicDelete = id;
  };

  $scope.deleteTopic = function(id, index) {
    $http({
        method: 'POST',
        url: 'dashboard/deleteTopic/'+id+'?token='+$rootScope.currentToken,
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function (data){
      if(data == 1)
      {
        $scope.notifyToast('Topic Deleted.');
        $scope.topics.data.splice(index, 1);
      }
    });
  };

  $scope.pageMenu = function(id) {
    $http.get('dashboard/pageMenu/'+id+'?token='+$rootScope.currentToken)
    .success(function (data){
      if(data == 1)
      {
        $scope.notifyToast('Page added to Menu.');
      }
      else if(data == 0)
      {
        $scope.notifyToast('Page removed from Menu.');
      }
    });
  };

  $scope.deleteChannelConfirm = function(id) {
    $scope.channelDelete = id;
  };

  $scope.deleteChannel = function(id, index) {
    $http({
        method: 'POST',
        url: 'dashboard/deleteChannel/'+id+'?token='+$rootScope.currentToken,
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function (data){
      if(data == 1)
      {
        $scope.notifyToast('Channel Deleted.');
        $scope.channels.data.splice(index, 1);
      }
      else if(data == 0) {
        $scope.notifyToast('You cannot delete this channel.');
      }
    });
  };

  $scope.sortChannels = function(sortType) {
    var inverse = true;
    if(sortType == 'channelTitle' && $scope.channelSort != 'channelTitleInverse')
    {
      var inverse = false;
      $scope.channels.data = orderBy($scope.channels.data, sortType, inverse);
      $scope.channelSort = 'channelTitleInverse';
    }
    else if(sortType == 'channelTitle' && $scope.channelSort != 'channelTitle')
    {
      $scope.channels.data = orderBy($scope.channels.data, sortType, inverse);
      $scope.channelSort = 'channelTitle';
    }
    else if(sortType == 'channelDate' && $scope.channelSort != 'channelDate')
    {
      $scope.channels.data = orderBy($scope.channels.data, sortType, inverse);
      $scope.channelSort = 'channelDate';
    }
    else if(sortType == 'channelDate' && $scope.channelSort != 'channelDateInverse')
    {
      var inverse = false;
      $scope.channels.data = orderBy($scope.channels.data, sortType, inverse);
      $scope.channelSort = 'channelDateInverse';
    }
    else if(sortType == 'channelModified' && $scope.channelSort != 'channelModified')
    {
      $scope.channels.data = orderBy($scope.channels.data, sortType, inverse);
      $scope.channelSort = 'channelModified';
    }
    else if(sortType == 'channelModified' && $scope.channelSort != 'channelModifiedInverse')
    {
      var inverse = false;
      $scope.channels.data = orderBy($scope.channels.data, sortType, inverse);
      $scope.channelSort = 'channelModifiedInverse';
    }
  };

  $scope.channelMenu = function(id) {
    $http.get('dashboard/channelMenu/'+id+'?token='+$rootScope.currentToken)
    .success(function (data){
      if(data == 1)
      {
        $scope.notifyToast('Channel added to Menu.');
      }
      else if(data == 0)
      {
        $scope.notifyToast('Channel removed from Menu.');
      }
    });
  };

  $scope.unflag = function(id, index, type) {
    $http.get('dashboard/unflagReply/'+id+'?token='+$rootScope.currentToken)
    .success(function (data){
      if(data == 1)
      {
        $scope.notifyToast('Reply Unflagged.');
        if(type == 'reply')
        {
          $scope.replies[index].replyFlagged = 0;
        }
        else if(type == 'childReply')
        {
          angular.forEach($scope.replies, function(value) {
            value.childReplies[index].replyFlagged = 0;
          });
        }
      }
    });
  };

  $scope.deleteReply = function(id, index, type) {
    $http({
        method:'POST',
        url: 'dashboard/deleteReply?token='+$rootScope.currentToken,
        data: {replyID: id},
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    })
    .success(function(data){
      if(data == 1)
      {
        $scope.notifyToast('Reply Deleted.');
        if(type == 'reply')
        {
          $scope.replies.splice(index, 1);
        }
        else if(type == 'childReply')
        {
          angular.forEach($scope.replies, function(value) {
            value.childReplies.splice(index, 1);
          });
        }
      }
    });
  };

  $scope.featureReply = function(id, type, index) {
    $http.get('dashboard/featureReply/'+id+'?token='+$rootScope.currentToken)
    .success(function (data){
      if(data == 1)
      {
        $scope.notifyToast('Reply is now Featured!');
        if(type == 'reply')
        {
          $scope.replies[index].replyFeature = 1;
        }
        else if(type == 'childReply')
        {
          angular.forEach($scope.replies, function(value) {
            value.childReplies[index].replyFeature = 1;
          });
        }
      }
      else if(data == 0)
      {
        $scope.notifyToast('Reply has been removed from Featured!');
        if(type == 'reply')
        {
          $scope.replies[index].replyFeature = 0;
        }
        else if(type == 'childReply')
        {
          angular.forEach($scope.replies, function(value) {
            value.childReplies[index].replyFeature = 0;
          });
        }
      }
    });
  };

  $scope.approveReply = function(id, index, type) {
    $http.get('dashboard/approveReply/'+id+'?token='+$rootScope.currentToken)
    .success(function (data){
      if(data == 1)
      {
        $scope.notifyToast('Reply has been approved.');
        if(type == 'reply')
        {
          $scope.replies[index].replyApproved = 1;
        }
        else if(type == 'childReply')
        {
          angular.forEach($scope.replies, function(value) {
            value.childReplies[index].replyApproved = 1;
          });
        }
      }
    });
  };

  $scope.makeContent = function() {
    $mdBottomSheet.show({
      templateUrl: 'makeTopic.html',
      scope: $scope.$new(),
      escapeToClose: true,
      clickOutsideToClose: true,
      locals: {topicData: {data:""}, channelData: {data:""}, replyData:{data: ""}},
      controller: 'ContentSheetCtrl'
    });
  };

  $scope.editTopic = function(id, index) {
    $mdBottomSheet.show({
      templateUrl: 'editTopic.html',
      scope: $scope.$new(),
      escapeToClose: true,
      clickOutsideToClose: true,
      locals: {topicData: {data:$scope.topicDetail, index:index}, channelData: {data:""}, replyData:{data:""}},
      controller: 'ContentSheetCtrl'
    });
  };

  $scope.makeChannel = function() {
    $mdBottomSheet.show({
      templateUrl: 'makeChannel.html',
      scope: $scope.$new(),
      escapeToClose: true,
      clickOutsideToClose: true,
      locals: {topicData: {data: ""}, channelData: {data:""}, replyData:{data:""}},
      controller: 'ContentSheetCtrl'
    });
  };

  $scope.editChannel = function(id, index) {
    $mdBottomSheet.show({
      templateUrl: 'editChannel.html',
      scope: $scope.$new(),
      escapeToClose: true,
      clickOutsideToClose: true,
      locals: {topicData: {data:""}, channelData: {data:$scope.channelDetail, index:index}, replyData:{data:""}},
      controller: 'ContentSheetCtrl'
    });
  };

  $scope.editReply = function(reply, index, type) {
    $mdBottomSheet.show({
      templateUrl: 'editReply.html',
      scope: $scope.$new(),
      escapeToClose: true,
      clickOutsideToClose: true,
      locals: {topicData: {data:""}, channelData: {data:""}, replyData:{data:reply, index:index, type:type}},
      controller: 'ContentSheetCtrl'
    });
  };

  $scope.convertChannelMenu();
  $scope.convertPageMenu();

}])

.controller('ContentSheetCtrl', ['$scope', '$rootScope', '$stateParams', '$http', '$mdBottomSheet', '$mdToast', '$mdSidenav', '$interval', 'Upload', 'topicData', 'channelData', 'replyData', function($scope, $rootScope, $stateParams, $http, $mdBottomSheet, $mdToast, $mdSidenav, $interval, Upload, topicData, channelData, replyData) {

  $scope.topicData = {
    topicID: topicData.data.id,
    topicTitle: topicData.data.topicTitle,
    topicBody: topicData.data.topicBody,
    topicChannel: topicData.data.topicChannel,
    topicImg: topicData.data.topicImg,
    topicAudio: topicData.data.topicAudio,
    topicVideo: topicData.data.topicVideo,
    topicStatus: "",
    topicType: topicData.data.topicType,
    allowReplies: Number(topicData.data.allowReplies),
    showImage: Number(topicData.data.showImage)
  };
  $scope.channelData = {
    channelID: channelData.data.id,
    channelTitle: channelData.data.channelTitle,
    channelDesc:channelData.data.channelDesc,
    channelImg: channelData.data.channelImg
  };
  $scope.replyData = {
    replyID: replyData.data.id,
    replyBody: replyData.data.replyBody
  };

  $scope.activeSave = {
    show:false,
    topicID:0
  }

  $scope.channelList = {};

  $scope.displayFull = false;

  var saveInterval;

  $scope.fullSheet = function() {
    if($scope.displayFull == false)
    {
      $scope.displayFull = true;
    }
    else
    {
      $scope.displayFull = false;
    }
  };

  $scope.openLeftMenu = function() {
    $mdSidenav('left').toggle();
  };

  $scope.openRightMenu = function() {
    $mdSidenav('right').toggle();
  };

  $scope.createTopic = function() {
    $http.get('dashboard/createTopic?token='+$rootScope.currentToken)
    .success(function (data){
      $scope.channelList = data;
      if(channelData != "")
      {
        angular.forEach(data, function(value, key) {
          if(channelData.id == value.id)
          {
            $scope.channelData.channelID = value.id;
            $scope.channelData.channelTitle = value.channelTitle;
            $scope.channelData.channelDesc = value.channelDesc;
            $scope.channelData.channelImg = value.channelImg;
          }
        });
      }
    });
  };

  $scope.getReply = function() {
    $http.get('dashboard/editReply/'+replyData+'?token='+$rootScope.currentToken)
    .success(function (data){
      $scope.replyData.replyID = data.id
      $scope.replyData.replyBody = data.replyBody;
    });
  };

  $scope.closeSheet = function(){
    $mdBottomSheet.hide();
  };

  $scope.notifyToast = function(message) {
    $mdToast.show(
      $mdToast.simple()
        .textContent(message)
        .position('bottom left')
        .hideDelay(3000)
    );
  };

  $scope.doTopic = function(status) {
    if($scope.topicData.topicImg === undefined)
    {
      $scope.topicData.topicImg = "";
    }
    if($scope.topicData.topicBody === undefined)
    {
      $scope.topicData.topicBody = "";
    }
    Upload.upload({
      url: 'dashboard/postTopic?token='+$rootScope.currentToken,
      data: {
        "topicTitle": $scope.topicData.topicTitle,
        "topicBody": $scope.topicData.topicBody,
        "topicChannel": $scope.topicData.topicChannel,
        "topicStatus": status,
        "topicType": "Blog",
        "topicImg": $scope.topicData.topicImg,
        "allowReplies": $scope.topicData.allowReplies,
        "showImage": $scope.topicData.showImage
      },
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function(data){
      if(status == 'Published'){
        if(data != 0 && data != 403)
        {
          $scope.notifyToast('Successfully Posted Topic.');
          if($scope.activeChannel == 0 || $scope.activeChannel == data.topicChannel)
          {
            $scope.topics.data.unshift(data);
          }
          $scope.closeSheet();
        }
        else if(data == 0)
        {
          $scope.notifyToast('Please enter a Title, Body, and Channel.');
        }
      }
      else if(status == 'Draft')
      {
        if(data != 0 && data != 403)
        {
          $scope.notifyToast('Successfully Saved Topic.');
          $scope.topicData.topicID = data.id;
          if($scope.activeChannel == 0 || $scope.activeChannel == data.topicChannel)
          {
            $scope.topics.data.unshift(data);
          }
          $scope.activeSave.show = true;
          $scope.activeSave.topicID = data.id;
        }
        else if(data == 0)
        {
          $scope.notifyToast("Please enter a title to autosave your topic.");
        }
      }
    });
  };

  $scope.updateTopic = function(id, status) {
    if($scope.topicData.topicImg === undefined)
    {
      $scope.topicData.topicImg = "";
    }
    if($scope.topicData.topicBody === undefined)
    {
      $scope.topicData.topicBody = "";
    }
    Upload.upload({
      url: 'dashboard/updateTopic/'+id+'?token='+$rootScope.currentToken,
      data: {
        topicTitle: $scope.topicData.topicTitle,
        topicBody: $scope.topicData.topicBody,
        topicChannel: $scope.topicData.topicChannel,
        topicStatus: status,
        topicType: "Blog",
        topicImg: $scope.topicData.topicImg,
        allowReplies: $scope.topicData.allowReplies,
        showImage: $scope.topicData.showImage
      },
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function(data){
      if(status == 'Published')
      {
        if(data != 0 && data != 403)
        {
          $scope.notifyToast('Successfully Updated Topic.');
          $scope.topics.data.splice(topicData.index, 1);
          $scope.topics.data.splice(topicData.index, 0, data);
          $scope.$parent.topicDetail = data;
          $scope.closeSheet();
        }
        else if(data == 0)
        {
          $scope.notifyToast('Please enter a Title.');
        }
      }
      else if(status == 'Draft')
      {
        if(data != 0 && data != 403)
        {
          $scope.notifyToast('Successfully Saved Topic.');
          $scope.topics.data.splice(topicData.index, 1);
          $scope.topics.data.splice(topicData.index, 0, data);
          $scope.$parent.topicDetail = data;
        }
        else if(data == 0)
        {
          $scope.notifyToast('Please enter a Title to autosave.');
        }
      }
    });
  };

  $scope.doChannel = function() {
    if($scope.channelData.channelImg === undefined)
    {
      $scope.channelData.channelImg = "";
    }
    Upload.upload({
      url: 'dashboard/postChannel?token='+$rootScope.currentToken,
      data: {
        channelTitle: $scope.channelData.channelTitle,
        channelDesc: $scope.channelData.channelDesc,
        channelImg: $scope.channelData.channelImg
      },
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function(data){
      if(data != 0)
      {
        $scope.notifyToast('Successfully Posted Channel.');
        $scope.channels.data.push(data);
        $scope.closeSheet();
      }
      else if(data == 0)
      {
        $scope.notifyToast('Please enter a Title for your Channel.');
      }
    });
  };

  $scope.updateChannel = function(id) {
    if($scope.channelData.channelImg === undefined)
    {
      $scope.channelData.channelImg = "";
    }
    Upload.upload({
      url: 'dashboard/updateChannel/'+id+'?token='+$rootScope.currentToken,
      data: {
        channelTitle: $scope.channelData.channelTitle,
        channelDesc: $scope.channelData.channelDesc,
        channelImg: $scope.channelData.channelImg
      },
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function(data){
      if(data != 0 && data != 403)
      {
        $scope.notifyToast('Successfully Updated Channel.');
        $scope.channels.data.splice(channelData.index, 1);
        $scope.channels.data.splice(channelData.index, 0, data);
        $scope.closeSheet();
      }
      else if(data == 0)
      {
        $scope.notifyToast('Please enter a Title for your Channel.');
      }
    });
  };

  $scope.updateReply = function() {
    $http({
        method: 'PUT',
        url: 'dashboard/updateReply/'+replyData.data.id+'?token='+$rootScope.currentToken,
        data: {replyBody: $scope.replyData.replyBody},
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function(data){
      if(data != 0 && data != 403)
      {
        $scope.notifyToast('Successfully Updated Reply.');
        if(replyData.type == 'reply')
        {
          $scope.replies.splice(replyData.index, 1);
          $scope.replies.splice(replyData.index, 0, data);
        }
        else if(replyData.type == 'childReply')
        {
          angular.forEach($scope.replies, function(value) {
            value.childReplies.splice(replyData.index, 1);
            value.childReplies.splice(replyData.index, 0, data);
          });
        }
        $scope.closeSheet();
      }
      else if(data == 0)
      {
        $scope.notifyToast('Please enter a reply.');
      }
    });
  };

  $scope.stopAutoSave = function() {
    $interval.cancel(saveInterval);
  };
  $scope.autoSave = function() {

    $scope.stopAutoSave();

    saveInterval = $interval(function() {
      if($scope.topicData.topicID === undefined)
      {
        if($scope.topicData.topicType == 'Blog')
        {
          $scope.doTopic('Draft');
        }
      } else if($scope.topicData.topicID !== undefined)
      {
        if($scope.topicData.topicType == 'Blog')
        {
          $scope.updateTopic($scope.topicData.topicID, 'Draft');
        }
      }
    }, 300000);
  };

  $scope.createTopic();
  $scope.autoSave();

}])



//Begin Dashboard Users
.controller('DashboardUsersCtrl', ['$scope', '$rootScope', '$state', '$rootScope', '$http', '$mdDialog', '$mdToast', 'userData', function($scope, $rootScope, $state, $rootScope, $http, $mdDialog, $mdToast, userData) {

  $scope.users = userData.data.users;
  $scope.roles = userData.data.roles;

  var originatorEv;

  $scope.notifyToast = function(message) {
    $mdToast.show(
      $mdToast.simple()
        .textContent(message)
        .position('bottom left')
        .hideDelay(3000)
    );
  };

  $scope.getUsers = function() {
    $http.get('dashboard/getUsers?token='+$rootScope.currentToken)
    .success(function (data){
      $scope.users = data.users;
      $scope.roles = data.roles;
    });
  };

  $scope.activateUser = function(id) {
    $http.get('dashboard/activateUser/'+id+'?token='+$rootScope.currentToken)
    .success(function (data){
      if(data == 1)
      {
        $scope.notifyToast('User Activated');
      }
      else {
        $scope.notifyToast('User Deactivated.');
      }
    });
  };

  $scope.banUser = function(id) {
    $http.get('dashboard/banUser/'+id+'?token='+$rootScope.currentToken)
    .success(function (data){
      if(data == 1)
      {
        $scope.notifyToast('User was banned.');
      }
      else if (data == 2)
      {
        $scope.notifyToast('User was unbanned.');
      }
      else if(data == 0)
      {
        $scope.notifyToast('User Cannot be banned.');
      }
    });
  };

  $scope.deleteUser = function(id, index) {
    $http({
        method: 'POST',
        url: 'dashboard/deleteUser/'+id+'?token='+$rootScope.currentToken,
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function(data){
      if(data == 1)
      {
        $scope.users.splice(index, 1);
        $scope.notifyToast('User was Deleted.');
      }
      else {
        $scope.notifyToast('User Cannot be Deleted.');
      }
    });
  };

  $scope.filterRole = function(id) {
    $http.get('dashboard/filterRole/'+id+'?token='+$rootScope.currentToken)
    .success(function (data){
      $scope.users = data;
    });
  };

  $scope.deleteRole = function(id, index) {
    $http({
        method: 'POST',
        url: 'dashboard/deleteRole/'+id+'?token='+$rootScope.currentToken,
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function(data){
      if(data == 1)
      {
        $scope.roles.splice(index, 1);
        $scope.notifyToast('Role was Deleted.');
      }
      else {
        $scope.notifyToast('Role Cannot be Deleted.');
      }
    });
  };

  $scope.manage = function($mdOpenMenu, ev) {
    originatorEv = ev;
    $mdOpenMenu(ev);
  };

  $scope.addUser = function(ev) {
    $mdDialog.show({
      templateUrl: 'addUser.html',
      parent: angular.element(document.body),
      targetEvent: ev,
      clickOutsideToClose:true,
      locals: {userData: "", roleData: ""},
      scope: $scope.$new(),
      controller: 'userDialogCtrl',
    })
  };

  $scope.addRole = function(ev) {
    $mdDialog.show({
      templateUrl: 'addRole.html',
      parent: angular.element(document.body),
      targetEvent: ev,
      clickOutsideToClose:true,
      locals: {userData: "", roleData: ""},
      scope: $scope.$new(),
      controller: 'userDialogCtrl',
    })
  };

  $scope.editRole = function(ev, id, index) {
    $mdDialog.show({
      templateUrl: 'editRole.html',
      parent: angular.element(document.body),
      targetEvent: ev,
      clickOutsideToClose:true,
      locals: {userData: "", roleData: {id: id, index:index}},
      scope: $scope.$new(),
      controller: 'userDialogCtrl',
    })
  };

  $scope.editProfile = function(ev, id, index) {
    $mdDialog.show({
      templateUrl: 'views/templates/profileEdit-Dialog.html',
      parent: angular.element(document.body),
      targetEvent: ev,
      clickOutsideToClose:true,
      locals: {userData: {id:id, index:index}, roleData: ""},
      scope: $scope.$new(),
      controller: 'userDialogCtrl',
    })
  };

  $scope.setRole = function(ev, id, index) {
    $mdDialog.show({
      templateUrl: 'setRole.html',
      parent: angular.element(document.body),
      targetEvent: ev,
      clickOutsideToClose:true,
      locals: {userData: {id:id, index:index}, roleData: ""},
      scope: $scope.$new(),
      controller: 'userDialogCtrl',
    })
  };

}])

.controller('userDialogCtrl', ['$scope', '$rootScope', '$state', '$http', '$mdDialog', '$mdToast', 'Upload', 'userData', 'roleData', function($scope, $rootScope, $state, $http, $mdDialog, $mdToast, Upload, userData, roleData) {

  $scope.user = {};
  $scope.role = {};
  $scope.profile = {};

  $scope.notifyToast = function(message) {
    $mdToast.show(
      $mdToast.simple()
        .textContent(message)
        .position('bottom left')
        .hideDelay(3000)
    );
  };

  $scope.dialogClose = function() {
    $mdDialog.hide();
  };

  $scope.doAddUser = function() {
    var res = {newUserName: $scope.user.name, newUserEmail: $scope.user.email};
    var res = JSON.stringify(res);

    $http({
        method: 'POST',
        url: 'dashboard/addUser?token='+$rootScope.currentToken,
        data: res,
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function(data){
      if(data != 0 && data != 2 && data != 3 && data != 403)
      {
        $scope.users.push(data);
        $scope.notifyToast('User Added!');
        $mdDialog.hide();
      }
      else if(data == 0)
      {
        $scope.notifyToast('Please fill out all of the fields.');
      }
      else if(data == 2)
      {
        $scope.notifyToast('User already exists.');
      }
      else if(data == 3)
      {
        $scope.notifyToast('Email already exists.');
      }
    });
  };

  $scope.doAddRole = function() {
    var res = {roleName: $scope.role.name, roleDesc: $scope.role.description};
    var res = JSON.stringify(res);

    $http({
        method: 'POST',
        url: 'dashboard/addRole?token='+$rootScope.currentToken,
        data: res,
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function(data){
      if(data != 0 && data != 403)
      {
        $scope.roles.push(data);
        $scope.notifyToast('Role Added!');
        $mdDialog.hide();
      }
      else if(data == 0)
      {
        $scope.notifyToast('Please name your role.');
      }
    });
  };

  $scope.getRole = function() {
    $http.get('dashboard/editRole/'+roleData.id+'?token='+$rootScope.currentToken)
    .success(function (data){
      $scope.role = data;
    });
  };

  $scope.doRoleEdit = function() {
    $http({
        method: 'PUT',
        url: 'dashboard/updateRole/'+roleData.id+'?token='+$rootScope.currentToken,
        data: {roleName: $scope.role.roleName, roleDesc: $scope.role.roleDesc},
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function(data){
      if(data != 0 && data != 403)
      {
        $scope.notifyToast('Role Updated');
        $scope.roles.splice(roleData.index, 1);
        $scope.roles.splice(roleData.index, 0, data);
        $scope.dialogClose();
      }
      else if(data == 0)
      {
        $scope.notifyToast('Please enter a role name.');
      }
    });
  };

  $scope.getProfile = function() {
    $http.get('dashboard/editUser/'+userData.id+'?token='+$rootScope.currentToken)
    .success(function (data){
      $scope.profile = data;
    });
  };

  $scope.profileEdit = function() {
    Upload.upload({
      url: 'dashboard/updateProfile/'+userData.id+'?token='+$rootScope.currentToken,
      data: {
        displayName: $scope.profile.displayName,
        email: $scope.profile.email,
        avatar: $scope.profile.avatar,
        emailReply: $scope.profile.emailReply,
        emailDigest: $scope.profile.emailDigest
      },
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function(data){
      if(data != 0)
      {
        $scope.notifyToast('Successfully Updated Profile.');
        $scope.users.splice(userData.index, 1);
        $scope.users.splice(userData.index, 0, data);
        $mdDialog.hide();
      }
    });
  };

  $scope.doSetRole = function()
  {
    $http({
        method: 'PUT',
        url: 'dashboard/setRole/'+userData.id+'?token='+$rootScope.currentToken,
        data: {roleID: $scope.profile.role},
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function(data){
      if(data != 2 && data != 0 && data != 403)
      {
        $scope.notifyToast('Role was set.');
        $scope.users.splice(userData.index, 1);
        $scope.users.splice(userData.index, 0, data);
        $scope.dialogClose();
      }
      else if(data == 0)
      {
        $scope.notifyToast('Role not found.');
      }
      else if(data == 2)
      {
        $scope.notifyToast('Cannot change role.');
      }
    });
  };


  if(roleData != "")
  {
    $scope.getRole();
  }
  else if(userData != "")
  {
    $scope.getProfile();
  }

}])


.controller('DashboardOptionsCtrl', ['$scope', '$rootScope', '$state', '$http', '$filter', '$mdDialog', '$mdToast', 'Upload', 'optionData', function($scope, $rootScope, $state, $http, $filter, $mdDialog, $mdToast, Upload, optionData) {

  $scope.options = optionData.data.options;
  $scope.apps = optionData.data.apps;

  $scope.options.allowRegistration = Number($scope.options.allowRegistration);
  $scope.options.replyModeration = Number($scope.options.replyModeration);
  $scope.options.homeBanner = Number($scope.options.homeBanner);
  $scope.options.allowAsk = Number($scope.options.allowAsk);

  $scope.notifyToast = function(message) {
    $mdToast.show(
      $mdToast.simple()
        .textContent(message)
        .position('bottom left')
        .hideDelay(3000)
    );
  };

  $scope.saveOptions = function() {
    $http({
        method: 'POST',
        url: 'dashboard/saveOptions?token='+$rootScope.currentToken,
        data: {
          website:$scope.options.website,
          siteLogo:$scope.options.siteLogo,
          homePage: $scope.options.homePage,
          allowRegistration: $scope.options.allowRegistration,
          replyModeration: $scope.options.replyModeration,
          allowAsk: $scope.options.allowAsk,
          homeBanner: $scope.options.homeBanner,
          aboutWebsite: $scope.options.aboutWebsite
        },
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function(data){
      if(data == 1)
      {
        $scope.notifyToast('Options Saved.');
      }
      else if(data == 0)
      {
        $scope.notifyToast('Please input a website name.');
      }
    });
  };

  $scope.uploadApp = function() {
    Upload.upload({
      url: 'dashboard/postApp?token='+$rootScope.currentToken,
      data: {
        appData : $scope.appData.app,
      },
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function(data){
      if(data == 0)
      {
        $scope.notifyToast('No file specified.');
      }
      else if(data == 1)
      {
        $scope.notifyToast('App uploaded!');
      }
      else if(data == 2)
      {
        $scope.notifyToast('Not a valid ZIP!');
      }
      else if(data == 3)
      {
        $scope.notifyToast('No valid app.json found.')
      }

    });
  };

  $scope.activateApp = function(id, index) {
    $http({
      method: 'POST',
      url: 'dashboard/activateApp?token='+$rootScope.currentToken,
      data: {id: id},
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function (data){
      if(data == 1)
      {
        angular.forEach($scope.apps, function(value, key) {
          if(value.appActive == 1)
          {
            value.appActive = 0;
          }
          if(key == index)
          {
            value.appActive = 1;
          }
        });
        $scope.notifyToast('App activated!');
      }
      else
      {
        $scope.notifyToast('App could not be activated.');
      }
    }).error(function() {
      $scope.notifyToast('App could not be activated.');
    });
  };

  $scope.deleteApp = function(id, index) {
    $http({
      method: 'POST',
      url: 'dashboard/deleteApp?token='+$rootScope.currentToken,
      data: {id: id},
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function (data){
      if(data == 0)
      {
        $scope.notifyToast('You cannot delete this app.');
      }
      else if(data == 1)
      {
        $scope.notifyToast('App deleted.');
        $scope.apps.splice(index, 1);
      }
      else if(data == 2)
      {
        $scope.notifyToast('Please activate another app before deleting this activated one.');
      }
      else
      {
        $scope.notifyToast('App could not be deleted.');
      }
    }).error(function() {
      $scope.notifyToast('App could not be deleted.');
    });
  };

}])

.controller('InstallCtrl', ['$scope', '$state', '$http', '$mdToast', 'installDep', function($scope, $state, $http, $mdToast, installDep) {

  if(installDep.data != 0)
  {
    $state.go('home');
  }

  $scope.installData = {};

  $scope.notifyToast = function(message) {
    $mdToast.show(
      $mdToast.simple()
        .textContent(message)
        .position('bottom left')
        .hideDelay(3000)
    );
  };

  $scope.doInstall = function() {
    $http({
        method: 'POST',
        url: 'storeAPIInstall',
        data: {
          databaseUser:$scope.installData.databaseUser,
          databaseName:$scope.installData.databaseName,
          databasePassword:$scope.installData.databasePassword,
          siteName:$scope.installData.siteName,
          adminEmail:$scope.installData.adminEmail,
          emailHost:$scope.installData.emailHost,
          emailPort:$scope.installData.emailPort,
          emailName:$scope.installData.emailName,
          emailPass:$scope.installData.emailPass
        },
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function (data){
      if(data == 0)
      {
        $scope.notifyToast('Please fill out all of the fields.');
      }
      else if(data == 2)
      {
        $scope.notifyToast('Could not connect to the database.');
      }
      else if(data == 4)
      {
        $scope.notifyToast('Email is not valid.');
      }
      else {
        $scope.notifyToast('Success!');
        $http({
            method: 'POST',
            url: 'installAPIDB',
            data: {adminName:data.adminName, adminEmail:data.adminEmail, siteName:data.siteName},
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        }).success(function (data) {
          if(data == 1)
          {
            $scope.notifyToast('ReMark has been Successfully Installed! Check your Email to Sign In.');

            $state.go('main.home');
          } else {
            $scope.notifyToast('This should not happen. Get help.');
          }
        });
      }
    })
  };

}])
