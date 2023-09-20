<?php

namespace Pha\Modules\Home\Controllers;

use Pha\Core\BaseController;

class ControllerBase extends BaseController
{

    public function initialize()
    {
        parent::initialize();
        $this->initSession();
        $this->_session = parent::getDI()->getShared('session');
    }

}
