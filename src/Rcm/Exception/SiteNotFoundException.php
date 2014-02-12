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
 * @author    Rod McNew <rmcnew@relivinc.com>
 * @copyright 2012 Reliv International
 * @license   License.txt New BSD License
 * @version   GIT: <git_id>
 */
namespace Rcm\Exception;

/**
 * Reliv Common's language not found exception
 *
 * Is thrown when a language is not found
 *
 * @category  Reliv
 * @author    Rod McNew <rmcnew@relivinc.com>
 * @copyright 2012 Reliv International
 * @license   License.txt New BSD License
 * @version   Release: 1.0
 */
class SiteNotFoundException
    extends \InvalidArgumentException
    implements \Rcm\Exception\ExceptionInterface
{

}
