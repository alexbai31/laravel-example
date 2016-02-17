<?php

Route::get('/', "HomeController@main");
Route::get('/ios', "HomeController@ios");
Route::get('/android', "HomeController@android");
Route::any('/build/upload', "BuildController@uploadApk");
Route::any('/addbundle.php', 'BundleController@addBundle');
Route::any('/addbundle_android.php', 'BundleController@addBundle_android');
Route::get('/{build_type}/versions/{label}', "VersionController@main");
Route::get('/{build_type}/builds/{label}/{version}', "BuildController@main");
Route::get('/ios/builds/{label}/{version}/{build_number}', "BuildController@showiOSBuild");
Route::get('/android/builds/{label}/{version}/{build_number}', "BuildController@showAndroidBuild");
Route::get('/download', "DownloadController@get");

//Route::get('/get/apps', "ApiController@getApplications");
Route::get('/get/labels', "ApiController@getLabels");
Route::model("labelm", "Label");
Route::get('/get/vers/{labelm}', "ApiController@getVersions");
Route::model("versionm", "Version");
Route::get('/get/apps/{versionm}', "ApiController@getApplications");

