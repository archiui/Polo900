<?php

require_once(__DIR__ . '/../../ConfigLoader.php');

class StyleService {
    const PREFIX_FONT = "TITLE_";
    const PREFIX_FONT_META = "METADATA_";
    const PREFIX_FONT_DATA = "DATA_";
    const PREFIX_FONT_DESCRIPTION = "DESCRIPTION_";
    const PREFIX_INDEX = "INDEX_STYLE";
    const PREFIX_SUMMARY = "SUMMARY_";

    private $_document;
    private $_mysqli;
    private $_type_ids;

    public function __construct($db, $document) {
        $this->config = json_decode(file_get_contents(ConfigLoader::load('strumenti_styles.json', __DIR__ . '/../../')), TRUE);
        $this->_mysqli = $db;
        $this->_document = $document;
        $this->setStyles();
    }
    private function setStyles() {
        foreach($this->config as $type_id => $style){
                $type_name = $this->getTypeName($type_id);
                $this->_type_ids[$type_id] = $type_name;
                $this->_document->addFontStyle(self::PREFIX_FONT_DATA. $type_name, $style["data"]);
                $this->_document->addFontStyle(self::PREFIX_INDEX. $type_name, $style["index"]);
                $this->_document->addFontStyle(self::PREFIX_FONT. $type_name, $style["title"]);
                $this->_document->addFontStyle(self::PREFIX_FONT . $type_name . "_bold", array_merge($style["title"], array(
                    "bold" => true
                )));
                $this->_document->addFontStyle(self::PREFIX_FONT . $type_name . "_italic", array_merge($style["title"], array(
                    "italic" => true
                )));
                $this->_document->addFontStyle(self::PREFIX_FONT . $type_name . "_underline", array_merge($style["title"], array(
                    "underline" => 'single'
                )));
                $this->_document->addFontStyle(self::PREFIX_FONT_META. $type_name, $style["metadati"]);
                $this->_document->addFontStyle(self::PREFIX_FONT_META . $type_name . "_bold", array_merge($style["metadati"], array(
                    "bold" => true
                )));
                $this->_document->addFontStyle(self::PREFIX_FONT_META . $type_name . "_italic", array_merge($style["metadati"], array(
                    "italic" => true
                )));
                $this->_document->addFontStyle(self::PREFIX_FONT_META . $type_name . "_underline", array_merge($style["metadati"], array(
                    "underline" => 'single'
                )));
        }
        $this->_document->addFontStyle(self::PREFIX_SUMMARY . "TITLE", array(
            "bold" => true,
            "size" => 16,
            "name" => "Palatino"
        ));
        $this->_document->addFontStyle(self::PREFIX_SUMMARY . "CONTENT", array(
            "size" => 12,
            "name" => "Palatino"
        ));
    }
    private function getTypeName($type_id){
        $sql = <<<QUERY
        SELECT idno FROM ca_list_items WHERE item_id = {$type_id}
QUERY;
        $result = $this->_mysqli->query($sql);
        while($result->nextRow()) {
            $row = $result->getRow();
            return $row["idno"];
        }
    }
    public function getDataFontStyle($type_id, $parent_type_id){
        return self::PREFIX_FONT_DATA . $this->getStyle($type_id, $parent_type_id);
    }
    public function getIndiceFontStyle($type_id, $parent_type_id){
        return self::PREFIX_INDEX . $this->getStyle($type_id, $parent_type_id);
    }
    public function getSummaryTitleFontStyle(){
        return self::PREFIX_SUMMARY . "TITLE";
    }
    public function getSummaryContentFontStyle(){
        return self::PREFIX_SUMMARY . "CONTENT";
    }
    public function getTitleFontStyle($type_id, $parent_type_id){
        $styleName = self::PREFIX_FONT . $this->getStyle($type_id, $parent_type_id);
        return [
           "normal" => $styleName,
           "bold" => $styleName . "_bold",
           "italic" => $styleName . "_italic",
           "underline" => $styleName . "_underline"
        ];
    }
    public function getMetadatiFontStyle($type_id, $parent_type_id){
        $styleName = self::PREFIX_FONT_META . $this->getStyle($type_id, $parent_type_id);
        return [
           "normal" => $styleName,
           "bold" => $styleName . "_bold",
           "italic" => $styleName . "_italic",
           "underline" => $styleName . "_underline"
        ];
    }

    private function getStyle($type_id, $parent_type_id){
        $id = array_key_exists($type_id, $this->_type_ids) ? $type_id : $parent_type_id;
        return $this->_type_ids[$id]; 
    }
}