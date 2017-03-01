<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/1
 * Time: 10:06
 */
include_once __DIR__."/src/Event/Exception.php";
include_once __DIR__."/src/Event/UnknownMethodException.php";
include_once __DIR__."/src/Event/UnknownPropertyException.php";
include_once __DIR__."/src/Event/InvalidCallException.php";
include_once __DIR__."/src/Event/Object.php";
include_once __DIR__."/src/Event/Component.php";
include_once __DIR__."/src/Event/Event.php";

class Test extends \Event\Component{

    public function call(){
        $this->trigger('test');
    }
}

$a = new Test();
$a->on('test',function($event){
    echo 11111;
    var_dump($event);
});
echo 2324;
echo  "<pre>";

$a->call();