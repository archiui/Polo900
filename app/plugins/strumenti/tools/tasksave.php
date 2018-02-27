<?php
define('__CA_BASE_DIR__', $_SERVER['PWD']);
require(__CA_BASE_DIR__.'/setup.php');

require_once(__CA_LIB_DIR__ . "/ca/ApplicationPluginManager.php");

$plugin_path = __CA_APP_DIR__ . '/plugins/strumenti/';

// Recupero la transizione.
$transaction_file = __CA_CACHE_FILEPATH__ . '/transaction.json';
if (file_exists($transaction_file)) {
	$transiction = json_decode(file_get_contents($transaction_file), true);
}

if ($transiction == null) {
	exit();
}

$opo_app_plugin_manager = new ApplicationPluginManager();

//Recupero il file di configurazione del plugin
if (file_exists(__CA_CONF_DIR__.'/local/strumenti_conf')) {
	$opo_config = Configuration::load( __CA_CONF_DIR__ . '/local/strumenti_conf/strumenti.conf' );
} else if (file_exists(__CA_CONF_DIR__ . '/strumenti_conf')) {
	$opo_config = Configuration::load( __CA_CONF_DIR__ . '/strumenti_conf/strumenti.conf' );
} else {
	$opo_config = Configuration::load( $plugin_path . 'conf/strumenti.conf' );
}


runTransaction($transiction, $opo_config, $opo_app_plugin_manager, false, true, true);

// Resetto la transazione
$transiction = null;
file_put_contents($transaction_file, json_encode($transiction));
file_put_contents(__CA_CACHE_FILEPATH__."/finesecuzione", "w");

function runTransaction($transiction, $opo_config, $opo_app_plugin_manager, $insert=false, $update=false, $delete=false) {
    global $AUTH_CURRENT_USER_ID;
	require_once(__CA_MODELS_DIR__ . "/ca_objects.php");
	$map = $opo_config->get('mappatura_metadati');
	// Aggiorno per l'inserimento
	if ($insert) {
		// $inserimenti = $this->transiction['INSERT'];
	}

	// Aggiorno oggetti
	if ($update) {
		$aggiornamenti = $transiction['UPDATE'];
		foreach ($aggiornamenti as $id => $info) {
            $AUTH_CURRENT_USER_ID = 1;
			$object = new ca_objects($id);
            $object->load($id);
			// Aggiorno dati intrinseci
			if (isset($info['intr'])) {
				$object->set($info['intr']);
			}

			// Inserisco nuovi attributi
			if (isset($info['attr'])) {
				// Consistenza
				if (isset($info['attr']['consistenza'])) {
					$object->replaceAttribute(array($map['consistenza'] => $info['attr']['consistenza']), $map['consistenza']);
				}
				if (isset($info['attr']['numero_def'])) {
					$object->replaceAttribute(array($map['numero_dev'] => $info['attr']['numero_def']), $map['num_def']);
				}
				if (isset($info['attr']['prefix'])) {
					$object->replaceAttribute(array($map['prefix'] => $info['attr']['prefix']), $map['num_def']);
				}
				if (isset($info['attr']['romano'])) {
					$object->replaceAttribute(array($map['romano'] => $info['attr']['romano']), $map['num_def']);
				}
				if (isset($info['attr']['data']))  {
					$object->addAttribute(array($map['datadisplay'] => $info['attr']['data']['date_display'], $map['datarange'] => $info['attr']['data']['data_range'], $map['notedata'] => "Datazione calcolata"), $map['data']);
				}
			}
			$object->setMode(ACCESS_WRITE);
			$object->update(array('force' => true));
			$opo_app_plugin_manager->hookSaveItem(array('id' => $id, 'table_num' => 57, 'table_name' => "ca_objects", 'instance' => $object, 'is_insert' => false));
		}
	}

	// Elimino oggetti
	if ($delete) {
		$cancellati = $transiction['DELETE'];
		foreach ($cancellati as $id) {
			$object = new ca_objects($id);
			$object->setMode(ACCESS_WRITE);
			$object->delete();

			$opo_app_plugin_manager->hookDeleteItem(array('id' => $id, 'table_num' => 57, 'table_name' => "ca_objects", 'instance' => $object));
		}
	}
}
