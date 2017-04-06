<?php


declare ( strict_types = 1 );


namespace Messier\GCB\GitLab;


use Messier\GCB\GitLab\API\BranchOrTag;
use Messier\GCB\GitLab\API\Project;
use Messier\GCB\GitLab\API\ProjectCollection;
use Messier\GCB\GitLab\API\User;
use Messier\GCB\Packages\Application;
use Messier\HttpClient\Client;


class Api
{


   // <editor-fold desc="// – – –   P R I V A T E   F I E L D S   – – – – – – – – – – – – – – – – – – – – – – – –">

   /**
    * private_token=???
    *
    * @type string
    */
   private $_apiKey;

   /**
    * The GitLab API URL like http://messier-1001:8060/api/v4
    *
    * @type string
    */
   private $_apiUrl;

   /**
    * The type of the repository URLs that should be used
    *
    * @type string See {@see GitLabApi::REPO_URL_TYPE_SSH} and {@see GitLabApi::REPO_URL_TYPE_HTTP}
    */
   private $_repoUrlType;

   /**
    * @type \Messier\GCB\Packages\Application
    */
   private $_app;

   // </editor-fold>


   // <editor-fold desc="// – – –   P U B L I C   C L A S S   C O N S T A N T S   – – – – – – – – – – – – – – – –">

   /**
    * The repository URL type ssh for git specific SSH repository URLs
    */
   public const REPO_URL_TYPE_SSH  = 'ssh';

   /**
    * The repository URL type http
    */
   public const REPO_URL_TYPE_HTTP = 'http';

   /**
    * All known repository URL types
    */
   public const KNOWN_REPO_URL_TYPES = [ self::REPO_URL_TYPE_SSH, self::REPO_URL_TYPE_HTTP ];

   public const KNOWN_PROJECTS_ORDER_COLUMNS = [ 'id', 'name', 'path', 'created_at', 'updated_at', 'last_activity_at' ];

   // </editor-fold>


   // <editor-fold desc="// – – –   P U B L I C   C O N S T R U C T O R   – – – – – – – – – – – – – – – – – – – –">

   /**
    * Creates a new GitLabApi instance.
    */
   public function __construct()
   {

      $this->_apiKey        = null;
      $this->_apiUrl        = null;
      $this->_repoUrlType   = static::REPO_URL_TYPE_HTTP;

   }

   // </editor-fold>


   // <editor-fold desc="// – – –   P U B L I C   M E T H O D S   – – – – – – – – – – – – – – – – – – – – – – – –">


   // <editor-fold desc="// – – –   S E T T E R   – – – – – – – – – – – – –">

   /**
    * Sets the GitLab API key.
    *
    * @param string $apiKey The API key
    * @return \Messier\GCB\GitLab\Api
    */
   public function setApiKey( string $apiKey ) : Api
   {

      $this->_apiKey = $apiKey;

      return $this;

   }

   /**
    * Sets the GitLab API url.
    *
    * @param string $apiURL The API url
    * @return \Messier\GCB\GitLab\Api
    */
   public function setApiURL( string $apiURL ) : Api
   {

      $this->_apiUrl = \rtrim( $apiURL, '/' );

      return $this;

   }
   
   /**
    * Sets the type of the repository URLs that should be used inside the packages.json
    *
    * Valid values are defined by {@see GitLabApi::REPO_URL_TYPE_SSH} and {@see GitLabApi::REPO_URL_TYPE_HTTP}
    *
    * @param string $repoUrlType
    * @return \Messier\GCB\GitLab\Api
    */
   public function setRepoUrlType( string $repoUrlType ) : Api
   {

      if ( \in_array( $repoUrlType, static::KNOWN_REPO_URL_TYPES, true ) )
      {
         $this->_repoUrlType = $repoUrlType;
      }

      return $this;

   }

   // </editor-fold>


   // <editor-fold desc="// – – –   O T H E R   – – – – – – – – – – – – – -">

   /**
    * Gets all projects. If the user is an admin, also all projects of other users will be returned.
    *
    * @param \Messier\GCB\GitLab\API\User $user
    * @param string                       $orderBy
    * @param string                       $sort
    * @return \Messier\GCB\GitLab\API\ProjectCollection
    */
   public function getProjects( User $user, string $orderBy = 'last_activity_at', string $sort = 'desc' )
      : ProjectCollection
   {

      $projects = new ProjectCollection();

      $params = [ 'order_by' => $orderBy, 'sort' => $sort ];

      for ( $page = 1; \count( $pTmp = $this->getProjectsInternal( $user, $page, $params ) ); $page++ )
      {
         $projects->addRange( $pTmp );
      }

      return $projects;

   }

