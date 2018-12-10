<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
/** Ionic app **/
Route::post('login', 'api\LoginController@verify_user');
Route::post('guard-login','api\LoginController@guard_login');
Route::post('register', 'api\RegisterController@register_user');
Route::post('register-test', 'api\RegisterController@register_user_test');
Route::post('forgot-password', 'api\ForgotPasswordController@forgot_password');
Route::post('forgot-password-test', 'api\ForgotPasswordController@forgot_password_test');
Route::post('check-otp', 'api\ForgotPasswordController@check_otp');
Route::post('reset-password', 'api\ForgotPasswordController@resetPassowrd');
Route::get('country-list', 'api\RegisterController@country_list');
Route::get('state-list', 'api\RegisterController@state_list');
Route::get('carnival-list', 'api\HomeController@carnival_list');
Route::post('event-list', 'api\HomeController@event_list');
Route::post('event-list-test', 'api\HomeController@event_list_test');
Route::post('band-list', 'api\HomeController@band_list');
Route::post('event-detail', 'api\HomeController@event_detail');
Route::post('filter/country-event', 'api\FilterController@country_event');
Route::post('filter/country-event-test', 'api\FilterController@country_event_test');
Route::post('filter/country-local-event', 'api\FilterController@country_local_event');
Route::post('my-profile', 'api\LoginController@profile');
Route::post('all-users', 'api\LoginController@all_users');
Route::post('get-user-id', 'api\LoginController@get_user_id');
Route::post('other-profile', 'api\LoginController@other_profile');
Route::post('edit-profile', 'api\RegisterController@edit_profile');
Route::post('upload-image', 'api\RegisterController@uploadImage');
Route::post('band-detail', 'api\HomeController@band_detail');
Route::post('band-comment', 'api\HomeController@band_comment');
Route::post('event-comment', 'api\HomeController@event_comment');
Route::post('carnival-detail', 'api\HomeController@carnival_detail');
Route::post('save-carnival-key', 'api\HomeController@save_carnival_key');
Route::post('promoter-events', 'api\HomeController@promoter_events');
Route::post('promoter-event-create', 'api\HomeController@promoter_event_create');
Route::post('promoter-event-edit', 'api\HomeController@promoter_event_edit');
Route::post('event-upload-banner', 'api\HomeController@event_upload_banner');
Route::post('event-edit-banner', 'api\HomeController@event_change_banner');
Route::post('get-user-gallery-image', 'api\RegisterController@get_user_gallery_image');
Route::post('upload-user-gallery-image', 'api\RegisterController@upload_user_gallery_image');
Route::post('sent-friend-request', 'api\RegisterController@sent_friend_request');
Route::post('check-friend-request', 'api\RegisterController@check_friend_request');
Route::post('check-received-friend-request', 'api\RegisterController@check_received_friend_request');
Route::post('cancel-friend-request', 'api\RegisterController@cancel_friend_request');
Route::post('pending-friend-request', 'api\RegisterController@pending_friend_request');
Route::post('confirm-friend-request', 'api\RegisterController@confirm_friend_request');
Route::post('check-friend', 'api\RegisterController@check_friend');
Route::post('find-friend', 'api\RegisterController@find_friend');
Route::post('find-other-user-friend', 'api\RegisterController@find_other_user_friend');
Route::post('other-user-gallery', 'api\RegisterController@other_user_gallery');
Route::post('remove-friend', 'api\RegisterController@remove_friend');
Route::post('delete-friend-request', 'api\RegisterController@delete_friend_request');
Route::post('event-review-upload-image', 'api\HomeController@event_review_upload_image');
Route::post('band-review-upload-image', 'api\HomeController@band_review_upload_image');
Route::post('verify-tag', 'api\HomeController@verify_tag');
Route::post('event-message', 'api\HomeController@event_message');


Route::post('chat', 'api\MessageController@chatHistory');
Route::post('message/send', 'api\MessageController@ajaxSendMessage');
Route::post('threads', 'api\MessageController@threads');
Route::post('count-total-unread-messages', 'api\MessageController@countTotalUnreadMessages');

