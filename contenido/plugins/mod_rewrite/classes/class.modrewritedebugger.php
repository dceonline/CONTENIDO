<?php
/**
 * AMR debugger class
 *
 * @package     CONTENIDO Plugins
 * @subpackage  ModRewrite
 * @version     SVN Revision $Rev:$
 * @id          $Id$:
 * @author      Murat Purc <murat@purc.de>
 * @copyright   four for business AG <www.4fb.de>
 * @license     http://www.contenido.org/license/LIZENZ.txt
 * @link        http://www.4fb.de
 * @link        http://www.contenido.org
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

/**
 * Mod rewrite debugger class.
 *
 * @author      Murat Purc <murat@purc.de>
 * @package     CONTENIDO Plugins
 * @subpackage  ModRewrite
 */
class ModRewriteDebugger {

    /**
     * Flag to enable debugger
     * @var  bool
     */
    protected static $_bEnabled = false;

    /**
     * Enable debugger setter.
     * @param  bool  $bEnabled
     */
    public static function setEnabled($bEnabled) {
        self::$_bEnabled = (bool) $bEnabled;
    }

    /**
     * Adds variable to debugger.
     * Wrapper for <code>cDebug::getDebugger('visible_adv')</code>.
     *
     * @param  mixed   $mVar  The variable to dump
     * @param  string  $sLabel  Describtion for passed $mVar
     */
    public static function add($mVar, $sLabel = '') {
        if (!self::$_bEnabled) {
            return;
        }
        cDebug::getDebugger()->add($mVar, $sLabel);
    }

    /**
     * Returns output of all added variables to debug.
     * @return  string
     */
    public static function getAll() {
        if (!self::$_bEnabled) {
            return '';
        }
        ob_start();
        cDebug::getDebugger()->showAll();
        $sOutput = ob_get_contents();
        ob_end_clean();
        return $sOutput;
    }

    /**
     * Logs variable to debugger.
     * Wrapper for <code>cDebug::getDebugger(cDebug::DEBUGGER_FILE)</code>.
     *
     * @param  mixed   $mVar  The variable to log the contents
     * @param  string  $sLabel  Describtion for passed $mVar
     */
    public static function log($mVar, $sLabel = '') {
        if (!self::$_bEnabled) {
            return;
        }
        cDebug::getDebugger(cDebug::DEBUGGER_FILE)->show($mVar, $sLabel);
    }

}
