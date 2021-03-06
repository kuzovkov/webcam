<?php
/**
 * Created by PhpStorm.
 * User: user1
 * Date: 22.05.16
 * Time: 21:18
 */
if(session_status() !== PHP_SESSION_ACTIVE ) session_start();
require_once ('../include/common.inc.php');

$pages = 'include/page/';
$scripts = 'include/script/';
$root_dir = ''; /*если скрипт не в корневом каталоге сервера*/

$routes = array(
    '/' => $pages . 'index.php',
    '/upload' => $scripts . 'upload.php',
    '/images' => $pages . 'images.php',
    /*
    '/login' => $pages . 'login.php',
    '/auth' => $scripts . 'auth.php',
    '/settings' => $pages . 'settings.php',
    '404' => $pages . '404.php',
    '/upload' => $scripts . 'upload.php',
    '/get_email_data' => $scripts . 'get_email_data.php',
    '/del-all' => array($scripts . 'action.php',array('action' => 'del-all')),
    '/mark-as-nosend' => array($scripts . 'action.php',array('action' => 'mark-as-nosend')),
    '/mark-as-send' => array($scripts . 'action.php',array('action' => 'mark-as-send')),
    '/del-sended' => array($scripts . 'action.php',array('action' => 'del-sended')),
    '/set-data' => $pages . '/email_form.php',
    '/sended' => array($pages . 'index.php', array('type'=>'sended')),
    '/nosended' => array($pages . 'index.php', array('type'=>'nosended')),
    '/reset-db' => array($scripts . 'action.php', array('action' => 'reset-db')),
    '/get-list-emails' => $pages . 'list_data.php',
    '/send-email' => array($scripts . 'action.php', array('action' => 'send-email')),
    '/images' => $pages . 'img.php',
    '/img-upload' => $scripts . 'img_upload.php',
    '/del-images' => $scripts . 'img_del.php',
    '/save-data' => $scripts . 'save_data.php',
    '/get_email_report' => $scripts . 'get_email_report.php',
    '/help' => $pages . 'help.php',
    '/change-pass' => $pages . 'change_pass.php',
    */
);



if (isset($_SERVER['REQUEST_URI'])){

    $real_uri = $_SERVER['REQUEST_URI'];

    if (($p = strpos($real_uri, '?')) === false){
        $uri = substr(rtrim($real_uri, '/') , 0);
    }else{
        $uri = substr($real_uri, 0, strpos(rtrim($real_uri, '/'), '?'));
    }

    /*учет случая когда скрипт не в корневом каталоге сервера*/
    if (strlen($root_dir) && strpos($uri, $root_dir) === 0){
        $uri = substr($uri, strlen($root_dir));
    }
    if ($uri === '')
        $uri = '/';

    /**авторизация**/
    //if (!Auth::isAuth() && !in_array($uri, array('/login', '/auth'))) {  header('Location: /login'); exit();}
    /**авторизация**/

    if (isset($routes[$uri])){
        if(is_array($routes[$uri])){
            if (isset($routes[$uri][1]) && is_array($routes[$uri][1]))
                foreach($routes[$uri][1] as $key => $val)
                    $_GET[$key] = $val;
            $require = '../' . $routes[$uri][0];
        }else{
            $require = '../' . $routes[$uri];
        }

    }else{

        $require = '../' . $routes['404'];
    }

    require_once ($require);

}else{
    echo 'Access not allow';
}