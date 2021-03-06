<?php
/**
 * 2007-2016 PrestaShop
 *
 * Thirty Bees is an extension to the PrestaShop e-commerce software developed by PrestaShop SA
 * Copyright (C) 2017 Thirty Bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.thirtybees.com for more information.
 *
 * @author    Thirty Bees <contact@thirtybees.com>
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2017 Thirty Bees
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  PrestaShop is an internationally registered trademark & property of PrestaShop SA
 */

/**
 * Class ContactCore
 *
 * @since 1.0.0
 */
class ContactCore extends ObjectModel
{
    // @codingStandardsIgnoreStart
    public $id;
    /** @var string Name */
    public $name;
    /** @var string e-mail */
    public $email;
    /** @var string Detailed description */
    public $description;
    public $customer_service;
    // @codingStandardsIgnoreEnd

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'     => 'contact',
        'primary'   => 'id_contact',
        'multilang' => true,
        'fields'    => [
            'email'            => ['type' => self::TYPE_STRING, 'validate' => 'isEmail', 'size' => 128],
            'customer_service' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],

            /* Lang fields */
            'name'             => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 32],
            'description'      => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml'],
        ],
    ];

    /**
     * Return available contacts
     *
     * @param int $idLang Language ID
     *
     * @return array Contacts
     */
    public static function getContacts($idLang)
    {
        $shopIds = Shop::getContextListShopID();
        $sql = 'SELECT *
				FROM `'._DB_PREFIX_.'contact` c
				'.Shop::addSqlAssociation('contact', 'c', false).'
				LEFT JOIN `'._DB_PREFIX_.'contact_lang` cl ON (c.`id_contact` = cl.`id_contact`)
				WHERE cl.`id_lang` = '.(int) $idLang.'
				AND contact_shop.`id_shop` IN ('.implode(', ', array_map('intval', $shopIds)).')
				GROUP BY c.`id_contact`
				ORDER BY `name` ASC';

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }

    /**
     * Return available categories contacts
     *
     * @return array Contacts
     *
     * @since   1.0.0
     * @version 1.0.0 Initial version
     */
    public static function getCategoriesContacts()
    {
        $shopIds = Shop::getContextListShopID();

        return Db::getInstance()->executeS(
            '
			SELECT cl.*
			FROM '._DB_PREFIX_.'contact ct
			'.Shop::addSqlAssociation('contact', 'ct', false).'
			LEFT JOIN '._DB_PREFIX_.'contact_lang cl
				ON (cl.id_contact = ct.id_contact AND cl.id_lang = '.(int) Context::getContext()->language->id.')
			WHERE ct.customer_service = 1
			AND contact_shop.`id_shop` IN ('.implode(', ', array_map('intval', $shopIds)).')
			GROUP BY ct.`id_contact`
		'
        );
    }
}
