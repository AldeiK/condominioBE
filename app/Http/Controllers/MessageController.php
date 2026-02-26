<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Events\MessageSent;
use App\Models\Notification;
use App\Events\NotificationSent;

class MessageController extends Controller
{
    public function index($department)
    {
        // Orden viejo -> nuevo (para que el nuevo quede abajo)
        return Message::where('department_id', $department)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function store(Request $request)
    {
        $mensaje = Message::create([
            'user_name' => $request->user_name,
            'department_id' => $request->department_id,
            'message' => $request->message,
        ]);

        // 🔴 Emite por WebSocket a los demás
        broadcast(new MessageSent($mensaje))->toOthers();

        // crear notificación para el resto de la aplicación
        $notification = Notification::create([
            'type' => 'mensaje',
            'message' => "Nuevo mensaje de {$mensaje->user_name}",
            'url' => '/chat',
        ]);
        broadcast(new NotificationSent($notification));

        return response()->json($mensaje);
    }
}