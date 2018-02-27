<?php

    var_dump($_REQUEST);

    file_put_content('/tmp/file.json', json_encode($_REQUEST));
