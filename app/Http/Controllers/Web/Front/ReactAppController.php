<?php
/*
 * Chapa Livre — Serve a SPA React para todas as rotas de usuário.
 * O React Router cuida da navegação interna (/perfil, /meus-anuncios, etc.)
 */

namespace App\Http\Controllers\Web\Front;

use App\Http\Controllers\Controller;

class ReactAppController extends Controller
{
    /**
     * Serve o index.html do bundle React para qualquer rota de usuário.
     * O React Router gerencia o roteamento do lado do cliente.
     */
    public function serve()
    {
        $indexPath = public_path('react/index.html');

        if (!file_exists($indexPath)) {
            abort(503, 'React app não encontrado. Execute: cd react-app && npm run build');
        }

        return response()->file($indexPath, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }
}
