<?php
namespace Secupay\Methods;

use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Translation\Translator;

class CreditDebitCardPaymentMethod extends AbstractPaymentMethod
{
    use Loggable;

    /**
     * Defines whether the payment method is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        //return true;
        if ($this->configRepo->get('Secupay.creditcard_active') === "true") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns the payment method's name that is displayed to the customer.
     *
     * @return string
     */
    public function getName(string $lang = 'de'): string
    {
        return 'Secupay Credit / Debit Card';
        /** @var Translator $translator */
        $translator = pluginApp(Translator::class);

        $title = $translator->trans('secupay::Payment.CreditDebitCardTitle', [], $lang);
        if (! empty($title)) {
            return $title;
        } else {
            return 'Secupay Credit / Debit Card';
        }
    }

    /**
     * Returns the fee that is applied when this payment method is used.
     *
     * @return float
     */
    public function getFee(): float
    {
        $fee = $this->configRepo->get('secupay.creditcard_fee');
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

        $title = $translator->trans('secupay::Payment.CreditDebitCardDescription', [], $lang);
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

        $iconUrl = $translator->trans('secupay::Payment.CreditDebitCardIconUrl', [], $lang);
        if (!empty($iconUrl)) {
            return $iconUrl;
        } else {
            return $this->getImagePath('credit-debit-card.svg');
        }
    }
}
