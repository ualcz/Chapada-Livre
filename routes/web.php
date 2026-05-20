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

use App\Http\Controllers\Web\Front\ReactAppController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::
		namespace('Setup')->group(__DIR__ . '/web/setup.php');

Route::middleware(['installed'])
	->group(function () {
		$usarReact = true;

		// Compartilha a decisão com o resto do sistema
		config(['app.usar_react' => $usarReact]);

		$authBasePath = urlGen()->getAuthBasePath();
		$adminBasePath = urlGen()->getAdminBasePath();

		// Rotas de Sistema (Sempre ativas)
		Route::namespace('Admin')->prefix($adminBasePath)->group(__DIR__ . '/web/admin.php');

		// Rotas de Autenticação e Front-end (Condicionais ou Híbridas)
		Route::namespace('Auth')->prefix($authBasePath)->group(__DIR__ . '/web/auth.php');
		Route::namespace('Front')->group(__DIR__ . '/web/front.php');

		/*
		 * SEO: redireciona URLs antigas de categoria
		 * Ex.: /buscar?cat=97  ->  /category/{slug}
		 *
		 * Observação: quando o usuário navega dentro da SPA, esse redirect não é acionado (não há request ao servidor).
		 * Por isso, existe também uma normalização equivalente no frontend (Search.tsx).
		 */
		Route::get('buscar', function (Request $request) {
			$cat = $request->query('cat');

			if (!empty($cat) && is_string($cat) && ctype_digit($cat)) {
				$category = \App\Models\Category::find((int) $cat);
				if ($category && !empty($category->slug)) {
					return redirect()->to('/category/' . $category->slug, 301);
				}
			}

			return app(ReactAppController::class)->serve($request);
		})->name('buscar');

		// Se o React estiver ativo, ele assume como "fallback" para qualquer rota não tratada acima
		// (Isso permite que o React Router cuide de tudo no front-end)
		if ($usarReact) {
			// Rotas nomeadas para evitar erros em redirecionamentos do sistema (ex: middleware auth)
			Route::get('/', [ReactAppController::class, 'serve'])->name('homepage');

			// Auth aliases
			Route::get('login', [ReactAppController::class, 'serve'])->name('login');
			Route::get('login', [ReactAppController::class, 'serve'])->name('auth.login.showForm');
			Route::get('cadastro', [ReactAppController::class, 'serve'])->name('register');
			Route::get('cadastro', [ReactAppController::class, 'serve'])->name('auth.register.showForm');
			Route::get('esqueci-senha', [ReactAppController::class, 'serve'])->name('auth.forgot.password.showForm');

			// ============================================================
			// ADMIN SESSION BRIDGES (React → Web)
			// ATENÇÃO: estas rotas DEVEM ficar registradas aqui, antes do
			// Route::fallback, caso contrário o ReactAppController as
			// intercepta e o React Router exibe erro 404.
			// ============================================================
			Route::get('admin-login-bridge', function () {
				$token = request()->query('token');
				if ($token) {
					$personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
					if ($personalAccessToken && $personalAccessToken->tokenable) {
						$user = $personalAccessToken->tokenable;
						if (doesUserHaveStaffPermission($user)) {
							auth('web')->login($user);
							request()->session()->regenerate();

							if (request()->wantsJson() || request()->ajax()) {
								return response()->json([
									'success'      => true,
									'redirect_url' => urlGen()->adminUrl(),
								]);
							}

							return redirect(urlGen()->adminUrl());
						}
					}
				}

				if (request()->wantsJson() || request()->ajax()) {
					return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
				}

				return redirect('/login');
			});

			Route::get('admin-logout-bridge', function () {
				if (auth('web')->check()) {
					auth('web')->logout();
					request()->session()->invalidate();
					request()->session()->regenerateToken();
				}
				return response()->json(['success' => true]);
			});

			Route::fallback([ReactAppController::class, 'serve'])->name('react.app');
		}
	});

// Social Login - Redundância para garantir que rotas hardcoded (React) ou salvas (Google) sempre funcionem
Route::
		namespace('Auth')->group(function () {
			Route::get('auth/connect/{provider}', [\App\Http\Controllers\Web\Auth\SocialController::class, 'redirectToProvider']);
			Route::get('auth/connect/{provider}/callback', [\App\Http\Controllers\Web\Auth\SocialController::class, 'handleProviderCallback']);
		});
