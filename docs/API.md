# Documentação da API ALSAP

Base URL local: `http://localhost:8000`. Todos os caminhos preservam o prefixo e a capitalização do backend Spring. São 27 endpoints ao todo.

## Convenções

- Requisições com body enviam `Content-Type: application/json` e um objeto JSON.
- Respostas com conteúdo usam `Content-Type: application/json; charset=utf-8`.
- IDs são inteiros no caminho. Não há autenticação, parâmetros de query, paginação ou ordenação no original.
- `200` é usado nas consultas e em criações de Áudio, Endereço e Questão; `201` em criações de Narrativa e Resposta; `204` nas exclusões.
- Erros PHP retornam `{ "timestamp": "...", "status": 404, "error": "Not Found", "message": "...", "path": "..." }`. A configuração Spring original não define um formato de erro próprio.
- Os campos de entrada omitidos em `PUT` são tratados como `null`, conforme os mappers Java. `NarrativaRequest.resumo` é aceito e ignorado pelo original.

## Áudio

### `GET /alsap/Audio/all`
Descrição: lista todos os áudios. Headers: nenhum obrigatório. Parâmetros: nenhum. Body: nenhum. Sucesso `200`: array de objetos Áudio. Erro: objeto JSON HTTP `500` se houver falha de banco.

### `GET /alsap/Audio/{id}`
Descrição: consulta um áudio. Headers: nenhum obrigatório. Parâmetros: `id` no caminho. Body: nenhum. Sucesso `200`: objeto Áudio. Erro: `404` para ID inexistente.

### `POST /alsap/Audio/create`
Descrição: cria áudio e associa opcionalmente resposta/narrativa. Headers: `Content-Type: application/json`. Parâmetros: nenhum. Body: `{"nome":"...","transcricao":"...","audio_url":"...","tipo":0,"resposta_id":1,"narrativa_id":null}`. Sucesso `200`: ID numérico. Erro: `400` para JSON/tipo inválido; `404` para associação inexistente.

### `PUT /alsap/Audio/update/{id}`
Descrição: atualiza os campos do áudio e, opcionalmente, associação. Headers: `Content-Type: application/json`. Parâmetros: `id` no caminho. Body: mesmo formato da criação. Sucesso `200`: objeto Áudio. Erro: `400` para tipo inválido; `404` para áudio ou associação inexistente.

### `DELETE /alsap/Audio/delete/{id}`
Descrição: exclui um áudio. Headers: nenhum obrigatório. Parâmetros: `id` no caminho. Body: nenhum. Sucesso `204`: sem body. Erro: `404` se não existir.

Objeto Áudio: `{"id":1,"nome":"...","transcricao":"...","audio_url":"...","tipo":0}`. As referências-pai são omitidas como no `@JsonBackReference`.

## Endereço

### `GET /alsap/Endereco/all`
Descrição: lista endereços. Headers: nenhum obrigatório. Parâmetros/body: nenhum. Sucesso `200`: array de Endereço. Erro: `500` em falha de banco.

### `GET /alsap/Endereco/{id}`
Descrição: consulta endereço. Headers: nenhum obrigatório. Parâmetros: `id` no caminho. Body: nenhum. Sucesso `200`: objeto Endereço. Erro: `404` se não existir.

### `POST /alsap/Endereco/create`
Descrição: cria endereço. Headers: `Content-Type: application/json`. Parâmetros: nenhum. Body: `{"nome":"...","cep":"...","latitude":0.0,"longitude":0.0}`. Sucesso `200`: ID numérico. Erro: `400` para tipo inválido; `500` para falha de banco.

### `PUT /alsap/Endereco/update/{id}`
Descrição: atualiza endereço. Headers: `Content-Type: application/json`. Parâmetros: `id` no caminho. Body: mesmo formato da criação. Sucesso `200`: objeto Endereço. Erro: `404` se não existir; `400` para tipo inválido.

### `DELETE /alsap/Endereco/delete/{id}`
Descrição: exclui endereço. Headers: nenhum obrigatório. Parâmetros: `id` no caminho. Body: nenhum. Sucesso `204`: sem body. Erro: `404` se inexistente ou `500` se houver respostas vinculadas.

Objeto Endereço: `{"id":1,"nome":"...","cep":"...","latitude":0.0,"longitude":0.0}`.

## Narrativa

### `GET /alsap/Narrativa/all`
Descrição: lista narrativas e seus áudios. Headers/parâmetros/body: nenhum. Sucesso `200`: array de Narrativa. Erro: `500` em falha de banco.

### `GET /alsap/Narrativa/{id}`
Descrição: consulta narrativa e áudios. Headers: nenhum obrigatório. Parâmetros: `id` no caminho. Body: nenhum. Sucesso `200`: objeto Narrativa. Erro: `404` se não existir.

