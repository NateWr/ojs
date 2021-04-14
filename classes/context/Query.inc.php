<?php
/**
 * @file classes/context/Query.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class context
 *
 * @brief A class to get contexts and information about contexts
 */

namespace App\Context;

use PKP\Context\Query as PKPContextQuery;

class Query extends PKPContextQuery
{
    public function validate(string $action, array $props, array $allowedLocales, string $primaryLocale): array
    {
        $errors = parent::validate($action, $props, $allowedLocales, $primaryLocale);

        if (!isset($props['journalThumbnail'])) {
            return $errors;
        }

        // If a journal thumbnail is passed, check that the temporary file exists
        // and the current user owns it
        $user = \Application::get()->getRequest()->getUser();
        $userId = $user ? $user->getId() : null;
        import('lib.pkp.classes.file.TemporaryFileManager');
        $temporaryFileManager = new \TemporaryFileManager();
        if (isset($props['journalThumbnail']) && empty($errors['journalThumbnail'])) {
            foreach ($allowedLocales as $localeKey) {
                if (empty($props['journalThumbnail'][$localeKey]) || empty($props['journalThumbnail'][$localeKey]['temporaryFileId'])) {
                    continue;
                }
                if (!$temporaryFileManager->getFile($props['journalThumbnail'][$localeKey]['temporaryFileId'], $userId)) {
                    if (!is_array($errors['journalThumbnail'])) {
                        $errors['journalThumbnail'] = [];
                    }
                    $errors['journalThumbnail'][$localeKey] = [__('common.noTemporaryFile')];
                }
            }
        }

        return $errors;
    }
}
