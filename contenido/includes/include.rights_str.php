<?php

/**
 * This file contains the backend page for structure rights management.
 *
 * @package Core
 * @subpackage Backend
 * @author Unknown
 * @copyright four for business AG <www.4fb.de>
 * @license http://www.contenido.org/license/LIZENZ.txt
 * @link http://www.4fb.de
 * @link http://www.contenido.org
 */

defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

// notice $oTpl is filled and generated in file include.rights.php this file
// renders $oTpl to browser
include_once(cRegistry::getBackendPath() . 'includes/include.rights.php');
// set the areas which are in use fore selecting these
$possible_area = "'" . implode("','", $area_tree[$perm->showareas("str")]) . "'";
$sql = "SELECT A.idarea, A.idaction, A.idcat, B.name, C.name
        FROM " . $cfg["tab"]["rights"] . " AS A, " . $cfg["tab"]["area"] . " AS B, " . $cfg["tab"]["actions"] . " AS C
        WHERE user_id = '" . $db->escape($userid) . "' AND idclient = " . cSecurity::toInteger($rights_client) . "
        AND A.type = 0 AND idlang = " . cSecurity::toInteger($rights_lang) . " AND B.idarea IN ($possible_area)
        AND idcat != 0 AND A.idaction = C.idaction AND A.idarea = C.idarea AND A.idarea = B.idarea";
$db->query($sql);

$rights_list_old = array();
while ($db->nextRecord()) { // set a new rights list fore this user
    $rights_list_old[$db->f(3) . "|" . $db->f(4) . "|" . $db->f("idcat")] = "x";
}

if (($perm->have_perm_area_action("user_overview", $action)) && ($action == "user_edit")) {
    $ret = cRights::saveRights();
    if ($ret === true) {
        $sMessage = $notification->returnNotification('ok', i18n('Changes saved'));
    }
} else {
    if (!$perm->have_perm_area_action("user_overview", $action)) {
        $sMessage = $notification->returnNotification("error", i18n("Permission denied"));
    }
}
$sJsBefore = '';
$sJsAfter = '';
$sJsExternal = '';
$sTable = '';

$sJsExternal .= '<script type="text/javascript" src="scripts/expandCollapse.js"></script>' . "\n";

// declare new javascript variables;
$sJsBefore .= "var itemids = [];
               var actareaids = [];\n";

// Init Table
$oTable = new cHTMLTable();
$oTable->updateAttributes(array(
    "class" => "generic",
    "cellspacing" => "0",
    "cellpadding" => "2"
));
$objHeaderRow = new cHTMLTableRow();
$objHeaderItem = new cHTMLTableHead();
$objFooterRow = new cHTMLTableRow();
$objFooterItem = new cHTMLTableData();
$objRow = new cHTMLTableRow();
$objItem = new cHTMLTableData();

// table header
// 1. zeile
$headeroutput = "";
$items = "";
$objHeaderItem->updateAttributes(array(
    "class" => "center",
    "valign" => "top",
    "align" => "left"
));
$objHeaderItem->setContent(i18n("Category"));
$items .= $objHeaderItem->render();
$objHeaderItem->advanceID();
$objHeaderItem->setContent("&nbsp;");
$items .= $objHeaderItem->render();
$objHeaderItem->advanceID();

$possible_areas = array();
$aSecondHeaderRow = array();

// look for possible actions in mainarea [] in str and con
foreach ($right_list["str"] as $value2) {
    // if there are some actions
    if (is_array($value2["action"])) {
        foreach ($value2["action"] as $key3 => $value3) {
            // set the areas that are in use
            if ($value3 != "str_newtree") {
                $possible_areas[$value2["perm"]] = "";

                // set the possible areas and actions for this areas
                $sJsBefore .= "actareaids[\"$value3|" . $value2["perm"] . "\"]=\"x\";\n";

                $objHeaderItem->updateAttributes(array(
                    "class" => "center",
                    "valign" => "top",
                    "align" => "center"
                ));
                $objHeaderItem->setContent($lngAct[$value2["perm"]][$value3] ? $lngAct[$value2["perm"]][$value3] : "&nbsp;");
                $items .= $objHeaderItem->render();
                $objHeaderItem->advanceID();

                $aSecondHeaderRow[] = "<input type=\"checkbox\" name=\"checkall_" . $value2["perm"] . "_$value3\" value=\"\" onClick=\"setRightsFor('" . $value2["perm"] . "', '$value3', '')\">";
            }
        }
    }
}

