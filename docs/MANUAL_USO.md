# Manual de uso: registrar uma questão completa

Este passo a passo cria uma questão, uma resposta vinculada a ela, um áudio associado à resposta e uma narrativa associada ao mesmo áudio. As chamadas usam PowerShell e pressupõem que a API e o MySQL já estejam em execução e configurados. Veja [PRIMEIRA_EXECUCAO.md](PRIMEIRA_EXECUCAO.md) se ainda precisar iniciar o projeto.

## Como os registros se relacionam

A resposta precisa apontar para uma questão e um endereço. O áudio pode apontar para a resposta. A narrativa pode apontar para esse mesmo áudio. Ao final, a questão retorna suas respostas, a resposta retorna seus áudios e a narrativa também retorna os áudios associados.

A API não recebe o arquivo de áudio: `audio_url` deve conter um endereço acessível onde o arquivo já esteja hospedado.

## 1. Defina a URL da API

Com o servidor embutido do PHP:

```powershell
$base = "http://localhost:8000"
```

Com Apache no XAMPP instalado na pasta `htdocs/al` e `APP_BASE_PATH=/al/public` configurado no `.env`:

```powershell
$base = "http://localhost/al/public"
```

Use apenas uma das opções acima. Os exemplos seguintes usam `$base`.

Para abrir o painel administrativo no mesmo servidor, acesse `$base/admin/questoes.php`. Use a navegação para abrir respostas, áudios e localizações. O painel consome os mesmos endpoints documentados em [API.md](API.md), e o endpoint de áudio recebe `audio_url` (não faz upload nem hospeda arquivos).

## 2. Crie o endereço da resposta

A resposta exige um endereço existente. Ajuste nome, CEP e coordenadas para o local real:

```powershell
$enderecoId = Invoke-RestMethod -Method Post -Uri "$base/alsap/Endereco/create" -ContentType "application/json" -Body (@{
    nome = "Macapá - AP"
    cep = "68900-000"
    latitude = 0.0349
    longitude = -51.0694
} | ConvertTo-Json)

"ID do endereço: $enderecoId"
```

A API retorna o ID criado como número. Guarde-o na variável `$enderecoId` para usá-lo na próxima etapa.

## 3. Crie a questão

Informe a descrição e o tipo da questão. Use o tipo correspondente ao seu levantamento, por exemplo `QFF` ou `QSL`:

```powershell
$questaoId = Invoke-RestMethod -Method Post -Uri "$base/alsap/Questao/create" -ContentType "application/json" -Body (@{
    descricao = "Como você chama este objeto?"
    tipo = "QFF"
} | ConvertTo-Json)

"ID da questão: $questaoId"
```

## 4. Crie a resposta e vincule-a à questão

A resposta associa os IDs da questão e do endereço criados acima:

```powershell
$respostaId = Invoke-RestMethod -Method Post -Uri "$base/alsap/Respostas/create" -ContentType "application/json" -Body (@{
    endereco_id = [int]$enderecoId
    questao_id = [int]$questaoId
} | ConvertTo-Json)

"ID da resposta: $respostaId"
```

## 5. Crie o áudio da resposta

Troque `https://servidor.exemplo/audio.mp3` por uma URL real do arquivo de áudio. `tipo` é um número inteiro; o exemplo usa `0`.

```powershell
$audioId = Invoke-RestMethod -Method Post -Uri "$base/alsap/Audio/create" -ContentType "application/json" -Body (@{
    nome = "Gravacao da resposta"
    transcricao = "Exemplo de transcricao do audio"
    audio_url = "https://servidor.exemplo/audio.mp3"
    tipo = 0
    resposta_id = [int]$respostaId
} | ConvertTo-Json)

"ID do áudio: $audioId"
```

## 6. Crie a narrativa e vincule o áudio

Informe os dados do narrador e a transcrição. O campo `audio_id` associa esta narrativa ao áudio criado na etapa anterior. O título da narrativa é gerado automaticamente a partir do campo `local`.

```powershell
$narrativaId = Invoke-RestMethod -Method Post -Uri "$base/alsap/Narrativa/create" -ContentType "application/json" -Body (@{
    nome = "Nome do participante"
    idade = "35"
    sexo = "F"
    escolaridade = "Ensino medio"
    local = "Macapá - AP"
    transcricao = "Transcricao completa da narrativa."
    audio_id = [int]$audioId
} | ConvertTo-Json)

"ID da narrativa: $narrativaId"
```

## 7. Confira o resultado

Consulte a questão para ver a resposta, seu endereço e os áudios associados:

```powershell
Invoke-RestMethod -Uri "$base/alsap/Questao/$questaoId" | ConvertTo-Json -Depth 10
```

Confira também a narrativa e o áudio:

```powershell
Invoke-RestMethod -Uri "$base/alsap/Narrativa/$narrativaId" | ConvertTo-Json -Depth 10
Invoke-RestMethod -Uri "$base/alsap/Audio/$audioId"
```

A resposta da questão deve conter a resposta criada; essa resposta deve conter o endereço e o áudio. A narrativa deve listar o áudio associado.

## Se ocorrer um erro

- **Erro 404 ao criar resposta:** confira se `endereco_id` e `questao_id` são IDs existentes.
- **Erro 404 ao criar áudio:** confira se `resposta_id` existe.
- **Erro 404 ao criar narrativa:** confira se `audio_id` existe.
- **Erro de conexão com o banco:** confirme se o MySQL está ativo, se `database/schema.sql` foi importado e se as credenciais do `.env` estão corretas.
- **Áudio não toca:** confira se `audio_url` aponta para um arquivo acessível pelo navegador. A API armazena a URL, mas não envia nem hospeda o arquivo.

As rotas, formatos de resposta e operações de atualização/exclusão estão descritos em [API.md](API.md).