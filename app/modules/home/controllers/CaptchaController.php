<?php

namespace Pha\Modules\Home\Controllers;

use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use Pha\Core\BaseController;

class CaptchaController extends BaseController
{

    private function sessInit()
    {
        $this->initSession();
        $this->_session = parent::getDI()->getShared('session');
    }

    public function indexAction()
    {
        $this->sessInit();
        $pb = new PhraseBuilder(5, 'abcdefghijklmnpqrstuvwxyz1234567890');
        $cb = new CaptchaBuilder(null, $pb);
        $cb->build();
        $code = $cb->getPhrase();
        $this->_session->set('pub_verify_code', $code);
        header('Content-type: image/jpeg');
        $cb->output();
        exit();
    }

}
