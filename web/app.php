<?php


$applicationFolder = \dirname( __DIR__ );


include $applicationFolder . '/vendor/autoload.php';


$app = new \Messier\GCB\Packages\Application( $applicationFolder );


$app->run(
   // Trigger reload if requested
   (
      isset( $_SERVER[ 'HTTP_X_GITLAB_TOKEN' ] )
      &&
      $_SERVER[ 'HTTP_X_GITLAB_TOKEN' ] === \Messier\GCB\Packages\Application::EXTERNAL_APP_TOKEN
   )
   ||
   (
      isset( $_GET[ 'gitlab_token' ] )
      &&
      $_GET[ 'gitlab_token' ] === \Messier\GCB\Packages\Application::EXTERNAL_APP_TOKEN
   )
);

