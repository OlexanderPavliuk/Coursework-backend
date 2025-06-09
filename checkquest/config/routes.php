<?php
use Core\Router;

$router = new Router();


$router->get('/welcome', 'WelcomeController@index');

$router->get('/login', 'AuthController@showLoginForm')->middleware('auth');
$router->post('/login', 'AuthController@login')->middleware('auth');
$router->get('/logout', 'AuthController@logout')->middleware('auth');
$router->get('/', 'AuthController@showLoginForm')->middleware('auth');
$router->post('/api/update-account', 'ProfileController@updateAccount');

$router->get('/dashboard', 'DashboardController@index')->middleware('auth');
$router->get('/api/profile-data', 'ProfileController@getProfileData')->middleware('auth');
$router->post('/api/update-description', 'ProfileController@updateDescription')->middleware('auth');
$router->post('/task/add', 'TaskController@add')->middleware('auth');
$router->post('/task/complete', 'TaskController@complete')->middleware('auth');
$router->post('/task/delete', 'TaskController@delete')->middleware('auth');

$router->post('/task/update', 'TaskController@update');

$router->get('/leaderboard', 'LeaderboardController@index')->middleware('auth');
$router->get('/statistics', 'StatisticsController@index')->middleware('auth');

$router->get('/register', 'RegisterController@index')->middleware('auth');
$router->post('/register', 'RegisterController@index')->middleware('auth');

$router->post('/habit/track', 'HabitController@track')->middleware('auth');
$router->post('/suggest', 'SuggestionController@generate')->middleware('auth');
$router->get('/health-check', 'HealthController@check')->middleware('auth');
$router->post('/health-check', 'HealthController@check')->middleware('auth');
$router->post('/avatar/change', 'ProfileController@changeAvatar')->middleware('auth');
$router->post('/store/purchase', 'StoreController@purchaseItem')->middleware('auth');



return $router;
