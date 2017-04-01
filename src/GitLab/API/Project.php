<?php
/**
 * @author     Messier 1001 <messier.1001+code@gmail.com>
 * @copyright  ©2017, Messier 1001
 * @package    Messier\GCB\GitLab\API
 * @since      2017-03-29
 * @version    0.1.0
 */


declare( strict_types = 1 );


namespace Messier\GCB\GitLab\API;


/**
 * Defines a class that …
 *
 * @since v0.1.0
 */
class Project
{


   // <editor-fold desc="// – – –   P U B L I C   F I E L D S   – – – – – – – – – – – – – – – – – – – – – – – – –">

   /**
    * The unique project ID.
    *
    * @type int
    */
   public $id                    = 0;

   /**
    * The project default branch name.
    *
    * @type string
    */
   public $defaultBranch         = 'master';

   /**
    * The SSH URL of the repository. (e.g.: git@messier-1001:Messier-PHP-Lib/Messier.Core.git)
    *
    * @type string|null
    */
   public $urlToRepoSSH          = null;

   /**
    * The HTTP URL of the repository. (e.g.: http://messier-1001:8060/Messier-PHP-Lib/Messier.Core.git)
    *
    * @type string
    */
   public $urlToRepoHTTP         = null;

   /**
    * The Web URL of the repository. (e.g.: http://messier-1001:8060/Messier-PHP-Lib/Messier.Core)
    *
    * @type string
    */
   public $urlWeb                = null;

   /**
    * The project name. (e.g.: "Messier.Core")
    *
    * @type string
    */
   public $name                  = null;

   /**
    * The project path. (e.g.: "Messier.Core")
    *
    * @type string
    */
   public $path                  = null;

   /**
    * The project path with namespace. (e.g.: "Messier-PHP-Lib/Messier.Core")
    *
    * @type string
    */
   public $pathWithNamespace     = null;

   /**
    * The DateTime of last project activity
    *
    * @type \DateTime
    */
   public $lastActivityAt;

   protected const REQUIRED_FIELDS = [
      'id', 'default_branch', 'ssh_url_to_repo', 'http_url_to_repo', 'web_url',
      'name', 'path', 'path_with_namespace', 'last_activity_at'
   ];

   // </editor-fold>


   // <editor-fold desc="// – – –   P U B L I C   C O N S T R U C T O R   – – – – – – – – – – – – – – – – – – – –">

   /**
    * Project constructor.
    *
    * @param array $projectData The array with the data of a single project provided by the GitLab project API
    * @throws \InvalidArgumentException If a required project data field not exists.
    */
   public function __construct( array $projectData )
   {

      foreach ( static::REQUIRED_FIELDS as $requiredField )
      {
         if ( ! isset( $projectData[ $requiredField ] ) )
         {
            throw new \InvalidArgumentException(
               'Invalid project data. Missing required field "' . $requiredField . '".'
            );
         }
      }

      $this->id                  = $projectData[ 'id' ];
      $this->defaultBranch       = $projectData[ 'default_branch' ];
      $this->urlToRepoSSH        = $projectData[ 'ssh_url_to_repo' ];
      $this->urlToRepoHTTP       = $projectData[ 'http_url_to_repo' ];
      $this->urlWeb              = $projectData[ 'web_url' ];
      $this->name                = $projectData[ 'name' ];
      $this->path                = $projectData[ 'path' ];
      $this->pathWithNamespace   = $projectData[ 'path_with_namespace' ];
      $this->lastActivityAt      = new \DateTime( $projectData[ 'last_activity_at' ] );

   }

   // </editor-fold>


}

