<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Guard;
use App\Models\Ticket;
use App\Models\Event;
use File;
use Illuminate\Support\Facades\Hash;
use Mail;
use DB;
use Config;
use Yajra\Datatables\Facades\Datatables;
use Redirect;
use Auth;
use Illuminate\Support\Facades\Input;

class GuardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
	public function __construct()
    {
		$this->middleware('admin', ['except' => 'logout']);
    }
	
    public function index()
    {
        return view('admin.guards.list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
		 
        $promoters    = User::orderBy('name', 'asc')->where('type','promoter')->get();
        $events    = Event::orderBy('event_name', 'asc')->get();
        return view('admin.guards.add')->with(['promoters'=>$promoters,'events'=>$events]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
			'user_name' =>'required|unique:guards,user_name',
			'password' => 'required',
			'event_id' => 'required',
		]; 
		$validator = Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{ 
			$messages = $validator->messages();
			if (!empty($messages)) {
				if ($messages->has('password')) {
					return Redirect::route('guard-create')->with('error','Select password.');
				}
				if ($messages->has('user_name')) {
					return Redirect::route('guard-create')->with('error','Entered user name already exists.');
				}
				if ($messages->has('event_id')) {
					return Redirect::route('guard-create')->with('error','Select evrnt');
				}			         						   
			}
		}
		$guard = new Guard();
		$guard->user_name       = $request->input('user_name');
		$guard->event_id       = $request->input('event_id');
		$guard->promoter_id       = $request->input('promoter_id') ? $request->input('promoter_id') :Auth::user()->id;
		$guard->password   = Hash::make($request->input('password'));
		$guard->is_active  = ($request->input('is_active') == 1)? '1':'0';
		$guard->save();
		return Redirect::route('guard-list')->with('message','Guard Successfully Added.');	
    }
    
	   
    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $promoters    = User::orderBy('name', 'asc')->where('type','promoter')->get();
        $events    = Event::orderBy('event_name', 'asc')->get();
		$guard  = Guard::find($id);
        return view('admin.guards.edit')->with(['promoters'=>$promoters,'events'=>$events,'guard'=>$guard]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $rules = [
			'user_name' =>'required|unique:users,user_name,'.$id,
			'event_id' => $request->input('promoter_id')  ? '' : 'required',
		]; 
		$validator = Validator::make(Input::all(), $rules);
		if ($validator->fails())
		{ 
			$messages = $validator->messages();
			if (!empty($messages)) {
				
				if ($messages->has('user_name')) {
					return Redirect::route('guard-edit',$id)->with('error','Entered User_name Already Exists.');
				}
				if ($messages->has('event_id')) {
					return Redirect::route('guard-edit',$id)->with('error','Select Event');
				}			         						   
			}
		}
		$guard = Guard::findOrFail($id);
		$guard->user_name  = $request->input('user_name');
		$guard->event_id   = $request->input('event_id');
		if(!empty($request->input('password')))
			$guard->password  = Hash::make($request->input('password'));
		
		$guard->is_active  = ($request->input('is_active') == 1)? '1':'0';
		$guard->promoter_id       = $request->input('promoter_id') ? $request->input('promoter_id') :Auth::user()->id;
		$guard->save();
		return Redirect::route('guard-list')->with('message','Guard Successfully Edited.');   
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $guard = Guard::findOrFail($id);
		$guard->delete();
        return Redirect::route('guard-list')->with('message','Guard Successfully Deleted.');
    }
	
	public function getAjaxGuards()
    {
        $guards    = Guard::latest('id');
		return Datatables::of($guards)->make(true);
    }
	
	public function editGuardApproveStatus($id,$status){
		 $guard = Guard::findOrFail($id);
		 $guard->is_active = $status;
		 $guard->save();
		 return redirect()->back()->with('message','Guard updated.');
	}
	
	public function postUpdateStatus(Request $request){
		$id = $request->id;
		$val = $request->value;
		$guard  = Guard::find($id);
		if($guard){
			$guard->is_active = $val;
			$guard->save();
			return response( array('data' =>'Updated successfuly' ,'response' => 1));
		} else {
			return response( array('data' =>'error' ,'response' => 0));
		}
	}
}
