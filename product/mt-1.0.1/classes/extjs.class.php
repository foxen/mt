<?php
/**
 * extjs.class.php, mt-1.0.1
 * 
 * Класс подготавливает к отображению страницу
 * содержащюю клиентский js(extJS 3)
 * 
 */
class Extjs{
    
    function show_with_content($module, $userId, $instance, $root, $withApp = true){
        $content = '';
        
        if ($withApp){
            $file = $instance.'/views/classes.js';
            if (is_file($file)){
                $content = file_get_contents($file);
            }
        }
        
        $file = $instance.'/modules/'.$module.'/views/classes.js';
        if (is_file($file)){
            $content = $content.file_get_contents($file);
        }

        $file = $instance.'/modules/'.$module.'/views/module.js';
        if (is_file($file)){
            $content = $content.file_get_contents($file);
        }

        $this->show_page($root, $content, $module, $module);
    }
    
    
    function show_page($root, $content, $pageTitle = 'Application', $module='none'){
        $css = '<link rel="stylesheet" type="text/css" href="'.$root.'/resources/css/application.css"/>';
        if ($module!='none'){
            $css = $css.'<link rel="stylesheet" type="text/css" href="'.$root.'/modules/'.$module.'/resources/css/module.css"/>';
        }
        $pageContent = <<<EOD
<html>
    <head>
        <title>$pageTitle</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <meta http-equiv="Content-language" value="ru" />
        <link rel="stylesheet" type="text/css" href="$root/libraries/ext3/resources/css/ext-all.css" />
        <script src="$root/libraries/ext3/adapter/ext/ext-base.js"></script>
        <script src="$root/libraries/ext3/ext-all-debug.js"></script>
        <script src="$root/libraries/ext3/src/locale/ext-lang-ru.js"></script>
        $css
        <script type="text/javascript">
            Ext.BLANK_IMAGE_URL = "$root/libraries/ext3/resources/images/default/s.gif";
            Ext.ns("Application");
            Ext.onReady(
                function() {
                    Ext.QuickTips.init();
                    $content
                }
            );
        </script>
    </head>
    <body>
    </body>
</html>
EOD;
    echo $pageContent;
    
    }

}


