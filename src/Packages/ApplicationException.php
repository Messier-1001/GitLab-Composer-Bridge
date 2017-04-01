<?php
/**
 * @author     Messier 1001 <messier.1001+code@gmail.com>
 * @copyright  ©2017, Messier 1001
 * @package    Messier\GCB\Packages
 * @since      2017-03-27
 * @version    0.1.0
 */


declare( strict_types = 1 );


namespace Messier\GCB\Packages;


/**
 * Defines a class that …
 *
 * @since v0.1.0
 */
class ApplicationException extends \Exception
{


   /**
    * ApplicationException constructor.
    *
    * @param string          $message
    * @param int             $code
    * @param null|\Throwable $previous
    */
   public function __construct( string $message, int $code = 255, ?\Throwable $previous = null )
   {

      parent::__construct( $message, $code, $previous );

   }


}

