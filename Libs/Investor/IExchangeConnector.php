<?php
namespace TwentyTwoEstore\Libs\Investor;

interface IExchangeConnector
{
	/**
	 * Get exchange rates of specified Stock
	 * @param string $stock
	 * @param \DateTime $startDate
	 * @param \DateTime $endDate
	 * @return array
	 */
	public function getExchangeRates ($stock, \DateTime $startDate, \DateTime $endDate);
}
