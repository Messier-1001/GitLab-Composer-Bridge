<?php
/**
 * @author     Messier 1001 <messier.1001+code@gmail.com>
 * @copyright  ©2017, Messier 1001
 * @package    Messier\GCB\GitLab\API
 * @since      2017-03-26
 * @version    0.1.0
 */


declare( strict_types = 1 );


namespace Messier\GCB\GitLab\API;


/**
 * Defines a class that …
 *
 * @since v0.1.0
 */
class BranchOrTag
{


   // <editor-fold desc="// – – –   P U B L I C   F I E L D S   – – – – – – – – – – – – – – – – – – – – – – – – –">

   /**
    * The branch or tag name
    *
    * @type string
    */
   public $name;

   /**
    * The branch or tag last commit id. 40 char hex key
    *
    * @type string
    */
   public $commitId;

   /**
    * The tag release info.
    *
    * @type string|null
    */
   public $release;

   // </editor-fold>


   // <editor-fold desc="// – – –   P U B L I C   C O N S T R U C T O R   – – – – – – – – – – – – – – – – – – – –">

   /**
    * BranchOrTag constructor.
    *
    * @param array $branchData
    */
   public function __construct( array $branchData )
   {

      $this->name                = $branchData[ 'name' ];
      $this->commitId            = $branchData[ 'commit' ][ 'id' ];
      $this->release             = $branchData[ 'release' ] ?? null;

   }

   // </editor-fold>


}

