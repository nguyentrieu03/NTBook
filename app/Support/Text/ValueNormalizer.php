<?php
namespace App\Support\Text;
use Illuminate\Support\Str;

final class ValueNormalizer
{
    public static function convertToCode(string $value): string 
    {
        // Bỏ dấu tiếng Việt + lowercase 
        $result = Str::lower(Str::ascii(trim($value)));

        // Thay 1 hoặc nhiều khoảng trắng bằng dấu gạch dưới
        $result = preg_replace('/\s+/', '_', $result);

        // Chỉ giữ lại a-z, 0-9, và dấu gạch dưới
        $result = preg_replace('/[^a-z0-9_]/', '', $result);

        return $result;
    }
}