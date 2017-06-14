Yii2 Multiparser
================
Минимальные требования: php 7
Универсальна библиотека для парсинга и обработки контента с различных сайтов. Multiparser использует как и обычную загрузку через curl, так и мульти - загрузку multicurl, а также использует популярную библиотеку php simple dom parser  (официальный сайт с документацией -  http://simplehtmldom.sourceforge.net/manual.htm ) для обработки полученных данных.
Использование:

1. Добавляем компонент в конфигурацию консольных комманд фреймворка Yii2:
===================================================================================
    'components' => [
             'multiparser' => [
                'class' => 'multiparser\GetMultiContent',//класс
                'message' => true,//Отображать вывод процесса работы скрипта, по умолчанию false
                'cookies_path' => '@app/runtime/curl/my_cookies_parser.txt', // путь к записи файла cookies парсируемых сайтов, по умолчанию ''
        ],
    ],
===================================================================================

2.Загрузка с высокой скоростью контента используя multicurl. Создаем контроллер консольной комманды фреймворка Yii2:

===========================================================================================================================
<?php
namespace app\commands;
use Yii;


class ParserController extends \yii\console\Controller{
    
    public function actionIndex(){
        //массив ссылок 
        $url = [
            'http://rozetka.com.ua/prestigio_smartbook_141a03_psb141a03bfw_mb_cis/p12467569/',
            'http://rozetka.com.ua/acer_nx_gfteu_004/p13720121/',
            'http://rozetka.com.ua/lenovo_80r20069ua/p5905617/',
            'http://rozetka.com.ua/acer_nx_gceeu_098/p13716558/'
        ]; 
        
        foreach(Yii::$app->multiparser->init($url) as $teg){
            
            foreach($teg->find('a') as $atribut) //обработка библиотекой hp simple dom parser        (официальный сайт с документацией -  http://simplehtmldom.sourceforge.net/manual.htm )
                echo $atribut->href . "\n";
        }
    }
}
==========================================================================================================

3. Загрузка в обычном режиме используя curl и разработку консольных команд фреймворка Yii2: 

==========================================================================================================
<?php
namespace app\commands;
use Yii;

class ParserController extends \yii\console\Controller{
    
    public function actionIndex(){

        $url = 'http://rozetka.com.ua/prestigio_smartbook_141a03_psb141a03bfw_mb_cis/p12467569/';  

        $html = Yii::$app->multiparser->init($url);

            foreach($html->find('a') as $element) 
                echo $element->href . "\n";
    }
}
========================================================================================================