<?php


/** /if ( ! isset( $_SERVER[ 'HTTP_X_GITLAB_TOKEN' ] )
     ||
     $_SERVER[ 'HTTP_X_GITLAB_TOKEN' ] !== \Messier\GCB\Packages\Application::EXTERNAL_APP_TOKEN )
{
   echo 'ERROR: Invalid request';
}/**/


$applicationFolder = \dirname( __DIR__ );


include $applicationFolder . '/vendor/autoload.php';


$app = new \Messier\GCB\Packages\Application( $applicationFolder );


\file_put_contents( $app->getTriggerReloadFilePath(), '1' );


echo 'OK';

