<?php

namespace App\Http\Controllers;

use App\Events\QueueStatusUpdated;
use App\Models\Setting;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function getQueueStatus()
    {
        $isOpen = Setting::getQueueStatus();
        return response()->json(['is_open' => $isOpen]);
    }

    public function toggleQueueStatus(Request $request)
    {
        // Здесь можно добавить проверку прав доступа
        $isOpen = Setting::getQueueStatus();

        // Переключаем состояние очереди
        $newStatus = !$isOpen;

        // Обновляем состояние очереди
        Setting::setQueueStatus($newStatus);

        // Транслируем событие для клиентов
        event(new QueueStatusUpdated($newStatus));

        $message = $newStatus ? 'Очередь открыта.' : 'Очередь закрыта.';

        return response()->json(['message' => $message, 'is_open' => $newStatus]);
    }
}
