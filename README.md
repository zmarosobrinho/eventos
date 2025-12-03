# WikiEventos

Aplicação simples no estilo wiki para cadastro colaborativo de eventos. Qualquer pessoa pode consultar os eventos, mas é necessário estar logado para criar, editar ou excluir.

## Requisitos
- Python 3.11+
- pip

## Como rodar
1. Crie um ambiente virtual e instale dependências:
   ```bash
   python -m venv .venv
   source .venv/bin/activate
   pip install -r requirements.txt
   ```
2. Exporte uma `SECRET_KEY` se desejar customizar a chave da sessão:
   ```bash
   export SECRET_KEY="sua-chave-secreta"
   ```
3. Execute o servidor:
   ```bash
   flask --app app run --debug
   ```
4. Acesse em `http://localhost:5000`.

## Funcionalidades
- Cadastro e login de usuários.
- Criação de eventos com cidade, data/hora inicial, data/hora final opcional para eventos de vários dias, descrição e informação de valor.
- Indicação se o evento é gratuito ou pago.
- Edição e exclusão colaborativa por qualquer usuário autenticado.
- Listagem pública dos eventos.
