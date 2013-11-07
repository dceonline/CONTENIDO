<?php
/**
 * This file contains the backend page for editing template configurations.
 *
 * @package          Core
 * @subpackage       Backend
 * @version          SVN Revision $Rev:$
 *
 * @author           Jan Lengowski, Olaf Niemann
 * @copyright        four for business AG <www.4fb.de>
 * @license          http://www.contenido.org/license/LIZENZ.txt
 * @link             http://www.4fb.de
 * @link             http://www.contenido.org
 */

defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

if (!isset($idtpl)) {
    $idtpl = 0;
}

if ($idtpl != 0 && $idtplcfg != 0) {
// ############ @FIXME Same code as in contenido/includes/include.pretplcfg_edit.php
    $sql = "SELECT number FROM " . $cfg["tab"]["container"] . " WHERE idtpl = '" . cSecurity::toInteger($idtpl) . "'";
    $db->query($sql);

    $varstring = array();

    while ($db->nextRecord()) {
        $number = $db->f('number');
        $CiCMS_VAR = "C{$number}CMS_VAR";

        if (isset($_POST[$CiCMS_VAR]) && is_array($_POST[$CiCMS_VAR])) {
            if (!isset($varstring[$number])) {
                $varstring[$number] = '';
            }
            // NOTE: We could use http_build_query here!
            foreach ($_POST[$CiCMS_VAR] as $key => $value) {
                $varstring[$number] .= $key . '=' . urlencode(stripslashes($value)) . '&';
            }
        }
    }

    // Update/insert in container_conf
    if (count($varstring) > 0) {
        // Delete all containers
        $sql = "DELETE FROM " . $cfg["tab"]["container_conf"] . " WHERE idtplcfg = '" . cSecurity::toInteger($idtplcfg) . "'";
        $db->query($sql);

        foreach ($varstring as $col => $val) {
            // Insert all containers
            $sql = "INSERT INTO " . $cfg["tab"]["container_conf"] . " (idtplcfg, number, container) " .
                    "VALUES ('" . cSecurity::toInteger($idtplcfg) . "', '" . cSecurity::toInteger($col) . "', '" . $db->escape($val) . "') ";

            $db->query($sql);
        }
    }
// ###### END FIXME

    if ($idart) {
        //echo "art: idart: $idart, idcat: $idcat";
        $sql = "UPDATE " . $cfg["tab"]["art_lang"] . " SET idtplcfg = '" . cSecurity::toInteger($idtplcfg) . "' WHERE idart='$idart' AND idlang='" . cSecurity::toInteger($lang) . "'";
        $db->query($sql);
    } else {
        //echo "cat: idart: $idart, idcat: $idcat";
        $sql = "UPDATE " . $cfg["tab"]["cat_lang"] . " SET idtplcfg = '" . cSecurity::toInteger($idtplcfg) . "' WHERE idcat='$idcat' AND idlang='" . cSecurity::toInteger($lang) . "'";
        $db->query($sql);
    }

    if ($changetemplate == 1 && $idtplcfg != 0) {
        // update template conf
        $sql = "UPDATE " . $cfg["tab"]["tpl_conf"] . " SET idtpl='" . cSecurity::toInteger($idtpl) . "' WHERE idtplcfg='" . cSecurity::toInteger($idtplcfg) . "'";
        $db->query($sql);

        // delete old configured containers
        $sql = "DELETE FROM " . $cfg["tab"]["container_conf"] . " WHERE idtplcfg='" . cSecurity::toInteger($idtplcfg) . "'";
        $db->query($sql);
        $changetemplate = 0;
    } else {
        // donut
    }

    if ($changetemplate != 1) {
        if (isset($idart) && 0 != $idart) {
            conGenerateCode($idcat, $idart, $lang, $client);
            //backToMainArea($send);
        } else {
            conGenerateCodeForAllartsInCategory($idcat);
            if ($back == 'true') {
                backToMainArea($send);
            }
        }
    }
} elseif ($idtpl == 0) {

    // template deselected

    if (isset($idtplcfg) && $idtplcfg != 0) {
        $sql = "DELETE FROM " . $cfg["tab"]["tpl_conf"] . " WHERE idtplcfg = '" . cSecurity::toInteger($idtplcfg) . "'";
        $db->query($sql);

        $sql = "DELETE FROM " . $cfg["tab"]["container_conf"] . " WHERE idtplcfg = '" . cSecurity::toInteger($idtplcfg) . "'";
        $db->query($sql);
    }

    $idtplcfg = 0;
    if (!isset($changetemplate)) {
        $changetemplate = 0;
    }

    if ($idcat != 0 && $changetemplate == 1 && !$idart) {
        // Category
        $sql = "SELECT idtplcfg FROM " . $cfg["tab"]["cat_lang"] . " WHERE idcat = '" . cSecurity::toInteger($idcat) . "' AND idlang = '" . cSecurity::toInteger($lang) . "'";
        $db->query($sql);
        $db->nextRecord();
        $tmp_idtplcfg = $db->f("idtplcfg");

        $sql = "DELETE FROM " . $cfg["tab"]["tpl_conf"] . " WHERE idtplcfg = '" . cSecurity::toInteger($tmp_idtplcfg) . "'";
        $db->query($sql);

        $sql = "DELETE FROM " . $cfg["tab"]["container_conf"] . " WHERE idtplcfg = '" . cSecurity::toInteger($tmp_idtplcfg) . "'";
        $db->query($sql);

        $sql = "UPDATE " . $cfg["tab"]["cat_lang"] . " SET idtplcfg = 0 WHERE idcat = '" . cSecurity::toInteger($idcat) . "' AND idlang = '" . cSecurity::toInteger($lang) . "'";
        $db->query($sql);

        conGenerateCodeForAllartsInCategory($idcat);
        backToMainArea($send);
    } elseif (isset($idart) && $idart != 0 && $changetemplate == 1) {

        // Article
        $sql = "SELECT idtplcfg FROM " . $cfg["tab"]["art_lang"] . " WHERE idart = '" . cSecurity::toInteger($idart) . "' AND idlang = '" . cSecurity::toInteger($lang) . "'";
        $db->query($sql);
        $db->nextRecord();
        $tmp_idtplcfg = $db->f("idtplcfg");

        $sql = "DELETE FROM " . $cfg["tab"]["tpl_conf"] . " WHERE idtplcfg = '" . cSecurity::toInteger($tmp_idtplcfg) . "'";
        $db->query($sql);

        $sql = "DELETE FROM " . $cfg["tab"]["container_conf"] . " WHERE idtplcfg = '" . cSecurity::toInteger($tmp_idtplcfg) . "'";
        $db->query($sql);

        $sql = "UPDATE " . $cfg["tab"]["art_lang"] . " SET idtplcfg = 0 WHERE idart = '" . cSecurity::toInteger($idart) . "' AND idlang = '" . cSecurity::toInteger($lang) . "'";
        $db->query($sql);

        conGenerateCodeForAllartsInCategory($idcat);
        //backToMainArea($send);
    }
} else {

    if ($changetemplate == 1) {
        if (!$idart) {
            $sql = "SELECT idtplcfg FROM " . $cfg["tab"]["cat_lang"] . " WHERE idcat = '" . cSecurity::toInteger($idcat) . "' AND idlang = '" . cSecurity::toInteger($lang) . "'";
            $db->query($sql);
            $db->nextRecord();
            $tmp_idtplcfg = $db->f("idtplcfg");

            $sql = "DELETE FROM " . $cfg["tab"]["tpl_conf"] . " WHERE idtplcfg = '" . cSecurity::toInteger($tmp_idtplcfg) . "'";
            $db->query($sql);

            $sql = "DELETE FROM " . $cfg["tab"]["container_conf"] . " WHERE idtplcfg = '" . cSecurity::toInteger($tmp_idtplcfg) . "'";
            $db->query($sql);
        } else {
            $sql = "SELECT idtplcfg FROM " . $cfg["tab"]["art_lang"] . " WHERE idart = '" . cSecurity::toInteger($idart) . "' AND idlang = '" . cSecurity::toInteger($lang) . "'";
            $db->query($sql);
            $db->nextRecord();
            $tmp_idtplcfg = $db->f("idtplcfg");

            $sql = "DELETE FROM " . $cfg["tab"]["tpl_conf"] . " WHERE idtplcfg = '" . cSecurity::toInteger($tmp_idtplcfg) . "'";
            $db->query($sql);

            $sql = "DELETE FROM " . $cfg["tab"]["container_conf"] . " WHERE idtplcfg = '" . cSecurity::toInteger($tmp_idtplcfg) . "'";
            $db->query($sql);
        }
    }
    conGenerateCodeForAllartsInCategory($idcat);
}

?>
