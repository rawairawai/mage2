<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\RequireJs\Block\Html\Head;

use Magento\Theme\Block\Html\Head\AssetBlockInterface;

/**
 * Block responsible for including RequireJs config on the page
 */
class Config extends \Magento\Framework\View\Element\AbstractBlock implements AssetBlockInterface
{
    /**
     * @var \Magento\Framework\RequireJs\Config
     */
    private $config;

    /**
     * @var \Magento\RequireJs\Model\FileManager
     */
    private $fileManager;

    /**
     * @var \Magento\Framework\View\Asset\LocalInterface
     */
    private $asset;

    /**
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Framework\RequireJs\Config $config
     * @param \Magento\RequireJs\Model\FileManager $fileManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\RequireJs\Config $config,
        \Magento\RequireJs\Model\FileManager $fileManager,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->fileManager = $fileManager;
    }

    /**
     * Include RequireJs configuration as an asset on the page
     *
     * @return \Magento\Framework\View\Asset\LocalInterface
     */
    public function getAsset()
    {
        if (!$this->asset) {
            $this->asset = $this->fileManager->createRequireJsAsset();
        }
        return $this->asset;
    }

    /**
     * Include base RequireJs configuration necessary for working with Magento application
     *
     * @return string|void
     */
    protected function _toHtml()
    {
        return "<script type=\"text/javascript\">\n"
            . $this->config->getBaseConfig()
            . "</script>\n";
    }
}
