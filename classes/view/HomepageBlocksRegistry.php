<?php

/**
 * @file classes/view/MetadataBlockRepository.php
 *
 * Copyright (c) 2026 Simon Fraser University
 * Copyright (c) 2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Repository
 *
 * @brief A repository to register and load metadata blocks.
 */

namespace APP\view;

use APP\core\Application;
use APP\facades\Repo;
use APP\template\TemplateManager;
use PKP\context\Context;
use PKP\db\DAORegistry;
use PKP\submission\GenreDAO;
use PKP\view\HomepageBlock;

class HomepageBlocksRegistry extends \PKP\view\HomepageBlocksRegistry
{
    protected function registerDefaultBlocks(): void
    {
        parent::registerDefaultBlocks();

        $this->register(
            new HomepageBlock(
                component: 'homepage.issue-summary',
                title: __('manager.homepageBlocks.issueSummary'),
                forSite: false,
            )
        );
        $this->register(
            new HomepageBlock(
                component: 'homepage.latest-articles',
                title: __('submissions.published.latest'),
                loader: function (?Context $context) {
                    $collector = Repo::submission()
                        ->getCollector()
                        ->filterByLatestPublished(true)
                        ->limit(9);
                    if ($context) {
                        $collector->filterByContextIds([$context->getId()]);
                    } else {
                        $collector->filterByContextIds([Application::SITE_CONTEXT_ID_ALL]);
                    }
                    $latestPublications = $collector->getMany();

                    $genreDao = DAORegistry::getDAO('GenreDAO'); /** @var GenreDAO $genreDao */
                    $templateMgr = TemplateManager::getManager(Application::get()->getRequest());
                    $templateMgr->assign([
                        'latestPublications' => $latestPublications,
                        'latestPublicationsTitle' => __('submissions.published.latest'),
                        'latestPublicationsDescription' => $context
                            ? __('submissions.published.latest.description', [
                                'url' => Application::get()->getRequest()->url(null, 'issue', 'archive'),
                            ])
                            : __('submissions.published.latest.description.site', [
                                'url' => Application::get()->getRequest()->url(null, 'search'),
                            ]),
                        'primaryFileGenreIds' => $genreDao->getIdsBy(
                            contextIds: $context ? [$context->getId()] : null,
                            supplementary: false,
                            dependent: false,
                        )->toArray(),
                        'supplementaryFileGenreIds' => $genreDao->getIdsBy(
                            contextIds: $context ? [$context->getId()] : null,
                            supplementary: true,
                        )->toArray(),
                        'sections' => $context
                            ? Repo::section()
                                ->getCollector()
                                ->filterByContextIds([$context->getId()])
                                ->getMany()
                            : [],
                    ]);
                }
            )
        );
        $this->register(
            new HomepageBlock(
                component: 'homepage.categories',
                title: __('submissions.browseByCategory'),
                forSite: false,
                loader: function (Context $context) {
                    $categories = Repo::category()
                        ->getCollector()
                        ->filterByContextIds([$context->getId()])
                        ->filterByParentIds([null])
                        ->getMany();

                    $templateMgr = TemplateManager::getManager(Application::get()->getRequest());
                    $templateMgr->assign([
                        'categories' => $categories,
                        'maxCategoriesAsBlocks' => 9,
                        'browseByCategoryTitle' => __('submissions.browseByCategory'),
                        'browseByCategoryDescription' => __('submissions.browseByCategory.description', [
                            'url' => Application::get()->getRequest()->url(null, 'issue', 'archive'),
                        ]),
                    ]);
                }
            )
        );
    }
}
