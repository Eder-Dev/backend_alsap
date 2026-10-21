# Configuração e primeira execução

Este guia prepara a API ALSAP PHP em Windows com XAMPP. Execute os comandos a partir da pasta raiz do projeto, onde ficam `composer.json`, `.env.example` e `public/`.

## Pré-requisitos

- XAMPP com PHP 8.1 ou superior e MySQL/MariaDB.
- Extensões PHP `pdo`, `pdo_mysql`, `mbstring` e `json` habilitadas.
- Composer é opcional; o projeto não depende de pacotes externos para executar.

No PowerShell, confira a versão do PHP e as extensões usando os executáveis do XAMPP:

```powershell
& "C:\Program Files\xampp\php\php.exe" --version
& "C:\Program Files\xampp\php\php.exe" -m
```

Confirme que a lista inclui `mbstring`, `PDO`, `pdo_mysql` e `json`.

## 1. Configure o banco

1. Abra o XAMPP Control Panel e inicie o serviço **MySQL**.
2. Na raiz do projeto, crie o arquivo local de ambiente a partir do exemplo:

   ```powershell
   Copy-Item .env.example .env
   ```

3. Edite `.env` e informe os dados da sua instalação. Exemplo para XAMPP com usuário `root` sem senha:

   ```env
   APP_ENV=development
   APP_BASE_PATH=
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=alsap
   DB_USERNAME=root
   DB_PASSWORD=
   ```

   Se o seu MySQL usa outra senha, preencha `DB_PASSWORD`. O arquivo `.env` é local e não deve ser enviado ao repositório.

   Se estiver acessando a aplicação pelo Apache do XAMPP usando uma subpasta, configure `APP_BASE_PATH` com o prefixo que aparece na URL. Por exemplo, para uma URL como `http://localhost/al/public/alsap/Endereco/all`, defina:

   ```env
   APP_BASE_PATH=/al/public
   ```

   Assim, o prefixo `/al/public` é removido antes da busca da rota e a API recebe `/alsap/Endereco/all`. Deixe o valor vazio ao usar o servidor embutido do PHP ou quando o Apache já aponta diretamente para `public/`.

4. Crie as tabelas importando `database/schema.sql`. Uma opção visual é abrir `http://localhost/phpmyadmin`, selecionar **Importar**, escolher esse arquivo e executar. O script cria o banco `alsap` e as tabelas necessárias. Se preferir outro método, use um cliente MySQL para executar o conteúdo de `database/schema.sql`.

## 2. Inicie a API

Na raiz do projeto, execute:

```powershell
& "C:\Program Files\xampp\php\php.exe" -S localhost:8000 -t public public\index.php
```

Mantenha esse terminal aberto enquanto usar a API. A URL base será `http://localhost:8000`. O servidor embutido do PHP é suficiente para desenvolvimento; não é necessário iniciar o Apache do XAMPP para esse modo.

## 3. Faça uma verificação inicial

Com o servidor ativo e o banco configurado, abra `http://localhost:8000/alsap/Endereco/all` no navegador ou execute em outro terminal:

```powershell
Invoke-RestMethod http://localhost:8000/alsap/Endereco/all
```

Se o banco estiver vazio, a resposta esperada é uma lista vazia. Para uma verificação que não exige conexão com o banco, abra outro terminal na raiz do projeto e execute:

```powershell
& "C:\Program Files\xampp\php\php.exe" tests\run.php
```

Os exemplos de requisição e a lista de rotas estão em [API.md](API.md).

## Composer (opcional)

Não é necessário instalar dependências para iniciar a API. Se quiser gerar o autoloader Composer, execute na raiz do projeto:

```powershell
composer install
```

A aplicação também possui carregamento automático próprio e continua funcionando sem a pasta `vendor/`.

## Problemas comuns

- **A conexão com o banco falha:** confirme que o MySQL está iniciado, que os valores de `.env` correspondem à sua instalação e que o schema foi importado no banco `alsap`.
- **Erro informando configuração incompleta:** confira se `DB_DATABASE` e `DB_USERNAME` estão preenchidos em `.env`.
- **Extensão ausente ou driver MySQL indisponível:** habilite `pdo_mysql` e `mbstring` na configuração do PHP usado pelo XAMPP e confira novamente com `php -m`.
- **A porta 8000 já está ocupada:** inicie o servidor em outra porta, por exemplo `-S localhost:8001`, e acesse `http://localhost:8001`.
