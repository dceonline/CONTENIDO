<?php
/**
 * Project:
 * CONTENIDO Content Management System
 *
 * Description:
 * CONTENIDO setup script
 *
 * Requirements:
 * @con_php_req 5
 *
 * @package    CONTENIDO setup
 * @version    0.2.5
 * @author     unknown
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 *
 *
 * {@internal
 *   created  unknown
 *   modified 2008-07-07, bilal arslan, added security fix
 *   modified 2011-01-21, Ortwin Pinke, added php-errorhandling function calls, uncomment if needed
 *   modified 2011-02-24, Murat Purc, extended mysql extension detection and some other changes
 *   modified 2011-02-28, Murat Purc, normalized setup startup process and some cleanup/formatting
 *
 *   $Id$:
 * }}
 *
 */

if (!defined('CON_FRAMEWORK')) {
     die('Illegal call');
}


$currentStep = (isset($_REQUEST['step'])) ? $_REQUEST['step'] : '';

switch ($currentStep) {
    case 'setuptype':
        checkAndInclude(CON_SETUP_PATH . '/steps/setuptype.php');
        break;
    case 'setup1':
        checkAndInclude(CON_SETUP_PATH . '/steps/setup/step1.php');
        break;
    case 'setup2':
        checkAndInclude(CON_SETUP_PATH . '/steps/setup/step2.php');
        break;
    case 'setup3':
        checkAndInclude(CON_SETUP_PATH . '/steps/setup/step3.php');
        break;
    case 'setup4':
        checkAndInclude(CON_SETUP_PATH . '/steps/setup/step4.php');
        break;
    case 'setup5':
        checkAndInclude(CON_SETUP_PATH . '/steps/setup/step5.php');
        break;
    case 'setup6':
        checkAndInclude(CON_SETUP_PATH . '/steps/setup/step6.php');
        break;
    case 'setup7':
        checkAndInclude(CON_SETUP_PATH . '/steps/setup/step7.php');
        break;
    case 'setup8':
        checkAndInclude(CON_SETUP_PATH . '/steps/setup/step8.php');
        break;
    case 'upgrade1':
        checkAndInclude(CON_SETUP_PATH . '/steps/upgrade/step1.php');
        break;
    case 'upgrade2':
        checkAndInclude(CON_SETUP_PATH . '/steps/upgrade/step2.php');
        break;
    case 'upgrade3':
        checkAndInclude(CON_SETUP_PATH . '/steps/upgrade/step3.php');
        break;
    case 'upgrade4':
        checkAndInclude(CON_SETUP_PATH . '/steps/upgrade/step4.php');
        break;
    case 'upgrade5':
        checkAndInclude(CON_SETUP_PATH . '/steps/upgrade/step5.php');
        break;
    case 'upgrade6':
        checkAndInclude(CON_SETUP_PATH . '/steps/upgrade/step6.php');
        break;
    case 'upgrade7':
        checkAndInclude(CON_SETUP_PATH . '/steps/upgrade/step7.php');
        break;
    case 'doupgrade':
        checkAndInclude(CON_SETUP_PATH . '/steps/upgrade/doupgrade.php');
        break;
    case 'doinstall':
        checkAndInclude(CON_SETUP_PATH . '/steps/setup/doinstall.php');
        break;
    case 'languagechooser':
    default:
        checkAndInclude(CON_SETUP_PATH . '/steps/languagechooser.php');
        break;
}

?>