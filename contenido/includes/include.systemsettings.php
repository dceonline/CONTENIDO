<?php
/**
 * Project:
 * CONTENIDO Content Management System
 *
 * Description:
 * CONTENIDO System Settings Screen
 *
 * Requirements:
 * @con_php_req 5.0
 *
 *
 * @package    CONTENIDO Backend Includes
 * @version    1.7.0
 * @author     Timo A. Hummel
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since CONTENIDO release <= 4.6
 *
 * {@internal
 *   created 2003-11-18
 *   modified 2008-06-27, Frederic Schneider, add security fix
 *   modified 2008-11-13,  Timo Trautmann - Fixed wron escaping of chars
 *     modified 2012-02-15, Rusmir Jusufovic show messages
 *   $Id$:
 * }}
 *
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

$aManagedValues = array('versioning_prune_limit', 'update_check', 'update_news_feed', 'versioning_path', 'versioning_activated',
                        'update_check_period', 'system_clickmenu', 'system_mail_host', 'system_mail_sender',
                        'system_mail_sender_name', 'pw_request_enable', 'maintenance_mode', 'codemirror_activated',
                        'backend_preferred_idclient', 'generator_basehref', 'generator_xhtml', 'imagemagick_available',
                        'system_insight_editing_activated');

if ($action == "systemsettings_save_item")
{
    if(strpos($auth->auth["perm"], "sysadmin") === false) {
        $sWarning = $notification->returnNotification("error", i18n("You don't have the permission to make changes here."), 1).'<br>';
    } else {
        if (!in_array($systype.'_'.$sysname, $aManagedValues)) {
            setSystemProperty ($systype, $sysname, $sysvalue, $csidsystemprop);
            if(isset($x))
                $sWarning = $notification->returnNotification("info", i18n('Saved changes successfully!'), 1).'<br>';
            else
                $sWarning = $notification->returnNotification("info", i18n('Created new item successfully!'), 1).'<br>';
        } else {
           $sWarning = $notification->returnNotification("warning", i18n('Please set this property in systemsettings directly'), 1).'<br>';
        }
    }
}

if ($action == "systemsettings_delete_item")
{
    if(strpos($auth->auth["perm"], "sysadmin") === false) {
        $sWarning = $notification->returnNotification("error", i18n("You don't have the permission to make changes here."), 1).'<br>';
    } else {
        deleteSystemProperty($systype, $sysname);
        $sWarning = $notification->returnNotification("info", i18n('Deleted item successfully!'), 1).'<br>';
    }
}

$settings = getSystemProperties(1);

$list = new UI_List;
$list->setSolidBorder(true);
$list->setCell(1,1, i18n("Type"));
$list->setCell(1,2, i18n("Name"));
$list->setCell(1,3, i18n("Value"));
$list->setCell(1,4, "&nbsp;");
$list->setBorder(1);

$count = 2;

$oLinkEdit = new Link;
$oLinkEdit->setCLink($area, $frame, "systemsettings_edit_item");
$oLinkDelete = new Link;
$oLinkDelete->setCLink($area, $frame, "systemsettings_delete_item");
if(strpos($auth->auth["perm"], "sysadmin") === false) {
    $oLinkEdit->setContent('<img src="'.$cfg["path"]["contenido_fullhtml"].$cfg['path']['images'].'editieren_off.gif" alt="'.i18n("Edit").'" title="'.i18n("Edit").'">');
    $oLinkDelete->setContent('<img src="'.$cfg["path"]["contenido_fullhtml"].$cfg['path']['images'].'delete_inact.gif" alt="'.i18n("Delete").'" title="'.i18n("Delete").'">');
} else {
    $oLinkEdit->setContent('<img src="'.$cfg["path"]["contenido_fullhtml"].$cfg['path']['images'].'editieren.gif" alt="'.i18n("Edit").'" title="'.i18n("Edit").'">');
    $oLinkDelete->setContent('<img src="'.$cfg["path"]["contenido_fullhtml"].$cfg['path']['images'].'delete.gif" alt="'.i18n("Delete").'" title="'.i18n("Delete").'">');
}

$spacer = new cHTMLImage;
$spacer->setWidth(5);

if (is_array($settings))
{
    foreach ($settings as $key => $types)
    {
        foreach ($types as $type => $value)
        {
            $oLinkEdit->setCustom("sysname", urlencode($type));
            $oLinkEdit->setCustom("systype", urlencode($key));

            $oLinkDelete->setCustom("sysname", urlencode($type));
            $oLinkDelete->setCustom("systype", urlencode($key));

            $link = $oLinkEdit;
            $dlink = $oLinkDelete->render();

            if (in_array($key.'_'.$type, $aManagedValues)) {
                #ignore record

            } else if (($action == "systemsettings_edit_item") && (stripslashes($systype) == $key) && (stripslashes($sysname) == $type) && (strpos($auth->auth["perm"], "sysadmin") !== false)) {
                $oInputboxValue = new cHTMLTextbox("sysvalue", $value['value']);
                $oInputboxName = new cHTMLTextbox("sysname", $type);
                $oInputboxType = new cHTMLTextbox("systype", $key);

                $hidden = '<input type="hidden" name="csidsystemprop" value="'.$value['idsystemprop'].'">';
                $sSubmit = '<input type="image" style="vertical-align:top;" value="submit" src="'.$cfg["path"]["contenido_fullhtml"].$cfg['path']['images'].'submit.gif">';

                $list->setCell($count,1, $oInputboxType->render(true));
                $list->setCell($count,2, $oInputboxName->render(true));
                $list->setCell($count,3, $oInputboxValue->render(true).$hidden.$sSubmit);

            } else {
                $sMouseoverTemplate = '<span class="tooltip" title="%1$s">%2$s</span>';

                if (strlen($type) > 35) {
                    $sShort = htmlspecialchars(cApiStrTrimHard($type, 35));
                    $type = sprintf($sMouseoverTemplate, htmlspecialchars(addslashes($type), ENT_QUOTES), $sShort);
                }

                if (strlen($value['value']) > 35) {
                    $sShort = htmlspecialchars(cApiStrTrimHard($value['value'], 35));
                    $value['value'] = sprintf($sMouseoverTemplate, htmlspecialchars(addslashes($value['value']), ENT_QUOTES), $sShort);
                }

                if (strlen($key) > 35) {
                    $sShort = htmlspecialchars(cApiStrTrimHard($key, 35));
                    $key = sprintf($sMouseoverTemplate, htmlspecialchars(addslashes($key), ENT_QUOTES), $sShort);
                }

                !strlen(trim($value['value'])) ? $sValue = '&nbsp;' : $sValue = $value['value'];

                $list->setCell($count,1, $key);
                $list->setCell($count,2, $type);
                $list->setCell($count,3, $sValue);
            }

            if (!in_array($key.'_'.$type, $aManagedValues)) {
                $list->setCell($count,4, $spacer->render().$link->render().$spacer->render().$dlink.$spacer->render());
                $count++;
            }
        }
    }
}

if ($count == 2)
{
    $list->setCell($count, 4, "");
    $list->setCell($count, 1, i18n("No defined properties"));
    $list->setCell($count, 2, "");
    $list->setCell($count, 3, "");
}
unset($form);

$form = new UI_Table_Form("systemsettings");
$form->setVar("area",$area);
$form->setVar("frame", $frame);
$form->setVar("action", "systemsettings_save_item");
$form->addHeader(i18n("Add new variable"));
$inputbox = new cHTMLTextbox ("systype");
$form->add(i18n("Type"),$inputbox->render());

$inputbox = new cHTMLTextbox ("sysname");
$form->add(i18n("Name"),$inputbox->render());

$inputbox = new cHTMLTextbox ("sysvalue");
$form->add(i18n("Value"),$inputbox->render());


if ($action == "systemsettings_edit_item")
{
    if(strpos($auth->auth["perm"], "sysadmin") === false) {
        $sWarning = $notification->returnNotification("error", i18n("You don't have the permission to make changes here."), 1).'<br>';
        $sListstring = $list->render();
    } else {
        $form2 = new UI_Form("systemsettings");
        $form2->setVar("area",$area);
        $form2->setVar("frame", $frame);
        $form2->setVar("action", "systemsettings_save_item");
        $form2->add('list', $list->render());
        $sListstring = $form2->render();
    }
} else {
    $sListstring = $list->render();
}

$page = new UI_Page;
$sTooltippScript = '<script type="text/javascript" src="scripts/jquery/jquery.js"></script>
                    <script type="text/javascript" src="scripts/jquery.tipsy.js"></script>
                    <script type="text/javascript" src="scripts/registerTipsy.js"></script>';

$page->addScript('tooltippstyle', '<link rel="stylesheet" type="text/css" href="styles/tipsy.css" />');
$page->addScript('tooltip-js', $sTooltippScript);

$content = $sWarning."\n".$sListstring."<br>";
if(strpos($auth->auth["perm"], "sysadmin") !== false) {
    $content .= $form->render();
}

$page->setContent($content);
$page->render();

?>