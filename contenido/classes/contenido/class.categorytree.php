<?php
/**
 * Project:
 * CONTENIDO Content Management System
 *
 * Description:
 * Category tree
 *
 * Requirements:
 * @con_php_req 5.0
 *
 *
 * @package    CONTENIDO API
 * @version    1.5
 * @author     Timo Hummel
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 *
 * {@internal
 *   created  2005-08-30
 *   $Id$:
 * }}
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

/**
 * Category tree collection
 * @package    CONTENIDO API
 * @subpackage Model
 */
class cApiCategoryTreeCollection extends ItemCollection {

    public function __construct($select = false) {
        global $cfg;
        parent::__construct($cfg['tab']['cat_tree'], 'idtree');
        $this->_setJoinPartner('cApiCategoryCollection');
        $this->_setItemClass('cApiTree');
        if ($select !== false) {
            $this->select($select);
        }
    }

    /** @deprecated  [2011-03-15] Old constructor function for downwards compatibility */
    public function cApiCategoryTreeCollection($select = false) {
        cDeprecated("Use __construct() instead");
        $this->__construct($select);
    }

    /**
     * Returns category tree structure by selecting the data from several tables ().
     * @param  int  $client  Client id
     * @param  int  $lang  Language id
     * @return  array  Category tree structure as follows:
     * <pre>
     * $arr[n]  (int)  idtree value
     * $arr[n]['idcat']  (int)
     * $arr[n]['level'] (int)
     * $arr[n]['idtplcfg']  (int)
     * $arr[n]['visible']  (int)
     * $arr[n]['name']  (string)
     * $arr[n]['public']  (int)
     * $arr[n]['urlname']  (string)
     * $arr[n]['is_start']  (int)
     * </pre>
     */
    function getCategoryTreeStructureByClientIdAndLanguageId($client, $lang) {
        global $cfg;

        $aCatTree = array();

        $sql = 'SELECT * FROM `:cat_tree` AS A, `:cat` AS B, `:cat_lang` AS C '
                . 'WHERE A.idcat = B.idcat AND B.idcat = C.idcat AND C.idlang = :idlang AND idclient = :idclient '
                . 'ORDER BY idtree';

        $sql = $this->db->prepare($sql, array(
            'cat_tree' => $this->table,
            'cat' => $cfg['tab']['cat'],
            'cat_lang' => $cfg['tab']['cat_lang'],
            'idlang' => (int) $lang,
            'idclient' => (int) $client,
                ));
        $this->db->query($sql);

        while ($this->db->next_record()) {
            $aCatTree[$this->db->f('idtree')] = array(
                'idcat' => $this->db->f('idcat'),
                'level' => $this->db->f('level'),
                'idtplcfg' => $this->db->f('idtplcfg'),
                'visible' => $this->db->f('visible'),
                'name' => $this->db->f('name'),
                'public' => $this->db->f('public'),
                'urlname' => $this->db->f('urlname'),
                'is_start' => $this->db->f('is_start')
            );
        }

        return $aCatTree;
    }

}

/**
 * Category tree item
 * @package    CONTENIDO API
 * @subpackage Model
 */
class cApiCategoryTree extends Item {

    /**
     * Constructor Function
     * @param  mixed  $mId  Specifies the ID of item to load
     */
    public function __construct($mId = false) {
        global $cfg;
        parent::__construct($cfg['tab']['cat_tree'], 'idtree');
        $this->setFilters(array(), array());
        if ($mId !== false) {
            $this->loadByPrimaryKey($mId);
        }
    }

}

################################################################################
# Old version of category tree class
#
# NOTE: Class implemetation below is deprecated and the will be removed in
#       future versions of contenido.
#       Don't use it, it's still available due to downwards compatibility.

/**
 * Single category tree item
 * @deprecated  [2011-10-11] Use cApiCategoryTree instead of this class.
 */
class cApiTree extends cApiCategoryTree {

    public function __construct($mId = false) {
        cDeprecated("Use class cApiCategoryTree instead");
        parent::__construct($mId);
    }

    /** @deprecated  [2011-03-15] Old constructor function for downwards compatibility */
    public function cApiTree($mId = false) {
        cDeprecated("Use __construct() instead");
        $this->__construct($mId);
    }

}

?>