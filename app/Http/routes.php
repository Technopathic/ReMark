<?php

Route::get('getAPIInstall', 'InstallationController@getAPIInstall');
Route::post('storeAPIInstall', 'InstallationController@storeAPIInstall');
Route::post('installAPIDB', 'InstallationController@installAPIDB');

Route::group(['prefix' => 'dashboard', 'middleware' => 'cors'], function()
{
  //Dashboard
  Route::get('/', 'DashboardController@Dashboard');

  Route::get('getCatalogue', 'DashboardController@getCatalogue');
  Route::post('runFeed', 'DashboardController@runFeed');
  Route::get('getFeeds', 'DashboardController@getFeeds');
  Route::get('selectFeed/{id}', 'DashboardController@selectFeed');
  Route::post('deleteFeed', 'DashboardController@deleteFeed');
  Route::get('getBookmarks', 'DashboardController@getBookmarks');
  Route::post('bookmarkFeed', 'DashboardController@bookmarkFeed');
  Route::post('unBookmarkFeed', 'DashboardController@unBookmarkFeed');

  Route::get('notifications', 'DashboardController@getNotifications');
  Route::get('showNotifications/{type}', 'DashboardController@showNotifications');
  Route::get('openNotification/{id}', 'DashboardController@openNotification');
  Route::post('deleteNotification/{id}', 'DashboardController@deleteNotification');

  Route::get('getOptions', 'DashboardController@getOptions');
  Route::post('saveOptions', 'DashboardController@saveOptions');

  Route::post('postApp', 'DashboardController@storeApp');
  Route::post('activateApp', 'DashboardController@activateApp');
  Route::post('deleteApp', 'DashboardController@deleteApp');

  //Users
  Route::get('getUsers', 'UsersController@getUsers');
  Route::get('editUser/{id}', 'UsersController@editUser');
  Route::post('deleteUser/{id}', 'UsersController@deleteUser');
  Route::get('banUser/{id}', 'UsersController@banUser');
  Route::get('resetPassword/{id}', 'UsersController@resetPassword');
  Route::get('activateUser/{id}', 'UsersController@activateUser');
  Route::post('addUser', 'UsersController@storeUser');
  Route::post('updateProfile/{id}', 'UsersController@updateProfile');
  Route::post('addRole', 'UsersController@storeRole');
  Route::get('editRole/{id}', 'UsersController@editRole');
  Route::get('filterRole/{id}', 'UsersController@filterRole');
  Route::put('updateRole/{id}', 'UsersController@updateRole');
  Route::post('deleteRole/{id}', 'UsersController@deleteRole');
  Route::put('setRole/{id}', 'UsersController@setRole');

  //Content
  Route::get('getContent', 'RemarkAdminsController@getContent');
  Route::get('getTopics/{id}', 'RemarkAdminsController@getTopics');
  Route::get('getTopic/{id}', 'RemarkAdminsController@getTopic');
  Route::get('getChannels', 'RemarkAdminsController@getChannels');
  Route::get('createTopic', 'RemarkAdminsController@createTopic');
  Route::post('postTopic', 'RemarkAdminsController@storeTopic');
  Route::get('editTopic/{id}', 'RemarkAdminsController@editTopic');
  Route::post('updateTopic/{id}','RemarkAdminsController@updateTopic');
  Route::post('deleteTopic/{id}', 'RemarkAdminsController@deleteTopic');
  Route::get('setFeature/{id}', 'RemarkAdminsController@setFeature');
  Route::post('postChannel', 'RemarkAdminsController@storeChannel');
  Route::post('updateChannel/{id}', 'RemarkAdminsController@updateChannel');
  Route::post('deleteChannel/{id}', 'RemarkAdminsController@deleteChannel');
  Route::get('pageMenu/{id}', 'RemarkAdminsController@pageMenu');
  Route::get('unflagReply/{id}', 'RemarkAdminsController@unflagReply');
  Route::get('editReply/{id}', 'RemarkAdminsController@editReply');
  Route::put('updateReply/{id}', 'RemarkAdminsController@updateReply');
  Route::post('deleteReply', 'RemarkAdminsController@deleteReply');
  Route::get('featureReply/{id}', 'RemarkAdminsController@featureReply');
  Route::get('approveReply/{id}', 'RemarkAdminsController@approveReply');

  Route::get('messages', 'RemarkAdminsController@messages');
  Route::post('deleteMessage', 'RemarkAdminsController@deleteMessage');
  Route::get('showMessage/{id}', 'RemarkAdminsController@showMessage');

});

Route::group(['prefix' => 'api', 'middleware' => 'cors'], function()
{
    Route::get('getInfo', 'RemarksController@getInfo');

    Route::resource('authenticate', 'AuthenticateController', ['only' => ['index']]);
    Route::post('authenticate', 'AuthenticateController@authenticate');
    Route::get('authenticate/user', 'AuthenticateController@getAuthenticatedUser');
    Route::post('signUp', 'AuthenticateController@doSignUp');
    Route::post('confirmToken', 'AuthenticateController@confirmToken');
    Route::post('resetPassword', 'AuthenticateController@resetPassword');
    Route::post('confirmReset/{token}', 'AuthenticateController@confirmReset');
    Route::get('refreshToken', 'AuthenticateController@refreshToken');

    Route::get('main', 'RemarksController@main');
    Route::get('getTopics/channel={channel}&count={count}&length={length}', 'RemarksController@getTopics');
    Route::get('getFeatured', 'RemarksController@getFeatured');
    Route::get('getDetail/{slug}', 'RemarksController@getDetail');
    Route::get('getReplies/{slug}', 'RemarksController@getReplies');
    Route::post('postReply', 'RemarksController@storeReply');
    Route::get('voteTopic/{id}', 'RemarksController@voteTopic');
    Route::get('getChannels', 'RemarksController@getChannels');
    Route::get('getChannel/{slug}', 'RemarksController@getChannel');

    Route::get('getUser/{name}', 'RemarksController@getUser');
    Route::post('updateProfile', 'RemarksController@updateProfile');
    Route::post('deactivateUser', 'RemarksController@deactivateUser');

    Route::post('postMessage', 'RemarksController@storeMessage');

    Route::post('search', 'RemarksController@search');
});

Route::get('channel/{slug}', 'RemarksController@staticChannel');
Route::any('{path?}', 'RemarksController@index')->where("path", ".+");
