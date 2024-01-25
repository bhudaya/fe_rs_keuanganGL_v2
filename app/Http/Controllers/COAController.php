<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Booking;
use DB;
use GuzzleHttp\Client;


class COAController extends Controller
{
    private $token ; 

    // view page all booking
    public function allcoa()
    {
        //$allBookings = DB::table('bookings')->get();
        //return view('formcoa.allcoa',compact('allBookings'));
        //$apiUrl = 'http://localhost:8000/api/coa_list';

        $apiUrl = config('api.be_api_url') ;
        $apiUrlCoa =  $apiUrl ."coa_list";



       
            //---- token BE get from login ------
            $this->token = session('sess_token');;        


            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$this->token ,
                
            ];

            $response = $client->get($apiUrlCoa, [
                'headers' => $headers,
                //'query' => $data,
            ]);

            $apiResponse = $response->getBody()->getContents();

            $decodedApiResponse = json_decode($apiResponse, true);

            //return response()->json($decodedApiResponse);
            //print_r( json_encode($decodedApiResponse['data']));


            $allBookings = $decodedApiResponse['data'] ;
            return view('formcoa.allcoa',compact('allBookings'));

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }


    }

    // coa add
    public function coaAdd()
    {
        $data = DB::table('room_types')->get();
        $user = DB::table('users')->get();
        return view('formcoa.coaadd',compact('data','user'));
    }
    
    // coa edit
    public function coaEdit($bkg_id)
    {
        $coaEdit = DB::table('bookings')->where('bkg_id',$bkg_id)->first();
        return view('formcoa.coaedit',compact('coaEdit'));
    }

    // coa save record
    public function saveRecord(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'room_type'     => 'required|string|max:255',
            'total_numbers' => 'required|string|max:255',
            'date' => 'required|string|max:255',
            'time' => 'required|string|max:255',
            'arrival_date'  => 'required|string|max:255',
            'depature_date' => 'required|string|max:255',
            'email'      => 'required|string|max:255',
            'phone_number'  => 'required|string|max:255',
            'fileupload' => 'required|file',
            'message'    => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {

            $photo= $request->fileupload;
            $file_name = rand() . '.' .$photo->getClientOriginalName();
            $photo->move(public_path('/assets/upload/'), $file_name);
           
            $booking = new Booking;
            $booking->name = $request->name;
            $booking->room_type     = $request->room_type;
            $booking->total_numbers  = $request->total_numbers;
            $booking->date  = $request->date;
            $booking->time  = $request->time;
            $booking->arrival_date   = $request->arrival_date;
            $booking->depature_date  = $request->depature_date;
            $booking->email       = $request->email;
            $booking->ph_number   = $request->phone_number;
            $booking->fileupload  = $file_name;
            $booking->message     = $request->message;
            $booking->save();
            
            DB::commit();
            Toastr::success('Create new booking successfully :)','Success');
            return redirect()->route('form/allbooking');
            
        } catch(\Exception $e) {
            DB::rollback();
            Toastr::error('Add Booking fail :)','Error');
            return redirect()->back();
        }
    }

    // update record
    public function updateRecord(Request $request)
    {
        DB::beginTransaction();
        try {

            if (!empty($request->fileupload)) {
                $photo = $request->fileupload;
                $file_name = rand() . '.' . $photo->getClientOriginalExtension();
                $photo->move(public_path('/assets/upload/'), $file_name);
            } else {
                $file_name = $request->hidden_fileupload;
            }

            $update = [
                'bkg_id' => $request->bkg_id,
                'name'   => $request->name,
                'room_type'  => $request->room_type,
                'total_numbers' => $request->total_numbers,
                'date'   => $request->date,
                'time'   => $request->time,
                'arrival_date'   => $request->arrival_date,
                'depature_date'  => $request->depature_date,
                'email'   => $request->email,
                'ph_number' => $request->phone_number,
                'fileupload'=> $file_name,
                'message'   => $request->message,
            ];

            Booking::where('bkg_id',$request->bkg_id)->update($update);
        
            DB::commit();
            Toastr::success('Updated booking successfully :)','Success');
            return redirect()->back();
        } catch(\Exception $e) {
            DB::rollback();
            Toastr::error('Update booking fail :)','Error');
            return redirect()->back();
        }
    }

    // delete record booking
    public function deleteRecord(Request $request)
    {
        try {

            Booking::destroy($request->id);
            unlink('assets/upload/'.$request->fileupload);
            Toastr::success('Booking deleted successfully :)','Success');
            return redirect()->back();
        
        } catch(\Exception $e) {

            DB::rollback();
            Toastr::error('Booking delete fail :)','Error');
            return redirect()->back();
        }
    }

}
