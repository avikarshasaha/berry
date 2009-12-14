<?php
// ������ ������ ������ �� RAR
// ������ 0.1
// �����: ������� ������ a.k.a Ramon
// E-mail: alex@rembish.ru
// Copyright 2009

class RAR {

    // ������� ������ ������ ������ �� $filename ��� �������������
    // PECL-���������� rar.
    function getFileList($filename) {
        // ������� ��� ��������� COUNT ������ �� ������ (little-endian).
        // ����� �� �������� ���������� ������������ ������� - ���������� �
        // ������� �����������.
        if (!function_exists("temp_getBytes")) {
            function temp_getBytes($data, $from, $count) {
                $string = substr($data, $from, $count);
                $string = strrev($string);

                return hexdec(bin2hex($string));
            }
        }

        // ������� ������� ����
        $id = fopen($filename, "rb");
        if (!$id)
            return false;

        // �������� - �������� �� ���� RAR-�������
        $markHead = fread($id, 7);
        if (bin2hex($markHead) != "526172211a0700")
            return false;

        // �������� �������� MAIN_HEAD ����
        $mainHead = fread($id, 7);
        if (ord($mainHead[2]) != 0x73)
            return false;
        $headSize = temp_getBytes($mainHead, 5, 2);

        // ���������� �� ������� ������� "���������" ����� � �����
        fseek($id, $headSize - 7, SEEK_CUR);

        $files = array();
        while(!feof($id)) {
            // ������ �������� �����
            $block = fread($id, 7);
            $headSize = temp_getBytes($block, 5, 2);
            if ($headSize <= 7)
                break;

            // ���������� ������� ����� ������ �� ����� ��������� ��
            // ���������������� ��������
            $block .= fread($id, $headSize - 7);
            // ���� ��� �������� ����, �� �������� ��� ������������
            if (ord($block[2]) == 0x74) {
                // ������� ������� �������� � ������ ������������ ���� �
                // ��������� � ��������� �������.
                $packSize = temp_getBytes($block, 7, 4);
                fseek($id, $packSize, SEEK_CUR);

                // ������ �������� �����: r - read only, h - hidden,
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

                // ������ ��� �����, ������� �� � ����� ���������, CRC � ���������
                $files[] = array(
                    "filename" => substr($block, 32, temp_getBytes($block, 26, 2)),
                    "size" => temp_getBytes($block, 11, 4),
                    "compressed_size" => $packSize,
                    "crc" => temp_getBytes($block, 16, 4),
                    "attributes" => $attributes,
                );
            } else {
                // ���� ������ ���� �� ��������, �� ���������� � ������ ����������
                // ��������������� �������� ADD_SIZE
                $flags = temp_getBytes($block, 3, 2);
                if ($flags & 0x8000) {
                    $addSize = temp_getBytes($block, 7, 4);
                    fseek($id, $addSize, SEEK_CUR);
                }
            }
        }
        fclose($id);

        // ���������� ������ ������
        return $files;
    }
}