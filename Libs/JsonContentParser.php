<?php
namespace TwentyTwoEstore\Libs;

class JsonContentParser implements IContentParser
{
	/**
	 * (non-PHPdoc)
	 * @see \TwentyTwoEstore\Libs\IContentParser::getContentFormat()
	 */
	public function getContentFormat ()
	{
		return 'json';
	}

	/**
	 * (non-PHPdoc)
	 * @see \TwentyTwoEstore\Libs\IContentParser::parse()
	 */
	public function parse ($input)
	{
		$json = json_decode($input, true);

		if (!isset($json)){
			throw new ExecuteException("\$input can't be decoded or the encoded data is deeper than the recursion limit");
		}

		return $json;
	}
}