   /**
    * Gets the content of a project file.
    *
    * @param int    $projectId       The ID of the project
    * @param string $filePath        The file path
    * @param string $branchOrTagName The branch or tag name
    * @return bool|string
    */
   public function getFileContent( int $projectId, string $filePath, string $branchOrTagName )
   {

      // Load the file JSON
      $response = $this->get(
         static::getProjectPath( $projectId, 'repository/files/' . static::encodePathPart( $filePath ) ),
         [
            'private_token'   => $this->_apiKey,
            'ref'             => $branchOrTagName
         ]
      );

      // Return the content if defined
      if ( isset( $response[ 'content' ] ) && \is_string( $response[ 'content' ] ) )
      {
         return \base64_decode( $response[ 'content' ] );
      }

      // File not found
      return false;

   }

   /**
    * Gets the composer.json content of defined project or null if no valid composer.json exists
    *
    * @param \Messier\GCB\GitLab\API\Project $project The project
    * @param string|null                     $branchOrTagName The name of a repository branch or tag
    * @return array|null
    */
   public function getComposerJSON( Project $project, ?string $branchOrTagName = null ) : ?array
   {

      // Use project default branch if no brach or tag name is defined
      if ( empty( $branchOrTagName ) )
      {
         $branchOrTagName = $project->defaultBranch;
      }

      // Get the file content
      if ( false === ( $content = $this->getFileContent( $project->id, 'composer.json', $branchOrTagName ) ) )
      {
         return null;
      }

      // Decode the JSO to a associative array
      $composerJSON = \json_decode( $content, true );

      // Return null if $composerJSON is not a array, not have a value assigned to 'name' or have a name, not matching
      // case depending to project path with namespace (group)
      if ( ! \is_array( $composerJSON ) ||
           ! isset( $composerJSON[ 'name' ] ) ||
           0 !== \strcasecmp( $composerJSON[ 'name' ], $this->_app->handleCaseless( $project->pathWithNamespace ) ) )
      {
         return null;
      }

      // Return the valid composer.json array
      return $composerJSON;

   }

   /**
    * Gets all branches of an specific project.
    *
    * @param int $projectId
    * @return \Messier\GCB\GitLab\API\BranchOrTag[]
    */
   public function getBranches( int $projectId ) : array
   {

      // Load branches JSON
      $branches = $this->get(
         static::getProjectPath( $projectId, 'repository/branches' ),
         [ 'private_token'   => $this->_apiKey ]
      );

      // Return a empty array on error
      if ( isset( $branches[ 'error' ] ) )
      {
         return [];
      }

      // Convert all branches array items to BranchOrTag instances
      \array_walk( $branches, function( &$item ) { $item = new BranchOrTag( $item ); } );

      return $branches;

   }

   /**
    * Gets all tags of an specific project.
    *
    * @param int $projectId
    * @return \Messier\GCB\GitLab\API\BranchOrTag[]
    */
   public function getTags( int $projectId ) : array
   {

      // Load tags JSON
      $tags = $this->get(
         static::getProjectPath( $projectId, 'repository/tags' ),
         [ 'private_token'   => $this->_apiKey ]
      );

      // Return a empty array on error
      if ( isset( $tags[ 'error' ] ) )
      {
         return [];
      }

      // Convert all tags array items to BranchOrTag instances
      \array_walk( $tags, function( &$item ) { $item = new BranchOrTag( $item ); } );

      return $tags;

   }

   /**
    * Gets all branches and tags of an specific project.
    *
    * @param  int $projectId
    * @return \Messier\GCB\GitLab\API\BranchOrTag[]
    */
   public function getBranchesAndTags( int $projectId ) : array
   {

      return \array_merge(
         $this->getBranches( $projectId ),
         $this->getTags( $projectId )
      );

   }

   /**
    * Your personal user data.
    *
    * @return \Messier\GCB\GitLab\API\User|null
    */
   public function getMyUser() : ?User
   {

      $user = $this->get( '/user', [ 'private_token' => $this->_apiKey ] );

      if ( isset( $branches[ 'error' ] ) )
      {
         return null;
      }

      return new User( $user );

   }

   /**
    * Gets all project depending references as associative Array.
    *
    * The keys are the versions, the values are arrays with data for 'version', 'source' => 'url', 'source' => 'type'
    * and 'source' => 'reference'
    *
    * @param \Messier\GCB\GitLab\API\Project $project
    * @param \Messier\GCB\Packages\Application $app
    * @return array|null
    */
   public function getRefs( Project $project, Application $app ) : ?array
   {

      $this->_app = $app;

      $data = [];

      try
      {
         $branchesAndTags = $this->getBranchesAndTags( $project->id );
         foreach ( $branchesAndTags as $ref )
         {
            if ( false !== ( $refData = $this->getRef( $project, $ref ) ) )
            {
               $data[ $refData[ 'version' ] ] = $refData;
            }
         }
      }
      catch ( \Throwable $ex ) { }

      return 0 < \count( $data ) ? $data : null;

   }

