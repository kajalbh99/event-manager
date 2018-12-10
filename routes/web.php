<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
//Auth::routes();

/*
* Admin routes
*/
use Cartalyst\Stripe\Laravel\Facades\Stripe;
Route::get('/testing', function(){
	\Stripe\Stripe::setApiKey ( env('STRIPE_SECRET') );
	\Stripe\Stripe::setClientId( env('STRIPE_CLIENT_ID'));
	$stripe = Stripe::make(env('STRIPE_SECRET'));
	App\Models\AllocatePdfs::where('ticket_id',2222)->delete();
	exit;
	try{
		$refund = \Stripe\Refund::create(array(
		  "charge" => "ch_1Cyfi5LQRy3oPPsKSQsmoHel",
		  "refund_application_fee" => true,
		)/* , array("stripe_account" => "acct_1Cv4WABGInTllWJp") */);
		echo '<pre>';
		print_r($refund);
		exit;
	}
	catch (Exception $e) {
		echo $e->getMessage();
	} 
	/* try {
		
		$charge = $stripe->charges()->create(array(
		  "amount" => 90,
		  "currency" => "usd",
		  "source" => "tok_visa",
		  "application_fee" => 9,
		), array("stripe_account" => "acct_1CqzK3AZYZCWGMHM"));
		
		if($charge['status'] == 'succeeded') { 
			
			echo 'success';
		} else {
			echo 'payment error';
		}  
	}
	catch (Exception $e) {
		echo $e->getMessage();
	} catch(\Cartalyst\Stripe\Exception\CardErrorException $e) {
		echo $e->getMessage();
	} catch(\Cartalyst\Stripe\Exception\MissingParameterException $e) {
		echo $e->getMessage();
	}   */
	
	/* $acct = $stripe->account()->create(array(
	  "type" => "custom",
	  "country" => "US",
	  "external_account" => array(
		"object" => "bank_account",
		"country" => "US",
		"currency" => "usd",
		"routing_number" => "110000000",
		"account_number" => "000123456789",
	  ),
	  "tos_acceptance" => array(
		"date" => 1532333631,
		"ip" => "103.43.152.121"
	  )
	));
	echo '<pre>';
	print_r($acct);
	exit; */
	
	
})->name('testing');
Route::any('uploadpdf', function(){
	
})->name('uploadpdf');
Route::any('/uploadpdf','CronController@uploadpdf')->name('uploadpdf');
Route::get('admin', 'Admin\LoginController@getLogin')->name('admin');
Route::post('admin/login', 'Admin\LoginController@postLogin')->name('admin-login');

