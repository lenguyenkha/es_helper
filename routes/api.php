<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group(['middleware' => [], 'prefix' => 'v1'], function () {
    //Search API
    Route::get('/suggestions/search-100k-words', 'API\SuggestionController@searchWithText100kWords')->name('suggestion.searchWithText100kWords');
    Route::get('/suggestions/search-65-words', 'API\SuggestionController@searchWithText65words')->name('suggestion.searchWithText65words');
    Route::get('/suggestions/search-opendb', 'API\SuggestionController@searchWithTextOpenDb')->name('suggestion.searchWithTextOpenDb');

    //Analyze API
    Route::get('/analyze', 'API\SuggestionController@analyzeText')->name('suggestion.analyzeText');
});

Route::group(['middleware' => [], 'prefix' => 'v2'], function () {

    //Search API
    Route::get('/suggestions/search-100k-words', 'API\SuggestionController@searchWithText100kWordsV2')->name('suggestion.searchWithText100kWordsV2');
    Route::get('/suggestions/search-100k-words/analyze', 'API\SuggestionController@analyzeTextWhyMatching100kWords')->name('suggestion.analyzeTextWhyMatching100kWords');

    Route::get('/analyze', 'API\SuggestionController@analyzeTextV2')->name('suggestion.analyzeTextV2');
});

Route::group(['middleware' => [], 'prefix' => 'v3'], function () {

    //Search API
    Route::get('/suggestions/search-100k-words', 'API\SuggestionController@searchWithText100kWordsV3')->name('suggestion.searchWithText100kWordsV3');
    Route::get('/suggestions/search-100k-words/analyze', 'API\SuggestionController@analyzeTextWhyMatching100kWordsV3')->name('suggestion.analyzeTextWhyMatching100kWordsV3');

    Route::get('/analyze', 'API\SuggestionController@analyzeTextV3')->name('suggestion.analyzeTextV3');
});