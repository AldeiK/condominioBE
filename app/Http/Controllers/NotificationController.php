<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Events\NotificationSent;

class NotificationController extends Controller
{
    public function index()
    {
        // devolver todas las notificaciones más recientes primero
        return Notification::orderBy('created_at', 'desc')->get();
    }

    public function show($id)
    {
        return Notification::findOrFail($id);
    }

    public function store(Request $request)
    {
        // permitimos crear notificaciones desde API para pruebas
        $data = $request->validate([
            'type' => 'required|string',
            'message' => 'required|string',
            'url' => 'nullable|url',
        ]);

        $notification = Notification::create($data);
        broadcast(new NotificationSent($notification));
        return response()->json($notification);
    }

    // helper para generar notificaciones de tipos frecuentes
    public function notify(Request $request, $type)
    {
        $message = $request->input('message') ?? ucfirst($type) . ' disponible';
        $url = $request->input('url');

        $notification = Notification::create([
            'type' => $type,
            'message' => $message,
            'url' => $url,
        ]);

        broadcast(new NotificationSent($notification));
        return response()->json($notification);
    }
}
