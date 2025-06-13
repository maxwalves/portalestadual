<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOrgAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $resource = null): Response
    {
        $user = $request->user();
        
        if (!$user) {
            abort(401, 'Usuário não autenticado.');
        }

        // Admin de sistema sempre tem acesso
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        // Admin Paranacidade tem acesso a tudo relacionado a obras
        if ($user->hasRole('admin_paranacidade')) {
            return $next($request);
        }

        // Técnico Paranacidade pode visualizar tudo, mas não editar
        if ($user->hasRole('tecnico_paranacidade')) {
            // Permitir apenas métodos de visualização
            if (in_array($request->method(), ['GET', 'HEAD'])) {
                return $next($request);
            }
            abort(403, 'Técnicos do Paranacidade podem apenas visualizar informações.');
        }

        // Para usuários de secretaria, verificar se têm organização
        if (!$user->organizacao_id) {
            abort(403, 'Usuário deve estar vinculado a uma organização.');
        }

        // Admin de Secretaria - acesso completo à sua secretaria
        if ($user->hasRole('admin_secretaria')) {
            return $this->checkSecretariaAccess($request, $next, $user, $resource);
        }

        // Técnico de Secretaria - apenas visualização da sua secretaria
        if ($user->hasRole('tecnico_secretaria')) {
            // Permitir apenas métodos de visualização
            if (in_array($request->method(), ['GET', 'HEAD'])) {
                return $this->checkSecretariaAccess($request, $next, $user, $resource);
            }
            abort(403, 'Técnicos de secretaria podem apenas visualizar informações.');
        }

        // Se chegou até aqui, não tem permissão
        abort(403, 'Acesso não autorizado para este recurso.');
    }

    /**
     * Verificar acesso específico da secretaria
     */
    private function checkSecretariaAccess(Request $request, Closure $next, $user, ?string $resource): Response
    {
        // Se não há recurso específico para verificar, permitir acesso
        if (!$resource) {
            return $next($request);
        }

        // Verificar acesso baseado no recurso
        switch ($resource) {
            case 'organizacao':
                return $this->checkOrganizacaoOwnership($request, $next, $user);
                
            case 'termo_adesao':
                return $this->checkTermoAdesaoOwnership($request, $next, $user);
                
            case 'demanda':
                return $this->checkDemandaOwnership($request, $next, $user);
                
            case 'acao':
                return $this->checkAcaoOwnership($request, $next, $user);
                
            default:
                return $next($request);
        }
    }

    /**
     * Verificar se o usuário pode acessar a organização
     */
    private function checkOrganizacaoOwnership(Request $request, Closure $next, $user): Response
    {
        $organizacao = $request->route('organizacao');
        $organizacaoId = $organizacao ? $organizacao->id : $organizacao;
        
        if ($organizacaoId && $organizacaoId != $user->organizacao_id) {
            abort(403, 'Você só pode acessar dados da sua própria organização.');
        }
        
        return $next($request);
    }

    /**
     * Verificar se o usuário pode acessar o termo de adesão
     */
    private function checkTermoAdesaoOwnership(Request $request, Closure $next, $user): Response
    {
        $termo = $request->route('termo');
        
        if ($termo && $termo->organizacao_id != $user->organizacao_id) {
            abort(403, 'Você só pode acessar termos de adesão da sua organização.');
        }
        
        return $next($request);
    }

    /**
     * Verificar se o usuário pode acessar a demanda
     */
    private function checkDemandaOwnership(Request $request, Closure $next, $user): Response
    {
        $demanda = $request->route('demanda');
        
        if ($demanda) {
            // Carregar termo de adesão se não estiver carregado
            if (!$demanda->relationLoaded('termoAdesao')) {
                $demanda->load('termoAdesao');
            }
            
            if ($demanda->termoAdesao->organizacao_id != $user->organizacao_id) {
                abort(403, 'Você só pode acessar demandas da sua organização.');
            }
        }
        
        return $next($request);
    }

    /**
     * Verificar se o usuário pode acessar a ação
     */
    private function checkAcaoOwnership(Request $request, Closure $next, $user): Response
    {
        $acao = $request->route('acao');
        
        if ($acao) {
            // Carregar demanda e termo se não estiverem carregados
            if (!$acao->relationLoaded('demanda.termoAdesao')) {
                $acao->load('demanda.termoAdesao');
            }
            
            if ($acao->demanda->termoAdesao->organizacao_id != $user->organizacao_id) {
                abort(403, 'Você só pode acessar ações da sua organização.');
            }
        }
        
        return $next($request);
    }
}
