<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\EventReview;
use App\Models\EventReviewMedias;
use App\Models\BandReview; 
use App\Models\BandReviewMedias;
use App\Models\HotelReview;
use App\Models\HotelReviewMedias;
use App\Models\TransportationReview;
use App\Models\TransportationReviewMedias;
use Redirect;
use Yajra\Datatables\Facades\Datatables;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     */
    public function index()
    {	$EventReview  = EventReview::where('is_approved','=',0)->with('event','user')->orderBy('id', 'DESC')->get();
        $BandReview   = BandReview::where('is_approved','=',0)->with('band','user')->orderBy('id', 'DESC')->get();
		$HotelReview   = HotelReview::where('is_approved','=',0)->with('hotel','user')->orderBy('id', 'DESC')->get();
		$TransportationReview   = TransportationReview::where('is_approved','=',0)->with('transportation','user')->orderBy('id', 'DESC')->get();
        return view('admin.reviews.list_reviews')->with(['event_review' => $EventReview,'band_review' => $BandReview,'hotel_review'=>$HotelReview,'transportation_review'=>$TransportationReview ]);
    }


    public function postEventreview($id)
	{
		$PostEventReview  = EventReview::where('id','=',$id)->get();
		$PostEventReviewimages  =EventReviewMedias::where('event_reviews_id','=',$id)->get();
		
		return view('admin.reviews.view_reviews')->with(['event_review' => $PostEventReview,'event_review_image'=>$PostEventReviewimages]);
		
		//echo "<pre>";print_r($PostEventReview_images);die();
	}
	
	public function postBandreview($id)
	{
		$PostEventReview  = BandReview::where('id','=',$id)->get();
		$PostBandReviewimages  =BandReviewMedias::where('band_reviews_id','=',$id)->get();
		
		return view('admin.reviews.view_band_reviews')->with(['band_review' => $PostEventReview,'band_review_image'=>$PostBandReviewimages]);
	
	}
	
	public function posthotelreview($id)
	{
		$PostHoteltReview  = HotelReview::where('id','=',$id)->get();
		$PosthotelReviewimages  =HotelReviewMedias::where('hotel_reviews_id','=',$id)->get();
		
		return view('admin.reviews.view_hotel_reviews')->with(['hotel_review' => $PostHoteltReview,'hotel_review_image'=>$PosthotelReviewimages]);
	
	}
	public function posttransportationreview($id)
	{
		$PostTransportationReview  = TransportationReview::where('id','=',$id)->get();
		$PosttransportationReviewimages  =TransportationReviewMedias::where('transportation_reviews_id','=',$id)->get();
		
		return view('admin.reviews.view_transportation_reviews')->with(['transportation_review' => $PostTransportationReview,'transportation_review_image'=>$PosttransportationReviewimages]);
	
	}
	
	public function getAjaxEventReview()
	{

		$event_review  = EventReview::with(['event','user'])->has('event')->where('is_approved',0)->orderBy('id', 'desc')->get();

		return Datatables::of($event_review)
		 ->add_column('event_name', function($event_review) {
		return $event_review->event->event_name;})
		 ->add_column('user_name', function($event_review) {
		return $event_review->user->user_name;})
		->editColumn('event_id', function($event_review) { return $event_review->event->event_name; }) 
		->editColumn('user_id', function($event_review) { return $event_review->user->user_name; }) 
		 /* ->add_column('action', function($event_review) {
		return '<button class="btn btn-primary test" onclick="eventapprove('.$event_review->id.');" >Approve</button>&nbsp;<button class="btn btn-danger test" onclick="eventdelete('.$event_review->id.');" >Delete</button>
		<a class="btn btn-success" href="view-event-review/'.$event_review->id.'">View</a>'
				;}) */

		->make(true);
	}
		
	public function getAjaxBandReview()
	{
		$band_review  = BandReview::with(['band','user'])->has('band')->where('is_approved',0)->orderBy('id', 'desc')->get();
		return Datatables::of($band_review)
		->add_column('band_name', function($band_review) {
		return $band_review->band->band_name;})
		->add_column('user_name', function($band_review) {
		return $band_review->user->user_name;})
		/* ->add_column('action', function($band_review) {
		return '<button class="btn btn-primary test" onclick="bandapprove('.$band_review->id.');" >Approve</button>&nbsp;<button class="btn btn-danger test" onclick="banddelete('.$band_review->id.');" >Delete</button>
		<a class="btn btn-success" href="view-band-review/'.$band_review->id.'">View</a>'
			;}) */	
		->make(true);
	}
	
	public function getAjaxHotelReview()
	{
		$hotel_review  = HotelReview::with(['hotel','user'])->has('hotel')->where('is_approved',0)->orderBy('id', 'desc')->get();
		return Datatables::of($hotel_review)
		->add_column('hotel_name', function($hotel_review) {
		return $hotel_review->hotel->hotel_name;})
		->add_column('user_name', function($hotel_review) {
		return $hotel_review->user->user_name;})
		/* ->add_column('action', function($hotel_review) {
		return '<button class="btn btn-primary test" onclick="hotelapprove('.$hotel_review->id.');" >Approve</button>&nbsp;<button class="btn btn-danger test" onclick="hoteldelete('.$hotel_review->id.');" >Delete</button>
		<a class="btn btn-success" href="view-hotel-review/'.$hotel_review->id.'">View</a>'
			;})	 */
		->make(true);
	}
	
	public function getAjaxTransportationReview()
	{
		$transportation_review  = TransportationReview::with(['transportation','user'])->has('transportation')->where('is_approved',0)->orderBy('id', 'desc')->get();
		return Datatables::of($transportation_review)
		->add_column('transportation_name', function($transportation_review) {
		return $transportation_review->transportation->transportation_name;})
		->add_column('user_name', function($transportation_review) {
		return $transportation_review->user->user_name;})
		/* ->add_column('action', function($transportation_review) {
		return '<button class="btn btn-primary test" onclick="transportationapprove('.$transportation_review->id.');" >Approve</button>&nbsp;<button class="btn btn-danger test" onclick="transportationdelete('.$transportation_review->id.');" >Delete</button>
		<a class="btn btn-success" href="view-transportation-review/'.$transportation_review->id.'">View</a>'
		;})	 */
		->make(true);
	}
}
