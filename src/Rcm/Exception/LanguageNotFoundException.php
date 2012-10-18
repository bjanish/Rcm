<?php
/**
 * Reliv Common's language not found exception
 *
 * Is thrown when a language is not found
 *
 * PHP version 5.3
 *
 * LICENSE: No License yet
 *
 * @category  Reliv
 * @package   Common\Exception
 * @author    Rod McNew <rmcnew@relivinc.com>
 * @copyright 2012 Reliv International
 * @license   License.txt New BSD License
 * @version   GIT: <git_id>
 * @link      http://ci.reliv.com/confluence
 */
namespace Rcm\Exception;

/**
 * Reliv Common's language not found exception
 *
 * Is thrown when a language is not found
 *
 * @category  Reliv
 * @package   Common\Exception
 * @author    Rod McNew <rmcnew@relivinc.com>
 * @copyright 2012 Reliv International
 * @license   License.txt New BSD License
 * @version   Release: 1.0
 * @link      http://ci.reliv.com/confluence
 */
class LanguageNotFoundException
    extends \InvalidArgumentException
    implements \Rcm\Exception\ExceptionInterface
{

}
