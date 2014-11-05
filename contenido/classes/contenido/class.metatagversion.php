<?php
/**
 * This file contains the meta tag version collection and item class.
 *
 * @package          Core
 * @subpackage       GenericDB_Model
 * @version          SVN Revision $Rev:$
 *
 * @author           Jann Dieckmann
 * @copyright        four for business AG <www.4fb.de>
 * @license          http://www.contenido.org/license/LIZENZ.txt
 * @link             http://www.4fb.de
 * @link             http://www.contenido.org
 */

defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

/**
 * Metatag version collection
 *
 * @package Core
 * @subpackage GenericDB_Model
 */
class cApiMetaTagVersionCollection extends ItemCollection {

    /**
     * Constructor
     */
    public function __construct() {
        global $cfg;
        parent::__construct($cfg['tab']['meta_tag_version'], 'idmetatagversion');
        $this->_setItemClass('cApiMetaTagVersion');

        // set the join partners so that joins can be used via link() method
        $this->_setJoinPartner('cApiArticleLanguageVersionCollection');
        $this->_setJoinPartner('cApiMetaTypeCollection');
    }

    /**
     * Creates a meta tag entry.
     *
     * @param int $idArtLang
     * @param int $idMetaType
     * @param string $metaValue
     * @param string $version
     * @return cApiMetaTagVersion
     */
    public function create($idMetaTag, $idArtLang, $idMetaType, $metaValue, $version) {
        
        // create item
        $item = $this->createNewItem();

        $item->set('idmetatag', $idMetaTag, false);
        $item->set('idartlang', $idArtLang, false);
        $item->set('idmetatype', $idMetaType, false);
        $item->set('metavalue', $metaValue, false);
        $item->set('version', $version, false);
        $item->store();

        return $item;
        
    }

    /**
     * Returns a meta tag entry by article language and meta type and version.
     *
     * @param int $idArtLang
     * @param int $idMetaType
     * @param int $version
     * @return cApiMetaTagVersion|NULL
     */
    public function fetchByArtLangMetaTypeAndVersion($idArtLang, $idMetaType, $version) {
        $this->select('idartlang=' . (int) $idArtLang . ' AND idmetatype=' . (int) $idMetaType . ' AND version=' . (int) $version);
        return $this->next();
    }
    
    /**
     * Returns idmetatagversions by where-clause
     *
     * @param int $where
     * @return int[]
     */
    public function fetchByArtLangAndMetaType($where) {
        $metaTagVersionColl = new cApiMetaTagVersionCollection();
        $metaTagVersionColl->select($where);

        while($item = $metaTagVersionColl->next()){
            $ids[] = $item->get('idmetatagversion');
        }

        return $ids;

    }

}

/**
 * Metatag version item
 *
 * @package Core
 * @subpackage GenericDB_Model
 */
class cApiMetaTagVersion extends Item {

    /**
     * Constructor Function
     *
     * @param mixed $id Specifies the ID of item to load
     */
    public function __construct($id = false) {
        global $cfg;
        parent::__construct($cfg['tab']['meta_tag_version'], 'idmetatagversion');
        $this->setFilters(array(), array());
        if ($id !== false) {
            $this->loadByPrimaryKey($id);
        }
    }

    /**
     * Updates meta value of an entry.
     *
     * @param string $metaValue
     * @return bool
     */
    public function updateMetaValue($metaValue) {
        $this->set('metavalue', $metaValue, false);
        return $this->store();
    }

    /**
     * Marks this meta value as current
     *
     */
    public function markAsCurrent() {
        $metaTagColl = new cApiMetaTagCollection();
        $metaTag = $metaTagColl->fetchByArtLangAndMetaType($this->get('idartlang'), $this->get('idmetatype'));
        $metaTag->set('metavalue', $this->get('metavalue'), false);
        return $metaTag->store();
    }
    
    /**
     * Marks this meta value as editable
     *
     */
    public function markAsEditable($version) {
        $metaTagVersionColl = new cApiMetaTagVersionCollection();
        $metaTagVersionColl->create($this->get('idmetatag'), $this->get('idartlang'), $this->get('idmetatype'), $this->get('metavalue'), $version);
    }
    
    /**
     * Userdefined setter for meta tag fields.
     *
     * @param string $name
     * @param mixed $value
     * @param bool $safe Flag to run defined inFilter on passed value
     */
    public function setField($name, $value, $safe = true) {
        switch ($name) {
            case 'idartlang':
                $value = (int) $value;
                break;
			case 'idmetatype':
                $value = (int) $value;
                break;
        }

        return parent::setField($name, $value, $safe);
    }

}
