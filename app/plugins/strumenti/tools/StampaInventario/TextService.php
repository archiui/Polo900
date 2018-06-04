<?php

class TextService{

     public static function addText($section, $text, $fontStyle = null, $indentazione = null) {
        $text = utf8_decode(htmlspecialchars($text));
        if($fontStyle){
            $section->addText($text, $fontStyle, $indentazione);
        }else{
            $section->addText($text);
        }
    }
}