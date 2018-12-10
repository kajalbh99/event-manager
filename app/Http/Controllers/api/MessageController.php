<?php

namespace App\Http\Controllers\api;
use App\Http\Controllers\Controller;
use App\User;
use App\Models\UserFriend;
use Illuminate\Http\Request;
use Nahid\Talk\Facades\Talk;
use Auth;
use View;
use DB;
use Mail;
use Config;

class MessageController extends Controller
{
    protected $authUser;
    public function __construct(Request $req)
    {
		if(isset($req->user_one) && $req->user_one!=null)
		{
			$user_one = User::where('email',$req->user_one)->first();
			if($user_one)
			{
				Talk::setAuthUserId($user_one->id);
			
				return response()->json(['status'=>'success','data'=>Talk::threads()], 200);
			}
			else{
				return response()->json(['status'=>'failure','message'=>'User not found'], 400);
			}
			
		}
		else{
			return response()->json(['status'=>'failure'], 400);
		}
		
        
    }

    public function chatHistory(Request $req)
    {
		if(isset($req->user_two) && isset($req->user_one))
		{
			$user_one = User::where('email',$req->user_one)->first();
			if($user_one && $req->user_two)
			{
				$conversations = Talk::getMessagesAllByUserId($req->user_two, 0, 500);
				$user = '';
				$messages = [];
				if(!$conversations) {
					$user = User::find($req->user_two);
				} else {
					$user = $conversations->withUser;
					$messages = $conversations->messages;
					foreach($messages as $mk=>$mv)
					{   
					    $thumbnail = public_path('uploads/user_profile/'.$mv->sender->id.'/thumbnail_'.$mv->sender->profile_photo);
						if(file_exists($thumbnail)){
							$mv->sender->profile_photo = 'thumbnail_'.$mv->sender->profile_photo;
						}
						if($mv->user_id!=$user_one->id)
							Talk::makeSeen($mv->id);
					}
					
				}
				//$threads = Talk::threads();
				return response(array('status'=>'success','messages'=>$messages, 'response' => 1));	
				//return response()->json(['status'=>'success','messages'=>$messages,'threads'=>$threads], 200);
			}
			else{
				return response(array('status'=>'failure', 'messages'=>'User not found', 'response' => 0));
				//return response()->json(['status'=>'failure','message'=>'User not found'], 400);
			}
			
		}
		else{
			return response(array('status'=>'failure', 'messages'=>'Please provide user emails', 'response' => 0));
			//return response()->json(['status'=>'failure','message'=>'Please provide user emails'], 400);
		}
        
    }

    public function ajaxSendMessage(Request $request)
    {
       
		$rules = [
			'message_data'=>'required',
			'user_two'=>'required'
		];

		$this->validate($request, $rules);
		$user_two = User::where('id',$request->user_two)->first();
		$body = $request->input('message_data');
		if(isset($request->user_two))
		{
			if ($message = Talk::sendMessageByUserId($request->user_two, $body)) {
				Mail::send('admin.emails.message',
					['data'=>$body,'heading'=>'You received new message.'],
					function($message) use($user_two)
					{
					   $message->to($user_two->email)->subject('Carnivalist New Message.');
					}
				);
				return response(array('status'=>'success', 'response'=>1));
				//return response()->json(['status'=>'success'], 201);
			}
		}
		else{
			return response(array('status'=>'failure', 'response'=>0));
			//return response()->json(['status'=>'failure'], 400);
		}
        
    }

    public function ajaxDeleteMessage(Request $request, $id)
    {
        if ($request->ajax()) {
            if(Talk::deleteMessage($id)) {
                //return response()->json(['status'=>'success'], 200);
				return response(array('status'=>'success', 'response'=>1));
            }

            //return response()->json(['status'=>'errors', 'msg'=>'something went wrong'], 401);
			return response(array('status'=>'errors','msg'=>'something went wrong', 'response'=>0));
        }
    }

    public function tests()
    {
        dd(Talk::channel());
    }
	
	public function threads(Request $req)
    {
		$data = array();
		if(isset($req->user_one) && $req->user_one!=null)
		{
			
			$user_one = User::where('email',$req->user_one)->first();
			if($user_one)
			{
				$threads = Talk::threads();
				foreach($threads as $k=>$v)
				{	
					$thumbnail = public_path('uploads/user_profile/'.$v->withUser->id.'/thumbnail_'.$v->withUser->profile_photo);
					if(file_exists($thumbnail)){
						$v->withUser->profile_photo = 'thumbnail_'.$v->withUser->profile_photo;
					}
					
					$threads[$k]->unread = $this->countThreadUnreadMessages($v->withUser ? $v->withUser->id: '',$v->thread ? $v->thread->conversation_id:'');
					 
					$threads[$k]->is_friend = $this->checkisfriend($req->user_one,$v->withUser ? $v->withUser->id:'');
				}
				return response( array('data' =>$threads ,'response' =>1));
			}
			else{
				$data['message'] = "Please fill all required fields";
				return response( array('data' =>$data ,'response' => 0));
			}
			
		}
		else{
			$data['message'] = "Please fill all required fields";
			return response( array('data' =>$data ,'response' => 0));
		}
		
        
    }
	
	public function countTotalUnreadMessages(Request $req)
    {	
		$data = array();
		if(isset($req->user_one) && $req->user_one!=null)
		{
			
			$user_one = User::where('email',$req->user_one)->first();
			if($user_one)
			{
				$total_count = 0;
				$threads = DB::table('conversations')
							->join('messages', 'messages.conversation_id', '=', 'conversations.id')
							->where('messages.user_id', '!=', $user_one->id)
							->where('messages.is_seen', '=', 0)
							->where(function ($query) use($user_one) {
								$query->where('conversations.user_one',$user_one->id)
									  ->orWhere('conversations.user_two',$user_one->id);
							
							})
							
							->count();
				if($threads)
				{
					$total_count = $threads;
				}
				return response( array('data' =>$total_count ,'response' =>1));
			}
			else{
				$data['message'] = "Please fill all required fields";
				return response( array('data' =>$data ,'response' => 0));
			}
			
		}
		else{
			$data['message'] = "Please fill all required fields";
			return response( array('data' =>$data ,'response' => 0));
		}		       
    }
	
	public function countThreadUnreadMessages($user_id,$conversation_id)
    {		
		$total_count = 0;
		if(isset($user_id) && $user_id!=null)
		{			
			if($user_id)
			{
				if($conversation_id)
				{
					
					$threads = DB::table('messages')
								->where('user_id', '=', $user_id)
								->where('is_seen', '=', 0)
								->where('conversation_id', '=', $conversation_id)
								->count();
					
					if($threads>0)
					{
						$total_count = $threads;
					}
					
				}
				
			}						
		}		
		return $total_count;				       
    }
	
	public function checkisfriend($user_email,$friend_id)
	{
		$isfriend = 0;
		$user = User::where('email',$user_email)->first();
		if($user)
		{
			$friend = UserFriend::where(function($q) use($user){
				$q->where('user_id',$user->id)->orWhere('friend_id',$user->id);
			})
			->where(function($q1) use($friend_id){
				$q1->where('user_id',$friend_id)->orWhere('friend_id',$friend_id);
			})
			->where('is_friend','1')
			->count();
			if($friend > 0 )
			{
				$isfriend = 1;
			}
		}
		return $isfriend;
		
	}
}
