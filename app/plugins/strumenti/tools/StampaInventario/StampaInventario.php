<?php
require_once(__CA_MODELS_DIR__ . "/ca_bundle_displays.php");
require_once(__CA_MODELS_DIR__ . "/ca_objects.php");
require_once(__CA_LIB_DIR__ . "/core/Db.php");
require_once(__DIR__ . "/TextService.php");
require_once(__DIR__ . "/StyleService.php");
require_once(__DIR__ . "/FieldService.php");
require_once(__DIR__ . "/RelatedService.php");
require_once(__DIR__ . "/Stack.php");


class StampaInventario {
    private $_config;
    private $_request;
    private $_stack;
    private $_mysqli;
    private $_document;
    private $_section;
    private $_map_entities;
    private $_styleService;


    public function __construct($po_request, $config) {
        $this->_request = $po_request;
        $this->_config = $config;
        $this->_stack = new Stack();
        $this->_mysqli = new Db();
    }

    private function _frontpage($start_id) {
        $user = $this->_request->getUser();
        $groupList= array_map(function($item){
            return $item["name"];
        }, $user->getUserGroups());
        $groupS = implode(",", $groupList);
        $object = new ca_objects($start_id);
        $id = $object->get('object_id');
        $preferred_label = $object->getPreferredLabels();
        $preferred_label = reset($preferred_label[$id]);
        $preferred_label = $preferred_label[0]['name'];

        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(__CA_APP_DIR__ . '/plugins/strumenti/tools/StampaInventario/TemplateInventario.docx');
        $templateProcessor->setValue('nomesito', htmlspecialchars(__CA_APP_DISPLAY_NAME__));
        $templateProcessor->setValue('username', htmlspecialchars($user->getName()));
        $templateProcessor->setValue('groupname', htmlspecialchars($groupS));
        $templateProcessor->setValue('nomefondo', htmlspecialchars($preferred_label));
	    $templateProcessor->setValue('dataodierna', date('d/m/Y'));
        $templateProcessor->saveAs(__CA_BASE_DIR__ . '/media/' . $preferred_label . 'Inventario.docx');
        return __CA_BASE_DIR__ . '/media/' . $preferred_label . 'Inventario.docx';
    }
    private function _newDocument($start_id) {
        $file = $this->_frontpage($start_id);
        $this->_document = \PhpOffice\PhpWord\IOFactory::load($file);
        $this->_styleService = new StyleService($this->_mysqli, $this->_document);
        $this->_document->setDefaultFontName($this->_config->get('fontFamily'));
        $this->_document->setDefaultFontSize($this->_config->get('fontSize'));
        $this->_document->setDefaultParagraphStyle(array(
            'spaceBefore' => \PhpOffice\PhpWord\Shared\Converter::pointToTwip(0),
            'spaceAfter' => \PhpOffice\PhpWord\Shared\Converter::pointToTwip(0),
            'lineHeight' => 1
        ));
        $this->_section = $this->_document->addSection(array('pageNumberingStart' => 1));
        $footer = $this->_section->addFooter();
        $footer->addPreserveText("{PAGE}");
        return $file;
    }

    public function run($start_id, $typeSummary = array()) {
        set_time_limit(0);
        ini_set("memory_limit", "2048M");
        // Generazione del frontpage e inizializzazione del documeno word
        $file = $this->_newDocument($start_id);
        // Aggiungo il primo elemento dallo stack;
        $this->_stack->push([
           "id"=> $start_id,
           "indent"=>0
        ]);
        // Incomincio ad analizzare gli elementi
        $index = 1;
        $autoreService = new RelatedService($this->_mysqli, $this->_section, $this->_styleService, 20, "Entità collegate", "nomi", "ca_entities", "entity_id", "displayname", true);
        $luogoService = new RelatedService($this->_mysqli, $this->_section, $this->_styleService, 72, "Luoghi collegati", "luoghi", "ca_places", "place_id", "name", false);
        $fieldService = new FieldService($this->_config, $this->_document, $this->_section, $this->_mysqli, $typeSummary, $this->_styleService, $autoreService, $luogoService);
        while (!$this->_stack->isEmpty()) {
            $item = $this->_stack->pop();
            $object_id = $item["id"];
            $indent = $item["indent"];
            $object = new ca_objects($object_id);
            
            $nextIndent = $fieldService->printFields($object, $indent, $index++);

            // cerco figli
            $children = $this->_mysqli->query("SELECT object_id FROM ca_objects WHERE parent_id = " . $object_id . " AND deleted = 0 ORDER BY ordine DESC, object_id DESC");
            if ($children->numRows() > 0) {
                while($children->nextRow()) {
                    $row = $children->getRow();
                    $this->_stack->push([
                        "id" => $row['object_id'],
                        "indent" => $nextIndent
                    ]);
                }
            }
        }
        // Genera la struttura per le entità
        $autoreService->sommarioRelated($this->_section, $this->_styleService);
        $luogoService->sommarioRelated($this->_section, $this->_styleService);

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($this->_document, 'Word2007');
        $objWriter->save($file);
        return $file;
    }
}