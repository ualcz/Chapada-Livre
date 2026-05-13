<?php
/*
 * LaraClassifier - Classified Ads Web Application
 * Copyright (c) BeDigit. All Rights Reserved
 *
 * Website: https://laraclassifier.com
 * Author: Mayeul Akpovi (BeDigit - https://bedigit.com)
 *
 * LICENSE
 * -------
 * This software is provided under a license agreement and may only be used or copied
 * in accordance with its terms, including the inclusion of the above copyright notice.
 * As this software is sold exclusively on CodeCanyon,
 * please review the full license details here: https://codecanyon.net/licenses/standard
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Jaybizzle\CrawlerDetect\CrawlerDetect;
use Symfony\Component\HttpFoundation\Response;

class BlockBots
{
	/**
	 * Block the request if it's from a bot/crawler
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function handle(Request $request, Closure $next): Response
	{
		$userAgent = strtolower($request->userAgent() ?? '');
		
		// Prioridade Absoluta: Permite Google, Bing e outros robôs conhecidos em qualquer rota
		if (str_contains($userAgent, 'google') 
			|| str_contains($userAgent, 'bot') 
			|| str_contains($userAgent, 'crawl') 
			|| str_contains($userAgent, 'spider') 
			|| str_contains($userAgent, 'bing')) {
			return $next($request);
		}

		$crawlerDetect = new CrawlerDetect();
		$errorMessage = 'Bot access is not allowed';
		
		// Always block bot/crawler requests to the Admin Panel or to the API
		if (isAdminPanelRoute() || isApiRoute()) {
			if ($crawlerDetect->isCrawler()) {
				abort(403, $errorMessage);
			}
			
			if (isAdminPanelRoute()) {
				// Check for empty user agent (common for bots)
				if (empty($userAgent)) {
					abort(403, 'Access denied for unknown useragent');
				}
			}
		}
		
		// Check if bot blocking is enabled in settings
		$blockingEnabled = config('settings.seo.block_bots_enabled', '0');
		
		// If bot blocking is not enabled, allow all requests
		if ($blockingEnabled != '1') {
			return $next($request);
		}
		
		if ($crawlerDetect->isCrawler()) {
			// Get blocked bots list from settings
			$blockedBotsString = config('settings.seo.blocked_bots', '');
			$blockedBots = $this->parseBotsString($blockedBotsString);
			
			// Check if the bot is explicitly blocked
			foreach ($blockedBots as $blockedBot) {
				if (str_contains($userAgent, $blockedBot)) {
					abort(403, $errorMessage);
				}
			}
			
			// Get the allowed bots list from settings
			$allowedBotsString = config('settings.seo.allowed_bots', '');
			$allowedBots = $this->parseBotsString($allowedBotsString);
			
			// Check if it's an allowed bot
			foreach ($allowedBots as $allowedBot) {
				if (str_contains($userAgent, $allowedBot)) {
					return $next($request);
				}
			}
			
			// Bot is not in the allowed list, block it
			abort(403, $errorMessage);
		}
		
		return $next($request);
	}
	
	/**
	 * Parse bots string from settings (one bot per line) into array
	 *
	 * @param string $botsString
	 * @return array
	 */
	private function parseBotsString(string $botsString): array
	{
		if (empty($botsString)) {
			return [];
		}
		
		// Split by new lines and filter empty values
		$bots = preg_split('/\r\n|\r|\n/', $botsString);
		$bots = array_filter($bots, fn ($bot) => !empty(trim($bot)));
		
		// Convert to lowercase and trim whitespace
		return array_map(fn ($bot) => strtolower(trim($bot)), $bots);
	}
}
