# Sistema de Frases para Lives (PHP + MySQL)

Projeto pronto para hospedagem compartilhada e envio manual por FTP.

## 1) Arquivos para enviar ao servidor
Envie **todos** os arquivos e pastas abaixo para a pasta do site (ex.: `public_html`):

- `index.php`
- `login.php`
- `logout.php`
- `conexao.php`
- `seguranca.php`
- `topo.php`
- `rodape.php`
- `produtos.php`
- `categorias.php`
- `mensagens.php`
- `produto_categorias.php`
- `apresentacao.php`
- `assets/estilo.css`
- `banco.sql` (este pode ficar só no seu computador após importar)

## 2) Como importar o banco
1. Abra o phpMyAdmin da hospedagem.
2. Crie um banco de dados (ou use um já criado).
3. Clique em **Importar** e selecione o arquivo `banco.sql`.
4. Edite o arquivo `conexao.php` com os dados reais de host, banco, usuário e senha do MySQL.

## 3) Quais páginas abrir no navegador
1. Abra `https://seudominio.com/login.php` e faça login.
2. Depois use o menu:
   - `produtos.php` para cadastrar produtos
   - `categorias.php` para cadastrar categorias
   - `mensagens.php` para cadastrar frases e vincular categorias
   - `produto_categorias.php` para vincular categorias aos produtos
   - `apresentacao.php` para selecionar produto e ver frases aleatórias

## Observação importante
- Troque o usuário e senha definidos em `seguranca.php` antes de publicar.
