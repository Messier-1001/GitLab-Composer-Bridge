<?php
/**
 * @author     Messier 1001 <messier.1001+code@gmail.com>
 * @copyright  ©2017, Messier 1001
 * @package    Messier\GCB\Packages
 * @since      2017-03-29
 * @version    0.1.0
 */


declare( strict_types = 1 );


namespace Messier\GCB\Packages;


use Messier\GCB\GitLab\Api;
use Messier\GCB\GitLab\API\Project;
use Messier\GCB\GitLab\API\ProjectCollection;


class Application
{


   // <editor-fold desc="// – – –   P R I V A T E   F I E L D S   – – – – – – – – – – – – – – – – – – – – – – – –">

   /**
    * The Application root folder
    *
    * @type string
    */
   private $_rootFolder;
   private $_packagesJSONFile;
   private $_triggerReloadFile;
   private $_configFile;
   /** @type array */
   private $_config;
   /** @type \Messier\GCB\GitLab\API\ProjectCollection */
   private $_projects;
   /** @type \Messier\GCB\GitLab\Api */
   private $_api;

   // </editor-fold>


   public const EXTERNAL_APP_TOKEN = 'GITLAB_COMPOSER_BRIDGE_RELOAD';


   // <editor-fold desc="// – – –   P U B L I C   C O N S T R U C T O R   – – – – – – – – – – – – – – – – – – – –">

   /**
    * Application constructor.
    *
    * @param string $applicationFolder
    * @throws \Messier\GCB\Packages\ApplicationException
    */
   public function __construct( string $applicationFolder )
   {

      if ( ! \is_dir( $applicationFolder ) )
      {
         throw new ApplicationException( 'Bad application folder: "' . $applicationFolder . '" not exists!' );
      }

      $this->_rootFolder = \rtrim( $applicationFolder, '/\\' );

      $cacheFolder = $this->_rootFolder . '/cache';

      if ( ! \is_dir( $cacheFolder ) )
      {
         throw new ApplicationException( 'The application cache folder "' . $cacheFolder . '" not exists!' );
      }

      if ( ! \is_writable( $cacheFolder ) )
      {
         throw new ApplicationException( 'The application cache folder "' . $cacheFolder . '" is not writable!' );
      }

      $this->_packagesJSONFile  = $cacheFolder . '/packages.json';
      $this->_configFile        = $this->_rootFolder . '/config/gitlab.json';
      $this->_triggerReloadFile = $cacheFolder . '/.trigger-reload';

      $this->loadConfig();

      $this->_api = ( new Api() )
         ->setApiKey( $this->_config[ 'apiKey' ] )
         ->setApiURL( $this->_config[ 'apiUrl' ] )
         ->setRepoUrlType( $this->_config[ 'method' ] );

   }

   // </editor-fold>


   // <editor-fold desc="// – – –   P R O T E C T E D   M E T H O D S   – – – – – – – – – – – – – – – – – – – – –">

