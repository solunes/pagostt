<?php
namespace Solunes\Pagostt;

use Illuminate\Support\Facades\Facade;

class PagosttFacade extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'pagostt';
	}
}