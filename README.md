# Backend ALSAP

API REST do Atlas Linguístico Sonoro do Amapá, migrada de Java/Spring Boot e PostgreSQL para PHP 8.1+ e MySQL. Este diretório contém a implementação PHP independente. A aplicação Java original permanece em `../src/main/java`; o frontend não foi alterado.

## Requisitos

- PHP 8.1 ou superior com extensões `pdo`, `pdo_mysql` e `json`.
- MySQL 8.0 ou superior (ou MariaDB compatível com InnoDB e `utf8mb4`).
- Apache com `mod_rewrite` ou servidor embutido do PHP.
- Composer é opcional: não há dependências de terceiros de runtime.

## Instalação

1. Clone o repositório e entre na pasta:

    ```powershell
    git clone https://github.com/afcoelho44/backend_alsap.git
    cd backend_alsap\transfer
    ```

2. Copie `.env.example` para `.env` e ajuste os dados locais do MySQL:

    ```env
    APP_ENV=development
    DB_HOST=localhost
    DB_PORT=3306
    DB_DATABASE=alsap
    DB_USERNAME=root
    DB_PASSWORD=
    ```

    O `.env` é ignorado pelo Git. Não coloque credenciais nele se for versioná-lo por engano.

3. Crie o banco e as tabelas. No XAMPP, inicie MySQL e importe `database/schema.sql` pelo phpMyAdmin ou terminal:

    ```powershell
    C:\Program Files\xampp\mysql\bin\mysql.exe -u root < database\schema.sql
    ```

    Se o servidor exigir senha, informe-a interativamente com `-p`. O esquema é derivado das entidades JPA originais; não há dados iniciais no repositório, portanto não existe seed inventado.

4. Opcionalmente gere o autoloader Composer. Não há bibliotecas externas declaradas:

    ```powershell
    composer install --no-dev
    ```

5. Inicie o servidor de desenvolvimento:

    ```powershell
    C:\Program Files\xampp\php\php.exe -S localhost:8000 -t public public\index.php
    ```

    A API estará em `http://localhost:8000`. Para Apache, configure o `DocumentRoot`/VirtualHost para a pasta `public/` e habilite `mod_rewrite` e `.htaccess`; isso impede expor os arquivos de configuração e código-fonte.

    A imagem Docker também pode ser usada com o banco MySQL/MariaDB externo:

    ```powershell
    docker build -t alsap-api .
    docker run --rm -p 8080:80 --env-file .env -e DB_HOST=host.docker.internal alsap-api
    ```

## Estrutura PHP

```text
transfer/
├── src/main/php/
│   ├── Config/       # ambiente, PDO e resposta HTTP
│   ├── Controller/   # endpoints REST
│   ├── Entity/       # serialização JSON das entidades e relações
│   ├── Mapper/       # conversão dos DTOs para campos persistidos
│   ├── Model/        # DTOs de entrada e resumo de questão
│   ├── Repository/   # queries PDO parametrizadas
│   ├── Routes/       # definição das rotas e roteador
│   ├── Service/      # regras de negócio
│   └── Utils/        # validação de tipos de DTO
├── public/           # front controller e configuração Apache
├── database/         # schema MySQL
├── docs/             # documentação completa da API
└── tests/            # verificações executáveis sem framework
```

O `.env` é carregado sem dependência adicional; PDO usa prepared statements, erros por exceção e prepares nativos. Em produção defina `APP_ENV=production` para omitir detalhes internos das respostas HTTP 500.

## Testes

Execute os testes locais independentes do banco:

```powershell
C:\Program Files\xampp\php\php.exe tests\run.php
```

Validação de todas as operações com persistência exige MySQL em execução, schema importado e `.env` configurado. Depois, valide os endpoints conforme exemplos em [docs/API.md](docs/API.md).

## Compatibilidade e diferenças

- Os 27 caminhos, capitalização, verbos, campos DTO e status de sucesso seguem os controllers Java. A API original não implementava autenticação, upload de arquivo, paginação nem ordenação; nada disso foi adicionado.
- Consultas de entidades retornam arrays/objetos JSON com os relacionamentos que Jackson expunha. Os IDs retornados pelas criações permanecem números JSON.
- Erros de registro inexistente são normalizados para HTTP 404; no Spring atual várias dessas exceções não têm handler e podem resultar em 500. Erros de validação de tipo JSON são HTTP 400.
- A exclusão de áudio também funciona quando ele está associado somente a narrativa; o service Java tenta acessar uma resposta nula nessa situação.
- O código Java original foi mantido como referência da migração, mas a execução PHP usa `public/index.php` e o schema MySQL.

## Créditos

Projeto ALSAP: Ana Fábia Coelho dos Santos, Engenharia de Software – UDESC. Repositório de origem: [afcoelho44/backend_alsap](https://github.com/afcoelho44/backend_alsap).



