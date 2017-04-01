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
 * A GitLab project collection.
 *
 * @since v0.1.0
 */
class ProjectCollection implements \ArrayAccess, \IteratorAggregate, \Countable
{


   // <editor-fold desc="// – – –   P R I V A T E   F I E L D S   – – – – – – – – – – – – – – – – – – – – – – – –">

   /** @type \Messier\GCB\GitLab\API\Project[] */
   private $_projects;
   /** @type int */
   private $_index;
   /** @type \DateTime|null */
   private $_lastActivityAt;

   // </editor-fold>


   // <editor-fold desc="// – – –   P U B L I C   C O N S T R U C T O R   – – – – – – – – – – – – – – – – – – – –">

   /**
    * ProjectCollection constructor.
    *
    * @param array $initialProjectsData
    */
   public function __construct( array $initialProjectsData = [] )
   {

      $this->_projects       = [];
      $this->_lastActivityAt = null;
      $this->_index          = 0;

      if ( \count( $initialProjectsData ) )
      {
         $this->addRange( $initialProjectsData );
      }

   }

   // </editor-fold>


   // <editor-fold desc="// – – –   P U B L I C   M E T H O D S   – – – – – – – – – – – – – – – – – – – – – – – –">

   /**
    * Add a single project
    *
    * @param  array|\Messier\GCB\GitLab\API\Project $project
    * @return \Messier\GCB\GitLab\API\ProjectCollection
    */
   public function add( $project ) : ProjectCollection
   {

      if ( null === $project ) { return $this; }

      if ( \is_array( $project ) )
      {
         try
         {
            $p = new Project( $project );
            $this->_projects[] = $p;
            if ( null === $this->_lastActivityAt || $this->_lastActivityAt < $p->lastActivityAt )
            {
               $this->_lastActivityAt = $p->lastActivityAt;
            }
         }
         catch ( \Throwable $ex ) {}
         return $this;
      }

      if ( ! ( $project instanceof Project ) ) { return $this; }

      $this->_projects[] = $project;
      if ( null === $this->_lastActivityAt || $this->_lastActivityAt < $project->lastActivityAt )
      {
         $this->_lastActivityAt = $project->lastActivityAt;
      }

      return $this;

   }

   /**
    * Adds one or more projects
    *
    * @param array $projects
    * @return \Messier\GCB\GitLab\API\ProjectCollection
    */
   public function addRange( array $projects ) : ProjectCollection
   {

      foreach ( $projects as $project )
      {
         $this->add( $project );
      }

      return $this;

   }

   /**
    * Gets the last project activity date or NULL if no project is defined.
    *
    * @return \DateTime|null
    */
   public function getLastActivityDate() : ?\DateTime
   {

      return $this->_lastActivityAt;

   }

   /**
    * Gets if one or more projects are registered.
    *
    * @return bool
    */
   public function hasProject() : bool
   {

      return 0 < $this->count();

   }

   /**
    * Return the current Project element
    *
    * @return \Messier\GCB\GitLab\API\Project
    */
   public function current()
   {

      return $this->_projects[ $this->_index ];

   }

   /**
    * Move forward to next Project element.
    */
   public function next()
   {

      $this->_index++;

   }

   /**
    * Return the key (index) of the current Project element.
    *
    * @return int
    */
   public function key()
   {

      return $this->_index;

   }

   /**
    * Checks if current position is valid
    *
    * @return boolean
    */
   public function valid()
   {

      return $this->_index < $this->count();

   }

   /**
    * Rewind the Iterator to the first element.
    */
   public function rewind()
   {

      $this->_index = 0;

   }

   /**
    * Whether a offset exists
    *
    * @param int $offset
    * @return boolean
    */
   public function offsetExists( $offset )
   {

      return $offset > -1 && $offset < $this->count();

   }

   /**
    * Offset to retrieve.
    *
    * @param int $offset
    * @return \Messier\GCB\GitLab\API\Project
    */
   public function offsetGet( $offset )
   {

      return $this->_projects[ $offset ];

   }

   /**
    * Offset to set
    *
    * @param int|null $offset
    * @param \Messier\GCB\GitLab\API\Project|array $value
    */
   public function offsetSet( $offset, $value )
   {

      if ( ! ( $value instanceof  Project ) )
      {

         if ( ! \is_array( $value ) )
         {
            return;
         }

         try { $value = new Project( $value ); }
         catch ( \Throwable $ex ) { return; }

      }

      if ( null === $offset )
      {
         $this->_projects[] = $value;
      }
      else
      {
         $this->_projects[ $offset ] = $value;
      }

      if ( null === $this->_lastActivityAt || $value->lastActivityAt > $this->_lastActivityAt )
      {
         $this->_lastActivityAt = $value->lastActivityAt;
      }

   }

   /**
    * Offset to unset
    *
    * @param int $offset
    */
   public function offsetUnset( $offset )
   {

      unset( $this->_projects[ $offset ] );

      $this->findLastActivityDate();

   }

   /**
    * Count elements of an object
    *
    * @return int
    */
   public function count()
   {

      return \count( $this->_projects );

   }

   /**
    * Retrieve an external iterator
    * @return \Traversable
    */
   public function getIterator()
   {

      return new \ArrayIterator( $this->_projects );

   }

   // </editor-fold>


   // <editor-fold desc="// – – –   P R O T E C T E D   M E T H O D S   – – – – – – – – – – – – – – – – – – – – –">

   protected function findLastActivityDate()
   {
      $this->_lastActivityAt = null;
      foreach ( $this->_projects as $project )
      {
         if ( null === $this->_lastActivityAt || $this->_lastActivityAt < $project->lastActivityAt )
         {
            $this->_lastActivityAt = $project->lastActivityAt;
         }
      }
   }

   // </editor-fold>


}