// checkbox for all rights
$objHeaderItem->setContent(i18n("Check all"));
$items .= $objHeaderItem->render();
$objHeaderItem->advanceID();
$aSecondHeaderRow[] = '<input type="checkbox" name="checkall" value="" onclick="setRightsForAll()">';

$objHeaderRow->updateAttributes(array(
    "class" => "textw_medium"
));
$objHeaderRow->setContent($items);
$items = "";
$headeroutput .= $objHeaderRow->render();
$objHeaderRow->advanceID();

// 2. zeile
$objHeaderItem->updateAttributes(array(
    "class" => "center",
    "valign" => "",
    "align" => "center",
    "style" => "border-top-width: 0px;"
));
$objHeaderItem->setContent("&nbsp;");
$items .= $objHeaderItem->render();
$objHeaderItem->advanceID();
$objHeaderItem->setContent("&nbsp;");
$items .= $objHeaderItem->render();
$objHeaderItem->advanceID();

foreach ($aSecondHeaderRow as $value) {
    $objHeaderItem->setContent($value);
    $items .= $objHeaderItem->render();
    $objHeaderItem->advanceID();
}
$objHeaderRow->updateAttributes(array(
    "class" => "textw_medium"
));
$objHeaderRow->setContent($items);
$items = "";
$headeroutput .= $objHeaderRow->render();
$objHeaderRow->advanceID();

// table content
$output = "";

$sql = "SELECT A.idcat, level, name,parentid FROM " . $cfg["tab"]["cat_tree"] . " AS A, " . $cfg["tab"]["cat"] . " AS B, " . $cfg["tab"]["cat_lang"] . " AS C WHERE A.idcat=B.idcat AND B.idcat=C.idcat
        AND C.idlang='" . cSecurity::toInteger($rights_lang) . "' AND B.idclient='" . cSecurity::toInteger($rights_client) . "' ORDER BY idtree";
$db->query($sql);

$counter = array();
$parentid = "leer";
$aRowname = array();
$iLevel = 0;

