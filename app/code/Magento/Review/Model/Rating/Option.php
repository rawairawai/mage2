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
namespace Magento\Review\Model\Rating;

/**
 * Rating option model
 *
 * @method \Magento\Review\Model\Resource\Rating\Option _getResource()
 * @method \Magento\Review\Model\Resource\Rating\Option getResource()
 * @method int getRatingId()
 * @method \Magento\Review\Model\Rating\Option setRatingId(int $value)
 * @method string getCode()
 * @method \Magento\Review\Model\Rating\Option setCode(string $value)
 * @method int getValue()
 * @method \Magento\Review\Model\Rating\Option setValue(int $value)
 * @method int getPosition()
 * @method \Magento\Review\Model\Rating\Option setPosition(int $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Option extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Review\Model\Resource\Rating\Option');
    }

    /**
     * @return $this
     */
    public function addVote()
    {
        $this->getResource()->addVote($this);
        return $this;
    }

    /**
     * @param mixed $id
     * @return $this
     */
    public function setId($id)
    {
        $this->setOptionId($id);
        return $this;
    }
}
