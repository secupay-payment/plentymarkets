<?php
namespace Secupay\Methods;

use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Translation\Translator;

class PostFinancePayPaymentMethod extends AbstractPaymentMethod
{
    use Loggable;

    /**
     * Defines whether the payment method is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        if ($this->configRepo->get('secupay.postfinancepay_active') == "true") {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Check if this payment method should be searchable in the back end.
     *
     * @return bool
     */
    public function isBackendSearchable(): bool
    {
        return true;
    }
    
    /**
     * Check if this payment method should be active in the back end.
     *
     * @return bool
     */
    public function isBackendActive(): bool
    {
        return true;
    }

    /**
     * Returns the payment method's name that is displayed to the customer.
     *
     * @return string
     */
    public function getName(string $lang = 'de'): string
    {
        /** @var Translator $translator */
        $translator = pluginApp(Translator::class);

        $title = $translator->trans('secupay::Payment.PostfinancePayTitle', [], $lang);
        if (! empty($title)) {
            return $title;
        } else {
            return 'Postfinance Pay';
        }
    }

    /**
     * Returns the fee that is applied when this payment method is used.
     *
     * @return float
     */
    public function getFee(): float
    {
        $fee = $this->configRepo->get('secupay.postfinancepay_fee');
        if (! empty($fee)) {
            return (float) $fee;
        } else {
            return 0.00;
        }
    }

    /**
     * Returns the payment method's description.
     *
     * @return string
     */
    public function getDescription(string $lang = 'de'): string
    {
        /** @var Translator $translator */
        $translator = pluginApp(Translator::class);

        $title = $translator->trans('secupay::Payment.PostfinancePayDescription', [], $lang);
        if (! empty($title)) {
            return $title;
        } else {
            return '';
        }
    }

    /**
     * Returns the payment method's description.
     *
     * @return string
     */
    public function getIcon(string $lang = 'de'): string
    {
        /** @var Translator $translator */
        $translator = pluginApp(Translator::class);

        $iconUrl = $translator->trans('secupay::Payment.PostfinancePayIconUrl', [], $lang);
        if (!empty($iconUrl)) {
            return $iconUrl;
        } else {
            return $this->getImagePath('pf_pay.svg');
        }
    }
}
