<?php
namespace PaysonAB\PaysonCheckout2\Model\Config\Source;
/**
 * Class CheckoutTheme
 *
 * @package PaysonAB\PaysonCheckout2\Model\Config\Source
 */
class CheckoutTheme implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 'White', 'label' => __('White')],['value' => 'Blue', 'label' => __('Blue')],['value' => 'Gray', 'label' => __('Gray')],['value' => 'GrayTextLogos', 'label' => __('GrayTextLogos')],['value' => 'BlueTextLogos', 'label' => __('BlueTextLogos')],['value' => 'WhiteTextLogos', 'label' => __('WhiteTextLogos')],['value' => 'GrayNoFooter', 'label' => __('GrayNoFooter')],['value' => 'BlueNoFooter', 'label' => __('BlueNoFooter')],['value' => 'WhiteNoFooter', 'label' => __('WhiteNoFooter')]];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return ['White' => __('White'),'Blue' => __('Blue'),'Gray' => __('Gray'),'GrayTextLogos' => __('GrayTextLogos'),'BlueTextLogos' => __('BlueTextLogos'),'WhiteTextLogos' => __('WhiteTextLogos'),'GrayNoFooter' => __('GrayNoFooter'),'BlueNoFooter' => __('BlueNoFooter'),'WhiteNoFooter' => __('WhiteNoFooter')];
    }
}
