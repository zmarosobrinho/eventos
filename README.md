# Sistema de Frases para Lives (PHP + MySQL)

Sistema interno (sem página pública) para cadastrar produtos, categorias e frases, e depois exibir frases aleatórias relacionadas ao produto escolhido.

## Requisitos
- PHP 8.1+
- MySQL 8+
- Servidor web (Apache/Nginx) ou servidor embutido do PHP para testes

## Estrutura de dados
- `products`: nome + descrição (`MEDIUMTEXT`)
- `categories`: categorias de produto
- `messages`: frases/mensagens
- `product_categories`: relação N:N entre produtos e categorias
- `category_messages`: relação N:N entre categorias e mensagens

O script `schema.sql` cria o banco e todas as tabelas.

## Configuração
1. Crie o banco/tabelas:
   ```bash
   mysql -u root -p < schema.sql
   ```
2. Configure acesso ao banco via variáveis de ambiente (opcional):
   ```bash
   export DB_HOST=127.0.0.1
   export DB_NAME=eventos
   export DB_USER=root
   export DB_PASS=''
   ```
3. Rode localmente:
   ```bash
   php -S localhost:8080
   ```
4. Acesse `http://localhost:8080`.

## Fluxo de uso
1. Cadastrar produtos em `products.php`.
2. Cadastrar categorias em `categories.php`.
3. Cadastrar mensagens e vincular às categorias em `messages.php`.
4. Associar categorias aos produtos em `product_categories.php`.
5. Em `live.php`, selecionar o produto para visualizar frases aleatórias.

## Tela de apresentação (`live.php`)
- Mostra **título do produto** e **descrição completa**.
- Exibe em **grid**, em ordem aleatória, todas as frases vinculadas às categorias daquele produto.
- O produto selecionado fica salvo na sessão (`$_SESSION['live_product_id']`) e também na URL (`?product_id=`), então ao atualizar a página o mesmo produto permanece selecionado e as frases são sorteadas novamente.
