<?php

/**
 * @file plugins/generic/mapPluginExample/MapPluginExamplePlugin.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MapPluginExamplePlugin
 * @ingroup plugins_generic_browsebysection
 *
 * @brief Plugin that adds an institutional home field to a journal.
 */
use APP\Facade\Map;

import('lib.pkp.classes.plugins.GenericPlugin');

class MapPluginExamplePlugin extends GenericPlugin
{
    /**
     * @copydoc Plugin::register
     *
     * @param null|mixed $mainContextId
     */
    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path);
        if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) {
            return $success;
        }
        if ($success && $this->getEnabled()) {

            /**
             * Extend the Schema map which maps objects to the REST API
             */
            Map::extend(PKP\Announcement\Maps\Schema::class, function($output, Announcement $item, PKP\Announcement\Maps\Schema $map) {

                /**
                 * Days since announcement was posted
                 */
                $then = new DateTime($item->getData('datePosted'));
                $now = new DateTime();
                $output['daysSince'] = $now->diff($then)->format("%a");

                /**
                 * Don't extend summary maps
                 */
                if (!$map->isSummary) {
                    /**
                     * Prevent duplicate DB lookups when extension needs to
                     * retrieve data from the database
                     */
                    static $contactEmail = '';
                    if (empty($contactEmail)) {
                        $contactEmail = (string) Application::get()->getRequest()->getContext()->getData('contactEmail');
                    }
                    $output['contactEmail'] = $contactEmail;
                }

                return $output;
            });

            /**
             * Extend the OAI map which maps objects to OAI records
             */
            Map::extend(PKP\Announcement\Maps\OAI::class, function(DOMElement $node, Announcement $item, PKP\Announcement\Maps\OAI $map) {
                $xml = $map->xml;
                foreach ($node->childNodes as $childNode) {
                    if ($childNode->nodeName === 'header') {
                        /**
                         * Get the XML document from the map
                         */
                        $xml = $map->xml;
                        $childNode->appendChild($xml->createElement('oai-extension', 'example-extension'));
                        break;
                    }
                }
                return $node;
            });
        }
        return $success;
    }

    /**
     * @copydoc PKPPlugin::getDisplayName
     */
    public function getDisplayName()
    {
        return __('plugins.generic.mapPluginExample.name');
    }

    /**
     * @copydoc PKPPlugin::getDescription
     */
    public function getDescription()
    {
        return __('plugins.generic.mapPluginExample.description');
    }
}
