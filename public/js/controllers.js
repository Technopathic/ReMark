angular.module('remark.controllers', [])

.controller('MainCtrl', ['$scope', '$state', '$http', '$rootScope', '$mdDialog', '$mdBottomSheet', '$mdToast', '$timeout', 'mainData', function($scope, $state, $http, $rootScope, $mdDialog, $mdBottomSheet, $mdToast, $timeout, mainData) {

  $scope.mainPages = mainData.data.pages;
  $scope.mainOptions = mainData.data.options;

  $scope.user = {};
  $scope.searchResults = {};

  $scope.showSearch = false;

  $scope.notifyToast = function(message) {
    $mdToast.show(
      $mdToast.simple()
        .textContent(message)
        .position('bottom left')
        .hideDelay(3000)
    );
  };

  $scope.authDialog = function(ev) {
    $mdDialog.show({
        templateUrl: 'views/templates/auth.html',
        parent: angular.element(document.body),
        targetEvent: ev,
        clickOutsideToClose:true,
        bindToController: true,
        controller: 'AuthCtrl'
      });
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

    $scope.askDialog = function(ev) {
      $mdDialog.show({
        templateUrl: 'views/templates/ask-Dialog.html',
        parent: angular.element(document.body),
        targetEvent: ev,
        clickOutsideToClose:true,
        bindToController: true,
        controller: 'AskCtrl'
      });
    };

    $scope.dialogClose = function() {
      $mdDialog.hide();
    };

    $scope.closeSheet = function(){
      $mdBottomSheet.hide();
    };

    $scope.openMenu = function($mdOpenMenu, ev) {
      originatorEv = ev;
      $mdOpenMenu(ev);
    };

    $scope.signOut = function() {
     localStorage.removeItem('user');
     $rootScope.authenticated = false;
     $rootScope.currentUser = null;
     $rootScope.currentToken = null;
     $scope.notifyToast('Bye for now! Hope to see you again soon!');
   };

   $scope.showProfile = function(ev, id) {
     $mdDialog.show({
       templateUrl: 'views/templates/profile-Dialog.html',
       parent: angular.element(document.body),
       targetEvent: ev,
       clickOutsideToClose:true,
       bindToController: true,
       controller: 'ProfileCtrl',
       locals: {id: id}
     });
   };

   $scope.toggleSearch = function() {
     if($scope.showSearch == false)
     {
       $scope.showSearch = true;
     }
     else {
       $scope.showSearch = false;
     }
   };

   $scope.doSearch = function() {
     $http({
         method: 'POST',
         url: 'api/search',
         data: { searchType: $scope.searchType, searchContent:$scope.searchContent },
         headers: {'Content-Type': 'application/x-www-form-urlencoded'}
     }).success(function (data){
       if(data != 0 && data != 2)
       {
         $scope.searchResults = data;
       }
       else if(data == 0)
       {
         $scope.notifyToast("Enter your search.");
       }
       else if(data == 2)
       {
         $scope.notifyToast("Nothing found.");
       }
     });
   };

   $scope.footerMenu = function() {
     $mdBottomSheet.show({
       templateUrl: 'footerMenu.html',
       escapeToClose: true,
       clickOutsideToClose: true,
       scope:$scope.$new(),
       locals: {mainPages: $scope.mainPages, mainOptions: $scope.mainOptions},
       controller:
        function($scope, $mdBottomSheet, mainPages, mainOptions) {
          $scope.mainPages = mainPages;
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
}])

.controller('AuthCtrl', ['$scope', '$state', '$http', '$rootScope', '$mdDialog', '$mdToast', '$stateParams', function($scope, $state, $http, $rootScope, $mdDialog, $mdToast, $stateParams) {

  $scope.auth = {};

  $scope.state = $state.current.name;


  $scope.notifyToast = function(message) {
    $mdToast.show(
      $mdToast.simple()
        .textContent(message)
        .position('bottom left')
        .hideDelay(3000)
    );
  };

  $scope.authDialog = function(ev) {
    $mdDialog.show({
        templateUrl: 'views/templates/auth.html',
        parent: angular.element(document.body),
        targetEvent: ev,
        clickOutsideToClose:true,
        bindToController: true,
        controller: 'AuthCtrl'
      });
    };

  $scope.doAuth = function() {
    $http({
        method: 'POST',
        url: 'api/checkEmail',
        data: {email: $scope.auth.email},
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    })
    .success(function(data) {
      if(data == 1)
      {
        $http({
            method: 'POST',
            url: 'api/signUp',
            data: {email: $scope.auth.email},
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        })
        .success(function(data) {
          if(data == 1)
          {
            $scope.notifyToast('Thanks for signing up! Please check your email to sign in.');
          }
          else if(data == 2)
          {
            $scope.notifyToast('Email is already registered.');
          }
          else if(data == 3)
          {
            $scope.notifyToast('Username is already registered.');
          }
          else if(data == 5)
          {
            $scope.notifyToast('Signing up is not allowed.');
          }

          $scope.dialogClose();
        });
      }
      if(data == 2)
      {
        $scope.notifyToast('Check your email to Sign In.');
        $scope.dialogClose();
      }
      else if(data == 0)
      {
        $scope.notifyToast('Please fill out your email.');
      }
      else if(data == 5)
      {
        $scope.notifyToast('Signing In is not allowed.');
        $scope.dialogClose();
      }

    }).error(function(data) {
      $scope.notifyToast('Login Incorrect.');
    });
  };

  $scope.dialogClose = function() {
    $mdDialog.hide();
  };

}])

.controller('AskCtrl', ['$scope', '$state', '$http', '$rootScope', '$mdDialog', '$mdToast', function($scope, $state, $http, $rootScope, $mdDialog, $mdToast) {

  $scope.askData = {};

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

  $scope.doAsk = function() {
    $http({
        method: 'POST',
        url: 'api/postMessage?token='+$rootScope.currentToken,
        data: {askBody: $scope.askData.askBody},
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function(data){
      if(data == 1)
      {
        $scope.notifyToast('Your question has been asked.');
      }
      else if(data == 2)
      {
        $scope.notifyToast('You are not allowed to ask questions.');
      }
      else if(data == 3)
      {
        $scope.notifyToast('You have sent 5 messages in the past hour.');
      }
      else {
        $scope.notifyToast('Could not ask question.');
      }
    }).error(function(data) {
      if(data.error == "token_expired"){
        $rootScope.authenticated = false;
        $scope.notifyToast('Session expired. Please relog.');
      }
    });
  };

}])

.controller('ProfileEditCtrl', ['$scope', '$state', '$http', '$rootScope', '$mdDialog', '$mdToast', 'Upload', function($scope, $state, $http, $rootScope, $mdDialog, $mdToast, Upload) {

  $scope.profile = {
    displayName: $rootScope.currentUser.displayName,
    email: $rootScope.currentUser.email,
    avatar:$rootScope.currentUser.avatar,
    emailDigest: $rootScope.currentUser.emailDigest,
    emailReply: $rootScope.currentUser.emailReply
  };

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

  $scope.profileEdit = function() {
    Upload.upload({
      url: 'api/updateProfile?token='+$rootScope.currentToken,
      data: {
        displayName: $scope.profile.displayName,
        email: $scope.profile.email,
        avatar: $scope.profile.avatar,
        emailDigest: $scope.profile.emailDigest,
        emailReply: $scope.profile.emailReply
      },
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function(data){
      if(data == 2)
      {
        $scope.notifyToast('Your file is over 2MB.');
      }
      else if(data != 0 && data != 2)
      {
        $scope.notifyToast('Successfully Updated Profile.');
        $rootScope.currentUser.displayName = data.displayName;
        $rootScope.currentUser.email = data.email;
        $rootScope.currentUser.avatar = data.avatar;
        $rootScope.currentUser.emailDigest = data.emailDigest;
        $rootScope.currentUser.emailReply = data.emailReply;
        $mdDialog.hide();
      }
    }).error(function(data) {
      if(data.error == "token_expired"){
        $rootScope.authenticated = false;
        $scope.notifyToast('Session expired. Please relog.');
      }
    });
  };

  $scope.deactivate = function() {
    $scope.deactivateWarning = true;
  };

  $scope.doDeactivate = function() {
    $http({
        method: 'POST',
        url: 'api/deactivateUser?token='+$rootScope.currentToken,
        data: res,
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function(data) {
      localStorage.removeItem('user');
      $rootScope.authenticated = false;
      $rootScope.currentUser = null;
      $rootScope.currentToken = null;
      $scope.notifyToast('You have been deactivated. Good-bye.');
    }).error(function(data) {
      if(data.error == "token_expired")
      {
        $rootScope.authenticated = false;
        $scope.notifyToast('Session expired. Please relog.');
      }
    });
  };

}])


.controller('HomeCtrl', ['$scope', '$rootScope', '$state', '$http', function($scope, $rootScope, $state, $http) {

  $http.get('api/getInfo').success(function(data) {
    $rootScope.metaservice.set(data.website, data.aboutWebsite, "");
  });

}])


.controller('DetailCtrl', ['$scope', '$rootScope', '$state', '$stateParams', '$location', '$http', '$mdBottomSheet', '$mdToast', '$mdDialog', 'hotkeys', 'detailData', 'replyData', function($scope, $rootScope, $state, $stateParams, $location, $http, $mdBottomSheet, $mdToast, $mdDialog, hotkeys, detailData, replyData) {

  $scope.topic = detailData.data.topic;
  $scope.author = detailData.data.user;
  $scope.related = detailData.data.relates;
  $scope.previousTopic = detailData.data.previousTopic;
  $scope.nextTopic = detailData.data.nextTopic;

  $scope.replies = replyData.data.replies;

  $scope.facebook = encodeURI('https://facebook.com/sharer/sharer.php?u='+$location.absUrl());
  $scope.twitter = encodeURI('https://twitter.com/intent/tweet/?text='+$scope.topic.topicTitle+'&url='+$location.absUrl());
  $scope.google = encodeURI('https://plus.google.com/share?url='+$location.absUrl());
  $scope.tumblr = encodeURI('https://www.tumblr.com/widgets/share/tool?posttype=link&title='+$scope.topic.topicTitle+'&caption='+$scope.topic.topicTitle+'&content='+$location.absUrl()+'&canonicalUrl='+$location.absUrl()+'&shareSource=tumblr_share_button');
  $scope.email = encodeURI('mailto:?subject='+$scope.topic.topicTitle+'&body='+$location.absUrl());
  $scope.pinterest = encodeURI('https://pinterest.com/pin/create/button/?url='+$location.absUrl()+'&media='+$location.absUrl()+'&summary='+$scope.topic.topicTitle);
  $scope.reddit = encodeURI('https://reddit.com/submit/?url='+$location.absUrl());

  hotkeys.add({
    combo: 'left',
    description: 'Previous Page',
    callback: function() {
      $state.go('main.details', { topicSlug: $scope.previousTopic.topicSlug });
    }
  });

  hotkeys.add({
    combo: 'right',
    description: 'Next Page',
    callback: function() {
      $state.go('main.details', { topicSlug: $scope.nextTopic.topicSlug });
    }
  });

  $scope.voteTopic = function(id) {
    $http.get('api/voteTopic/' + $scope.topic.id + '?token='+$rootScope.currentToken)
    .success(function (data){
      if(data == 1)
      {
        $scope.notifyToast('You Liked This Topic.');
        $scope.topic.topicVotes = $scope.topic.topicVotes + 1;
      }
      else if(data == 0)
      {
        $scope.notifyToast('Like Removed.');
        $scope.topic.topicVotes = $scope.topic.topicVotes - 1;
      }
    }).error(function(data) {
      if(data.error == "token_expired"){
        $rootScope.authenticated = false;
        $scope.notifyToast('Session expired. Please relog.');
      }
    });
  };

  

}])

.controller('DetailSheetCtrl', ['$scope', '$rootScope', '$stateParams', '$http', '$mdBottomSheet', '$mdToast', 'topicID', 'parentID', function($scope, $rootScope, $stateParams, $http, $mdBottomSheet, $mdToast, topicID, parentID) {

  $scope.replyData = {
    topicID: topicID,
    replyBody: "",
    parentID: parentID
  };

  $scope.displayFull = false;

  $scope.closeReply = function() {
    $mdBottomSheet.hide();
  };

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

  $scope.notifyToast = function(message) {
    $mdToast.show(
      $mdToast.simple()
        .textContent(message)
        .position('bottom left')
        .hideDelay(3000)
    );
  };

  $scope.doReply = function() {
    $http({
        method: 'POST',
        url: 'api/postReply?token='+$rootScope.currentToken,
        data: {topicID: $scope.replyData.topicID, replyBody: $scope.replyData.replyBody, parentID: $scope.replyData.parentID},
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function(data){
      if(data != 0 && data != 2 && data != 3 && data != 4 && data != 5)
      {
        $scope.notifyToast('Successfully Posted Reply.');
        if(data.replyParent == 0)
        {
          $scope.replies.push(data);
        }
        else
        {
          angular.forEach($scope.replies, function(value) {
            if(value.id == data.replyParent)
            {
              value.childReplies.push(data);
              value.childCount = Number(value.childCount) + 1;
            }
          });
        }
        $scope.closeReply();
      }
      else if(data == 2)
      {
        $scope.notifyToast('Your reply will be checked for approval.');
      }
      else if(data == 0){
        $scope.notifyToast('Your reply was empty.');
      }
      else if(data == 3)
      {
        $scope.notifyToast('The reply limit is 500 characters.');
      }
      else if(data == 4)
      {
        $scope.notifyToast('You can only have 1 image, 1 video, and 1 link.');
      }
      else if(data == 5)
      {
        $scope.notifyToast("Slow down! You've made 5 replies in an hour.");
      }
      else if(data == 6)
      {
        $scope.notifyToast("You cannot reply to this child post.");
      }
      else if(data == 7)
      {
        $scope.notifyToast("Replies are not allowed on this topic.");
      }
    }).error(function(data) {
      if(data.error == "token_expired"){
        $rootScope.authenticated = false;
        $scope.notifyToast('Session expired. Please relog.');
      }
    });
  };

}])

.controller('ChannelCtrl', ['$scope', '$state', '$stateParams', '$http', 'channelData', function($scope, $state, $stateParams, $http, channelData) {

  $scope.channel = channelData.data;

}])

.controller('ChannelsCtrl', ['$scope', '$state', '$http', 'channelsData', function($scope, $state, $http, channelsData) {

  $scope.channels = channelsData.data;

}])

.controller('ProfileCtrl', ['$scope', '$state', '$stateParams', '$http', 'id', function($scope, $state, $stateParams, $http, id) {

  $scope.user = {};
  var id = id;

  $scope.getProfile = function() {
    $http.get('api/getUser/' + id)
    .success(function(data) {
      $scope.user = data;
    })
  };

  $scope.getProfile();
}])

.controller('ConfirmCtrl', ['$scope', '$rootScope', '$state', '$stateParams', '$mdToast', '$http', '$timeout', function($scope, $rootScope, $state, $stateParams, $mdToast, $http, $timeout) {

  $scope.token = $stateParams.token;
  $scope.confirmMessage = null;

  $scope.notifyToast = function(message) {
    $mdToast.show(
      $mdToast.simple()
        .textContent(message)
        .position('top right')
        .hideDelay(3000)
    );
  };
  $scope.confirmToken = function() {
    $http({
        method: 'POST',
        url: 'api/signIn',
        data: {token: $scope.token},
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).success(function(data){
      if(data == 0)
      {
        $scope.notifyToast('You were banned.');
      }
      else if(data == 2)
      {
        $scope.notifyToast('Token not found or expired.');
      }
      else
      {
        var token = JSON.stringify(data.token);
        localStorage.setItem('token', token);
        $rootScope.currentToken = data.token;
        $http.get('api/authenticate/user?token='+ data.token)
        .success(function(data) {
          var user = JSON.stringify(data.user);

          localStorage.setItem('user', user);
          $rootScope.authenticated = true;
          $rootScope.currentUser = data.user;

          $state.go('main.home');
        });
      }
    }).finally(function(){
      $scope.notifyToast('Welcome Back, '+$rootScope.currentUser.displayName+'!');
    });
  };

  $scope.confirmToken();

}])
