<?php

declare(strict_types=1);

/**
 * @package     Joomla.Administrator
 * @subpackage  com_nativesitemap
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$diagnostic = $this->diagnostic;
?>
<form action="<?php echo Route::_('index.php?option=com_nativesitemap'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="container-fluid">
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title"><?php echo Text::_('COM_NATIVESITEMAP_DIAGNOSTIC_TITLE'); ?></h2>
                <p class="card-text mb-0"><?php echo Text::_('COM_NATIVESITEMAP_DIAGNOSTIC_INTRO'); ?></p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h2 class="h4"><?php echo Text::_('COM_NATIVESITEMAP_ROBOTS_STATUS'); ?></h2>

                <?php if ($this->robotsError !== null) : ?>
                    <div class="alert alert-danger mb-0" role="alert">
                        <?php echo Text::sprintf('COM_NATIVESITEMAP_ROBOTS_DIAGNOSTIC_ERROR', htmlspecialchars($this->robotsError, ENT_QUOTES, 'UTF-8')); ?>
                    </div>
                <?php elseif ($this->robotsStatus !== null) : ?>
                    <?php $robotsStatus = $this->robotsStatus; ?>
                    <div class="alert alert-<?php echo $robotsStatus->isSynchronized() ? 'success' : 'warning'; ?>" role="status">
                        <?php echo Text::_($robotsStatus->isSynchronized()
                            ? 'COM_NATIVESITEMAP_ROBOTS_STATUS_OK'
                            : 'COM_NATIVESITEMAP_ROBOTS_STATUS_ACTION_REQUIRED'); ?>
                    </div>

                    <dl class="row mb-0">
                        <dt class="col-sm-3"><?php echo Text::_('COM_NATIVESITEMAP_ROBOTS_FILE'); ?></dt>
                        <dd class="col-sm-9"><code><?php echo htmlspecialchars($robotsStatus->getPath(), ENT_QUOTES, 'UTF-8'); ?></code></dd>

                        <dt class="col-sm-3"><?php echo Text::_('COM_NATIVESITEMAP_ROBOTS_EXISTS'); ?></dt>
                        <dd class="col-sm-9"><?php echo Text::_($robotsStatus->exists() ? 'JYES' : 'JNO'); ?></dd>

                        <dt class="col-sm-3"><?php echo Text::_('COM_NATIVESITEMAP_ROBOTS_WRITABLE'); ?></dt>
                        <dd class="col-sm-9"><?php echo Text::_($robotsStatus->isWritable() ? 'JYES' : 'JNO'); ?></dd>

                        <dt class="col-sm-3"><?php echo Text::_('COM_NATIVESITEMAP_ROBOTS_DIRECTIVE_COUNT'); ?></dt>
                        <dd class="col-sm-9"><?php echo $robotsStatus->getSitemapDirectiveCount(); ?></dd>

                        <dt class="col-sm-3"><?php echo Text::_('COM_NATIVESITEMAP_ROBOTS_CURRENT_URL'); ?></dt>
                        <dd class="col-sm-9"><code><?php echo htmlspecialchars($robotsStatus->getCurrentSitemapUrl() ?? Text::_('COM_NATIVESITEMAP_NOT_DEFINED'), ENT_QUOTES, 'UTF-8'); ?></code></dd>

                        <dt class="col-sm-3"><?php echo Text::_('COM_NATIVESITEMAP_ROBOTS_EXPECTED_URL'); ?></dt>
                        <dd class="col-sm-9"><code><?php echo htmlspecialchars($robotsStatus->getExpectedSitemapUrl(), ENT_QUOTES, 'UTF-8'); ?></code></dd>

                        <?php if ($this->robotsBackup !== null) : ?>
                            <dt class="col-sm-3"><?php echo Text::_('COM_NATIVESITEMAP_ROBOTS_LAST_BACKUP'); ?></dt>
                            <dd class="col-sm-9"><code><?php echo htmlspecialchars($this->robotsBackup, ENT_QUOTES, 'UTF-8'); ?></code></dd>
                        <?php endif; ?>
                    </dl>

                    <?php if ($robotsStatus->exists() && $robotsStatus->getContent() !== null) : ?>
                        <div class="mt-4">
                            <h3 class="h5"><?php echo Text::_('COM_NATIVESITEMAP_ROBOTS_CONTENT'); ?></h3>
                            <textarea
                                class="form-control font-monospace"
                                rows="18"
                                readonly
                                spellcheck="false"
                                aria-label="<?php echo Text::_('COM_NATIVESITEMAP_ROBOTS_CONTENT'); ?>"
                            ><?php echo htmlspecialchars($robotsStatus->getContent(), ENT_QUOTES, 'UTF-8'); ?></textarea>
                            <p class="form-text mb-0">
                                <?php echo Text::_('COM_NATIVESITEMAP_ROBOTS_CONTENT_HELP'); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($this->sitemap !== null) : ?>
            <div class="card mb-4 border-success">
                <div class="card-body">
                    <h2 class="h4"><?php echo Text::_('COM_NATIVESITEMAP_SITEMAP_STATUS'); ?></h2>
                    <dl class="row mb-0">
                        <dt class="col-sm-3"><?php echo Text::_('COM_NATIVESITEMAP_GENERATED_AT'); ?></dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars((string) $this->sitemap['generatedAt'], ENT_QUOTES, 'UTF-8'); ?></dd>

                        <dt class="col-sm-3"><?php echo Text::_('COM_NATIVESITEMAP_URL_COUNT'); ?></dt>
                        <dd class="col-sm-9"><?php echo (int) $this->sitemap['urls']; ?></dd>

                        <dt class="col-sm-3"><?php echo Text::_('COM_NATIVESITEMAP_FILE_SIZE'); ?></dt>
                        <dd class="col-sm-9"><?php echo HTMLHelper::_('number.bytes', (int) $this->sitemap['size']); ?></dd>

                        <dt class="col-sm-3"><?php echo Text::_('COM_NATIVESITEMAP_FILE_PATH'); ?></dt>
                        <dd class="col-sm-9"><code><?php echo htmlspecialchars((string) $this->sitemap['path'], ENT_QUOTES, 'UTF-8'); ?></code></dd>

                        <dt class="col-sm-3"><?php echo Text::_('COM_NATIVESITEMAP_PUBLIC_URL'); ?></dt>
                        <dd class="col-sm-9">
                            <a href="<?php echo htmlspecialchars((string) $this->sitemap['url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                                <?php echo htmlspecialchars((string) $this->sitemap['url'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </dd>
                    </dl>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($diagnostic !== null) : ?>
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="h5"><?php echo Text::_('COM_NATIVESITEMAP_ARTICLES'); ?></h3>
                            <p class="display-6 mb-0"><?php echo (int) $diagnostic['articles']; ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="h5"><?php echo Text::_('COM_NATIVESITEMAP_CATEGORIES'); ?></h3>
                            <p class="display-6 mb-0"><?php echo (int) $diagnostic['categories']; ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="h5"><?php echo Text::_('COM_NATIVESITEMAP_MENUS'); ?></h3>
                            <p class="display-6 mb-0"><?php echo (int) $diagnostic['menus']; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="h5"><?php echo Text::_('COM_NATIVESITEMAP_RAW_TOTAL'); ?></h3>
                            <p class="display-6 mb-0"><?php echo (int) $diagnostic['rawTotal']; ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="h5"><?php echo Text::_('COM_NATIVESITEMAP_DUPLICATES_REMOVED'); ?></h3>
                            <p class="display-6 mb-0"><?php echo (int) $diagnostic['duplicates']; ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100 border-success">
                        <div class="card-body">
                            <h3 class="h5"><?php echo Text::_('COM_NATIVESITEMAP_FINAL_TOTAL'); ?></h3>
                            <p class="display-6 mb-0"><?php echo (int) $diagnostic['finalTotal']; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            $samples = [
                'articleUrls' => 'COM_NATIVESITEMAP_ARTICLE_URLS',
                'categoryUrls' => 'COM_NATIVESITEMAP_CATEGORY_URLS',
                'menuUrls' => 'COM_NATIVESITEMAP_MENU_URLS',
                'finalUrls' => 'COM_NATIVESITEMAP_FINAL_URLS',
            ];
            ?>

            <?php foreach ($samples as $key => $title) : ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="h5"><?php echo Text::_($title); ?></h3>
                        <?php if ($diagnostic[$key] === []) : ?>
                            <p class="mb-0"><?php echo Text::_('COM_NATIVESITEMAP_NO_URL_FOUND'); ?></p>
                        <?php else : ?>
                            <div
                                class="border rounded p-3 overflow-auto"
                                style="max-height: 22rem;"
                                tabindex="0"
                                aria-label="<?php echo Text::_($title); ?>"
                            >
                                <ul class="mb-0 ps-3">
                                    <?php foreach ($diagnostic[$key] as $url) : ?>
                                        <li><code><?php echo htmlspecialchars((string) $url, ENT_QUOTES, 'UTF-8'); ?></code></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="alert alert-info" role="status">
                <?php echo Text::_('COM_NATIVESITEMAP_DIAGNOSTIC_NOT_RUN'); ?>
            </div>
        <?php endif; ?>

<?php if (!empty($this->diagnostic['xmlPreview'])) : ?>
    <div class="card mt-4">
        <div class="card-header">
            <h2 class="h4 mb-0"><?php echo Text::_('COM_NATIVESITEMAP_XML_PREVIEW'); ?></h2>
        </div>
        <div class="card-body">
            <p><?php echo Text::_('COM_NATIVESITEMAP_XML_PREVIEW_DESC'); ?></p>
            <pre class="mb-0 border rounded p-3 overflow-auto" style="max-height: 22rem;" tabindex="0"><code><?php echo htmlspecialchars($this->diagnostic['xmlPreview'], ENT_QUOTES, 'UTF-8'); ?></code></pre>
        </div>
    </div>
<?php endif; ?>
        <footer class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 border-top pt-4 mt-4 mb-3">
            <div>
                <p class="mb-1 fw-semibold"><?php echo Text::_('COM_NATIVESITEMAP_FOOTER_PRODUCT'); ?></p>
                <p class="mb-0 text-body-secondary small">
                    <?php echo Text::sprintf('COM_NATIVESITEMAP_FOOTER_VERSION', '0.10.3'); ?>
                </p>
            </div>

            <a
                href="https://orkacs.com"
                target="_blank"
                rel="noopener noreferrer"
                class="d-inline-flex align-items-center text-decoration-none"
                aria-label="<?php echo Text::_('COM_NATIVESITEMAP_FOOTER_PUBLISHER_LINK'); ?>"
            >
                <img
                    src="<?php echo Uri::root(true); ?>/media/com_nativesitemap/images/orka-logo.png"
                    alt="ORKA CS"
                    width="255"
                    height="81"
                    style="width: 128px; height: auto;"
                >
            </a>
        </footer>
    </div>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
