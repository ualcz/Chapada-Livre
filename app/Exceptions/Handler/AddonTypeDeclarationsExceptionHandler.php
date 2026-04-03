<?php

namespace App\Exceptions\Handler;

use App\Exceptions\Handler\Addon\OutToDateAddon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/*
 * Check if there are no problems in an addon code
 */

trait AddonTypeDeclarationsExceptionHandler
{
	use OutToDateAddon;
	
	/**
	 * @param \Throwable $e
	 * @return bool
	 */
	protected function isAddonTypeDeclarationsException(\Throwable $e): bool
	{
		// Check if there are no problems in an addon code
		return (
			method_exists($e, 'getFile') && method_exists($e, 'getMessage')
			&& !empty($e->getFile()) && !empty($e->getMessage())
			&& str_contains($e->getFile(), '/extras/addons/')
			&& str_contains($e->getMessage(), 'extras\addons\\')
			&& str_contains($e->getMessage(), 'must be compatible')
		);
	}
	
	/**
	 * @param \Throwable $e
	 * @param \Illuminate\Http\Request $request
	 * @return false|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
	 */
	protected function responseAddonTypeDeclarationsException(\Throwable $e, Request $request): Response|false|JsonResponse
	{
		$message = $this->getAddonTypeDeclarationsExceptionMessage($e, $request);
		
		return $this->responseCustomError($e, $request, $message);
	}
	
	// PRIVATE
	
	/**
	 * @param \Throwable $e
	 * @param \Illuminate\Http\Request $request
	 * @return string|null
	 */
	private function getAddonTypeDeclarationsExceptionMessage(\Throwable $e, Request $request): ?string
	{
		$message = $e->getMessage();
		
		return !empty($message) ? $this->tryToArchiveAddon($message) : null;
	}
}
