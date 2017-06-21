Yii2 Multiparser
================
Минимальные требования: php 7

Универсальна библиотека для парсинга и обработки контента с различных сайтов. Multiparser использует как  обычную загрузку через curl, так и мульти - загрузку multicurl, а также популярную библиотеку php simple dom parser  (официальный сайт с документацией -  http://simplehtmldom.sourceforge.net/manual.htm ) для обработки полученных данных.

Использование:
--------------
### 1. Добавляем компонент в конфигурацию  фреймворка Yii2 (пример с использованием консольных команд Yii2):
~~~
    'components' => [
             'multiparser' => [
                'class' => 'multiparser\GetMultiContent',//класс
                'message' => true,//Отображать вывод процесса работы скрипта, по умолчанию false
                'cookies_path' => '@app/runtime/curl/my_cookies_parser.txt', // путь к записи файла cookies парсируемых сайтов, по умолчанию ''
        ],
    ],
~~~

### 2.Загрузка с высокой скоростью контента используя multicurl. Создаем контроллер консольной комманды фреймворка Yii2:

```
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
        /**
         *  GetMultiContent->init($url) инициализирует парсер, принимает два параметра:
         *  $url type string - ссылки парсируемых сайтов, если передать массив отработает мультизагрузчик
         *  если строку отработает обычный загрузчик
         *  $no_parser type boolean - true, не создавать объект Simple_html_dom контент для обработки контента
         *  и вернет строку, false, создасть объект автоматически (по умолчанию false)
         */
        foreach(Yii::$app->multiparser->init($url) as $teg){
            
            foreach($teg->find('a') as $atribut) //обработка библиотекой php simple dom parser        (официальный сайт с документацией -  http://simplehtmldom.sourceforge.net/manual.htm )
                echo $atribut->href . "\n";
                echo Yii::$app->multiparser->info; //URL возвращаемого дескриптора
        }
    }
}
```

### 3. Загрузка в обычном режиме: 

```
<?php
namespace app\commands;
use Yii;

class ParserController extends \yii\console\Controller{
    
    public function actionIndex(){

        $url = 'http://rozetka.com.ua/prestigio_smartbook_141a03_psb141a03bfw_mb_cis/p12467569/';  
        /**
         *  GetMultiContent->init($url) инициализирует парсер, принимает два параметра:
         *  $url type string - ссылки парсируемых сайтов, если передать массив отработает мультизагрузчик
         *  если строку отработает обычный загрузчик
         *  $no_parser type boolean - true, не создавать объект Simple_html_dom контент для обработки контента
         *  и вернет строку, false, создасть объект автоматически (по умолчанию false)
         */
        $html = Yii::$app->multiparser->init($url);

            foreach($html->find('a') as $element) 
                echo $element->href . "\n";
    }
}
```