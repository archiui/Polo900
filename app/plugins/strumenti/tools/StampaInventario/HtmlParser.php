<?php

require_once(__DIR__ . "/TextService.php");

class HtmlParser{

    public static function parse($section, $content, $style, $indentazione){
        $dom = new domDocument('1.0', 'utf-8');
        $dom->preserveWhiteSpace = false;
        $dom->loadHTML("<html><body>" . html_entity_decode($content) . "</body></html>");
        $textrun = $section->addTextRun($indentazione);
        self::traverseNode($dom, $style, $textrun);
    }

    private static function traverseNode($domNode, $style, $textrun) {
        foreach ($domNode->childNodes as $node) {
            if($node->hasChildNodes()) {
                self::traverseNode($node, $style, $textrun);
            }else if(in_array($node->nodeName, array("#text","br"))){
                if($node->nodeName == "#text"){
                    if(!empty(trim($node->nodeValue))){
                        $prefix = "";
                        switch($domNode->nodeName){
                            case "strong":
                            case "b":
                                $font = $style["bold"];
                                break;
                            case "em":
                            case "i":
                                $font = $style["italic"];
                                break;
                            case "u":
                                $font = $style["underline"];
                                break;
                            case "li":
                                $font = $style["normal"];
                                $prefix = "- ";
                                break;
                            default:
                                $font = $style["normal"];
                        }
                        TextService::addText($textrun, $prefix . $node->nodeValue, $font);
                        if($domNode->nodeName == "li"){
                            $textrun->addTextBreak();
                        }
                    }
                }else{
                    $textrun->addTextBreak();
                }
            }
        }    
    }
}