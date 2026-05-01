<?php

class Buku
{
    public static function allFromJson(string $file): array
    {
        if (!file_exists($file)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($file), true);
        return is_array($data) ? $data : [];
    }
}
