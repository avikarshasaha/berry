<?php
// Чтение списка файлов из RAR
// Версия 0.1
// Автор: Алексей Рембиш a.k.a Ramon
// E-mail: alex@rembish.ru
// Copyright 2009

class RAR {

    // Функция чтения списка файлов из $filename без использования
    // PECL-расширения rar.
    function getFileList($filename) {
        // Функция для получения COUNT байтов из строки (little-endian).
        // Чтобы не засорять глобальное пространство функций - отправляем её
        // вовнуть материнской.
        if (!function_exists("temp_getBytes")) {
            function temp_getBytes($data, $from, $count) {
                $string = substr($data, $from, $count);
                $string = strrev($string);

                return hexdec(bin2hex($string));
            }
        }

        // Попытка открыть файл
        $id = fopen($filename, "rb");
        if (!$id)
            return false;

        // Проверка - является ли файл RAR-архивом
        $markHead = fread($id, 7);
        if (bin2hex($markHead) != "526172211a0700")
            return false;

        // Пытаемся прочесть MAIN_HEAD блок
        $mainHead = fread($id, 7);
        if (ord($mainHead[2]) != 0x73)
            return false;
        $headSize = temp_getBytes($mainHead, 5, 2);

        // Сдвигаемся на позицию первого "значащего" блока в файле
        fseek($id, $headSize - 7, SEEK_CUR);

        $files = array();
        while(!feof($id)) {
            // Читаем загловок блока
            $block = fread($id, 7);
            $headSize = temp_getBytes($block, 5, 2);
            if ($headSize <= 7)
                break;

            // Дочитываем остаток блока исходя из длины заголовка по
            // соответствующему смещению
            $block .= fread($id, $headSize - 7);
            // Если это файловый блок, то начинаем его обрабатывать
            if (ord($block[2]) == 0x74) {
                // Смотрим сколько занимает в архиве запакованный файл и
                // смещаемся к следующей позиции.
                $packSize = temp_getBytes($block, 7, 4);
                fseek($id, $packSize, SEEK_CUR);

                // Читаем атрибуты файла: r - read only, h - hidden,
                // s - system, d - directory, a - archived
                $attr = temp_getBytes($block, 28, 4);
                $attributes = "";
                if ($attr & 0x01)
                    $attributes .= "r";
                if ($attr & 0x02)
                    $attributes .= "h";
                if ($attr & 0x04)
                    $attributes .= "s";
                if ($attr & 0x10 || $attr & 0x4000)
                    $attributes = "d";
                if ($attr & 0x20)
                    $attributes .= "a";

                // Читаем имя файла, размеры до и после запаковки, CRC и аттрибуты
                $files[] = array(
                    "filename" => substr($block, 32, temp_getBytes($block, 26, 2)),
                    "size" => temp_getBytes($block, 11, 4),
                    "compressed_size" => $packSize,
                    "crc" => temp_getBytes($block, 16, 4),
                    "attributes" => $attributes,
                );
            } else {
                // Если данный блок не файловый, то пропускаем с учётом возможного
                // дополнительного смещения ADD_SIZE
                $flags = temp_getBytes($block, 3, 2);
                if ($flags & 0x8000) {
                    $addSize = temp_getBytes($block, 7, 4);
                    fseek($id, $addSize, SEEK_CUR);
                }
            }
        }
        fclose($id);

        // Возвращаем список файлов
        return $files;
    }
}