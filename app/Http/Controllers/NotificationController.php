<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function markAsRead(string $id)
    {
        $notification = auth()->user()->unreadNotifications()->findOrFail($id);
        $notification->markAsRead();
        $url = $notification->data['url'] ?? route('alerts.index');

        return redirect($url);
    }

    public function markAllAsRead(Request $request)
    {
        foreach (auth()->user()->unreadNotifications as $notification) {
            $notification->markAsRead();
        }

        return $request->wantsJson()
            ? response()->json(['success' => true])
            : redirect()->back();
    }
}
