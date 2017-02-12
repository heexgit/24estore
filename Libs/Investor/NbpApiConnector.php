<?php
namespace TwentyTwoEstore\Libs\Investor;

use TwentyTwoEstore\Libs;
use Decimal\Decimal;

class NbpApiConnector implements IExchangeConnector
{
	const API_URL = 'http://api.nbp.pl/api/';

	private $methods = array(
		'gold' => 'cenyzlota'
	);

	/**
	 * Max days amount in API request
	 * @var int
	 */
	const PACKET_SIZE = 93;

	/**
	 * Max days amount in API request
	 * @var int
	 */
	private $packetSize;

	/**
	 * DateTimeZone of dates of API response
	 * @var \DateTimeZone
	 */
	private $apiTimezone;

	/**
	 * @var Libs\IContentParser
	 */
	private $contentParser;

	public function __construct (Libs\IContentParser $contentParser)
	{
		$this->contentParser = $contentParser;
		// assume the NBP is in Europe/Warsaw timezone
		$this->apiTimezone = new \DateTimeZone('Europe/Warsaw');
		$this->packetSize(self::PACKET_SIZE);
	}

	/**
	 * (non-PHPdoc)
	 * @see \TwentyTwoEstore\Libs\IExchangeConnector::getGoldExchangeRates()
	 */
	public function getGoldExchangeRates (\DateTime $startDate, \DateTime $endDate)
	{
		$this->validateDays($startDate, $endDate);
		$this->normalizeDate($startDate);
		$this->normalizeDate($endDate);

		$curl = new Libs\CurlTransfererFacade(self::API_URL);

		$bulkSize = new \DateInterval('P'.$this->packetSize().'D');
		$stepSize = new \DateInterval('P'.($this->packetSize() + 1).'D');

		$contentParserFormat = $this->contentParser->getContentFormat();
		$echangeRates = array();

		for ($fromDate = clone $startDate; $fromDate < $endDate; $fromDate->add($stepSize)) {
			$toDate = clone $fromDate;
			$toDate->add($bulkSize);
			if ($toDate > $endDate){
				$toDate = $endDate;
			}

			$erroInfo = null;
			$content = $curl->getResponse(
					sprintf('%s/%s/%s/?format=%s', $this->methods['gold'], $this->getDateString($fromDate), $this->getDateString($toDate), $contentParserFormat),

					// response validation method
					function ($response) use ($fromDate, $toDate, &$erroInfo){
						$first4 = substr($response, 0, 4);
						switch ($first4) {
							case '400 ':
							case '404 ':
								$erroInfo = $response.PHP_EOL.
									sprintf("startDate='%s' endDate='%s'", strftime("%c", $fromDate->getTimestamp()), strftime("%x", $toDate->getTimestamp()));
								break;

							case '500 ':
								throw new Libs\ExecuteException($response);
						}
					}
				);

			// response isn't valid
			if (isset($erroInfo)){
				// place here a not fatal error register
			}

			// response is valid
			else{
				$parsedContent = $this->contentParser->parse($content);
				switch ($contentParserFormat)
				{
					case 'json':
						$stepResult = $this->normalizeJsonContent($parsedContent);
						break;

					case 'xml':
						$stepResult = $this->normalizeXmlContent($parsedContent);
						break;

					default:
						throw new Libs\ExecuteException("'$contentParserFormat' is not supported");
				}

				$echangeRates = array_merge($echangeRates, $stepResult);
			}
		};

		return $echangeRates;
	}

	/**
	 * Set or Get the packetSize
	 * @param int $value
	 * @return int
	 * @throws Libs\ExecuteException
	 */
	public function packetSize ($value = null)
	{
		if (isset($value)) {
			if (!is_int($value)){
				throw new Libs\ExecuteException("\$value has to be int; provided type: ".gettype($value));
			}
			$this->packetSize = $value;
		}
		return $this->packetSize;
	}

	/**
	 * Convert a JSON content to ExchangeRate[]
	 * @param array $parsedContent
	 * @return array
	 */
	private function normalizeJsonContent (array $parsedContent)
	{
		$result = array_map(function (array $item) {
			$resultItem = new ExchangeRate(new \DateTime($item['data'], $this->apiTimezone), new Decimal($item['cena']));
			return $resultItem;
		}, $parsedContent);
		return $result;
	}

	/**
	 * Convert a XML content to ExchangeRate[]
	 * @param \SimpleXMLElement $parsedContent
	 * @return array
	 */
	private function normalizeXmlContent (\SimpleXMLElement $parsedContent)
	{
		$result = array();

		foreach ($parsedContent as $item){
			$resultItem = new ExchangeRate(new \DateTime("$item->Data", $this->apiTimezone), new Decimal("$item->Cena"));
			$result[] = $resultItem;
		}
		return $result;
	}

	/**
	 * @param \DateTime $date
	 * @throws Libs\ExecuteException
	 */
	private function validateDays (\DateTime $startDate, \DateTime $endDate)
	{
		$this->validateDate($startDate);
		$this->validateDate($endDate);

		$interval = $endDate->diff($startDate);

		if ($interval->invert === 0){
			throw new Libs\ExecuteException("\$startDate can't be earlier than \$endDate");
		}
	}

	/**
	 * @param \DateTime $date
	 * @throws Libs\ExecuteException
	 */
	private function validateDate (\DateTime $date)
	{
		$now = new \DateTime();
		$interval = $now->diff($date);

		if ($interval->invert === 0 && $interval->days > 0){
			throw new Libs\ExecuteException("\$date can't be a future date");
		}
	}

	/**
	 * @param \DateTime $date
	 */
	private function normalizeDate (\DateTime $date)
	{
		// make sure the date is in proper timezone
		$date->setTimezone($this->apiTimezone);
	}

	/**
	 * @param \DateTime $date
	 */
	private function getDateString (\DateTime $date)
	{
		return $date->format('Y-m-d');
	}
}
