<?php
/**
 * @author     Messier 1001 <messier.1001+code@gmail.com>
 * @copyright  ©2017, Messier 1001
 * @package    Messier\GCB\GitLab\API
 * @since      2017-03-27
 * @version    0.1.0
 */


declare( strict_types = 1 );


namespace Messier\GCB\GitLab\API;


/**
 * Defines a GitLab user.
 *
 * @since v0.1.0
 */
class User
{


   // <editor-fold desc="// – – –   P U B L I C   F I E L D S   – – – – – – – – – – – – – – – – – – – – – – – – –">

   /**
    * The user name (e.g.: John Who)
    *
    * @type string
    */
   public $name;

   /**
    * The user login name (e.g.: john-who)
    *
    * @type string
    */
   public $userName;

   /**
    * The user ID.
    *
    * @type int
    */
   public $id;

   /**
    * The state of the user (e.g.: active)
    *
    * @type string
    */
   public $state;

   /**
    * Is the user a admin user?
    *
    * @type bool
    */
   public $isAdmin;

   /**
    * The user mail address
    *
    * @type string
    */
   public $email;

   // </editor-fold>


   // <editor-fold desc="// – – –   P U B L I C   C O N S T R U C T O R   – – – – – – – – – – – – – – – – – – – –">

   /**
    * User constructor.
    *
    * @param array $userData
    */
   public function __construct( array $userData )
   {

      $this->name             = $userData[ 'name' ];
      $this->userName         = $userData[ 'username' ];
      $this->id               = $userData[ 'id' ];
      $this->state            = $userData[ 'state' ] ?? 'inactive';
      $this->isAdmin          = $userData[ 'is_admin' ] ?? false;
      $this->email            = $userData[ 'email' ];

   }

   // </editor-fold>


}

