<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPhotoGallery;
use App\Models\Country;
use App\Models\State;
use DB;
use Redirect;
use Auth;
use File;
use Validator;
use Illuminate\Support\Facades\Input;
use Yajra\Datatables\Datatables;
class AjaxController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
	
	public function ajaxDeleteUserGalleryImage(Request $req)
	{
		$gallery_id  = $req->gallery_id;
		if(isset($gallery_id))
		{
			$gallery = UserPhotoGallery::findOrFail($gallery_id);
			if($gallery)
			{
				$destinationPath = public_path('uploads/user_photo_gallery/'.$gallery->user_id.'/'.$gallery->user_gallery_image); 
				if(file_exists($destinationPath)) {
					unlink($destinationPath);
					$gallery->delete();
					return response()->json(['success'=>$destinationPath],200);
				}
				else{
					return response()->json(['error'=>'error in deleting image'],400);
				}
				
			}
			else{
				return response()->json(['error'=>'can not find gallery'],400);
			}
		}
		else{
				return response()->json(['error'=>'can not find gallery id'],400);
		}
	}
    
}
