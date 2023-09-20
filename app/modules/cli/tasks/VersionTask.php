<?php

namespace Pha\Modules\Cli\Tasks;

class VersionTask extends \Phalcon\Cli\Task
{

    //命令行运行方式：php /www/wwwroot/my_obj/app/bootstrap_cli.php Version main
    //注意：VersionTask只要传入Version即可，不要打完
    //如果只执行：php /www/wwwroot/my_obj/app/bootstrap_cli.php，则默认执行MainTask中的main方法
    public function mainAction()
    {
        $config = $this->getDI()->get('config');
        echo $config['version'];
    }

}