while ($db->nextRecord()) {
    if ($db->f("level") == 0 && $db->f("preid") != 0) {
        $objItem->updateAttributes(array(
            "colspan" => "13"
        ));
        $objItem->setContent("&nbsp;");
        $items = $objItem->render();
        $objItem->advanceID();

        $objRow->setContent($items);
        $items = "";
        $output = $objRow->render();
        $objRow->advanceID();
    } else {
        if ($db->f("level") < $iLevel) {
            $iDistance = $iLevel - $db->f("level");
            for ($i = 0; $i < $iDistance; $i++) {
                array_pop($aRowname);
            }
            $iLevel = $db->f("level");
        }

        if ($db->f("level") >= $iLevel) {
            if ($db->f("level") == $iLevel) {
                array_pop($aRowname);
            } else {
                $iLevel = $db->f("level");
            }
            $aRowname[] = $db->f("idcat");
        }

        // find out parentid for inheritance
        // if parentid is the same increase the counter
        if ($parentid == $db->f("parentid")) {
            $counter[$parentid] ++;
        } else {
            $parentid = $db->f("parentid");
            // if these parentid is in use increase the counter
            if (isset($counter[$parentid])) {
                $counter[$parentid] ++;
            } else {
                $counter[$parentid] = 0;
            }
        }

        // set javscript array for itemids
        $sJsAfter .= "itemids[\"" . $db->f("idcat") . "\"]=\"x\";\n";

        $spaces = '<img src="images/spacer.gif" height="1" width="' . ($db->f("level") * 15) . '"><a><img src="images/spacer.gif" width="7" id="' . implode('_', $aRowname) . '_img"></a>';

        $objItem->updateAttributes(array(
            "class" => "td_rights0"
        ));
        $objItem->setContent('<img src="images/spacer.gif" height="1" width="' . ($db->f("level") * 15) . '"><a><img src="images/spacer.gif" width="7" id="' . implode('_', $aRowname) . '_img"></a> ' . $db->f("name"));
        $items .= $objItem->render();
        $objItem->advanceID();

        $objItem->updateAttributes(array(
            "class" => "td_rights0"
        ));
        $objItem->setContent("<a href=\"javascript:rightsInheritanceUp('$parentid', '$counter[$parentid]')\" class=\"action\"><img border=\"0\" src=\"images/pfeil_links.gif\" alt=\"" . i18n("Apply rights for this category to all categories on the same level or above") . "\" title=\"" . i18n("Apply rights for this category to all categories on the same level or above") . "\"></a> <a href=\"javascript:rightsInheritanceDown('" . $db->f("idcat") . "')\" class=\"action\"><img border=\"0\" src=\"images/pfeil_runter.gif\" alt=\"" . i18n("Apply rights for this category to all categories below the current category") . "\" title=\"" . i18n("Apply rights for this category to all categories below the current category") . "\"></a>");
        $items .= $objItem->render();
        $objItem->advanceID();
        // look for possible actions in mainarea[]

        foreach ($right_list["str"] as $value2) {
            // if there area some
            if (is_array($value2["action"])) {
                foreach ($value2["action"] as $key3 => $value3) {
                    // HACK!
                    if ($value3 != "str_newtree") {
                        // does the user have the right
                        if (in_array($value2["perm"] . "|$value3|" . $db->f("idcat"), array_keys($rights_list_old))) {
                            $checked = "checked=\"checked\"";
                        } else {
                            $checked = "";
                        }

                        // set the checkbox the name consits of
                        // areaid+actionid+itemid the id = parebntid+couter for
                        // these parentid+areaid+actionid
                        $objItem->updateAttributes(array(
                            "class" => "td_rights2"
                        ));
                        $objItem->setContent("<input type=\"checkbox\" id=\"str_" . $parentid . "_" . $counter[$parentid] . "_" . $value2["perm"] . "_$value3\" name=\"rights_list[" . $value2["perm"] . "|$value3|" . $db->f("idcat") . "]\" value=\"x\" $checked>");
                        $items .= $objItem->render();
                        $objItem->advanceID();
                    }
                }
            }
        }

        // checkbox for checking all actions fore this itemid
        $objItem->updateAttributes(array(
            "class" => "td_rights3"
        ));
        $objItem->setContent("<input type=\"checkbox\" name=\"checkall_" . $value2["perm"] . "_" . $value3 . "_" . $db->f("idcat") . "\" value=\"\" onClick=\"setRightsFor('" . $value2["perm"] . "', '$value3', '" . $db->f("idcat") . "')\">");
        $items .= $objItem->render();
        $objItem->advanceID();
    }
    $objRow->updateAttributes(array(
        "id" => implode('_', $aRowname),
        "style" => "display: table-row;"
    ));
    $objRow->setContent($items);
    $items = "";
    $output .= $objRow->render();
    $objRow->advanceID();
}

// table footer
$footeroutput = "";
$objItem->updateAttributes(array(
    "class" => "",
    "valign" => "top",
    "align" => "right",
    "colspan" => "16"
));
$objItem->setContent("<a href=\"javascript:submitrightsform('', 'area');\"><img src=\"" . $cfg['path']['images'] . "but_cancel.gif\" border=0></a><img src=\"images/spacer.gif\" width=\"20\"> <a href=\"javascript:submitrightsform('user_edit', '');\"><img src=\"" . $cfg['path']['images'] . "but_ok.gif\" border=0></a>");
$items = $objItem->render();
$objItem->advanceID();
$objFooterRow->setContent($items);
$items = "";
$footeroutput = $objFooterRow->render();
$objFooterRow->advanceID();

$oTable->setContent($headeroutput . $output . $footeroutput);
$sTable = stripslashes($oTable->render());
// Table end

$sJsAfter .= "init('" . i18n("Open category") . "', '" . i18n("Close category") . "');\n";

$oTpl->set('s', 'NOTIFICATION_SAVE_RIGHTS', $sMessage);
$oTpl->set('s', 'JS_SCRIPT_BEFORE', $sJsBefore);
$oTpl->set('s', 'JS_SCRIPT_AFTER', $sJsAfter);
$oTpl->set('s', 'RIGHTS_CONTENT', $sTable);
$oTpl->set('s', 'EXTERNAL_SCRIPTS', $sJsExternal);

$oTpl->generate('templates/standard/' . $cfg['templates']['rights']);
