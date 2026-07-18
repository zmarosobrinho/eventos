import os
from datetime import date, datetime
from functools import wraps

from flask import Flask, flash, redirect, render_template, request, session, url_for
from flask_sqlalchemy import SQLAlchemy
from werkzeug.security import check_password_hash, generate_password_hash


app = Flask(__name__)
app.config['SECRET_KEY'] = os.environ.get('SECRET_KEY', 'development-secret-key')
app.config['SQLALCHEMY_DATABASE_URI'] = os.environ.get('DATABASE_URL', 'sqlite:///amostras.db')
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False

db = SQLAlchemy(app)


class User(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.String(80), unique=True, nullable=False)
    password_hash = db.Column(db.String(255), nullable=False)
    products = db.relationship('Product', backref='owner', lazy=True, cascade='all, delete-orphan')

    def set_password(self, password: str) -> None:
        self.password_hash = generate_password_hash(password)

    def check_password(self, password: str) -> bool:
        return check_password_hash(self.password_hash, password)


class Product(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    title = db.Column(db.String(180), nullable=False)
    description = db.Column(db.Text, nullable=False)
    purchase_date = db.Column(db.Date, nullable=False)
    has_refund_goal = db.Column(db.Boolean, default=False, nullable=False)
    sales_required = db.Column(db.Integer, default=0, nullable=False)
    refunded = db.Column(db.Boolean, default=False, nullable=False)
    refunded_at = db.Column(db.Date)
    tiktok_links = db.Column(db.Text)
    sales_memo = db.Column(db.Text)
    created_by = db.Column(db.Integer, db.ForeignKey('user.id'), nullable=False)
    created_at = db.Column(db.DateTime, default=datetime.utcnow, nullable=False)
    sales = db.relationship('Sale', backref='product', lazy=True, cascade='all, delete-orphan')

    def sales_count(self) -> int:
        return len(self.sales)

    def remaining_sales(self) -> int:
        if not self.has_refund_goal:
            return 0
        return max(self.sales_required - self.sales_count(), 0)

    def goal_reached(self) -> bool:
        return self.has_refund_goal and self.sales_count() >= self.sales_required

    def status_label(self) -> str:
        if not self.has_refund_goal:
            return 'Sem reembolso'
        if self.refunded:
            return 'Reembolsado'
        if self.goal_reached():
            return 'Meta atingida'
        return 'Pendente'

    def link_list(self) -> list[str]:
        if not self.tiktok_links:
            return []
        return [link.strip() for link in self.tiktok_links.splitlines() if link.strip()]


class Sale(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    sold_on = db.Column(db.Date, nullable=False)
    notes = db.Column(db.String(255))
    product_id = db.Column(db.Integer, db.ForeignKey('product.id'), nullable=False)


def current_user():
    user_id = session.get('user_id')
    if user_id:
        return User.query.get(user_id)
    return None


def login_required(func):
    @wraps(func)
    def wrapper(*args, **kwargs):
        if not current_user():
            flash('Você precisa estar logado para realizar esta ação.', 'warning')
            return redirect(url_for('login', next=request.path))
        return func(*args, **kwargs)
    return wrapper


@app.before_request
def create_tables():
    db.create_all()


def parse_date(field_name: str) -> date:
    return date.fromisoformat(request.form[field_name])


def apply_product_form(product: Product) -> None:
    product.title = request.form['title'].strip()
    product.description = request.form['description'].strip()
    product.purchase_date = parse_date('purchase_date')
    product.has_refund_goal = request.form.get('has_refund_goal') == 'on'
    product.sales_required = int(request.form.get('sales_required') or 0) if product.has_refund_goal else 0
    product.refunded = request.form.get('refunded') == 'on' if product.has_refund_goal else False
    refunded_at_raw = request.form.get('refunded_at')
    product.refunded_at = date.fromisoformat(refunded_at_raw) if product.refunded and refunded_at_raw else None
    product.tiktok_links = request.form.get('tiktok_links', '').strip()
    product.sales_memo = request.form.get('sales_memo', '').strip()


@app.route('/')
@login_required
def index():
    sort = request.args.get('sort', 'purchase_date_desc')
    status = request.args.get('status', 'all')
    query = Product.query.filter_by(created_by=current_user().id)

    products = query.all()
    if status == 'refunded':
        products = [product for product in products if product.refunded]
    elif status == 'not_refunded':
        products = [product for product in products if product.has_refund_goal and not product.refunded]
    elif status == 'reached':
        products = [product for product in products if product.goal_reached() and not product.refunded]
    elif status == 'pending':
        products = [product for product in products if product.has_refund_goal and not product.goal_reached()]
    elif status == 'no_refund':
        products = [product for product in products if not product.has_refund_goal]

    if sort == 'purchase_date_asc':
        products.sort(key=lambda product: product.purchase_date)
    elif sort == 'status':
        products.sort(key=lambda product: (product.refunded, not product.goal_reached(), product.purchase_date), reverse=True)
    else:
        products.sort(key=lambda product: product.purchase_date, reverse=True)

    return render_template('index.html', products=products, sort=sort, status=status, user=current_user())


@app.route('/register', methods=['GET', 'POST'])
def register():
    if request.method == 'POST':
        username = request.form['username'].strip()
        password = request.form['password']
        if not username or not password:
            flash('Informe um usuário e senha.', 'danger')
            return redirect(url_for('register'))
        if User.query.filter_by(username=username).first():
            flash('Usuário já existe.', 'danger')
            return redirect(url_for('register'))
        user = User(username=username)
        user.set_password(password)
        db.session.add(user)
        db.session.commit()
        flash('Cadastro realizado com sucesso! Faça login.', 'success')
        return redirect(url_for('login'))
    return render_template('register.html', user=current_user())


@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        user = User.query.filter_by(username=request.form['username']).first()
        if user and user.check_password(request.form['password']):
            session['user_id'] = user.id
            flash('Login realizado com sucesso!', 'success')
            return redirect(request.args.get('next') or url_for('index'))
        flash('Usuário ou senha inválidos.', 'danger')
        return redirect(url_for('login'))
    return render_template('login.html', user=current_user())


@app.route('/logout')
def logout():
    session.clear()
    flash('Sessão encerrada.', 'info')
    return redirect(url_for('login'))


@app.route('/product/new', methods=['GET', 'POST'])
@login_required
def create_product():
    if request.method == 'POST':
        product = Product(created_by=current_user().id)
        apply_product_form(product)
        if not product.title or not product.description:
            flash('Preencha título e descrição.', 'danger')
            return redirect(url_for('create_product'))
        if product.has_refund_goal and product.sales_required < 1:
            flash('Informe quantas vendas são necessárias para reembolso.', 'danger')
            return redirect(url_for('create_product'))
        db.session.add(product)
        db.session.commit()
        flash('Produto cadastrado com sucesso!', 'success')
        return redirect(url_for('product_detail', product_id=product.id))
    return render_template('product_form.html', user=current_user())


@app.route('/product/<int:product_id>')
@login_required
def product_detail(product_id):
    product = Product.query.filter_by(id=product_id, created_by=current_user().id).first_or_404()
    return render_template('product_detail.html', product=product, user=current_user())


@app.route('/product/<int:product_id>/edit', methods=['GET', 'POST'])
@login_required
def edit_product(product_id):
    product = Product.query.filter_by(id=product_id, created_by=current_user().id).first_or_404()
    if request.method == 'POST':
        apply_product_form(product)
        if product.has_refund_goal and product.sales_required < 1:
            flash('Informe quantas vendas são necessárias para reembolso.', 'danger')
            return redirect(url_for('edit_product', product_id=product.id))
        db.session.commit()
        flash('Produto atualizado!', 'success')
        return redirect(url_for('product_detail', product_id=product.id))
    return render_template('product_form.html', product=product, user=current_user())


@app.route('/product/<int:product_id>/sale', methods=['POST'])
@login_required
def add_sale(product_id):
    product = Product.query.filter_by(id=product_id, created_by=current_user().id).first_or_404()
    sale = Sale(sold_on=date.fromisoformat(request.form['sold_on']), notes=request.form.get('notes', '').strip(), product=product)
    db.session.add(sale)
    db.session.commit()
    flash('Venda anotada.', 'success')
    return redirect(url_for('product_detail', product_id=product.id))


@app.route('/sale/<int:sale_id>/delete', methods=['POST'])
@login_required
def delete_sale(sale_id):
    sale = Sale.query.join(Product).filter(Sale.id == sale_id, Product.created_by == current_user().id).first_or_404()
    product_id = sale.product_id
    db.session.delete(sale)
    db.session.commit()
    flash('Venda removida.', 'info')
    return redirect(url_for('product_detail', product_id=product_id))


@app.route('/product/<int:product_id>/refund', methods=['POST'])
@login_required
def mark_refunded(product_id):
    product = Product.query.filter_by(id=product_id, created_by=current_user().id).first_or_404()
    product.refunded = True
    product.refunded_at = date.today()
    db.session.commit()
    flash('Reembolso marcado como feito.', 'success')
    return redirect(url_for('product_detail', product_id=product.id))


@app.route('/product/<int:product_id>/delete', methods=['POST'])
@login_required
def delete_product(product_id):
    product = Product.query.filter_by(id=product_id, created_by=current_user().id).first_or_404()
    db.session.delete(product)
    db.session.commit()
    flash('Produto removido.', 'info')
    return redirect(url_for('index'))


if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0')