Route::group(['middleware' => ['admin']], function () {
  Route::group(['prefix' => 'admin'], function () {           
  //dashboard
  Route::get('dashboard', 'Admin\UserController@index')->name('admin-home');
  Route::get('logout', 'Admin\LoginController@logout')->name('admin-logout');
  
  //Carnivals
  Route::get('carnivals-list', 'Admin\CarnivalController@index')->name('carnival-list');
  Route::any('carnival-add', 'Admin\CarnivalController@addCarnival')->name('carnival-add');
  Route::any('carnival-edit/{id}', 'Admin\CarnivalController@editCarnival')->name('carnival-edit');
  Route::get('carnival-delete/{id}', 'Admin\CarnivalController@deleteCarnival')->name('carnival-delete');
  
  Route::any('carnival-change-approve-status/{id}/{status}', 'Admin\CarnivalController@editCarnivalApproveStatus')->name('carnival_change_approve_status');
  

  //events
  Route::get('events-list', 'Admin\EventController@index')->name('event-list');
  Route::any('event-add', 'Admin\EventController@addEvent')->name('event-add');
  Route::any('event-edit/{id}', 'Admin\EventController@editEvent')->name('event-edit');
  Route::get('event-delete/{id}', 'Admin\EventController@deleteEvent')->name('event-delete');
  Route::post('event-review-approve', 'Admin\EventController@approveEventReview')->name('event-review-approve');
  Route::post('event-review-disapprove', 'Admin\EventController@disapproveEventReview')->name('event-review-disapprove');
   Route::post('event-review-delete', 'Admin\EventController@deleteEventReview')->name('event-review-delete');
	Route::any('event-change-approve-status/{id}/{status}', 'Admin\EventController@editEventApproveStatus')->name('event_change_approve_status');
   
  //users
  Route::get('users-list', 'Admin\UserController@listUsers')->name('user-list');
  Route::any('user-add', 'Admin\UserController@addUser')->name('user-add');
  Route::any('user-edit/{id}', 'Admin\UserController@editUser')->name('user-edit');
  Route::get('user-delete/{id}', 'Admin\UserController@deleteUser')->name('user-delete');
  Route::any('get-state-list/{country_id}', 'Admin\UserController@getStateList')->name('state-list');
  Route::any('user-change-approve-status/{id}/{status}', 'Admin\UserController@editUserApproveStatus')->name('user_change_approve_status');
   //brands
   Route::get('bands-list', 'Admin\BandController@index')->name('band-list');
   Route::any('band-add', 'Admin\BandController@addBand')->name('band-add');
   Route::any('band-edit/{id}', 'Admin\BandController@editBand')->name('band-edit');
   Route::get('band-delete/{id}', 'Admin\BandController@deleteBand')->name('band-delete');
   Route::post('band-review-approve', 'Admin\BandController@approveBandReview')->name('band-review-approve');
   Route::post('band-review-disapprove', 'Admin\BandController@disapproveBandReview')->name('band-review-disapprove');
   Route::post('band-review-delete', 'Admin\BandController@deleteBandReview')->name('band-review-delete');
   
	/***** by me ***/
	Route::get('promoters-list', 'Admin\PromotersController@index')->name('promoters-list');
	Route::get('tickets-list', 'Admin\TicketsController@index')->name('tickets-list');
	Route::get('ticket-detail/{id}', 'Admin\TicketsController@show')->name('ticket-detail');
	Route::any('promoter-edit/{id}', 'Admin\PromotersController@editUser')->name('promoter-edit');
	Route::any('promoter-add', 'Admin\PromotersController@addUser')->name('promoter-add');
	Route::any('promoter-add-event/{id}', 'Admin\EventController@addPromoterEvent')->name('promoter-add-event');
	/*****************/
	
	
	/************ guards ***********/
	
	Route::get('guard-list', 'Admin\GuardController@index')->name('guard-list');
	Route::get('guard-create', 'Admin\GuardController@create')->name('guard-create');
	Route::post('guard-add', 'Admin\GuardController@store')->name('guard-add');
	Route::get('guard-edit/{id}', 'Admin\GuardController@edit')->name('guard-edit');
	Route::post('guard-update/{id}', 'Admin\GuardController@update')->name('guard-update');
	Route::get('guard-delete/{id}', 'Admin\GuardController@destroy')->name('guard-delete');
	Route::any('guard-change-approve-status/{id}/{status}', 'Admin\GuardController@editGuardApproveStatus')->name('guard_change_approve_status');
	/* Route::resource('guards', 'Admin\GuardController')->names([
		'index' => 'guards',
		'create' => 'guards-create',
		'edit' => 'guards-edit',
	]); */
	/*******************************/
	 //hotels
	  Route::get('hotel-list', 'Admin\HotelController@index')->name('hotel-list');
	  Route::any('hotel-add', 'Admin\HotelController@addHotel')->name('hotel-add');
	  Route::any('hotel-edit/{id}', 'Admin\HotelController@editHotel')->name('hotel-edit');
	  Route::get('hotel-delete/{id}', 'Admin\HotelController@deleteHotel')->name('hotel-delete');
	  Route::post('hotel-review-approve', 'Admin\HotelController@approveHotelReview')->name('hotel-review-approve');
	  Route::post('hotel-review-disapprove', 'Admin\HotelController@disapproveHotelReview')->name('hotel-review-disapprove');
	  Route::post('hotel-review-delete', 'Admin\HotelController@deleteHotelReview')->name('hotel-review-delete');
	  Route::any('hotel-change-approve-status/{id}/{status}', 'Admin\HotelController@editHotelApproveStatus')->name('hotel_change_approve_status');
	  
	   //hotels
	  Route::get('transportation-list', 'Admin\TransportationController@index')->name('transportation-list');
	  Route::any('transportation-add', 'Admin\TransportationController@addTransportation')->name('transportation-add');
	  Route::any('transportation-edit/{id}', 'Admin\TransportationController@editTransportation')->name('transportation-edit');
	  Route::get('transportation-delete/{id}', 'Admin\TransportationController@deleteTransportation')->name('transportation-delete');
	  Route::post('transportation-review-approve', 'Admin\TransportationController@approveTransportationReview')->name('transportation-review-approve');
	  Route::post('transportation-review-disapprove', 'Admin\TransportationController@disapproveTransportationReview')->name('transportation-review-disapprove');
	  Route::post('transportation-review-delete', 'Admin\TransportationController@deleteTransportationReview')->name('transportation-review-delete');
	  Route::any('transportation-change-approve-status/{id}/{status}', 'Admin\TransportationController@editTransportationApproveStatus')->name('transportation_change_approve_status');
	  
	  
  });
  
	//reviews
	Route::get('unapproved-reviews', 'Admin\ReviewController@index')->name('review-list');
	Route::get('view-event-review/{id}', 'Admin\ReviewController@postEventreview')->name('view-event-review');
	Route::get('view-band-review/{id}', 'Admin\ReviewController@postBandreview')->name('view-band-review');
	Route::get('view-hotel-review/{id}', 'Admin\ReviewController@posthotelreview')->name('view-hotel-review');
	Route::get('view-transportation-review/{id}', 'Admin\ReviewController@posttransportationreview')->name('view-transportation-review');
  
 
  
	//********* ajax request ********/
	Route::any('ajax_delete_user_gallery_image', 'Admin\AjaxController@ajaxDeleteUserGalleryImage')->name('ajax_delete_user_gallery_image');
	Route::any('get_friend_list/{id}', 'Admin\UserController@getAjaxUserFriends')->name('ajax_user_friends');
	Route::any('get_promoter_events/{id}', 'Admin\PromotersController@getAjaxPromoterEvents')->name('ajax_promoter_events');
	Route::any('get_carnival_events/{id}', 'Admin\CarnivalController@getAjaxCarnivalEvents')->name('ajax_carnival_events');
	Route::any('ajax_get_promoters', 'Admin\PromotersController@getAjaxPromoters')->name('ajax_get_promoters');
	
	Route::any('ajax_get_users', 'Admin\UserController@getAjaxUsers')->name('ajax_get_users');
	Route::any('ajax_get_tickets', 'Admin\TicketsController@getAjaxTickets')->name('ajax_get_tickets');
	Route::any('ajax_get_hotels', 'Admin\HotelController@getAjaxHotels')->name('ajax_get_hotels');
	Route::any('ajax_get_transportation', 'Admin\TransportationController@getAjaxTransportation')->name('ajax_get_transportation');
	Route::post('ajax_ticket_update_status', 'Admin\TicketsController@postUpdateTicketStatus_new')->name('ajax_ticket_update_status');
	Route::post('ajax_ticket_refund_payment', 'Admin\TicketsController@refundTicketPayment')->name('ajax_ticket_refund_payment');
	Route::any('ajax_get_bands', 'Admin\BandController@getAjaxBands')->name('ajax_get_bands');
	Route::any('ajax_get_events', 'Admin\EventController@getAjaxEvents')->name('ajax_get_events');
	Route::any('ajax_get_carnival', 'Admin\CarnivalController@getAjaxCarnival')->name('ajax_get_carnival');
	
	Route::post('ajax_event_update_approval', 'Admin\EventController@postUpdateApproval')->name('ajax_event_update_approval');
	Route::post('ajax_event_update_status', 'Admin\EventController@postUpdateStatus')->name('ajax_event_update_status');
	
	
	Route::any('ajax_get_guards', 'Admin\GuardController@getAjaxGuards')->name('ajax_get_guards');
	/************************/
	
	/************ review by ajax ******/
	
	Route::any('ajax_get_event_review', 'Admin\ReviewController@getAjaxEventReview')->name('ajax_get_event_review');
	Route::any('ajax_get_band_review', 'Admin\ReviewController@getAjaxBandReview')->name('ajax_get_band_review');
	Route::any('ajax_get_hotel_review', 'Admin\ReviewController@getAjaxHotelReview')->name('ajax_get_hotel_review');
	Route::any('ajax_get_transportation_review', 'Admin\ReviewController@getAjaxTransportationReview')->name('ajax_get_transportation_review');
	/*****************************/
	
	/******* 23 aug 2018 ********/
	Route::post('ajax_user_change_status', 'Admin\UserController@postUpdateStatus')->name('ajax_user_change_status');
	Route::post('ajax_guard_change_status', 'Admin\GuardController@postUpdateStatus')->name('ajax_guard_change_status');
	Route::post('ajax_carnival_change_status', 'Admin\CarnivalController@postUpdateStatus')->name('ajax_carnival_change_status');
	Route::post('ajax_hotel_change_status', 'Admin\HotelController@postUpdateStatus')->name('ajax_hotel_change_status');
	Route::post('ajax_transportation_change_status', 'Admin\TransportationController@postUpdateStatus')->name('ajax_transportation_change_status');
	
	/******************************/
});

/**** invoice details*****/
Route::get('/invoice/{id}','InvoiceController@show')->name('invoice-details');
/*******************/

//reset password
Route::get('reset-password/{var?}', 'Admin\ResetPasswordController@resetPassword')->name('reset-password');
Route::any('change-password', 'Admin\ResetPasswordController@changePassword')->name('change-password');


/**** cron job*****/
Route::get('/update-yearly-events','CronController@updateEvent')->name('update-yearly-events');
Route::any('/ajax/removePdf','CronController@removePdf')->name('ajax.removepdf');
Route::any('/removePdfFile/{temp_id}/{filename}','CronController@removePdfFile')->name('removepdffile');
/*******************/
