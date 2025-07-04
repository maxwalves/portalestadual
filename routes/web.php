<?php

use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRoleController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/users/roles', [UserRoleController::class, 'index'])->name('admin.users.roles');
    Route::put('/admin/users/{user}/roles', [UserRoleController::class, 'update'])->name('admin.users.roles.update');
    Route::post('/admin/users/roles/mass-update', [UserRoleController::class, 'massUpdate'])->name('admin.users.roles.mass-update');
    Route::put('/admin/users/{user}/toggle-active', [UserRoleController::class, 'toggleActive'])->name('admin.users.toggle-active');
    Route::post('/admin/users/sync-ldap', [UserRoleController::class, 'syncLdapUsers'])->name('admin.users.sync-ldap');
    Route::post('/admin/users/sync-cidades', [UserRoleController::class, 'syncCidades'])->name('admin.users.sync-cidades');
    Route::post('/admin/users/store', [UserRoleController::class, 'store'])->name('admin.users.store');
    Route::delete('/admin/users/{user}', [UserRoleController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('/api/check-email', [UserController::class, 'checkEmail'])->name('api.check-email');

    // Rotas de Impersonate
    Route::post('/admin/impersonate/{user}', [ImpersonateController::class, 'impersonate'])->name('admin.impersonate');
});

// Rota para o Dashboard de Obras
Route::get('/dashboard/obras', function () {
    return view('planejamento.demo_sistema_obras');
})->middleware(['auth', 'verified'])->name('dashboard.obras');

// Rota para depuração (apenas em ambiente de desenvolvimento)
if (app()->environment('local')) {
    Route::get('/debug/auth', function () {
        $output = [
            'auth_guards'      => config('auth.guards'),
            'auth_providers'   => config('auth.providers'),
            'ldap_config'      => config('ldap.connections'),
            'ldap_auth_config' => config('ldap_auth'),
        ];

        return response()->json($output);
    });
}

