<?php
/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CalculationMigration\Business\Model\Calculator;

use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\Tax\Business\Model\AccruedTaxCalculatorInterface;
use Spryker\Zed\Tax\Business\TaxFacadeInterface;

class ItemTaxCalculator implements CalculatorInterface
{

    /**
     * @var \Spryker\Zed\Tax\Business\Model\AccruedTaxCalculatorInterface $accruedTaxCalculator
     */
    protected $accruedTaxCalculator;

    /**
     * @param \Spryker\Zed\Tax\Business\Model\AccruedTaxCalculatorInterface $accruedTaxCalculator
     */
    public function __construct(AccruedTaxCalculatorInterface $accruedTaxCalculator)
    {
        $this->accruedTaxCalculator = $accruedTaxCalculator;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return void
     */
    public function recalculate(QuoteTransfer $quoteTransfer)
    {
        $this->accruedTaxCalculator->resetRoundingErrorDelta();

        foreach ($quoteTransfer->getItems() as $itemTransfer) {

            $itemTransfer->setUnitTaxAmount(0);
            $itemTransfer->setSumTaxAmount(0);

            if (!$itemTransfer->getTaxRate()) {
                continue;
            }

            $unitTaxAmount = $this->calculateTaxAmount(
                $itemTransfer->getUnitGrossPrice(),
                $itemTransfer->getTaxRate()
            );

            $sumTaxAmount = $this->calculateTaxAmount(
                $itemTransfer->getSumGrossPrice(),
                $itemTransfer->getTaxRate()
            );

            $itemTransfer->setUnitTaxAmount((int)round($unitTaxAmount));
            $itemTransfer->setSumTaxAmount((int)round($sumTaxAmount));

            $itemTransfer->setUnitTaxAmount((int)round($itemTransfer->getUnitTaxAmount()));
            $itemTransfer->getSumTaxAmount((int)round($itemTransfer->getSumTaxAmount()));
        }
    }

    /**
     * @param int $price
     * @param float $taxRate
     *
     * @return float
     */
    protected function calculateTaxAmount($price, $taxRate)
    {
        return $this->accruedTaxCalculator->getTaxValueFromPrice($price, $taxRate);
    }

}
