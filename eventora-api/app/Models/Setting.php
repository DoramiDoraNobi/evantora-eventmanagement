<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type'];

    /**
     * Keys that should be encrypted at rest.
     */
    public static $encryptedKeys = [
        'midtrans_server_key',
        'midtrans_client_key',
        'paypal_client_id',
        'paypal_secret'
    ];

    /**
     * Get a setting value by key.
     */
    public static function getVal(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        if (!$setting) {
            return $default;
        }

        if ($setting->type === 'integer') {
            return (int) $setting->value;
        } elseif ($setting->type === 'boolean') {
            return filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
        } elseif ($setting->type === 'json') {
            return json_decode($setting->value, true);
        }

        if (in_array($key, self::$encryptedKeys)) {
            try {
                return \Illuminate\Support\Facades\Crypt::decryptString($setting->value);
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                // If it fails to decrypt, it might have been inserted via seeder/tinker as plain text.
                return $setting->value;
            }
        }

        return $setting->value;
    }

    /**
     * Set a setting value by key.
     */
    public static function setVal(string $key, $value, string $type = 'string')
    {
        if (is_array($value)) {
            $value = json_encode($value);
            $type = 'json';
        } elseif (is_bool($value)) {
            $value = $value ? '1' : '0';
            $type = 'boolean';
        } elseif (in_array($key, self::$encryptedKeys) && !empty($value)) {
            $value = \Illuminate\Support\Facades\Crypt::encryptString($value);
        }

        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type]
        );
    }
}
