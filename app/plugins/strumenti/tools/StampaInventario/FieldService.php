<?php

require_once(__CA_LIB_DIR__.'/core/Parsers/DisplayTemplateParser.php');
require_once(__DIR__ . "/HtmlParser.php");
require_once(__DIR__ . "/TextService.php");

class FieldService{

    private $_document;
    private $_config;
    private $_section;
    private $_styleService;
    private $_typeSummary;
    private $_mysqli;
    private $_autoreService;
    private $_luogoService;
    const OBJECT_TYPES = 22;

    public function __construct($config, $document, $section, $db, $typeSummary, $style, $autoreService, $luogoService){
        $this->_config = $config;
        $this->_document = $document;
        $this->_section = $section;
        $this->_mysqli = $db;
        $this->_typeSummary = $typeSummary;
        $this->_styleService = $style;
        $this->_autoreService = $autoreService;
        $this->_luogoService = $luogoService;
    }

    public function printFields($object, $indent, $index){
        $object_id = $object->get('object_id');
        $type_id = $object->getTypeID();
        $parent_type_id = $this->getParentType($type_id);
        $display_id = $this->_typeSummary[$type_id] ?: $this->_typeSummary[$parent_type_id];
        $lang = $this->_config->get("lang");
        if($display_id){
            $sql = <<<QUERY
            SELECT l.name as label
            FROM ca_objects o
            INNER JOIN ca_locales locale ON locale.language = '$lang'
            LEFT JOIN ca_object_labels l ON o.object_id = l.object_id and l.is_preferred = 1 and l.locale_id = locale.locale_id
            WHERE
            o.object_id = {$object_id}
QUERY;
            $qr_word = $this->_mysqli->query($sql);
            $title = "";
            while($qr_word->nextRow()) {
                $row = $qr_word->getRow();
                $title = $row["label"];
            }
            $sql = <<<QUERY
            SELECT bundle.settings, me.settings as meta_settings, o.object_id, o.idno, mel.name as element_name, me.element_code, mel2.name, me2.element_code as sub_element_code, me.datatype, me2.datatype as fieldType, av.item_id, lil.name_singular, lil.name_plural, av.value_longtext1
            FROM  ca_metadata_elements me
            INNER JOIN ca_locales locale ON locale.language = '$lang'
            INNER JOIN ca_metadata_element_labels mel ON me.element_id = mel.element_id AND mel.locale_id = locale.locale_id
            INNER JOIN ca_attributes a ON me.element_id = a.element_id and a.locale_id = locale.locale_id and a.table_num = 57
            INNER JOIN ca_objects o ON o.object_id = a.row_id
            LEFT JOIN ca_attribute_values av ON av.attribute_id = a.attribute_id 
            LEFT JOIN ca_metadata_elements me2 ON av.element_id = me2.element_id
            LEFT JOIN ca_metadata_element_labels mel2 ON me2.element_id = mel2.element_id AND mel2.locale_id = locale.locale_id
            LEFT JOIN ca_list_items li ON li.item_id = av.item_id
            LEFT JOIN ca_list_item_labels lil ON lil.item_id = li.item_id AND lil.locale_id = locale.locale_id 
            INNER JOIN (SELECT REPLACE(bdp.bundle_name,'ca_objects.','') as bundle_name, bdp.rank, bdp.settings
                FROM ca_bundle_displays bd
                INNER JOIN ca_bundle_display_placements bdp ON bdp.display_id = bd.display_id
                WHERE bd.display_id = {$display_id}
                ORDER BY bdp.rank ASC
            ) bundle ON bundle.bundle_name =  me.element_code
            WHERE
            a.row_id = {$object_id}
            order by bundle.rank asc
QUERY;
            $qr_word = $this->_mysqli->query($sql);
            $intattr = array();
            while($qr_word->nextRow()) {
                $row = $qr_word->getRow();
                if ($row['value_longtext1']) {
                    if(!isset($intattr[$row['element_code']])){
                        $intattr[$row['element_code']] = array(
                            "label" => $row['element_name'],
                            "settings" => $row['settings'],
                            "meta_settings" => $row['meta_settings'],
                            "values" => [],
                            
                        );
                    }
                    $intattr[$row['element_code']]["values"][] = $row['item_id'] ? $row['name_singular'] : $row['value_longtext1'];
                }
            }
            // titolo
            if(in_array($this->_config->get("unita_archivistica"), array($type_id,$parent_type_id))){
                $indent = 0;
            }
            $metadato_code = $this->_config->get('metadato_code');
            $indentazione = 'PARAGRAFO_' . $indent;
            $this->_document->addParagraphStyle($indentazione, array(
                'indent' => $indent * 0.5,
                'spaceBefore' => \PhpOffice\PhpWord\Shared\Converter::pointToTwip(0),
                'spaceAfter' => \PhpOffice\PhpWord\Shared\Converter::pointToTwip(0),
                'lineHeight' => 1
            ));
            $styleTitle = $this->_styleService->getTitleFontStyle($type_id, $parent_type_id);
            $styleData = $this->_styleService->getDataFontStyle($type_id, $parent_type_id);
            $styleIndice = $this->_styleService->getIndiceFontStyle($type_id, $parent_type_id);
            $styleMetadata = $this->_styleService->getMetadatiFontStyle($type_id, $parent_type_id);
            $titolo = (!empty($intattr[$metadato_code["numero_definitivo"]]["values"]) ? implode(", ", $intattr[$metadato_code["numero_definitivo"]]["values"]) . '. ': '') . $title;
            $datazione = !empty($intattr[$metadato_code["data"]]["values"]) ? $intattr[$metadato_code["data"]]["values"][0] : "";
            $table = $this->_section->addTable();
            $table->addRow();
            $cellTitle = $table->addCell(6700);
            HtmlParser::parse($cellTitle, $titolo, $styleTitle, $indentazione); 
            $table->addCell(2000)->addText($datazione, $styleData, array('align' => 'right'));
            $table->addCell(800)->addText("(".$index.")", $styleIndice, array('align' => 'right'));
            // descrizione
            if(!empty($intattr[$metadato_code["scopecontent"]]["values"])){
                HtmlParser::parse($this->_section, $intattr[$metadato_code["scopecontent"]]["values"][0], $styleMetadata, $indentazione);
                $this->_section->addTextBreak();
            }
            unset($intattr[$metadato_code["numero_definitivo"]]);
            unset($intattr[$metadato_code["data"]]);
            unset($intattr[$metadato_code["scopecontent"]]);
            // metadati
            foreach ($intattr as $key => $meta) {
                $this->evaluate($this->_section, $object_id, $meta, $styleMetadata, $indentazione);
            }
            $this->_autoreService->recuperaRelated($display_id, $object, $index, $styleMetadata, $indentazione);
            $this->_luogoService->recuperaRelated($display_id, $object, $index, $styleMetadata, $indentazione);
            $this->_section->addTextBreak();    
        }
        return $indent + 1;
    }

