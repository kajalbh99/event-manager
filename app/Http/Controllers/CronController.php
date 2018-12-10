<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventTicketType;
use App\Models\TicketTypePdfs;
use File;

class CronController extends Controller
{
    public function updateEvent(Request $req)
	{
		$events = Event::whereDate('event_date', '<', date('Y-m-d'))->where('yearly', '1')->limit(1000)->get();
		foreach($events as $k=>$event){
			$newEventDate = date("Y-m-d", strtotime(date("Y-m-d", strtotime($event->event_date)) . " + 365 day"));
			$event->event_date = $newEventDate;
			
			if($event->event_end_date){
				$newEventEndDate = date("Y-m-d", strtotime(date("Y-m-d", strtotime($event->event_end_date)) . " + 365 day"));
				$event->event_end_date = $newEventEndDate;
			} else{
				$event->event_end_date = $newEventDate;
			}
			$event->save();
		}
		
	}
	
	public function uploadpdf(Request $request){
		$files =  $request->file('files');
		$temp_id =  $request->temp_id;
		$response = array();
		if(count($files)>0){
			$deleteUrl = '';
            foreach($files as $file){
                if( !empty($file) )
                {  
					$file_name = time().rand(0,99).".".$file->getClientOriginalExtension();
					$destinationPath = public_path('uploads/temp/'.$temp_id); 
					if(!File::exists($destinationPath)) {
						File::makeDirectory($destinationPath, $mode = 0777, true, true);
					}
					$file->move($destinationPath, $file_name);
					
					$deleteUrl = Route('removepdffile',[$temp_id,$file_name]);
					$file_value_array = array("name"=>$file->getClientOriginalName(),"size"=>$file->getClientSize(),"type"=>"","url"=>URL('/').'/public/uploads/temp/'.$temp_id.'/'.$file_name,"deleteType"=>"DELETE","deleteUrl"=>$deleteUrl); 
					array_push($response,$file_value_array);
				}
				

                return json_encode(array("files"=>$response));

            }
        }
	}
	
	public function removePdfFile(Request $request){
		$temp_id = $request->temp_id;
		$file_name = $request->filename;
		$destinationPath = public_path('uploads/temp/'.$temp_id.'/'.$file_name);
		try{
			if(File::exists($destinationPath)) {
				unlink($destinationPath);
			}
			return response($temp_id, 200);
		}catch(\Exception $e){
			 return response($e->getMessage(), 500);
		}
		exit;
		
	}
	
	public function removepdf(Request $request){
		$pdf_id =  $request->pdf_id;
		$event_type_id =  $request->event_type_id;
		$ticket_type = EventTicketType::find($event_type_id);
		try{
			if($ticket_type){
				$pdf = $ticket_type->pdfs()->find($pdf_id);
				if($pdf->count() > 0){
					$destinationPath = public_path('uploads/ticket_pdfs/'.$event_type_id.'/'.$pdf->file); 
					if(File::exists($destinationPath)) {
						unlink($destinationPath);
					} 
					$pdf->delete();
					return response($pdf, 200);
				}else{
					return response('Pdf not found', 500);
				}
			}else{
				 return response('Ticket type not found', 500);
			}
		}catch(\Exception $e){
			 return response($e->getMessage(), 500);
		}
		
	}
}
