# Sistema de Frases para Lives (PHP + MySQL)

Sistema 100% em **PHP puro + MySQL**, sem framework, pronto para hospedagem compartilhada (ex.: **Hostgator**) com upload manual via **FTP/cPanel**.

## 1) Lista de arquivos para enviar ao servidor
Envie para a pasta do site (normalmente `public_html`):

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
- `banco.sql` (usado para importar o banco; depois pode remover do servidor)

## 2) Instalação manual na Hostgator (cPanel + phpMyAdmin)
1. Entre no **cPanel** da Hostgator.
2. Em **MySQL Databases**:
   - crie o banco de dados;
   - crie o usuário MySQL;
   - vincule usuário ao banco com **All Privileges**.
3. Abra o **phpMyAdmin** no cPanel.
4. Selecione o banco criado.
5. Clique em **Importar** e envie o arquivo `banco.sql`.
6. Faça upload dos arquivos para `public_html` (via **Gerenciador de Arquivos** do cPanel ou FTP).
7. Edite o arquivo `conexao.php` com os dados reais da Hostgator:
   - host do MySQL (geralmente `localhost`);
   - nome do banco;
   - usuário MySQL;
   - senha MySQL.
8. Edite `seguranca.php` e troque usuário/senha de acesso ao painel.
9. Acesse `https://seudominio.com/login.php`.

## 3) Páginas para usar no navegador
- `login.php` → entrar no sistema
- `produtos.php` → cadastrar produtos
- `categorias.php` → cadastrar categorias
- `mensagens.php` → cadastrar frases e vincular categorias
- `produto_categorias.php` → vincular categorias aos produtos
- `apresentacao.php` → selecionar produto e ver frases em ordem aleatória

## 4) Observações
- O sistema guarda o produto selecionado na sessão e também aceita `?produto_id=` na URL da `apresentacao.php`.
- Ao atualizar a página de apresentação, as frases são sorteadas novamente para o mesmo produto selecionado.
