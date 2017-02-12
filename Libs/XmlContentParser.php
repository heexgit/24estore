<?php
namespace TwentyTwoEstore\Libs;

class XmlContentParser implements IContentParser
{
	/**
	 * (non-PHPdoc)
	 * @see \TwentyTwoEstore\Libs\IContentParser::getContentFormat()
	 */
	public function getContentFormat ()
	{
		return 'xml';
	}

	/**
	 * (non-PHPdoc)
	 * @see \TwentyTwoEstore\Libs\IContentParser::parse()
	 */
	public function parse ($input)
	{
		$xml = new \SimpleXMLElement($input);

		if (!isset($xml)){
			throw new ExecuteException("\$input can't be decoded or the encoded data is deeper than the recursion limit");
		}

		return $xml;
	}
}
