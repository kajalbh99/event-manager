<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use App\Models\Carnival;
use App\Models\Event;
use Input;





class FilterController extends Controller
{	
	public function country_event(Request $request){
		
		$country_id = $request->input('country_id');        if($country_id == "undefined"){			$country_id = "";		}
		$carnival_id = $request->input('carnival_id');
		$start = '';
		$end = '';
		if(( $request->input('from') != "" ) && ( $request->input('to') != "" )){		
			$start    = substr($request->input('from'),4,11);
			$start = date('Y-m-d', strtotime($start));
			
			$end    = substr($request->input('to'),4,11);
			$end = date('Y-m-d', strtotime($end));
		}

		if( !empty($start) && !empty($end) && !empty($country_id) ){
			$event_detail = Event::where('country_id','=',$country_id)
									->where('is_active','=','1')
									->where('carnival_id','=',$carnival_id)
									->whereDate('event_date', '>=', $start)
									->whereDate('event_date', '<=', $end)
									->get();
			$data = $event_detail;
			return response( array('data' =>$data ,'response' => 1));
			
		}elseif($country_id>0 && !empty($country_id) ){
			$event_detail = Event::where('country_id','=',$country_id)
									->where('is_active','=','1')
									->where('carnival_id','=',$carnival_id)
									->whereDate('event_date', '>=', date('Y-m-d'))
									->get();
			$data = $event_detail;
			return response( array('data' =>$data ,'response' => 1));
			
		}elseif( $start != "" && $end != ""){
			$event_detail = Event::whereDate('event_date', '>=', $start)
									->where('is_active','=','1')
									->where('carnival_id','=',$carnival_id)
									->whereDate('event_date', '<=', $end)
									->get();
			$data = $event_detail;
			return response( array('data' =>$data ,'response' => 1));
		}else{
			return response( array('data' =>'error' ,'response' => 0));
		}
	}
	
	public function country_event_test(Request $request){
		
		$country_id = $request->input('country_id');        if($country_id == "undefined"){			$country_id = "";		}
		$carnival_id = $request->input('carnival_id');
		$start = '';
		$end = '';
		if(( $request->input('from') != "" ) && ( $request->input('to') != "" )){		
			$start    = substr($request->input('from'),4,11);
			$start = date('Y-m-d', strtotime($start));
			
			$end    = substr($request->input('to'),4,11);
			$end = date('Y-m-d', strtotime($end));
		}

		if( !empty($start) && !empty($end) && !empty($country_id) ){
			$event_detail = Event::where('country_id','=',$country_id)
									->where('is_active','=','1')
									->where('carnival_id','=',$carnival_id)
									->where('carnival_type','=','0')
									->whereDate('event_date', '>=', $start)
									->whereDate('event_date', '<=', $end)
									->get();
			$data = $event_detail;
			return response( array('data' =>$data ,'response' => 1));
			
		}elseif($country_id>0 && !empty($country_id) ){
			$event_detail = Event::where('country_id','=',$country_id)
									->where('is_active','=','1')
									->where('carnival_id','=',$carnival_id)
									->where('carnival_type','=','0')
									->whereDate('event_date', '>=', date('Y-m-d'))
									->get();
			$data = $event_detail;
			return response( array('data' =>$data ,'response' => 1));
			
		}elseif( $start != "" && $end != ""){
			$event_detail = Event::whereDate('event_date', '>=', $start)
									->where('is_active','=','1')
									->where('carnival_id','=',$carnival_id)
									->where('carnival_type','=','0')
									->whereDate('event_date', '<=', $end)
									->get();
			$data = $event_detail;
			return response( array('data' =>$data ,'response' => 1));
		}else{
			return response( array('data' =>'error' ,'response' => 0));
		}
	}
	
	public function country_local_event(Request $request){
		
		$country_id = $request->input('country_id');        if($country_id == "undefined"){			$country_id = "";		}
		$carnival_id = $request->input('carnival_id');
		$start = '';
		$end = '';
		if(( $request->input('from') != "" ) && ( $request->input('to') != "" )){		
			$start    = substr($request->input('from'),4,11);
			$start = date('Y-m-d', strtotime($start));
			
			$end    = substr($request->input('to'),4,11);
			$end = date('Y-m-d', strtotime($end));
		}

		if( !empty($start) && !empty($end) && !empty($country_id) ){
			$event_detail = Event::where('country_id','=',$country_id)
									->where('is_active','=','1')
									->where('carnival_id','=',$carnival_id)
									->where('carnival_type','=','1')
									->whereDate('event_date', '>=', $start)
									->whereDate('event_date', '<=', $end)
									->get();
			$data = $event_detail;
			return response( array('data' =>$data ,'response' => 1));
			
		}elseif($country_id>0 && !empty($country_id) ){
			$event_detail = Event::where('country_id','=',$country_id)
									->where('is_active','=','1')
									->where('carnival_id','=',$carnival_id)
									->where('carnival_type','=','1')
									->whereDate('event_date', '>=', date('Y-m-d'))
									->get();
			$data = $event_detail;
			return response( array('data' =>$data ,'response' => 1));
			
		}elseif( $start != "" && $end != ""){
			$event_detail = Event::whereDate('event_date', '>=', $start)
									->where('is_active','=','1')
									->where('carnival_id','=',$carnival_id)
									->where('carnival_type','=','1')
									->whereDate('event_date', '<=', $end)
									->get();
			$data = $event_detail;
			return response( array('data' =>$data ,'response' => 1));
		}else{
			return response( array('data' =>'error' ,'response' => 0));
		}
	}

}
