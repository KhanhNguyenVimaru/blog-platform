<?php

namespace App\Http\Controllers;

use App\Models\Notify;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;

class NotifyController extends Controller

{
    public function deleteNotify(Request $request)
    {
        try {
            Notify::where('id', $request->id)->delete();
            return ApiResponse::success(null, 'Delete notify successfully!');
        } catch (\Exception $e) {
            return ApiResponse::error('Error: ' . $e->getMessage());
        }
    }

    public function loadUserNotify()
    {
        try{
            $notifies = Notify::where('send_to_id', Auth::id())->orderBy('created_at', 'desc')->limit(6)->get();
            return ApiResponse::success($notifies, 'Load notify successfully!');
        } catch(\Exception $e){
            return ApiResponse::error('Error: ' . $e->getMessage());
        }
    }
}