   // </editor-fold>


   // </editor-fold>


   // <editor-fold desc="// – – –   P R O T E C T E D   M E T H O D S   – – – – – – – – – – – – – – – – – – – – –">

   /**
    * Gets the response array (JSON => Array) from declared API request
    *
    * @param  string $path
    * @param  array  $parameters
    * @return array
    */
   protected function get( string $path, array $parameters )
   {

      // Build the URL that should be called at gitlab api
      $url = $this->_apiUrl . $path;

      try
      {
         $rawContent = Client::Create()
                             ->setGetParameters( $parameters )
                             ->sendGet( $url );
      }
      catch ( \Throwable $ex )
      {
         // Error while getting the response
         return [ 'error' => $ex->getMessage() ];
      }

      if ( empty( $rawContent ) )
      {
         // No usable content => return a error response
         return [ 'error' => 'The URL "' . $url . '" not return a valid response!' ];
      }

      // Decode the JSON response to a associative array
      $response = \json_decode( $rawContent, true );
      if ( JSON_ERROR_NONE !== \json_last_error() )
      {
         // Decoding fails
         return [ 'error' => 'Invalid response format: ' . $rawContent ];
      }

      return $response;

   }

   /**
    * Gets all projects of defined user. Admins gets projects of all users
    *
    * @param \Messier\GCB\GitLab\API\User $user
    * @param int                          $page
    * @param array                        $params
    * @return array
    */
   protected function getProjectsInternal( User $user, int $page, array $params ) : array
   {

      if ( ! isset( $params[ 'private_token' ] ) ) { $params[ 'private_token' ]  = $this->_apiKey; }
      if ( ! isset( $params[ 'per_page' ]      ) ) { $params[ 'per_page' ]       = 100; }
      if ( ! isset( $params[ 'order_by' ]      ) ) { $params[ 'order_by' ]       = 'last_activity_at'; }
      if ( ! isset( $params[ 'sort' ]          ) ) { $params[ 'sort' ]           = 'desc'; }
      if ( ! $user->isAdmin                      ) { $params[ 'membership' ]     = 'true'; }

      $params[ 'page' ]     = $page;
      $params[ 'per_page' ] = \min( 100, \max( 10, $params[ 'per_page' ] ) );
      if ( 'asc' !== $params[ 'sort' ] && 'desc' !== $params[ 'sort' ] ) { $params[ 'sort' ] = 'desc'; }
      if ( ! \in_array( $params[ 'order_by' ], static::KNOWN_PROJECTS_ORDER_COLUMNS, true ) )
      {
         $params[ 'order_by' ] = 'last_activity_at';
      }

      $path = '/projects';

      $response = $this->get( $path, $params );

      if ( isset( $response[ 'error' ] ) )
      {
         return [];
      }

      return $response;

   }

   /**
    * …
    *
    * @param \Messier\GCB\GitLab\API\Project     $project
    * @param \Messier\GCB\GitLab\API\BranchOrTag $ref
    * @return array|bool|null
    */
   protected function getRef( Project $project, BranchOrTag $ref )
   {

      if ( \preg_match( '~^v?\d+\.\d+(\.\d+){0,2}(-(dev|patch|alpha|beta|RC)\d*)?$~', $ref->name ) )
      {
         $version = $ref->name;
      }
      else
      {
         $version = 'dev-' . $ref->name;
      }

      if ( null === ( $data = $this->getComposerJSON( $project, $ref->commitId ) ) )
      {
         // No valid composer.json => ignore this project ref
         return false;
      }

      // Get URL by defined type that should be used
      if ( static::REPO_URL_TYPE_SSH === $this->_repoUrlType )
      {
         $url = $project->urlToRepoSSH;
      }
      else
      {
         $url = $project->urlToRepoHTTP;
      }

      $data[ 'version' ] = $version;
      $data[ 'source' ]  = [
         'url'       => $url,
         'type'      => 'git',
         'reference' => $ref->commitId
      ];

      return $data;

   }

   // </editor-fold>


   // <editor-fold desc="// – – –   P R O T E C T E D   S T A T I C   M E T H O D S   – – – – – – – – – – – – – –">

   protected static function encodePathPart( string $pathPart )
   {

      return \str_replace( '.', '%2E', \rawurlencode( $pathPart ) );

   }

   protected static function getProjectPath( int $projectId, string $path )
   {
      return '/projects/' . $projectId . '/' . $path;
   }

   // </editor-fold>


}

