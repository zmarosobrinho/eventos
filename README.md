# Controle de Amostras TikTok

Aplicação web para administrar amostras e produtos comprados para divulgação no TikTok. O sistema controla produtos sem reembolso, produtos com meta de vendas para reembolso, vendas por data, links de divulgação, frases de efeito e status do reembolso.

## Requisitos
- Python 3.11+
- pip
- SQLite para desenvolvimento ou MySQL via `DATABASE_URL`

## Como rodar
1. Crie um ambiente virtual e instale dependências:
   ```bash
   python -m venv .venv
   source .venv/bin/activate
   pip install -r requirements.txt
   ```
2. Exporte uma `SECRET_KEY` e, se for usar MySQL, uma `DATABASE_URL` compatível com SQLAlchemy:
   ```bash
   export SECRET_KEY="sua-chave-secreta"
   export DATABASE_URL="mysql+pymysql://usuario:senha@localhost/amostras"
   ```
   Para MySQL, instale também o driver escolhido, por exemplo `pip install pymysql`.
3. Execute o servidor:
   ```bash
   flask --app app run --debug
   ```
4. Acesse em `http://localhost:5000`.

## Funcionalidades
- Cadastro e login de usuários.
- Cadastro de produto com data da compra, título, descrição, links do TikTok e memo de frases para venda.
- Marcação de produtos sem reembolso ou com quantidade mínima de vendas para reembolso.
- Registro da data de cada venda, com contagem automática até atingir a meta.
- Destaque de produtos com meta atingida e opção para marcar reembolso como feito.
- Filtros por compra, status de reembolso, meta pendente, meta atingida, reembolsados e sem reembolso.
- Página de venda por produto com descrição, links e frases de efeito cadastradas.