   protected function loadConfig()
   {

      if ( ! \file_exists( $this->_configFile ) )
      {
         throw new ApplicationException(
            'Bad application folder: The config file "' . $this->_configFile . '" not exists!'
         );
      }

      $tmpConfig = @\file_get_contents( $this->_configFile );

      if ( empty( $tmpConfig ) )
      {
         throw new ApplicationException(
            'Bad application config: "' . $this->_configFile . '" is not readable or empty!'
         );
      }

      $configArray = \json_decode( $tmpConfig, true );

      if ( ! \is_array( $configArray ) || JSON_ERROR_NONE !== json_last_error() )
      {
         throw new ApplicationException(
            'Bad application config: "' . $this->_configFile . '" contains a invalid/unknown format!'
         );
      }

      if ( ! isset( $configArray[ 'apiUrl' ],
                    $configArray[ 'apiKey' ],
                    $configArray[ 'method' ] ) )
      {
         throw new ApplicationException(
            'Bad application config: "' . $this->_configFile . '" contains not all required config-data!'
         );
      }

      $this->_config = $configArray;

   }
   protected function loadProjectData( Project $project )
   {

      $file  = $this->_rootFolder . '/cache/' . $project->pathWithNamespace . '.json';
      $mTime = $project->lastActivityAt->getTimestamp();
      $dir   = \dirname( $file );

      // Create the Cache folder if it not exists, or end with a error if it fails
      if ( ! \is_dir( $dir ) && ! @\mkdir( $dir, 0777, true ) && ! \is_dir( $dir ) )
      {
         throw new ApplicationException(
            'Can not load project data because the target folder "' . $dir . '" not exists and creation fails!'
         );
      }
      \chmod( $dir, 0777 );

      if ( \file_exists( $file ) && \filemtime( $file ) >= $mTime )
      {

         if ( 0 < \filesize( $file ) )
         {
            return \json_decode( \file_get_contents( $file ), true );
         }

         return false;

      }

      $data = $this->_api->getRefs( $project );

      if ( null !== $data )
      {
         \file_put_contents( $file, \json_encode( $data ) );
         \chmod( $file, 0777 );
         \touch( $file, $mTime );

         return $data;

      }

      $fp = \fopen( $file, 'wb' );
      \fclose( $fp );
      \chmod( $file, 0777 );
      \touch( $file, $mTime );

      return false;

   }

   // </editor-fold>


   // <editor-fold desc="// – – –   P U B L I C   M E T H O D S   – – – – – – – – – – – – – – – – – – – – – – – –">

   /**
    * Runs the application
    *
    * @param bool $reloadAlways Trigger also a reload if no changes was found and the cache is valid?
    * @throws \Messier\GCB\Packages\ApplicationException
    */
   public function run( bool $reloadAlways = false )
   {

      $this->_projects = $this->_api->getProjects( $this->_api->getMyUser() );

      $triggerReload = \file_exists( $this->_triggerReloadFile );

      if ( $triggerReload )
      {
         @unlink( $this->_triggerReloadFile );
      }

      if ( $reloadAlways ||                                     // Reload if requested
           $triggerReload ||                                    // …if triggered from external
           ! \file_exists( $this->_packagesJSONFile ) ||        // …if no cache file exists
           null === $this->_projects->getLastActivityDate() ||  // …if no last activity date is defined
           \filemtime( $this->_packagesJSONFile ) < $this->_projects->getLastActivityDate()->getTimestamp() ) // if old
      {

         // There is no existing packages.json file or the last activity is newer then current cache, or not defined
         // => Get all the required data

         $packages = [];

         // Loop all existing projects
         foreach ( $this->_projects as $project )
         {

            if ( $package = $this->loadProjectData( $project ) )
            {
               // Loading of project data (it means the composer.json + a bit more) from GitLab API was successful
               // Remember the project data
               $packages[ $project->pathWithNamespace ] = $package;
            }

         }

         // Convert $packages to JSON…
         $data = \json_encode(
            [
               'packages' => \array_filter( $packages )
            ]
         );

         // …and store it inside cache/packages.json cache file
         \file_put_contents( $this->_packagesJSONFile, $data );

      }

      // output JSON file or end with a HTTP/1.0 304 Not Modified header

      $mTime = filemtime( $this->_packagesJSONFile );

      \header( 'Content-Type: application/json');
      \header( 'Last-Modified: ' . \gmdate( 'r', $mTime ) );
      \header( 'Cache-Control: max-age=0' );

      if ( ! empty( $_SERVER[ 'HTTP_IF_MODIFIED_SINCE' ] ) &&
           ( $since = \strtotime( $_SERVER[ 'HTTP_IF_MODIFIED_SINCE' ] ) ) &&
           $since >= $mTime )
      {
         \header( 'HTTP/1.0 304 Not Modified' );
      }
      else
      {
         \readfile( $this->_packagesJSONFile );
      }

      exit;

   }

   /**
    * Gets the path of a file that triggers a reload if it exists
    *
    * @return string
    */
   public function getTriggerReloadFilePath() : string
   {

      return $this->_triggerReloadFile;

   }

   // </editor-fold>


}