### `POST /alsap/Narrativa/create`
Descrição: cria narrativa e gera `titulo` automaticamente como `Narrativa - CIDADE - NN`; associação de áudio é opcional. Headers: `Content-Type: application/json`. Body: `{"nome":"...","idade":"...","sexo":"...","escolaridade":"...","local":"Macapá - AP","transcricao":"...","resumo":"...","audio_id":1}`. Sucesso `201`: ID numérico. Erro: `400` para tipo inválido; `404` se áudio informado não existir.

### `PUT /alsap/Narrativa/update/{id}`
Descrição: atualiza dados e pode associar áudio. Headers: `Content-Type: application/json`. Parâmetros: `id` no caminho. Body: mesmo formato da criação. Sucesso `200`: objeto Narrativa. Erro: `400` para tipo inválido; `404` para narrativa/áudio inexistente.

### `DELETE /alsap/Narrativa/delete/{id}`
Descrição: exclui narrativa e áudios associados. Headers: nenhum obrigatório. Parâmetros: `id` no caminho. Body: nenhum. Sucesso `204`: sem body. Erro: `404` se inexistente.

Objeto Narrativa: `{"id":1,"nome":"...","idade":"...","sexo":"...","escolaridade":"...","local":"...","transcricao":"...","titulo":"...","audios":[]}`.

## Questão

### `GET /alsap/Questao/all`
Descrição: lista questões e respostas associadas. Headers/parâmetros/body: nenhum. Sucesso `200`: array de Questão. Erro: `500` em falha de banco.

### `GET /alsap/Questao/{id}`
Descrição: consulta questão e respostas. Headers: nenhum obrigatório. Parâmetros: `id` no caminho. Body: nenhum. Sucesso `200`: objeto Questão. Erro: `404` se não existir.

### `GET /alsap/Questao/tipo/qff`
Descrição: lista resumo de questões do tipo `QFF`. Headers/parâmetros/body: nenhum. Sucesso `200`: array `[{"id":1,"descricao":"..."}]`. Erro: `500` em falha de banco.

### `GET /alsap/Questao/tipo/qsl`
Descrição: lista resumo de questões do tipo `QSL`. Headers/parâmetros/body: nenhum. Sucesso `200`: array `[{"id":1,"descricao":"..."}]`. Erro: `500` em falha de banco.

### `POST /alsap/Questao/create`
Descrição: cria questão. Headers: `Content-Type: application/json`. Body: `{"descricao":"...","tipo":"QFF"}`. Sucesso `200`: ID numérico. Erro: `400` para tipo inválido; `500` para falha de banco.

### `PUT /alsap/Questao/update/{id}`
Descrição: atualiza questão. Headers: `Content-Type: application/json`. Parâmetros: `id` no caminho. Body: mesmo formato da criação. Sucesso `200`: objeto Questão. Erro: `404` se inexistente; `400` para tipo inválido.

### `DELETE /alsap/Questao/delete/{id}`
Descrição: exclui questão e seus vínculos. Headers: nenhum obrigatório. Parâmetros: `id` no caminho. Body: nenhum. Sucesso `204`: sem body. Erro: `404` se inexistente.

Objeto Questão: `{"id":1,"descricao":"...","tipo":"QFF","respostas":[]}`. Resposta inclui `endereco` e `audios`; não inclui a referência inversa a questões.

## Resposta

### `GET /alsap/Respostas/all`
Descrição: lista respostas, endereços e áudios. Headers/parâmetros/body: nenhum. Sucesso `200`: array de Resposta. Erro: `500` em falha de banco.

### `GET /alsap/Respostas/{id}`
Descrição: consulta resposta, endereço e áudios. Headers: nenhum obrigatório. Parâmetros: `id` no caminho. Body: nenhum. Sucesso `200`: objeto Resposta. Erro: `404` se não existir.

### `POST /alsap/Respostas/create`
Descrição: cria resposta associada a endereço e questão. Headers: `Content-Type: application/json`. Body: `{"endereco_id":1,"questao_id":1}`. Sucesso `201`: ID numérico. Erro: `400` para IDs inválidos; `404` para entidades referenciadas inexistentes.

### `PUT /alsap/Respostas/update/{id}`
Descrição: atualiza endereço e substitui questões associadas pela questão informada. Headers: `Content-Type: application/json`. Parâmetros: `id` no caminho. Body: mesmo formato da criação. Sucesso `200`: objeto Resposta. Erro: `400` para IDs inválidos; `404` para entidade inexistente.

### `DELETE /alsap/Respostas/delete/{id}`
Descrição: exclui resposta, áudios e vínculos de questões. Headers: nenhum obrigatório. Parâmetros: `id` no caminho. Body: nenhum. Sucesso `204`: sem body. Erro: `404` se inexistente.

Objeto Resposta: `{"id":1,"endereco":{"id":1,"nome":"...","cep":"...","latitude":0.0,"longitude":0.0},"audios":[]}`.