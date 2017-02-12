<?php
namespace TwentyTwoEstore\Libs;

final class ContentParserFactory
{
	public static function produce ($format)
	{
		switch ($format) {
			case 'xml':
				return new XmlContentParser();

			case 'json':
				return new JsonContentParser();

			default:
				throw new ExecuteException("Unsupported format '$format'");
		}
	}
}
