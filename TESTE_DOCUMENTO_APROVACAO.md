# Teste de Aprovação de Documentos

## Comandos para Testar no Tinker

### 1. Verificar um documento existente
```php
php artisan tinker

// Buscar um documento para teste
$documento = App\Models\Documento::where('status_documento', 'PENDENTE')->first();
if (!$documento) {
    $documento = App\Models\Documento::first();
}

// Verificar dados atuais
echo "ID: " . $documento->id . "\n";
echo "Status: " . $documento->status_documento . "\n";
echo "Observações atuais: '" . $documento->observacoes . "'\n";
```

### 2. Teste direto de atualização
```php
// Teste 1: Atualizar apenas status
$documento->update(['status_documento' => 'APROVADO']);
$documento->refresh();
echo "Após update status - Status: " . $documento->status_documento . "\n";

// Teste 2: Atualizar apenas observações  
$documento->update(['observacoes' => 'Teste de observação']);
$documento->refresh();
echo "Após update observações - Observações: '" . $documento->observacoes . "'\n";

// Teste 3: Atualizar tudo junto (como no controller)
$documento->update([
    'status_documento' => 'APROVADO',
    'data_aprovacao' => now(),
    'usuario_aprovacao_id' => 1,
    'observacoes' => 'Documento aprovado com comentário de teste',
    'motivo_reprovacao' => null,
    'updated_by' => 1
]);

$documento->refresh();
echo "Após update completo:\n";
echo "- Status: " . $documento->status_documento . "\n";
echo "- Observações: '" . $documento->observacoes . "'\n";
echo "- Data aprovação: " . $documento->data_aprovacao . "\n";
echo "- Usuário aprovação: " . $documento->usuario_aprovacao_id . "\n";
```

### 3. Verificar diretamente no banco
```php
// Consulta direta no banco
$resultado = DB::table('documentos')->where('id', $documento->id)->first();
echo "Banco direto - Observações: '" . $resultado->observacoes . "'\n";
echo "Banco direto - Status: " . $resultado->status_documento . "\n";
```

### 4. Teste com caracteres especiais
```php
$observacaoEspecial = 'Documento aprovado com acentos: ção, ã, é. E quebra\nde linha.';
$documento->update(['observacoes' => $observacaoEspecial]);
$documento->refresh();
echo "Observação especial: '" . $documento->observacoes . "'\n";
```

### 5. Teste do fillable
```php
// Verificar se observacoes está no fillable
$fillable = (new App\Models\Documento)->getFillable();
echo "Fillable contém observacoes? " . (in_array('observacoes', $fillable) ? 'SIM' : 'NÃO') . "\n";
print_r($fillable);
```

## Executar

```bash
php artisan tinker
```

E cole os comandos acima um por vez para identificar onde está o problema. 