Route::middleware(['auth'])->group(function () {
    
    // ===== ROTAS COM CONTROLE DE ACESSO POR ORGANIZAÇÃO =====
    
    // Organizações - apenas admins podem gerenciar
    Route::middleware(['role:admin|admin_paranacidade'])->group(function () {
        Route::resource('organizacoes', \App\Http\Controllers\OrganizacaoController::class)->parameters([
            'organizacoes' => 'organizacao'
        ]);
    });

    // Termos de Adesão - controle por organização
    Route::middleware([\App\Http\Middleware\CheckOrgAccess::class.':termo_adesao'])->group(function () {
        Route::resource('termos-adesao', \App\Http\Controllers\TermoAdesaoController::class)->parameters([
            'termos-adesao' => 'termo'
        ]);
    });

    // Demandas - controle por organização
    Route::middleware([\App\Http\Middleware\CheckOrgAccess::class.':demanda'])->group(function () {
        Route::resource('cadastros-demanda-gms', \App\Http\Controllers\CadastroDemandaGmsController::class);
        Route::post('cadastros-demanda-gms/sync', [\App\Http\Controllers\CadastroDemandaGmsController::class, 'sync'])->name('cadastros-demanda-gms.sync');
        Route::resource('demandas', \App\Http\Controllers\DemandaController::class);
    });

    // Ações - controle por organização
    Route::middleware([\App\Http\Middleware\CheckOrgAccess::class.':acao'])->group(function () {
        Route::resource('acoes', \App\Http\Controllers\AcaoController::class)->parameters([
            'acoes' => 'acao'
        ]);
        // API para buscar cidades do Paraná
        Route::get('api/cidades-parana', [\App\Http\Controllers\AcaoController::class, 'getCidadesParana'])->name('api.cidades-parana');
    });
    
    // ===== ROTAS DO SISTEMA DE WORKFLOW =====
    
    Route::middleware([\App\Http\Middleware\CheckOrgAccess::class])->group(function () {
        // Workflow da Ação
        Route::get('workflow/acao/{acao}', [\App\Http\Controllers\ExecucaoEtapaController::class, 'workflow'])->name('workflow.acao');
        
        // Visualização detalhada de etapa
        Route::get('workflow/acao/{acao}/etapa/{etapaFluxo}', [\App\Http\Controllers\ExecucaoEtapaController::class, 'etapaDetalhada'])->name('workflow.etapa-detalhada');
        
        // Gerenciamento de Etapas
        Route::post('workflow/acao/{acao}/etapa/{etapaFluxo}/iniciar', [\App\Http\Controllers\ExecucaoEtapaController::class, 'iniciarEtapa'])->name('workflow.iniciar-etapa');
        Route::post('workflow/execucao/{execucao}/concluir', [\App\Http\Controllers\ExecucaoEtapaController::class, 'concluirEtapa'])->name('workflow.concluir-etapa');
        Route::post('workflow/execucao/{execucao}/alterar-status', [\App\Http\Controllers\ExecucaoEtapaController::class, 'alterarStatusEtapa'])->name('workflow.alterar-status-etapa');
        Route::get('workflow/execucao/{execucao}/opcoes-status', [\App\Http\Controllers\ExecucaoEtapaController::class, 'getOpcoesStatus'])->name('workflow.opcoes-status');
        
        // Gerenciamento de Documentos
        Route::post('workflow/execucao/{execucao}/documento', [\App\Http\Controllers\ExecucaoEtapaController::class, 'uploadDocumento'])->name('workflow.upload-documento');
        Route::post('workflow/documento/{documento}/aprovar', [\App\Http\Controllers\ExecucaoEtapaController::class, 'aprovarDocumento'])->name('workflow.aprovar-documento');
        Route::post('workflow/documento/{documento}/reprovar', [\App\Http\Controllers\ExecucaoEtapaController::class, 'reprovarDocumento'])->name('workflow.reprovar-documento');
        
        // Histórico e Relatórios
        Route::get('workflow/execucao/{execucao}/historico', [\App\Http\Controllers\ExecucaoEtapaController::class, 'historicoEtapa'])->name('workflow.historico-etapa');
        
        // Fluxo Condicional - Escolha de Próxima Etapa
        Route::get('workflow/execucao/{execucao}/opcoes-transicao', [\App\Http\Controllers\ExecucaoEtapaController::class, 'getOpcoesTransicao'])->name('workflow.opcoes-transicao');
        Route::post('workflow/execucao/{execucao}/executar-transicao', [\App\Http\Controllers\ExecucaoEtapaController::class, 'executarTransicaoEscolhida'])->name('workflow.executar-transicao');
        
        // Finalização e Reativação de Projeto
        Route::post('workflow/acao/{acao}/finalizar-completo', [\App\Http\Controllers\ExecucaoEtapaController::class, 'finalizarProjetoCompleto'])->name('workflow.finalizar-projeto-completo');
        Route::post('workflow/acao/{acao}/reativar-projeto', [\App\Http\Controllers\ExecucaoEtapaController::class, 'reativarProjetoFinalizado'])->name('workflow.reativar-projeto')->middleware('role:admin|admin_paranacidade');
    });
    
    // ===== ROTAS DE GESTÃO DE WORKFLOW - APENAS ADMINS PARANACIDADE E SISTEMA =====
    
    Route::middleware(['role:admin|admin_paranacidade'])->group(function () {
        Route::resource('tipos-fluxo', \App\Http\Controllers\TipoFluxoController::class)->parameters([
            'tipos-fluxo' => 'tipo_fluxo'
        ]);
        // Nova rota para gerenciar etapas de um tipo de fluxo
        Route::get('tipos-fluxo/{tipo_fluxo}/etapas', [\App\Http\Controllers\TipoFluxoController::class, 'etapas'])->name('tipos-fluxo.etapas');

        Route::resource('etapas-fluxo', \App\Http\Controllers\EtapaFluxoController::class)->parameters([
            'etapas-fluxo' => 'etapa_fluxo'
        ]);
        
        // APIs para configuração de status e transições
        Route::post('api/etapa-status-opcoes/salvar', [\App\Http\Controllers\EtapaStatusOpcaoController::class, 'salvar'])->name('api.etapa-status-opcoes.salvar');
        Route::get('api/transicoes-etapa/{transicao}', [\App\Http\Controllers\TransicaoEtapaController::class, 'show'])->name('api.transicoes-etapa.show');
        Route::post('api/transicoes-etapa', [\App\Http\Controllers\TransicaoEtapaController::class, 'store'])->name('api.transicoes-etapa.store');
        Route::put('api/transicoes-etapa/{transicao}', [\App\Http\Controllers\TransicaoEtapaController::class, 'update'])->name('api.transicoes-etapa.update');
        Route::delete('api/transicoes-etapa/{transicao}', [\App\Http\Controllers\TransicaoEtapaController::class, 'destroy'])->name('api.transicoes-etapa.destroy');
    });

    // ===== ROTAS DE GESTÃO DOCUMENTAL - APENAS ADMINS PARANACIDADE E SISTEMA =====
    
    Route::middleware(['role:admin|admin_paranacidade'])->group(function () {
        Route::resource('tipos-documento', \App\Http\Controllers\TipoDocumentoController::class)->parameters([
            'tipos-documento' => 'tipo_documento'
        ]);
        Route::post('tipos-documento/{tipo_documento}/toggle-ativo', [\App\Http\Controllers\TipoDocumentoController::class, 'toggleAtivo'])->name('tipos-documento.toggle-ativo');
        
        // Grupos de Exigência
        Route::resource('grupo-exigencias', \App\Http\Controllers\GrupoExigenciaController::class)->parameters([
            'grupo-exigencias' => 'grupoExigencia'
        ]);
        Route::patch('grupo-exigencias/{grupoExigencia}/toggle-ativo', [\App\Http\Controllers\GrupoExigenciaController::class, 'toggleAtivo'])->name('grupo-exigencias.toggle-ativo');
        Route::post('grupo-exigencias/{grupoExigencia}/duplicar', [\App\Http\Controllers\GrupoExigenciaController::class, 'duplicar'])->name('grupo-exigencias.duplicar');
        
        // Gerenciamento de templates nos grupos
        Route::get('grupo-exigencias/{grupoExigencia}/templates', [\App\Http\Controllers\GrupoExigenciaController::class, 'gerenciarTemplates'])->name('grupo-exigencias.templates');
        Route::post('grupo-exigencias/{grupoExigencia}/templates/vincular', [\App\Http\Controllers\GrupoExigenciaController::class, 'vincularTemplate'])->name('grupo-exigencias.vincular-template');
        Route::delete('grupo-exigencias/{grupoExigencia}/templates/{templateDocumento}', [\App\Http\Controllers\GrupoExigenciaController::class, 'desvincularTemplate'])->name('grupo-exigencias.desvincular-template');
        Route::patch('grupo-exigencias/{grupoExigencia}/templates/{templateDocumento}', [\App\Http\Controllers\GrupoExigenciaController::class, 'atualizarVinculo'])->name('grupo-exigencias.atualizar-vinculo');
        
        Route::resource('template-documentos', \App\Http\Controllers\TemplateDocumentoController::class)->parameters([
            'template-documentos' => 'template_documento'
        ]);
        Route::get('template-documentos/{template_documento}/download-modelo', [\App\Http\Controllers\TemplateDocumentoController::class, 'downloadModelo'])->name('template-documentos.download-modelo');
        Route::get('template-documentos/{template_documento}/download-exemplo', [\App\Http\Controllers\TemplateDocumentoController::class, 'downloadExemplo'])->name('template-documentos.download-exemplo');
        Route::post('template-documentos/reordenar', [\App\Http\Controllers\TemplateDocumentoController::class, 'reordenar'])->name('template-documentos.reordenar');
        
        // APIs de apoio para gestão documental
        Route::get('api/tipos-documento/ativos', [\App\Http\Controllers\TipoDocumentoController::class, 'apiTiposAtivos'])->name('api.tipos-documento.ativos');
        Route::post('api/tipos-documento/{tipo_documento}/verificar-compatibilidade', [\App\Http\Controllers\TipoDocumentoController::class, 'verificarCompatibilidade'])->name('api.tipos-documento.verificar-compatibilidade');
        Route::get('api/grupo-exigencias/ativos', [\App\Http\Controllers\GrupoExigenciaController::class, 'apiGruposAtivos'])->name('api.grupo-exigencias.ativos');
        Route::get('api/grupo-exigencias/{grupoExigencia}/estatisticas', [\App\Http\Controllers\GrupoExigenciaController::class, 'apiEstatisticas'])->name('api.grupo-exigencias.estatisticas');
    });

    // ===== ROTAS DE DOCUMENTOS - ACESSO BASEADO EM ORGANIZAÇÃO =====
    
    Route::middleware([\App\Http\Middleware\CheckOrgAccess::class])->group(function () {
        Route::resource('documentos', \App\Http\Controllers\DocumentoController::class);
        Route::get('documentos/{documento}/download', [\App\Http\Controllers\DocumentoController::class, 'download'])->name('documentos.download');
        Route::post('documentos/{documento}/aprovar', [\App\Http\Controllers\DocumentoController::class, 'aprovar'])->name('documentos.aprovar');
        Route::post('documentos/{documento}/reprovar', [\App\Http\Controllers\DocumentoController::class, 'reprovar'])->name('documentos.reprovar');
        Route::post('documentos/{documento}/nova-versao', [\App\Http\Controllers\DocumentoController::class, 'novaVersao'])->name('documentos.nova-versao');
    });
    
    // ===== ROTAS DO SISTEMA DE WORKFLOW - STATUS, NOTIFICAÇÕES E HISTÓRICO =====
    
    // Status - apenas admins podem gerenciar
    Route::middleware(['role:admin|admin_paranacidade'])->group(function () {
        Route::resource('status', \App\Http\Controllers\StatusController::class);
        Route::post('status/{status}/toggle-ativo', [\App\Http\Controllers\StatusController::class, 'toggleAtivo'])->name('status.toggle-ativo');
        // APIs do Sistema de Workflow
        Route::get('api/status/categoria', [\App\Http\Controllers\StatusController::class, 'apiPorCategoria'])->name('api.status.categoria');
        Route::get('api/status/etapa', [\App\Http\Controllers\StatusController::class, 'apiStatusEtapa'])->name('api.status.etapa');
    });
    
    // Notificações - acesso baseado em organização
    Route::middleware([\App\Http\Middleware\CheckOrgAccess::class])->group(function () {
        Route::resource('notificacoes', \App\Http\Controllers\NotificacaoController::class)->only(['index', 'show']);
        Route::post('notificacoes/{notificacao}/marcar-lida', [\App\Http\Controllers\NotificacaoController::class, 'marcarLida'])->name('notificacoes.marcar-lida');
        Route::post('notificacoes/marcar-todas-lidas', [\App\Http\Controllers\NotificacaoController::class, 'marcarTodasLidas'])->name('notificacoes.marcar-todas-lidas');
        Route::post('notificacoes/enviar', [\App\Http\Controllers\NotificacaoController::class, 'enviar'])->name('notificacoes.enviar');
        Route::get('notificacoes/{notificacao}/reenviar', [\App\Http\Controllers\NotificacaoController::class, 'reenviar'])->name('notificacoes.reenviar');
        // APIs de notificações
        Route::get('api/notificacoes/contadores', [\App\Http\Controllers\NotificacaoController::class, 'apiContadores'])->name('api.notificacoes.contadores');
        Route::get('api/notificacoes/recentes', [\App\Http\Controllers\NotificacaoController::class, 'apiRecentes'])->name('api.notificacoes.recentes');
    });
    
    // Histórico de Etapas - acesso baseado em organização
    Route::middleware([\App\Http\Middleware\CheckOrgAccess::class])->group(function () {
        Route::get('historico-etapas', [\App\Http\Controllers\HistoricoEtapaController::class, 'index'])->name('historico-etapas.index');
        Route::get('historico-etapas/{historico}', [\App\Http\Controllers\HistoricoEtapaController::class, 'show'])->name('historico-etapas.show');
        Route::get('historico-etapas/execucao/{execucaoEtapa}', [\App\Http\Controllers\HistoricoEtapaController::class, 'porExecucao'])->name('historico-etapas.por-execucao');
        Route::get('historico-etapas/usuario/{usuario}', [\App\Http\Controllers\HistoricoEtapaController::class, 'relatorioUsuario'])->name('historico-etapas.relatorio-usuario');
        Route::get('historico-etapas/exportar', [\App\Http\Controllers\HistoricoEtapaController::class, 'exportar'])->name('historico-etapas.exportar');
        // APIs de histórico
        Route::get('api/historico-etapas/timeline/{execucaoEtapa}', [\App\Http\Controllers\HistoricoEtapaController::class, 'apiTimeline'])->name('api.historico-etapas.timeline');
        Route::get('api/historico-etapas/estatisticas', [\App\Http\Controllers\HistoricoEtapaController::class, 'apiEstatisticas'])->name('api.historico-etapas.estatisticas');
    });
});

require __DIR__ . '/auth.php';
