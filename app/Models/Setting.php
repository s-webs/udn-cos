<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function getQueueStatus()
    {
        $setting = self::where('key', 'queue_status')->first();
        if ($setting) {
            return $setting->value === 'open';
        }
        // По умолчанию очередь открыта
        return true;
    }

    public static function setQueueStatus($isOpen)
    {
        return self::updateOrCreate(
            ['key' => 'queue_status'],
            ['value' => $isOpen ? 'open' : 'closed']
        );
    }
}
