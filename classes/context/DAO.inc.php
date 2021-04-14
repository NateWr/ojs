<?php
/**
 * @file classes/context/DAO.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class context
 *
 * @brief A class to interact with the contexts database.
 */

namespace APP\Context;

use Journal;
use stdClass;

class DAO extends \PKP\Context\DAO
{
    /** @copydoc EntityDAOBase::TABLE */
    public const TABLE = 'journals';

    /** @copydoc EntityDAOBase::SETTINGS_TABLE */
    public const SETTINGS_TABLE = 'journal_settings';

    /** @copydoc EntityDAOBase::PRIMARY_KEY_COLUMN */
    public const PRIMARY_KEY_COLUMN = 'journal_id';

    /** @copydoc EntityDAOBase::PRIMARY_TABLE_COLUMNS */
    public const PRIMARY_TABLE_COLUMNS = [
        'id' => 'journal_id',
        'enabled' => 'enabled',
        'urlPath' => 'path',
        'primaryLocale' => 'primary_locale',
        'seq' => 'seq',
    ];

    /**
     * @copydoc EntityDAOBase::newDataObject()
     */
    public static function newDataObject(): Journal
    {
        import('classes.journal.Journal');
        return new \Journal();
    }

    /**
     * @copydoc EntityDAOBase::_get()
     */
    public static function get(int $id): Journal
    {
        return parent::_get($id);
    }

    /**
     * @copydoc EntityDAOBase::_fromRow()
     */
    public static function fromRow(stdClass $row): Journal
    {
        return parent::_fromRow($row);
    }

    /**
     * @copydoc EntityDAOBase::_insert()
     */
    public static function insert(Journal $context): int
    {
        return parent::_insert($context);
    }

    /**
     * @copydoc EntityDAOBase::_update()
     */
    public static function update(Journal $context)
    {
        return parent::_update($context);
    }

    /**
     * @copydoc EntityDAOBase::_delete()
     */
    public static function delete(Journal $context): bool
    {
        return parent::_delete($context);
    }
}
