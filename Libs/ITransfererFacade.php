<?php
namespace TwentyTwoEstore\Libs;

interface ITransfererFacade
{
	/**
	 * Set the url of host
	 * @param string $host
	 */
	public function __construct ($host);

	/**
	 * Return the response for specified $query
	 * @param string $query
	 * @param \closure $validateRespnseDelegate
	 * @return string
	 */
	public function getResponse ($query, \closure $validateRespnseDelegate = null);
}
