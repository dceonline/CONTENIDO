<?php
/**
 * Project:
 * CONTENIDO Content Management System
 *
 * Description:
 * Controls all CONTENIDO backend actions
 *
 * Requirements:
 * @con_php_req 5.0
 *
 *
 * @package    CONTENIDO Backend Classes
 * @version    0.1.0
 * @author     Jan Lengowski
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since CONTENIDO release <= 4.6
 *
 * {@internal
 *   created unknown
 *   $Id$:
 * }}
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

class Contenido_Backend {

    /**
     * Debug flag
     * @deprecated [2012-03-12] No longer needed. The debug mode gets chosen by the system settings.
     */
    var $debug = 0;

    /**
     * Possible actions
     * @var array
     */
    var $actions = array();

    /**
     * Files
     * @var array
     */
    var $files = array();

    /**
     * Stores the frame number
     * @var int
     */
    var $frame = 0;

    /**
     * Errors
     * @var array
     */
    var $errors = array();

    /**
     * Save area
     * @var string
     */
    var $area = '';

    /**
     * Set the frame number in which the file is loaded
     * @return void
     */
    function setFrame($frame_nr = 0) {
        $frame_nr = cSecurity::toInteger($frame_nr);
        $this->frame = $frame_nr;
    }

    /**
     * Loads all required data from the DB and stores it in the $actions and $files array
     *
     * @param $area string selected area
     * @return void
     */
    function select($area) {
        // Required global vars
        global $cfg, $client, $lang, $db, $perm, $action, $idcat;
        global $idcat, $idtpl, $idmod, $idlay;

        if (isset($idcat)) {
            $itemid = $idcat;
        } elseif (isset($idtpl)) {
            $itemid = $idtpl;
        } elseif (isset($idmod)) {
            $itemid = $idmod;
        } elseif (isset($idlay)) {
            $itemid = $idlay;
        } else {
            $itemid = 0;
        }

        $itemid = cSecurity::toInteger($itemid);
        $area = cSecurity::escapeDB($area, $db);

        // Store Area
        $this->area = $area;

        // extract actions
        $sql = "SELECT
                    b.name AS name,
                    b.code AS code,
                    b.relevant as relevant_action,
                    a.relevant as relevant_area
                FROM
                    " . $cfg["tab"]["area"] . " AS a,
                    " . $cfg["tab"]["actions"] . " AS b
                WHERE
                    a.name   = '" . $area . "' AND
                    b.idarea = a.idarea AND
                    a.online = '1'";

        // Check if the user has access to this area.
        // Yes -> Grant him all actions
        // No  -> Grant him only action which are irrelevant = (Field 'relevant' is 0)

        if (!$perm->have_perm_area_action($area)) {
            $sql .= " AND a.relevant = '0'";
        }

        $db->query($sql);

        while ($db->next_record()) {

            // Save the action only access to the desired action is granted.
            // If this action is relevant for rights check if the user has permission to
            // execute this action

            if ($db->f("relevant_action") == 1 && $db->f("relevant_area") == 1) {

                if ($perm->have_perm_area_action_item($area, $db->f("name"), $itemid)) {
                    $this->actions[$area][$db->f('name')] = $db->f('code');
                }

                if ($itemid == 0) {
                    // itemid not available, since its impossible the get the correct rights out
                    // we only check if userrights are given for these three items on any item
                    if ($action == "mod_edit" || $action == "tpl_edit" || $action == "lay_edit") {
                        if ($perm->have_perm_area_action_anyitem($area, $db->f("name"))) {
                            $this->actions[$area][$db->f('name')] = $db->f('code');
                        }
                    }
                }
            } else {
                $this->actions[$area][$db->f('name')] = $db->f('code');
            }
        }

        $sql = "SELECT
                    b.filename AS name,
                    b.filetype AS type,
                    a.parent_id AS parent_id
                FROM
                    " . $cfg['tab']['area'] . " AS a,
                    " . $cfg['tab']['files'] . " AS b,
                    " . $cfg['tab']['framefiles'] . " AS c
                WHERE
                    a.name    = '" . $area . "' AND
                    b.idarea  = a.idarea AND
                    b.idfile  = c.idfile AND
                    c.idarea  = a.idarea AND
                    c.idframe = '" . $this->frame . "' AND
                    a.online  = '1'";

        // Check if the user has access to this area.
        // Yes -> Extract all files
        // No  -> Extract only irrelevant Files = (Field 'relevant' is 0)
        if (!$perm->have_perm_area_action($area)) {
            $sql .= " AND a.relevant = '0'";
        }
        $sql .= " ORDER BY b.filename";

        $db->query($sql);

        while ($db->next_record()) {

            // Test if entry is a plug-in. If so don't add the Include path
            if (strstr($db->f('name'), "/")) {
                $filepath = $cfg["path"]["plugins"] . $db->f('name');
            } else {
                $filepath = $cfg["path"]["includes"] . $db->f('name');
            }

            // If filetype is Main AND parent_id is 0 file is a sub file
            if ($db->f('parent_id') != 0 && $db->f('type') == 'main') {
                $this->files['sub'][] = $filepath;
            }

            $this->files[$db->f('type')][] = $filepath;
        }

        $debug = "Files:\n" . print_r($this->files, true) . "\n"
               . "Actions:\n" . print_r($this->actions[$this->area], true) . "\n"
               . "Information:\n"
               . "Area: $area\n" . "Action: $action\n"
               . "Client: $client\n" . "Lang: $lang\n";
        cDebug($debug);
    }

    /**
     * Checks if choosen action exists. If so, execute/eval it.
     *
     * @param $action String Action to execute
     * @return $action String Code for selected Action
     */
    function getCode($action) {
        $actionCodeFile = $cfg['path']['contenido'] . 'includes/type/action/include.' . $action . '.action.php';
        if (cFileHandler::exists($actionCodeFile)) {
            return cFileHandler::read($actionCodeFile);
        }

        return '';
    }

    /**
     * Returns the specified file path.
     * Distinction between 'inc' and 'main' files.
     *
     * 'inc'  => Required file like functions/classes etc.
     * 'main' => Main file
     *
     * @param $which String 'inc' / 'main'
     */
    function getFile($which) {
        if (isset($this->files[$which])) {
            return $this->files[$which];
        }
    }

    /**
     * Creates a log entry for the specified parameters.
     *
     * @param $idcat  Category-ID
     * @param $idart  Article-ID
     * @param $client Client-ID
     * @param $lang   Language-ID
     * @param $action Action (ID or canonical name)
     */
    function log($idcat, $idart, $client, $lang, $idaction) {
        global $perm, $auth;

        if (!cSecurity::isInteger($client)) {
            return;
        } elseif (!cSecurity::isInteger($lang)) {
            return;
        }

        $oDb = cRegistry::getDb();

        $timestamp = date('Y-m-d H:i:s');
        $idcatart = 0;

        $idcat = (int) $idcat;
        $idart = (int) $idart;
        $client = (int) $client;
        $lang = (int) $lang;
        $idaction = $oDb->escape($idaction);

        if ($idcat > 0 && $idart > 0) {
            $oCatArtColl = new cApiCategoryArticleCollection();
            $oCatArt = $oCatArtColl->fetchByCategoryIdAndArticleId($idcat, $idart);
            $idcatart = $oCatArt->get('idcatart');
        }

        $oldaction = $idaction;
        $idaction = $perm->getIDForAction($idaction);

        if ($idaction != '') {
            $oActionLogColl = new cApiActionlogCollection();
            $oActionLogColl->create($auth->auth["uid"], $client, $lang, $idaction, $idcatart, $timestamp);
        } else {
            echo $oldaction . " is not in the actions table!<br><br>";
        }
    }

}

?>