/************** events *************/
Route::post('event-members', 'api\EventsController@commitee_members_list');
Route::post('event-reviews', 'api\EventsController@events_review_list');
Route::post('event-user-purchased-events', 'api\EventsController@user_purchased_events');
Route::get('event-get-all-members','api\HomeController@get_all_users');
Route::post('event-country-user-count', 'api\EventsController@countryBasedUserCount');
Route::post('event-gender-user-count', 'api\EventsController@genderBasedUserCount');
Route::post('event-age-user-count', 'api\EventsController@ageBasedUserCount');
/*****************************/

/**** guards *****/
Route::post('promoter-guard-create', 'api\HomeController@promoter_guard_create');
Route::post('promoter-guards', 'api\HomeController@promoter_guards');
Route::post('delete-guard', 'api\GuardController@deleteGuard');
Route::post('scan-ticket', 'api\GuardController@scanTicket');
Route::post('scanned-ticket-list', 'api\GuardController@scannedTicketList');
Route::post('guard-event-list','api\GuardController@guradEventList');
/***********************/

/************** tickets *************/
Route::post('event-create-ticket-request', 'api\EventsController@create_ticket_request');
Route::post('event-count-ticket-request', 'api\EventsController@count_ticket_request');
Route::post('event-change-status-of-ticket', 'api\EventsController@change_status_of_ticket');
Route::post('event-transfer-ticket', 'api\EventsController@transferTicket');
Route::post('ticket-detail', 'api\EventsController@ticketDetail');

/*****************************/

/************** payment *************/
Route::any('payment/charge', 'api\StripeController@charge'); 
/*****************************/

/************** update number of clicks f event *************/
Route::post('event/addclick', 'api\EventsController@addclick'); 
/*****************************/

/*********** facebook login ************/
Route::post('facebook-login', 'api\RegisterController@facebook_login');
/****************************************/

/*********** trip planner login ************/
Route::post('trip-planner', 'api\TripPlannerController@index');
/****************************************/

Route::post('hotel-comment', 'api\TripPlannerController@hotel_comment');
Route::post('transportation-comment', 'api\TripPlannerController@transportation_comment');
Route::post('hotel-review-upload-image', 'api\TripPlannerController@hotel_review_upload_image');
Route::post('transportation-review-upload-image', 'api\TripPlannerController@transportation_review_upload_image');

/** Angular Website **/
Route::get('upcoming-events','api\HomeController@upcoming_events');
Route::post('upcoming-events-filter','api\HomeController@upcoming_events_by_filter');
Route::get('total-counts','api\HomeController@total_count');
Route::get('home-page-gallery','api\HomeController@home_page_gallery');
Route::post('search-events','api\HomeController@search_events');
Route::post('event-list-website', 'api\EventsController@event_list_website');

/************ Testing routes ***************/
Route::post('promoter-event-create-test', 'api\HomeController@promoter_event_create_test');
Route::post('promoter-guard-create-test', 'api\HomeController@promoter_guard_create_test');
Route::post('promoter-events-test', 'api\HomeController@promoter_events_test');
Route::post('event-detail-test', 'api\HomeController@event_detail_test');
Route::post('promoter-event-edit-test', 'api\HomeController@promoter_event_edit_test');
Route::post('seats-available-by-ticket-type', 'api\HomeController@seatsByTicketType');
Route::post('event-create-ticket-request-test', 'api\EventsController@create_ticket_request_test_new');
Route::post('event-change-status-of-ticket-test', 'api\EventsController@change_status_of_ticket_test_new');
Route::post('event-user-purchased-events-test', 'api\EventsController@user_purchased_events_test');
Route::post('scan-ticket-test', 'api\GuardController@scanTicketTest');
Route::post('scanned-ticket-list-test', 'api\GuardController@scannedTicketListTest');
Route::post('ticket-detail-test', 'api\EventsController@ticketDetailTest');
Route::post('event-count-ticket-request-test', 'api\EventsController@count_ticket_request_test');
Route::post('ticket-detail-by-name-email', 'api\EventsController@ticketDetailByNameOrEmail');
Route::post('event-count-promoter-ticket-request-test', 'api\EventsController@count_promoter_ticket_request_test');

/***** 17 aug 2018 *********************/
Route::post('delete-user-gallery-image', 'api\RegisterController@delete_user_gallery_image');

/***** 22 Aug 2018 ******/
Route::any('initialize-app-data', 'api\CommonController@getAppdata');