     private function getParentType($type_id) {
        $result = null;
        $sql =<<<QUERY
        SELECT item_id, parent_id
        FROM ca_list_items
        WHERE
        item_id = $type_id
QUERY;
        $query = $this->_mysqli->query($sql);
        if ($query->numRows() > 0) {
            while($query->nextRow()) {
                $row = $query->getRow();
                $result = $row['parent_id'] == self::OBJECT_TYPES ?
                    $row['item_id'] :
                    $this->getParentType($row['parent_id']);
            }
        }
        return $result;
    }

    private function evaluate($section, $object_id, $meta, $font, $indentazione){
        $settings = $this->decodifica($meta["settings"]);
        $meta_settings = $this->decodifica($meta["meta_settings"]);
        if(!empty($settings["format"])){
            $value = DisplayTemplateParser::evaluate($settings["format"], "ca_objects", array($object_id));
            HtmlParser::parse($section, $value, $font, $indentazione); 
        }else if(isset($meta_settings["usewysiwygeditor"]) && $meta_settings["usewysiwygeditor"] == 1){
            $value = implode(", ", $meta["values"]);
            HtmlParser::parse($section, "<em>" . $meta["label"]. ":</em> " . $value, $font, $indentazione);
        }else{
            $value = implode(", ", $meta["values"]);
            $textrun = $section->addTextRun($indentazione);
            TextService::addText($textrun, $meta["label"]. ": ", $font["italic"]);
            TextService::addText($textrun, $value, $font["normal"]);
        }
    }

    private function decodifica($settings){
        $result = unserialize(base64_decode($settings));
        if(is_string($result)){
            $result = unserialize(base64_decode($result));
        }
        return $result;
    }
}