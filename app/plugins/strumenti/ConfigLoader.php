<?php

class ConfigLoader {

    public static function load($file, $path_base){
        if (file_exists(__CA_CONF_DIR__.'/local/strumenti_conf')) {
		    return  __CA_CONF_DIR__ . '/local/strumenti_conf/' . $file;
	    } else if (file_exists(__CA_CONF_DIR__ . '/strumenti_conf')) {
            return  __CA_CONF_DIR__ . '/strumenti_conf/' . $file;
	    } else {
            return  $path_base . 'conf/' . $file;
	    }
    }
}