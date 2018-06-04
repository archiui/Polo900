<?php

class RelatedService {

    private $_map_entities = [];
    private $_section;
    private $_styleService;
    private $_label;
    private $_type_id;
    private $_mysql;

    public function __construct($mysql, $section, $style, $rel_id, $label, $label_indice, $table, $key_id, $key_label, $isPrintRelLabel){
        $this->_mysql = $mysql;
        $this->_section = $section;
        $this->_styleService = $style;
        $this->_rel_id = $rel_id;
        $this->_table = $table;
        $this->_key_id = $key_id;
        $this->_key_label = $key_label;
        $this->_label = $label;
        $this->_isPrintRelLabel = $isPrintRelLabel;
        $this->_label_indice = $label_indice;
    }

    public function recuperaRelated($display_id, $object, $index, $font, $indent){
        $sql = <<<QUERY
            SELECT bdp.bundle_name, bdp.settings
            FROM ca_bundle_displays bd
            INNER JOIN ca_bundle_display_placements bdp ON bdp.display_id = bd.display_id
            WHERE bd.display_id = {$display_id} AND bdp.bundle_name = '{$this->_table}'
            ORDER BY bdp.rank ASC
QUERY;
        $result = $this->_mysql->query($sql);
        $settings = [];
        while($result->nextRow()) {
            $row = $result->getRow();
            $settings = $this->decodifica($row["settings"]);
            break;
        }
        //restrict_to_relationship_types relationship_type_id
        $entities = $object->getRelatedItems($this->_rel_id);
        $result = [];
        foreach ($entities as $entity) {
            $id = $entity[$this->_key_id];
            if (!isset($this->_map_entities[$id])) {
                $this->_map_entities[$id] = array(
                    "name" => $entity[$this->_key_label],
                    "index" => array()
                );
            }
            $this->_map_entities[$id]['index'][] = $index;
            if(!empty($settings) && in_array($entity["relationship_type_id"], $settings["restrict_to_relationship_types"])){
                if(!isset($result[$entity["relationship_typename"]])){
                    $result[$entity["relationship_typename"]] = [];
                }
               $result[$entity["relationship_typename"]][] = $entity[$this->_key_label];
            }
        }
        if(!empty($result)){
            foreach($result as $rel_name => $items){
                $textrun = $this->_section->addTextRun($indent);
                TextService::addText($textrun, ($this->_isPrintRelLabel ? ucfirst($rel_name) : $this->_label) . ": ", $font["italic"]);
                TextService::addText($textrun, implode(", ", array_unique($items)), $font["normal"]);
            } 
        }
    }
    public function sommarioRelated($section, $styleService){
        if(!empty($this->_map_entities)){
            $section->addPageBreak();
            $styleTitle = $styleService->getSummaryTitleFontStyle();
            $styleContent = $styleService->getSummaryContentFontStyle();
            TextService::addText($section, "Indice dei " . $this->_label_indice, $styleTitle);
            TextService::addText($section, "I numeri accanto a ciascun lemma costituiscono il rimando al puntatore associato a ciascuna unità e riportato a fianco di ogni descrizione archivistica", $styleTitle);
            $section->addTextBreak();    
            usort($this->_map_entities, function ( $a, $b ) {
               return strtoupper($a['name']) < strtoupper($b['name']) ? -1 : 1;
            });
            foreach ($this->_map_entities as $entity) {
                $textRun = $section->createTextRun();
                TextService::addText($textRun, $entity['name'], $styleContent, 'PARAGRAFO_0');
                TextService::addText($textRun, ", " . implode(", ", array_unique($entity['index'])), $styleContent, 'PARAGRAFO_0');
            }
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