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

        // Verificar se é uma rota de workflow - tratamento especial
        if ($this->isWorkflowRoute($request)) {
            return $this->checkWorkflowAccess($request, $next, $user);
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
     * Verificar se é uma rota de workflow
     */
    private function isWorkflowRoute(Request $request): bool
    {
        return str_starts_with($request->route()->getName() ?? '', 'workflow.');
    }

    /**
     * Verificar acesso específico para rotas de workflow
     */
    private function checkWorkflowAccess(Request $request, Closure $next, $user): Response
    {
        // Para rotas de workflow, verificar acesso baseado no recurso específico
        $routeName = $request->route()->getName();
        
        // Se há execução na rota, verificar acesso específico
        $execucao = $request->route('execucao');
        if ($execucao) {
            return $this->checkExecucaoEtapaAccess($request, $next, $user, $execucao);
        }

        // Se há ação na rota, verificar acesso à ação
        $acao = $request->route('acao');
        if ($acao) {
            return $this->checkAcaoWorkflowAccess($request, $next, $user, $acao);
        }

        // Para outras rotas de workflow sem parâmetros específicos
        return $next($request);
    }

    /**
     * Verificar acesso a execução de etapa
     */
    private function checkExecucaoEtapaAccess(Request $request, Closure $next, $user, $execucao): Response
    {
        // Carregar relacionamentos necessários
        if (!$execucao->relationLoaded('etapaFluxo')) {
            $execucao->load('etapaFluxo');
        }
        if (!$execucao->relationLoaded('acao.demanda.termoAdesao')) {
            $execucao->load('acao.demanda.termoAdesao');
        }

        $userOrgId = $user->organizacao_id;
        $etapaFluxo = $execucao->etapaFluxo;
        $organizacaoAcao = $execucao->acao->demanda->termoAdesao->organizacao_id;

        // Permitir acesso se for:
        // 1. Organização que criou a ação (solicitante da ação)
        // 2. Organização solicitante da etapa
        // 3. Organização executora da etapa
        $temAcesso = ($userOrgId === $organizacaoAcao) ||
                     ($userOrgId === $etapaFluxo->organizacao_solicitante_id) ||
                     ($userOrgId === $etapaFluxo->organizacao_executora_id);

        if (!$temAcesso) {
            \Log::warning('Acesso negado para execução de etapa', [
                'user_id' => $user->id,
                'user_org_id' => $userOrgId,
                'execucao_id' => $execucao->id,
                'org_acao' => $organizacaoAcao,
                'org_solicitante_etapa' => $etapaFluxo->organizacao_solicitante_id,
                'org_executora_etapa' => $etapaFluxo->organizacao_executora_id,
                'route' => $request->route()->getName()
            ]);
            abort(403, 'Você não tem acesso a esta execução de etapa.');
        }

        \Log::info('Acesso concedido para execução de etapa', [
            'user_id' => $user->id,
            'user_org_id' => $userOrgId,
            'execucao_id' => $execucao->id,
            'route' => $request->route()->getName()
        ]);

        return $next($request);
    }

    /**
     * Verificar acesso a ação no contexto de workflow
     */
    private function checkAcaoWorkflowAccess(Request $request, Closure $next, $user, $acao): Response
    {
        // Carregar demanda e termo se não estiverem carregados
        if (!$acao->relationLoaded('demanda.termoAdesao')) {
            $acao->load('demanda.termoAdesao');
        }

        $userOrgId = $user->organizacao_id;
        $organizacaoAcao = $acao->demanda->termoAdesao->organizacao_id;

        // No workflow, também precisamos verificar se o usuário está envolvido nas etapas
        // Por simplicidade, vamos permitir acesso se for da organização da ação
        // ou se estiver envolvido em alguma etapa do fluxo
        if ($userOrgId !== $organizacaoAcao) {
            // Verificar se está envolvido em alguma etapa
            $estaNasEtapas = \App\Models\EtapaFluxo::where('tipo_fluxo_id', $acao->tipo_fluxo_id)
                ->where(function ($query) use ($userOrgId) {
                    $query->where('organizacao_solicitante_id', $userOrgId)
                          ->orWhere('organizacao_executora_id', $userOrgId);
                })
                ->exists();

            if (!$estaNasEtapas) {
                abort(403, 'Você não tem acesso a esta ação.');
            }
        }

        return $next($request);
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
