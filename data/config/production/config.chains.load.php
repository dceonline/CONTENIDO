<?php
/**
 * This file contains all chains to load in the registry.
 *
 * @package          Core
 * @subpackage       Backend_ConfigFile
 * @version          SVN Revision $Rev:$
 *
 * @author           Unknown
 * @copyright        four for business AG <www.4fb.de>
 * @license          http://www.contenido.org/license/LIZENZ.txt
 * @link             http://www.4fb.de
 * @link             http://www.contenido.org
 */

defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

// get cec registry instance
$_cecRegistry = cApiCecRegistry::getInstance();

cInclude('includes', 'chains/include.chain.frontend.cat_backendaccess.php');
cInclude('includes', 'chains/include.chain.frontend.cat_access.php');
cInclude('includes', 'chains/include.chain.content.createmetatags.php');
cInclude('includes', 'chains/include.chain.frontend.createbasehref.php');

$_cecRegistry->addChainFunction('Contenido.Frontend.CategoryAccess', 'cecFrontendCategoryAccess');
$_cecRegistry->addChainFunction('Contenido.Frontend.CategoryAccess', 'cecFrontendCategoryAccess_Backend');
$_cecRegistry->addChainFunction('Contenido.Content.CreateMetatags', 'cecCreateMetatags');
$_cecRegistry->addChainFunction('Contenido.Frontend.BaseHrefGeneration', 'cecCreateBaseHref');
